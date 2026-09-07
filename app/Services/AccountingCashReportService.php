<?php

namespace App\Services;

use App\Models\CashReceipt;
use App\Models\CheckinDetail;
use App\Models\Currency;
use Illuminate\Support\Collection;

class AccountingCashReportService
{
    private array $legacyCostCache = [];

    public function rows(array $filters): Collection
    {
        $receipts = CashReceipt::query()
            ->where('status', 1)
            ->whereNotNull('checkout_id')
            ->whereHas('checkout')
            ->where('date', '<=', $filters['to'])
            ->with(['clientname', 'uname', 'checkout.managerid', 'checkout.supid', 'checkout.details.prodid.unitid', 'checkout.details.checkid'])
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
        // To'lov summasi cash_receiptsdan olinadi. Eski to'lovlarda currency_type
        // formadagi standart USD qiymatida qolib ketgan. Checkout esa summaning
        // haqiqiy valyutasi va tarixiy kursini saqlaydi, shuning uchun bog'langan
        // savdolarda aynan hujjat valyutasi ustun hisoblanadi.
        $paymentUsd = $this->paymentAmountToUsd(
            (float) $receipt->price,
            (int) $receipt->currency_type,
            (float) $receipt->currency_type_price,
            $checkout->currency_type !== null ? (int) $checkout->currency_type : null,
            $checkout->currency_type_price !== null ? (float) $checkout->currency_type_price : null
        );
        // `checkouts.details` matn ustuni details() relationi bilan bir xil nomda.
        // Property orqali o'qilsa relation o'rniga NULL/text qaytadi, shu sabab eager-loaded
        // relationni Eloquent relation storage'dan aniq olamiz.
        $details = $checkout->relationLoaded('details')
            ? $checkout->getRelation('details')
            : $checkout->details()->with('prodid.unitid')->get();
        $allocation = $this->allocatePayment($details ?? collect(), $paymentUsd, $previousUsd);

        $scheme = (string) ($checkout->commission_scheme ?? '');
        $kpiPercent = (float) ($checkout->kpi_percent ?? 0);
        $agentPercent = (float) ($checkout->agent_percent ?? 0);
        $venoxPercent = (float) ($checkout->venox_bonus_percent ?? 0);
        $shares = $this->splitPayment($paymentUsd, $kpiPercent, $agentPercent, $venoxPercent);

        return [
            'receipt_id' => $receipt->id,
            'checkout_code' => $checkout->code,
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
            'kpi' => $shares['kpi'],
            'agent_amount' => $shares['agent'],
            'venox' => $shares['venox'],
            'factory' => $shares['factory'],
        ];
    }

    public function splitPayment(float $paymentUsd, float $kpiPercent, float $agentPercent, float $venoxPercent): array
    {
        $kpi = $paymentUsd * $kpiPercent / 100;
        $agent = $paymentUsd * $agentPercent / 100;
        $venox = $paymentUsd * $venoxPercent / 100;

        return [
            'kpi' => $kpi,
            'agent' => $agent,
            'venox' => $venox,
            'factory' => $paymentUsd - $kpi - $agent - $venox,
        ];
    }

    public function paymentAmountToUsd(
        float $amount,
        int $receiptCurrencyType,
        float $receiptRate,
        ?int $checkoutCurrencyType,
        ?float $checkoutRate
    ): float {
        $currencyType = $checkoutCurrencyType ?: $receiptCurrencyType;
        $rate = ($checkoutRate && $checkoutRate > 1) ? $checkoutRate : $receiptRate;

        return $this->toUsd($amount, $currencyType, $rate);
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
                $unitCostUsd = $this->detailUnitCostUsd($detail);
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

    /**
     * Tannarxning asosiy manbasi — /checkins dagi haqiqiy kirim. Aynan shu
     * mahsulot va omborga savdo sanasigacha qilingan eng oxirgi faol oddiy
     * kirimning bir dona narxi hamda o'sha hujjat kursidan foydalaniladi.
     * Faqat mos kirim topilmasa checkoutda saqlangan eski tannarx zaxira bo'ladi.
     */
    private function detailUnitCostUsd($detail): float
    {
        $checkout = $detail->checkid ?? null;
        $date = optional($checkout)->date;
        if (! $checkout) {
            $storedCost = (float) $detail->tan_price;
            return $storedCost > 0
                ? $this->toUsd($storedCost, (int) $detail->currency_type, (float) $detail->currency_type_price)
                : 0.0;
        }
        $key = implode(':', [(int) $detail->product_id, (int) $detail->warehouse_id, (string) $date]);

        if (array_key_exists($key, $this->legacyCostCache)) {
            return $this->legacyCostCache[$key];
        }

        $query = CheckinDetail::query()
            ->with('checkid')
            ->whereHas('checkid', function ($query) {
                $query->where('status', 1)->where('type_id', 1);
            })
            ->where('checkin_details.product_id', $detail->product_id)
            ->where('checkin_details.status', 1)
            ->where('checkin_details.price', '>', 0);

        if ($detail->warehouse_id) {
            $query->where('checkin_details.warehouse_id', $detail->warehouse_id);
        }
        if ($date) {
            $query->whereHas('checkid', function ($query) use ($date) {
                $query->whereDate('date', '<=', $date);
            });
        }

        $checkin = $query
            ->join('checkins', 'checkins.id', '=', 'checkin_details.checkin_id')
            ->select('checkin_details.*')
            ->orderByDesc('checkins.date')
            ->orderByDesc('checkin_details.id')
            ->first();
        if (! $checkin) {
            $storedCost = (float) $detail->tan_price;
            return $this->legacyCostCache[$key] = $storedCost > 0
                ? $this->toUsd($storedCost, (int) $detail->currency_type, (float) $detail->currency_type_price)
                : 0.0;
        }

        $currencyType = (int) ($checkin->currency_type ?: optional($checkin->checkid)->currency_type);
        $currencyRate = (float) ($checkin->currency_type_price ?: optional($checkin->checkid)->currency_type_price);

        return $this->legacyCostCache[$key] = $this->toUsd(
            (float) $checkin->price,
            $currencyType,
            $currencyRate
        );
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
