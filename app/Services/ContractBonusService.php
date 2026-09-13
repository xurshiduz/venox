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
        $this->loadDebtRelations($client);
        $usdCheckouts = $client->checkouts->where('currency_type', 1);
        $allCheckoutIds = $client->checkouts->pluck('id')->all();
        $debt = (int) $client->currency_type === 1 ? (float) $client->balance : 0.0;

        foreach ($usdCheckouts as $checkout) {
            $sale = (float) $checkout->alldetails->sum('total_price');
            $linkedPayments = (float) $client->cashReceipts
                ->where('checkout_id', $checkout->id)
                ->where('currency_type', 1)
                ->sum('price');
            $debt += $sale - $linkedPayments;
        }

        $generalPayments = (float) $client->cashReceipts
            ->where('currency_type', 1)
            ->filter(fn ($receipt) => is_null($receipt->checkout_id) || ! in_array($receipt->checkout_id, $allCheckoutIds))
            ->sum('price');
        $returns = (float) $client->checkins
            ->where('type_id', 4)
            ->where('currency_type', 1)
            ->sum(fn ($checkin) => (float) $checkin->details->sum('total_price'));

        $debt -= $generalPayments + $returns + $this->debtOffsetUsd($client, 1);

        return max(0, $debt);
    }

    /** The same UZS debt formula used by checkout_debts_report. */
    public function debtUzs(Client $client): float
    {
        $this->loadDebtRelations($client);
        $allCheckoutIds = $client->checkouts->pluck('id')->all();
        $debt = (float) $client->balance;
        if ((int) $client->currency_type === 1) {
            $debt *= (float) $client->currency_type_price;
        }

        foreach ($client->checkouts as $checkout) {
            $sale = (float) $checkout->alldetails->sum('total_price');
            $linkedPayments = (float) $client->cashReceipts
                ->where('checkout_id', $checkout->id)
                ->where('currency_type', $checkout->currency_type)
                ->sum('price');
            $balance = $sale - $linkedPayments;
            if ((int) $checkout->currency_type === 1) {
                $balance *= (float) $checkout->currency_type_price;
            }
            $debt += $balance;
        }

        foreach ($client->cashReceipts->filter(fn ($receipt) => is_null($receipt->checkout_id) || ! in_array($receipt->checkout_id, $allCheckoutIds)) as $receipt) {
            $amount = (float) $receipt->price;
            if ((int) $receipt->currency_type === 1) {
                $amount *= (float) $receipt->currency_type_price;
            }
            $debt -= $amount;
        }

        foreach ($client->checkins->where('type_id', 4) as $checkin) {
            $amount = (float) $checkin->details->sum('total_price');
            if ((int) $checkin->currency_type === 1) {
                $amount *= (float) $checkin->currency_type_price;
            }
            $debt -= $amount;
        }

        $debt -= $this->debtOffsetUzs($client);

        return max(0, $debt);
    }

    public function debtOffsetUsd(Client $client, int $currencyType): float
    {
        $this->loadDebtRelations($client);
        $checkouts = $client->checkouts->keyBy('id');

        return (float) $client->contractBonusTransactions
            ->where('status', true)
            ->where('type', 'debt_offset')
            ->where('direction', 'debit')
            ->sum(function (ContractBonusTransaction $transaction) use ($checkouts, $currencyType) {
                return collect(data_get($transaction->meta, 'debt_allocations', []))
                    ->sum(function (array $allocation) use ($checkouts, $currencyType) {
                        $checkout = $checkouts->get((int) ($allocation['checkout_id'] ?? 0));

                        return $checkout && (int) $checkout->currency_type === $currencyType
                            ? (float) ($allocation['amount_usd'] ?? 0)
                            : 0.0;
                    });
            });
    }

    public function debtOffsetUzs(Client $client): float
    {
        $this->loadDebtRelations($client);
        $checkouts = $client->checkouts->keyBy('id');

        return (float) $client->contractBonusTransactions
            ->where('status', true)
            ->where('type', 'debt_offset')
            ->where('direction', 'debit')
            ->sum(function (ContractBonusTransaction $transaction) use ($checkouts) {
                return collect(data_get($transaction->meta, 'debt_allocations', []))
                    ->sum(function (array $allocation) use ($checkouts, $transaction) {
                        $checkout = $checkouts->get((int) ($allocation['checkout_id'] ?? 0));
                        if (! $checkout) {
                            return 0.0;
                        }

                        $rate = (float) $checkout->currency_type_price;
                        if ($rate <= 1) {
                            $rate = Currency::usdRateForDate($transaction->transaction_date);
                        }

                        return static::allocationAmountUzs(
                            (float) ($allocation['amount_native'] ?? 0),
                            (int) $checkout->currency_type,
                            $rate
                        );
                    });
            });
    }

    public static function allocationAmountUzs(float $amountNative, int $currencyType, float $usdRate): float
    {
        return $currencyType === 1 ? $amountNative * $usdRate : $amountNative;
    }

    private function loadDebtRelations(Client $client): void
    {
        $client->loadMissing([
            'checkouts.alldetails',
            'checkins.details',
            'cashReceipts' => fn ($query) => $query->where('status', 1),
            'contractBonusTransactions',
        ]);
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
