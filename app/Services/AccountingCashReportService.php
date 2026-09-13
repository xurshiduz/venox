<?php

namespace App\Services;

use App\Models\CashReceipt;
use App\Models\CheckinDetail;
use App\Models\Checkout;
use App\Models\Currency;
use Illuminate\Support\Collection;

class AccountingCashReportService
{
    private array $legacyCostCache = [];

    public function rows(array $filters): Collection
    {
        $filterClientIds = collect($filters['client_ids'] ?? [])->filter()->unique()->values();
        $includePurchaseCost = (bool) ($filters['include_purchase_cost'] ?? true);
        $receiptRelations = ['clientname', 'uname', 'checkout.managerid', 'checkout.supid', 'checkout.details.prodid.unitid'];
        $checkoutRelations = ['managerid', 'supid', 'details.prodid.unitid'];
        if ($includePurchaseCost) {
            $receiptRelations[] = 'checkout.details.checkid';
            $checkoutRelations[] = 'details.checkid';
        }

        $receipts = CashReceipt::query()
            ->where('status', 1)
            ->where('date', '<=', $filters['to'])
            ->when($filterClientIds->isNotEmpty(), function ($query) use ($filterClientIds) {
                $query->whereIn('client_id', $filterClientIds);
            })
            ->with($receiptRelations)
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $unlinkedClientIds = $receipts
            ->whereNull('checkout_id')
            ->pluck('client_id')
            ->filter()
            ->unique()
            ->values();
        $clientCheckouts = $unlinkedClientIds->isEmpty()
            ? collect()
            : Checkout::query()
                ->where('status', 1)
                ->where('checkout_tip_id', 1)
                ->where('type_id', 1)
                ->whereIn('client_id', $unlinkedClientIds)
                ->whereDate('date', '<=', $filters['to'])
                ->with($checkoutRelations)
                ->orderBy('date')
                ->orderBy('id')
                ->get()
                ->groupBy('client_id');

        $paidBefore = [];
        $rows = collect();

        foreach ($receipts as $receipt) {
            if ($receipt->checkout) {
                $checkout = $receipt->checkout;
                $checkoutId = (int) $checkout->id;
                $paymentUsd = $this->paymentAmountToUsd(
                    (float) $receipt->price,
                    (int) $receipt->currency_type,
                    (float) $receipt->currency_type_price,
                    $checkout->currency_type !== null ? (int) $checkout->currency_type : null,
                    $checkout->currency_type_price !== null ? (float) $checkout->currency_type_price : null
                );
                $rows->push($this->makeRow($receipt, $checkout, $paymentUsd, $paidBefore[$checkoutId] ?? 0, $includePurchaseCost));
                $paidBefore[$checkoutId] = ($paidBefore[$checkoutId] ?? 0) + $paymentUsd;
                continue;
            }

            // Checkoutga biriktirilmagan "qarz uchun" to'lovlar oldin hisobotdan
            // butunlay tushib qolardi. Ularni mijozning eski savdolariga FIFO
            // tartibida taqsimlaymiz; ortgan qismi ham alohida qatorda ko'rinadi.
            $remainingUsd = $this->legacyUnlinkedPaymentToUsd(
                (float) $receipt->price,
                (int) $receipt->currency_type,
                (float) $receipt->currency_type_price,
                (string) $receipt->comment
            );

            $checkouts = $clientCheckouts
                ->get($receipt->client_id, collect())
                ->filter(fn (Checkout $checkout) => (string) $checkout->date <= (string) $receipt->date);

            $receiptRows = collect();

            foreach ($checkouts as $checkout) {
                if ($remainingUsd <= 0.000001) {
                    break;
                }
                $checkoutId = (int) $checkout->id;
                $previousUsd = $paidBefore[$checkoutId] ?? 0;
                $unpaidUsd = max(0, $this->checkoutTotalUsd($checkout) - $previousUsd);
                if ($unpaidUsd <= 0.000001) {
                    continue;
                }
                $allocatedUsd = min($remainingUsd, $unpaidUsd);
                $receiptRows->push($this->makeRow($receipt, $checkout, $allocatedUsd, $previousUsd, $includePurchaseCost));
                $paidBefore[$checkoutId] = $previousUsd + $allocatedUsd;
                $remainingUsd -= $allocatedUsd;
            }

            if ($remainingUsd > 0.000001) {
                $receiptRows->push($this->makeUnallocatedRow($receipt, $remainingUsd));
            }

            if ($receiptRows->isNotEmpty()) {
                // Ichki FIFO taqsimoti hisob-kitob uchun saqlanadi, lekin hisobotda
                // har bir kassa kirimi aynan bitta qator bo'lib ko'rinishi kerak.
                $rows->push($this->combineReceiptRows($receiptRows));
            }
        }

        return $rows->filter(function (array $row) use ($filters) {
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

    private function makeRow(
        CashReceipt $receipt,
        Checkout $checkout,
        float $paymentUsd,
        float $previousUsd,
        bool $includePurchaseCost
    ): array
    {
        // `checkouts.details` matn ustuni details() relationi bilan bir xil nomda.
        // Property orqali o'qilsa relation o'rniga NULL/text qaytadi, shu sabab eager-loaded
        // relationni Eloquent relation storage'dan aniq olamiz.
        $details = $checkout->relationLoaded('details')
            ? $checkout->getRelation('details')
            : $checkout->details()->with('prodid.unitid')->get();
        $allocation = $this->allocatePayment($details ?? collect(), $paymentUsd, $previousUsd, $includePurchaseCost);

        $scheme = (string) ($checkout->commission_scheme ?? '');
        $kpiPercent = (float) ($checkout->kpi_percent ?? 0);
        $agentPercent = (float) ($checkout->agent_percent ?? 0);
        $venoxPercent = (float) ($checkout->venox_bonus_percent ?? 0);
        $shares = $this->splitPayment($paymentUsd, $kpiPercent, $agentPercent, $venoxPercent);

        return [
            'receipt_id' => $receipt->id,
            'client_id' => $receipt->client_id ?: $checkout->client_id,
            'checkout_id' => $checkout->id,
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
            'contract_bonus_usd' => $scheme === 'contract' ? $shares['venox'] : 0.0,
            'factory' => $shares['factory'],
        ];
    }

    private function checkoutTotalUsd(Checkout $checkout): float
    {
        $details = $checkout->relationLoaded('details')
            ? $checkout->getRelation('details')
            : $checkout->details()->get();

        return (float) $details->sum(function ($detail) {
            return $this->toUsd(
                (float) $detail->total_price,
                (int) $detail->currency_type,
                (float) $detail->currency_type_price
            );
        });
    }

    private function makeUnallocatedRow(CashReceipt $receipt, float $paymentUsd): array
    {
        return [
            'receipt_id' => $receipt->id,
            'client_id' => $receipt->client_id,
            'checkout_id' => null,
            'checkout_code' => null,
            'date' => $receipt->date,
            'agent' => optional($receipt->uname)->name ?: '—',
            'client' => optional($receipt->clientname)->name ?: '—',
            'scheme' => '',
            'scheme_group' => '',
            'products' => [],
            'product_ids' => [],
            'purchase_cost_usd' => 0.0,
            'unallocated_usd' => $paymentUsd,
            'payment_usd' => $paymentUsd,
            'kpi_percent' => 0.0,
            'agent_percent' => 0.0,
            'venox_percent' => 0.0,
            'kpi' => 0.0,
            'agent_amount' => 0.0,
            'venox' => 0.0,
            'contract_bonus_usd' => 0.0,
            'factory' => $paymentUsd,
        ];
    }

    /**
     * Bitta cash receipt bir nechta eski savdoni qoplasa ham uni hisobotda
     * bo'lib yubormaydi. Mahsulot va xarajatlar jamlanadi, komissiya summalari
     * esa har bir ichki FIFO bo'lagi bo'yicha hisoblangan holicha qo'shiladi.
     */
    public function combineReceiptRows(Collection $parts): array
    {
        $first = $parts->first();
        $paymentUsd = (float) $parts->sum('payment_usd');
        $products = collect($parts->pluck('products')->flatten(1))
            ->groupBy(fn (array $product) => implode(':', [
                (int) ($product['id'] ?? 0),
                (string) ($product['name'] ?? ''),
                (string) ($product['unit'] ?? ''),
            ]))
            ->map(function (Collection $group): array {
                $product = $group->first();
                $product['qty'] = (float) $group->sum('qty');

                return $product;
            })
            ->values()
            ->all();

        $schemes = $parts->pluck('scheme')->filter()->unique()->values();
        $schemeGroups = $parts->pluck('scheme_group')->filter()->unique()->values();
        $agents = $parts->pluck('agent')->filter(fn ($agent) => $agent && $agent !== '—')->unique()->values();
        $checkoutCodes = $parts->pluck('checkout_code')->filter()->unique()->values();
        $kpi = (float) $parts->sum('kpi');
        $agentAmount = (float) $parts->sum('agent_amount');
        $venox = (float) $parts->sum('venox');

        return array_merge($first, [
            'checkout_code' => $checkoutCodes->implode(', ') ?: null,
            'agent' => $agents->implode(', ') ?: ($first['agent'] ?? '—'),
            'scheme' => $schemes->count() > 1 ? 'Aralash' : (string) ($schemes->first() ?? ''),
            'scheme_group' => $schemeGroups->count() > 1 ? 'mixed' : (string) ($schemeGroups->first() ?? ''),
            'products' => $products,
            'product_ids' => collect($products)->pluck('id')->map(fn ($id) => (int) $id)->unique()->values()->all(),
            'purchase_cost_usd' => (float) $parts->sum('purchase_cost_usd'),
            'unallocated_usd' => (float) $parts->sum('unallocated_usd'),
            'payment_usd' => $paymentUsd,
            'kpi_percent' => $paymentUsd > 0 ? $kpi * 100 / $paymentUsd : 0.0,
            'agent_percent' => $paymentUsd > 0 ? $agentAmount * 100 / $paymentUsd : 0.0,
            'venox_percent' => $paymentUsd > 0 ? $venox * 100 / $paymentUsd : 0.0,
            'kpi' => $kpi,
            'agent_amount' => $agentAmount,
            'venox' => $venox,
            'contract_bonus_usd' => (float) $parts->sum('contract_bonus_usd'),
            'factory' => (float) $parts->sum('factory'),
        ]);
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
     * Eski, checkoutga biriktirilmagan kassa yozuvlarida valyuta forma orqali
     * saqlanmagan va DB standarti sabab currency_type=USD bo'lib qolgan.
     * Masalan, `600 000 Click` yozuvi 600 000 USD emas, 600 000 UZS.
     *
     * Aniq `$`/USD belgisi bor yozuvlar USD bo'lib qoladi. Belgisiz summa
     * hujjatdagi bir dollarlik kursdan ham katta bo'lsa, u legacy UZS summa
     * sifatida tarixiy kursga bo'linadi. Kichik, avvaldan USDga aylantirib
     * kiritilgan qarz to'lovlariga tegilmaydi.
     */
    public function legacyUnlinkedPaymentToUsd(
        float $amount,
        int $currencyType,
        float $documentRate,
        string $comment = ''
    ): float {
        if ($currencyType !== 1) {
            return $this->toUsd($amount, $currencyType, $documentRate);
        }

        $rate = $documentRate > 1 ? $documentRate : Currency::usdRate();
        $hasExplicitUsdMarker = (bool) preg_match('/\$|\bUSD\b|dollar|dollari|доллар/iu', $comment);

        if (! $hasExplicitUsdMarker && $rate > 1 && $amount >= $rate) {
            return $amount / $rate;
        }

        return $amount;
    }

    /**
     * Bir to'lovni checkout qatorlariga oldingi to'lovlar qoplagan joydan boshlab taqsimlaydi.
     */
    public function allocatePayment(
        Collection $details,
        float $paymentUsd,
        float $previousUsd = 0,
        bool $includePurchaseCost = true
    ): array
    {
        $remaining = max(0, $paymentUsd);
        $offset = max(0, $previousUsd);
        $products = [];
        $purchaseCostUsd = 0.0;
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
                if ($includePurchaseCost) {
                    $unitCostUsd = $this->detailUnitCostUsd($detail);
                    $purchaseCostUsd += $coveredQty * $unitCostUsd;
                }
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
