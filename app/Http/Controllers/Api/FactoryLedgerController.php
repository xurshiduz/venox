<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CashExpenditure;
use App\Models\CashExpenditureType;
use App\Models\CashReceiptType;
use App\Models\Client;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class FactoryLedgerController extends Controller
{
    public function receipt(Request $request)
    {
        $validated = $request->validate([
            'source_code' => ['required', 'uuid'],
        ]);

        $response = Http::timeout(15)->get(
            rtrim(config('services.lidaz_factory.url'), '/')
                . '/api/venox-ledger/receipt/' . $validated['source_code']
        );

        if (! $response->successful()) {
            abort(422, 'Factory receipt could not be verified.');
        }

        $factory = $response->json();
        validator($factory, [
            'source' => ['required', 'in:lidaz_factory'],
            'source_type' => ['required', 'in:cash_receipt'],
            'source_id' => ['required', 'integer', 'min:1'],
            'source_code' => ['required', 'uuid', 'in:' . $validated['source_code']],
            'date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'payment_type_name' => ['nullable', 'string'],
            'comment' => ['nullable', 'string'],
        ])->validate();

        $supplier = Client::query()
            ->where('name', config('services.lidaz_factory.supplier_name', 'LIDAZ MCHJ'))
            ->whereNotNull('is_supplier')
            ->firstOrFail();
        $expenseType = CashExpenditureType::query()
            ->where('name', config('services.lidaz_factory.expense_type_name', 'Оплата поставщику'))
            ->firstOrFail();
        $paymentType = CashReceiptType::query()
            ->when($factory['payment_type_name'] ?? null, function ($query, $name) {
                $query->where(function ($query) use ($name) {
                    $query->where('name_ru', $name)->orWhere('name_uz', $name);
                });
            })
            ->first() ?: CashReceiptType::findOrFail(config('services.lidaz_factory.default_payment_type_id', 1));
        $date = Carbon::parse($factory['date'])->format('Y-m-d');
        $hasSourceColumns = Schema::hasColumn('cash_expenditures', 'source_system');

        $expense = DB::transaction(function () use ($factory, $supplier, $expenseType, $paymentType, $date, $hasSourceColumns) {
            $sourceMarker = '[LIDAZ#' . $factory['source_id'] . ']';
            $expense = null;

            if ($hasSourceColumns) {
                $expense = CashExpenditure::query()
                    ->where('source_system', 'lidaz_factory')
                    ->where('source_type', 'cash_receipt')
                    ->where('source_id', $factory['source_id'])
                    ->lockForUpdate()
                    ->first();
            }

            $expense = $expense ?: CashExpenditure::query()
                ->where('comment', 'like', '%' . $sourceMarker . '%')
                ->lockForUpdate()
                ->first();

            // Adopt the existing manual import instead of duplicating it.
            if (! $expense) {
                $expense = CashExpenditure::query()
                    ->when($hasSourceColumns, fn ($query) => $query->whereNull('source_system'))
                    ->where('supplier_id', $supplier->id)
                    ->where('cash_expenditure_types', $expenseType->id)
                    ->whereDate('date', $date)
                    ->where('price', $factory['amount'])
                    ->lockForUpdate()
                    ->oldest('id')
                    ->first();
            }

            $comment = trim(($factory['comment'] ?: $date . ' №' . $factory['source_id']) . ' ' . $sourceMarker);
            $data = [
                'date' => $date,
                'cash_expenditure_types' => $expenseType->id,
                'cash_receipt_type_id' => $paymentType->id,
                'price' => (float) $factory['amount'],
                'comment' => $comment,
                'supplier_id' => $supplier->id,
            ];

            if ($hasSourceColumns) {
                $data += [
                    'source_system' => 'lidaz_factory',
                    'source_type' => 'cash_receipt',
                    'source_id' => $factory['source_id'],
                    'source_code' => $factory['source_code'],
                ];
            }

            if ($expense) {
                $expense->update($data);
                return $expense;
            }

            $user = User::findOrFail(config('services.lidaz_factory.user_id', 1));
            return CashExpenditure::create($data + [
                'code' => (string) \Illuminate\Support\Str::uuid(),
                'user_id' => $user->id,
                'store_id' => $user->store_id,
            ]);
        });

        return response()->json([
            'success' => true,
            'cash_expenditure_id' => $expense->id,
        ]);
    }
}
