<?php

namespace App\Services;

use App\Models\CashReceipt;
use App\Models\Currency;
use Illuminate\Support\Collection;

class AccountingCashReportService
{
    public function rows(array $filters): Collection
    {
        $receipts = CashReceipt::query()
            ->where('status', 1)
            ->whereNotNull('checkout_id')
            ->whereHas('checkout')
            ->where('date', '<=', $filters['to'])
            ->with(['clientname', 'uname', 'checkout.managerid', 'checkout.supid', 'checkout.details.prodid.unitid'])
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $paidBefore = [];
        return $receipts->map(function (CashReceipt $receipt) use (&$paidBefore) {
            $checkoutId = (int) $receipt->checkout_id;
            $row = $this->makeRow($receipt, $paidBefore[$checkoutId] ?? 0);
            $paidBefore[$checkoutId] = ($paidBefore[$checkoutId] ?? 0) + $row['payment_usd'];
            return $row;
        })->filter(function (array $row) use ($filters) {
            if ($row['date'] < $filters['from'] || $row['date'] > $filters['to']) {
                return false;
            }
            if (! empty($filters['scheme']) && $row['scheme_group'] !== $filters['scheme']) {
                return false;
            }
            if (! empty($filters['product_id']) && ! in_array((int) $filters['product_id'], $row['product_ids'], true)) {
                return false;
            }
            return true;
        })->values();
    }

    private function makeRow(CashReceipt $receipt, float $previousUsd): array
    {
        $checkout = $receipt->checkout;
        $paymentUsd = $this->toUsd((float) $receipt->price, (int) $receipt->currency_type, (float) $receipt->currency_type_price);
        $allocation = $this->allocatePayment($checkout->details, $paymentUsd, $previousUsd);

        $scheme = (string) ($checkout->commission_scheme ?? '');
        $kpiPercent = (float) ($checkout->kpi_percent ?? 0);
        $agentPercent = (float) ($checkout->agent_percent ?? 0);
        $venoxPercent = (float) ($checkout->venox_bonus_percent ?? 0);
        $kpi = $paymentUsd * $kpiPercent / 100;
        $agent = $paymentUsd * $agentPercent / 100;
        $venox = $paymentUsd * $venoxPercent / 100;

        return [
            'receipt_id' => $receipt->id,
            'date' => $receipt->date,
            'agent' => optional($checkout->managerid)->name ?: '—',
            'client' => optional($receipt->clientname)->name ?: optional($checkout->supid)->name ?: '—',
            'scheme' => $scheme,
            'scheme_group' => str_starts_with($scheme, 'venox_') ? 'venox_bonus' : $scheme,
            'products' => $allocation['products'],
            'product_ids' => $allocation['product_ids'],
            'purchase_cost_usd' => $allocation['purchase_cost_usd'],
            'unallocated_usd' => $allocation['unallocated_usd'],
            'payment_usd' => $paymentUsd,
            'kpi_percent' => $kpiPercent,
            'agent_percent' => $agentPercent,
            'venox_percent' => $venoxPercent,
            'kpi' => $kpi,
            'agent_amount' => $agent,
            'venox' => $venox,
            'factory' => $paymentUsd - $kpi - $agent - $venox,
        ];
    }

    /**
     * Bir to'lovni checkout qatorlariga oldingi to'lovlar qoplagan joydan boshlab taqsimlaydi.
     */
    public function allocatePayment(Collection $details, float $paymentUsd, float $previousUsd = 0): array
    {
        $remaining = max(0, $paymentUsd);
        $offset = max(0, $previousUsd);
        $products = [];
        $purchaseCostUsd = 0;
        $productIds = [];

        foreach ($details->sortBy('id') as $detail) {
            $qty = (float) $detail->qty;
            $lineUsd = $this->toUsd((float) $detail->total_price, (int) $detail->currency_type, (float) $detail->currency_type_price);
            if ($qty <= 0 || $lineUsd <= 0) {
                continue;
            }
            if ($offset >= $lineUsd) {
                $offset -= $lineUsd;
                continue;
            }

            $availableLineUsd = $lineUsd - $offset;
            $coveredUsd = min($remaining, $availableLineUsd);
            $coveredQty = $qty * ($coveredUsd / $lineUsd);
            if ($coveredQty > 0) {
                $unitCostUsd = $this->toUsd((float) $detail->tan_price, (int) $detail->currency_type, (float) $detail->currency_type_price);
                $purchaseCostUsd += $coveredQty * $unitCostUsd;
                $productIds[] = (int) $detail->product_id;
                $products[] = [
                    'id' => (int) $detail->product_id,
                    'name' => optional($detail->prodid)->name ?: 'Noma’lum tovar',
                    'qty' => $coveredQty,
                    'unit' => optional(optional($detail->prodid)->unitid)->name ?? 'dona',
                ];
                $remaining -= $coveredUsd;
            }
            $offset = 0;
            if ($remaining <= 0.000001) {
                break;
            }
        }
        return [
            'products' => $products,
            'product_ids' => array_values(array_unique($productIds)),
            'purchase_cost_usd' => $purchaseCostUsd,
            'unallocated_usd' => max(0, $remaining),
        ];
    }

    private function toUsd(float $amount, int $currencyType, float $documentRate): float
    {
        if ($currencyType === 1 || $currencyType === 0) {
            return $amount;
        }
        $rate = $documentRate > 1 ? $documentRate : Currency::usdRate();
        return $rate > 0 ? $amount / $rate : 0;
    }
}
