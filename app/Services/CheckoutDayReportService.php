<?php

namespace App\Services;

use App\Exports\CheckoutMonthExport;
use App\Models\CheckinDetail;
use App\Models\Client;
use App\Models\Currency;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CheckoutDayReportService
{
    public function __construct(
        private ApprovedProductPriceService $approvedPriceService,
        private AccountingCashReportService $accountingService
    ) {
    }

    public function rows(Collection $checkouts, $paymentType = 'all'): Collection
    {
        if ($checkouts->isEmpty()) {
            return collect();
        }

        $checkouts->loadMissing([
            'checkoutDetails.prodid.unitid',
            'checkoutDetails.warehouseid',
            'supid',
            'managerid',
            'payments' => fn ($query) => $query->where('status', 1)->with('tname'),
            'returns',
        ]);

        $snapshots = $this->debtSnapshots($checkouts);
        $checkinPrices = $this->checkinPrices($checkouts);
        $reportUsdRate = $this->approvedPriceService->usdRate();
        $rows = collect();

        foreach ($checkouts->sortBy(fn ($checkout) => $this->sortKey($checkout)) as $checkout) {
            $payments = $checkout->payments;
            if ($paymentType !== 'all') {
                $payments = $payments->where('cash_receipt_type', (int) $paymentType);
            }
            $paymentName = optional(optional($payments->first())->tname)->name;
            if ($paymentType === 'all' && $checkout->payments->count() > 1 && $paymentName) {
                $paymentName .= ' +';
            }

            foreach ($checkout->checkoutDetails->where('qty', '>', 0) as $detail) {
                $qty = (float) $detail->qty;
                $actualUnitPriceUsd = CheckoutMonthExport::checkoutDetailUnitPriceUsd($detail, $checkout);
                $factoryUnitPriceUsd = $this->latestFactoryPriceUsd(
                    $detail,
                    $checkout,
                    $checkinPrices->get($detail->product_id, collect())
                );
                $approvedPrices = $this->approvedPriceService->pricesFor((string) optional($detail->prodid)->name);
                $resolved = CheckoutMonthExport::resolveReportPricesUzs(
                    $approvedPrices,
                    $actualUnitPriceUsd,
                    $factoryUnitPriceUsd,
                    $reportUsdRate
                );

                $rows->push([
                    'checkout_id' => (int) $checkout->id,
                    'product' => optional($detail->prodid)->name ?? 'Noma’lum tovar',
                    'unit' => optional(optional($detail->prodid)->unitid)->name ?? '',
                    'qty' => $qty,
                    'factory_price_usd' => (float) $resolved['factory_uzs'] / $reportUsdRate,
                    'venox_price_usd' => (float) $resolved['sale_uzs'] / $reportUsdRate,
                    'actual_total_usd' => $actualUnitPriceUsd === null ? null : $qty * $actualUnitPriceUsd,
                    'client' => optional($checkout->supid)->name,
                    'debt_before_usd' => (float) ($snapshots[$checkout->id]['before'] ?? 0),
                    'debt_after_usd' => (float) ($snapshots[$checkout->id]['after'] ?? 0),
                    'warehouse' => optional($detail->warehouseid)->num_code,
                    'manager' => optional($checkout->managerid)->name,
                    'payment_type' => $paymentName,
                    'date' => Carbon::parse($checkout->date ?: $checkout->created_at)->format('Y-m-d'),
                    'invoice' => $checkout->number_work ?: 'Черновик ID'.$checkout->id,
                ]);
            }
        }

        return $rows;
    }

    public static function debtAfterSale(float $oldDebt, float $sale, float $payments, float $returns): float
    {
        return max(0, $oldDebt + $sale - $payments - $returns);
    }

    private function debtSnapshots(Collection $selectedCheckouts): array
    {
        $selectedIds = $selectedCheckouts->pluck('id')->map(fn ($id) => (int) $id)->flip();
        $clients = Client::with([
            'checkouts' => fn ($query) => $query
                ->where('type_id', 1)
                ->where('status', 1)
                ->with([
                    'checkoutDetails',
                    'returns',
                    'payments' => fn ($paymentQuery) => $paymentQuery->where('status', 1),
                ]),
            'cashReceipts' => fn ($query) => $query->where('status', 1),
            'checkins' => fn ($query) => $query->where('type_id', 4)->where('status', 1)->with('details'),
            'contractBonusTransactions' => fn ($query) => $query
                ->where('status', true)
                ->where('type', 'debt_offset')
                ->where('direction', 'debit'),
        ])->whereIn('id', $selectedCheckouts->pluck('client_id')->filter()->unique())->get();

        $snapshots = [];
        foreach ($clients as $client) {
            $runningDebt = $this->amountToUsd(
                (float) $client->balance,
                (int) $client->currency_type,
                (float) $client->currency_type_price,
                $client->created_at
            );
            $events = collect();

            foreach ($client->checkouts as $checkout) {
                $netSale = $this->checkoutTotalUsd($checkout);
                $events->push([
                    'key' => $this->sortKey($checkout, 10),
                    'kind' => 'sale',
                    'checkout' => $checkout,
                    'amount' => $netSale,
                ]);
            }
            foreach ($client->cashReceipts as $receipt) {
                $linkedCheckout = $client->checkouts->firstWhere('id', $receipt->checkout_id);
                $paymentKey = $this->sortKey($receipt, 20);
                if ($linkedCheckout
                    && Carbon::parse($receipt->date ?: $receipt->created_at)
                        ->lte(Carbon::parse($linkedCheckout->date ?: $linkedCheckout->created_at))) {
                    $paymentKey = $this->sortKey($linkedCheckout, 20).'-'.str_pad((string) $receipt->id, 12, '0', STR_PAD_LEFT);
                }
                $events->push([
                    'key' => $paymentKey,
                    'kind' => 'payment',
                    'amount' => -$this->paymentUsd($receipt, $linkedCheckout),
                ]);
            }
            foreach ($client->checkins as $checkin) {
                $events->push([
                    'key' => $this->sortKey($checkin, 30),
                    'kind' => 'return',
                    'amount' => -$this->amountToUsd(
                        (float) $checkin->details->sum('total_price'),
                        (int) $checkin->currency_type,
                        (float) $checkin->currency_type_price,
                        $checkin->date ?: $checkin->created_at
                    ),
                ]);
            }
            foreach ($client->contractBonusTransactions as $transaction) {
                $events->push([
                    'key' => $this->sortKey($transaction, 40, 'transaction_date'),
                    'kind' => 'bonus',
                    'amount' => -(float) $transaction->amount_usd,
                ]);
            }

            foreach ($events->sortBy('key')->values() as $event) {
                if ($event['kind'] === 'sale') {
                    $checkout = $event['checkout'];
                    if ($selectedIds->has((int) $checkout->id)) {
                        $oldDebt = $runningDebt;
                        $returnUsd = $this->checkoutReturnsUsd($checkout);
                        $grossSaleUsd = (float) $event['amount'] + $returnUsd;
                        $paymentUsd = (float) $checkout->payments
                            ->where('status', 1)
                            ->sum(fn ($receipt) => $this->paymentUsd($receipt, $checkout));
                        $snapshots[$checkout->id] = [
                            'before' => max(0, $oldDebt),
                            'after' => static::debtAfterSale($oldDebt, $grossSaleUsd, $paymentUsd, $returnUsd),
                        ];
                    }
                }

                $runningDebt += (float) $event['amount'];
            }
        }

        return $snapshots;
    }

    private function checkinPrices(Collection $checkouts): Collection
    {
        $productIds = $checkouts->flatMap(fn ($checkout) => $checkout->checkoutDetails->pluck('product_id'))
            ->filter()->unique()->values();

        return CheckinDetail::with('checkid')
            ->whereIn('product_id', $productIds)
            ->where('status', 1)
            ->where('price', '>', 1)
            ->get()
            ->groupBy('product_id');
    }

    private function latestFactoryPriceUsd($detail, $checkout, Collection $checkins): ?float
    {
        $checkoutDate = Carbon::parse($checkout->date ?: $checkout->created_at)->endOfDay();
        $available = $checkins->filter(function ($checkinDetail) use ($checkoutDate) {
            $date = optional($checkinDetail->checkid)->date ?: $checkinDetail->created_at;

            return $date && Carbon::parse($date)->lte($checkoutDate);
        });
        $sameWarehouse = $available->where('warehouse_id', $detail->warehouse_id);
        $latest = ($sameWarehouse->isNotEmpty() ? $sameWarehouse : $available)
            ->sortByDesc(fn ($checkinDetail) => $this->sortKey($checkinDetail->checkid ?: $checkinDetail))
            ->first();

        if (! $latest) {
            return null;
        }

        $checkin = $latest->checkid;
        $qty = (float) $latest->qty;
        $unitPrice = $qty > 0 && (float) $latest->total_price > 0
            ? (float) $latest->total_price / $qty
            : (float) $latest->price;
        $currencyType = (int) ($latest->currency_type ?? optional($checkin)->currency_type ?? 2);
        $rate = (float) ($latest->currency_type_price ?: optional($checkin)->currency_type_price);
        $date = optional($checkin)->date ?: $latest->created_at;

        return optional($checkin)->source_system === 'lidaz'
            ? AccountingCashReportService::lidazUnitPriceToUsd($unitPrice, $currencyType, $rate, $date)
            : Currency::documentAmountToUsd($unitPrice, $currencyType, $rate, $date);
    }

    private function checkoutTotalUsd($checkout): float
    {
        return (float) $checkout->checkoutDetails->sum(function ($detail) use ($checkout) {
            $unit = CheckoutMonthExport::checkoutDetailUnitPriceUsd($detail, $checkout);

            return $unit === null ? 0 : (float) $detail->qty * $unit;
        });
    }

    private function checkoutReturnsUsd($checkout): float
    {
        return (float) $checkout->returns->sum(fn ($return) => $this->amountToUsd(
            (float) $return->qty * (float) $return->price,
            (int) $checkout->currency_type,
            (float) $checkout->currency_type_price,
            $checkout->date ?: $checkout->created_at
        ));
    }

    private function paymentUsd($receipt, $checkout = null): float
    {
        if ($checkout) {
            return $this->accountingService->paymentAmountToUsd(
                (float) $receipt->price,
                (int) $receipt->currency_type,
                (float) $receipt->currency_type_price,
                (int) $checkout->currency_type,
                (float) $checkout->currency_type_price
            );
        }

        return $this->accountingService->legacyUnlinkedPaymentToUsd(
            (float) $receipt->price,
            (int) $receipt->currency_type,
            (float) $receipt->currency_type_price,
            (string) $receipt->comment
        );
    }

    private function amountToUsd(float $amount, int $currencyType, float $rate, $date): float
    {
        return Currency::documentAmountToUsd($amount, $currencyType, $rate, $date);
    }

    private function sortKey($model, int $priority = 10, string $dateField = 'date'): string
    {
        $date = data_get($model, $dateField) ?: data_get($model, 'created_at') ?: '2000-01-01';
        $createdAt = data_get($model, 'created_at');
        $time = $createdAt ? Carbon::parse($createdAt)->format('H:i:s.u') : '00:00:00.000000';

        return Carbon::parse($date)->format('Y-m-d').' '.$time.'-'.str_pad((string) $priority, 3, '0', STR_PAD_LEFT)
            .'-'.str_pad((string) (data_get($model, 'id') ?: 0), 12, '0', STR_PAD_LEFT);
    }
}
