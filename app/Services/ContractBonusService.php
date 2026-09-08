<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ContractBonusTransaction;
use App\Models\Currency;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ContractBonusService
{
    public function syncAccruals(): void
    {
        $rows = app(AccountingCashReportService::class)->rows([
            'from' => '2000-01-01',
            'to' => now()->toDateString(),
            'scheme' => '',
            'product_id' => null,
        ]);

        $activeReceiptIds = [];
        foreach ($rows as $row) {
            $amount = round((float) ($row['contract_bonus_usd'] ?? 0), 4);
            $receiptId = (int) ($row['receipt_id'] ?? 0);
            if ($receiptId <= 0 || $amount <= 0 || empty($row['client_id'])) {
                continue;
            }

            $activeReceiptIds[] = $receiptId;
            ContractBonusTransaction::updateOrCreate(
                ['cash_receipt_id' => $receiptId, 'type' => 'accrual'],
                [
                    'code' => optional(ContractBonusTransaction::where('cash_receipt_id', $receiptId)->where('type', 'accrual')->first())->code ?: (string) Str::uuid(),
                    'client_id' => $row['client_id'],
                    'checkout_id' => $row['checkout_id'] ?? null,
                    'transaction_date' => $row['date'],
                    'direction' => 'credit',
                    'amount_usd' => $amount,
                    'note' => 'Shartnoma foizi bo‘yicha avtomatik jamg‘arma',
                    'meta' => ['checkout_code' => $row['checkout_code'] ?? null],
                    'status' => true,
                ]
            );
        }

        ContractBonusTransaction::where('type', 'accrual')
            ->when($activeReceiptIds, fn ($query) => $query->whereNotIn('cash_receipt_id', array_unique($activeReceiptIds)))
            ->when(! $activeReceiptIds, fn ($query) => $query)
            ->update(['status' => false]);
    }

    public function balance(Client $client): float
    {
        $credit = (float) $client->contractBonusTransactions()->where('status', true)->where('direction', 'credit')->sum('amount_usd');
        $debit = (float) $client->contractBonusTransactions()->where('status', true)->where('direction', 'debit')->sum('amount_usd');

        return round($credit - $debit, 4);
    }

    public function debtUsd(Client $client): float
    {
        return (float) $client->activeDebts()->get()->sum(function ($checkout) {
            return Currency::documentAmountToUsd(
                (float) $checkout->total_price_debt,
                (int) $checkout->currency_type,
                (float) $checkout->currency_type_price,
                $checkout->date
            );
        });
    }

    public function redeem(Client $client, string $type, float $amountUsd, ?string $note, int $userId): ContractBonusTransaction
    {
        return DB::transaction(function () use ($client, $type, $amountUsd, $note, $userId) {
            $client = Client::whereKey($client->id)->lockForUpdate()->firstOrFail();
            $available = $this->balance($client);
            if ($amountUsd <= 0 || $amountUsd > $available + 0.0001) {
                throw ValidationException::withMessages(['amount_usd' => 'Summa mavjud Shartnoma bonusidan oshmasligi kerak.']);
            }

            $allocations = [];
            if ($type === 'debt_offset') {
                $remaining = $amountUsd;
                $debts = $client->activeDebts()->orderBy('date')->orderBy('id')->lockForUpdate()->get();
                foreach ($debts as $checkout) {
                    if ($remaining <= 0.0001) break;
                    $rate = ((float) $checkout->currency_type_price > 1)
                        ? (float) $checkout->currency_type_price
                        : Currency::usdRateForDate(now());
                    $debtUsd = Currency::documentAmountToUsd((float) $checkout->total_price_debt, (int) $checkout->currency_type, $rate, $checkout->date);
                    $usedUsd = min($remaining, $debtUsd);
                    $usedNative = (int) $checkout->currency_type === 1 ? $usedUsd : $usedUsd * $rate;
                    $checkout->total_price_payme = (float) $checkout->total_price_payme + $usedNative;
                    $checkout->total_price_debt = max(0, (float) $checkout->total_price_debt - $usedNative);
                    $checkout->save();
                    $allocations[] = ['checkout_id' => $checkout->id, 'checkout_code' => $checkout->code, 'amount_usd' => round($usedUsd, 4), 'amount_native' => round($usedNative, 4)];
                    $remaining -= $usedUsd;
                }
                if ($remaining > 0.0001) {
                    throw ValidationException::withMessages(['amount_usd' => 'Mijoz qarzi kiritilgan summadan kam.']);
                }
            }

            return ContractBonusTransaction::create([
                'code' => (string) Str::uuid(),
                'client_id' => $client->id,
                'user_id' => $userId,
                'transaction_date' => now()->toDateString(),
                'type' => $type,
                'direction' => 'debit',
                'amount_usd' => round($amountUsd, 4),
                'note' => $note,
                'meta' => $allocations ? ['debt_allocations' => $allocations] : null,
                'status' => true,
            ]);
        });
    }
}
