<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ContractBonusTransaction;
use App\Services\ContractBonusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContractBonusController extends Controller
{
    public function index(Request $request, ContractBonusService $service)
    {
        $service->syncAccruals();
        $search = trim((string) $request->input('search'));
        $clients = Client::query()
            ->whereHas('contractBonusTransactions', fn ($query) => $query->where('status', true))
            ->when($search, fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->withSum(['contractBonusTransactions as contract_credit_usd' => fn ($query) => $query->where('status', true)->where('direction', 'credit')], 'amount_usd')
            ->withSum(['contractBonusTransactions as contract_debit_usd' => fn ($query) => $query->where('status', true)->where('direction', 'debit')], 'amount_usd')
            ->orderBy('name')
            ->paginate(30)
            ->appends($request->query());

        foreach ($clients as $client) {
            $client->contract_balance_usd = round((float) $client->contract_credit_usd - (float) $client->contract_debit_usd, 2);
            $client->debt_usd = round($service->debtUsd($client), 2);
        }

        return view('backend.contract_bonuses.index', compact('clients', 'search'));
    }

    public function show(Client $client, ContractBonusService $service)
    {
        $service->syncAccruals();
        $transactions = $client->contractBonusTransactions()
            ->with(['user', 'receipt', 'checkout'])
            ->where('status', true)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(30);
        $balance = $service->balance($client);
        $debtUsd = $service->debtUsd($client);

        return view('backend.contract_bonuses.show', compact('client', 'transactions', 'balance', 'debtUsd'));
    }

    public function redeem(Request $request, Client $client, ContractBonusService $service)
    {
        abort_unless(Auth::user()->hasAnyRole('admin|cashier'), 403);
        $validated = $request->validate([
            'type' => ['required', 'in:gift,cash,debt_offset'],
            'amount_usd' => ['required', 'numeric', 'gt:0'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $service->redeem($client, $validated['type'], (float) $validated['amount_usd'], $validated['note'] ?? null, Auth::id());

        return back()->with('success', 'Shartnoma bonusi muvaffaqiyatli ishlatildi.');
    }
}
