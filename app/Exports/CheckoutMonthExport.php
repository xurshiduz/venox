<?php

namespace App\Exports;

use App\Models\CashReceipt;
use App\Models\Checkin;
use App\Models\CheckinDetail;
use App\Models\Checkout;
use App\Models\Client;
use App\Models\Currency;
use App\Services\AccountingCashReportService;
use App\Services\ApprovedProductPriceService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CheckoutMonthExport implements FromView, WithStyles
{
    protected $startDate;
    protected $endDate;
    protected $rowCount = 0;
    protected $rowLineCounts = [];
    protected $mergeRanges = [];

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function view(): View
    {
        $this->rowCount = 0;
        $this->rowLineCounts = [];
        $this->mergeRanges = [];

        $periodStart = Carbon::parse($this->startDate)->startOfDay();
        $periodEnd = Carbon::parse($this->endDate)->endOfDay();

        $checkouts = Checkout::with(['supid', 'managerid', 'checkoutDetails.prodid'])
            ->where('status', 1)
            ->whereDate('date', '>=', $periodStart->toDateString())
            ->whereDate('date', '<=', $periodEnd->toDateString())
            ->orderBy('date')
            ->orderBy('id')
            ->get();
        $productIds = $checkouts->flatMap(function ($checkout) {
            return $checkout->checkoutDetails->pluck('product_id');
        })->filter()->unique()->values();

        // Har bir sotuv qatori uchun o'sha sotuv sanasigacha mavjud bo'lgan
        // eng so'nggi haqiqiy kirim narxini topish uchun kirimlarni oldindan yuklaymiz.
        $checkinPrices = CheckinDetail::with('checkid')
            ->whereIn('product_id', $productIds)
            ->where('status', 1)
            // 1.00 narxli eski inventar/ko'chirish yozuvlari tannarx emas.
            ->where('price', '>', 1)
            ->whereHas('checkid', function ($query) use ($periodEnd) {
                $query->where('status', 1)
                    ->where('type_id', 1)
                    ->whereDate('date', '<=', $periodEnd->toDateString());
            })
            ->get()
            ->groupBy('product_id');

        $accounting = app(AccountingCashReportService::class);
        $approvedPriceService = app(ApprovedProductPriceService::class);
        $reportUsdRate = $approvedPriceService->usdRate();
        $clientPayments = [];
        $clientPaymentDates = [];
        $paymentBonusExpensesByClient = [];
        $clientUnallocatedPayments = [];

        $payments = CashReceipt::query()
            ->where('status', 1)
            ->whereDate('date', '>=', $periodStart->toDateString())
            ->whereDate('date', '<=', $periodEnd->toDateString())
            ->with([
                'checkout:id,client_id,manager_id,currency_type,currency_type_price,kpi_percent,venox_bonus_percent',
                'checkout.managerid:id,name',
                'uname:id,name',
            ])
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $paymentClientIds = $payments->map(function ($payment) {
            return $payment->client_id ?: optional($payment->checkout)->client_id;
        });
        $paymentAgentsByClient = $payments
            ->groupBy(fn ($payment) => (string) ($payment->client_id ?: optional($payment->checkout)->client_id))
            ->map(function (Collection $clientReceipts): string {
                return (string) $clientReceipts
                    ->map(fn ($payment) => optional(optional($payment->checkout)->managerid)->name
                        ?: optional($payment->uname)->name)
                    ->filter()
                    ->first();
            });
        $checkoutClientIds = static::mergeReportClientIds($checkouts->pluck('client_id'), []);
        $clientIds = static::mergeReportClientIds(
            $checkoutClientIds,
            $paymentClientIds
        );
        $clientsById = Client::whereIn('id', $clientIds)
            ->get()
            ->keyBy(fn ($client) => (string) $client->id);
        $cashReportClientIds = static::mergeReportClientIds([], $paymentClientIds)
            ->merge($payments->whereNull('checkout_id')->pluck('client_id')->filter())
            ->unique()
            ->values();
        $cashReportRows = collect();
        $cashReportRowsByReceipt = collect();
        $paymentAllocationRows = collect();

        if ($cashReportClientIds->isNotEmpty()) {
            // AccountingCashReportService eski, shartnomaga bog'lanmagan to'lovlarni
            // mijozning avvalgi savdolariga FIFO bo'yicha taqsimlab beradi. Shu
            // qatorlardan Venox bonus summasini ham olamiz.
            $cashReportRows = $accounting->rows([
                'from' => $periodStart->toDateString(),
                'to' => $periodEnd->toDateString(),
                'scheme' => null,
                'product_id' => null,
                'client_ids' => $cashReportClientIds->all(),
                'include_purchase_cost' => true,
            ]);
            $cashReportRowsByReceipt = $cashReportRows
                ->keyBy(fn (array $row) => (string) ($row['receipt_id'] ?? ''));
            $paymentAllocationRows = $cashReportRows
                ->groupBy(fn (array $row) => (string) $row['client_id']);

        }

        foreach ($payments as $payment) {
            $clientId = $payment->client_id ?: optional($payment->checkout)->client_id;
            if (! $clientId) {
                continue;
            }

            $key = (string) $clientId;
            $usd = $payment->checkout
                ? $accounting->paymentAmountToUsd(
                    (float) $payment->price,
                    (int) $payment->currency_type,
                    (float) $payment->currency_type_price,
                    $payment->checkout->currency_type !== null ? (int) $payment->checkout->currency_type : null,
                    $payment->checkout->currency_type_price !== null ? (float) $payment->checkout->currency_type_price : null
                )
                : $accounting->legacyUnlinkedPaymentToUsd(
                    (float) $payment->price,
                    (int) $payment->currency_type,
                    (float) ($payment->currency_type_price ?: Currency::usdRateForDate($payment->date ?: $payment->created_at)),
                    (string) $payment->comment
                );
            $cashReportRow = $cashReportRowsByReceipt->get((string) $payment->id, []);
            // Bog'langan to'lov faqat o'z checkout foizlarini ishlatadi.
            // Bog'lanmagan "qarz uchun" to'lovda esa AccountingCashReportService
            // aynan FIFO qoplangan hujjatlar ulushini hisoblab qaytaradi.
            $reportBonusUsd = static::reportBonusAmountUsd(
                $usd,
                $payment->checkout,
                $cashReportRow
            );
            $paymentBreakdown = static::paymentBreakdownUsd(
                $usd,
                $reportBonusUsd
            );
            $clientPayments[$key] = ($clientPayments[$key] ?? 0) + $paymentBreakdown['net_usd'];
            $paymentBonusExpensesByClient[$key] = ($paymentBonusExpensesByClient[$key] ?? 0) + $paymentBreakdown['bonus_usd'];
            $clientUnallocatedPayments[$key] = ($clientUnallocatedPayments[$key] ?? 0)
                + max(0, (float) ($cashReportRow['unallocated_usd'] ?? 0));
            $clientPaymentDates[$key][] = Carbon::parse($payment->date ?: $payment->created_at)->format('d.m.Y');
        }

        $closingDebts = $this->clientDebtTotalsUsd($clientIds->map(fn ($id) => (string) $id)->all(), $periodEnd);
        // Bu hisobotdagi bonus xarajatlari checkout formasidagi KPI + Venox
        // bonus yig'indisidir. Agent ulushi bu ustunga kirmaydi.
        $clientBonusExpenses = collect($paymentBonusExpensesByClient);
        $groupedRows = [];

        foreach ($checkouts as $checkout) {
            $clientKey = (string) $checkout->client_id;

            // To'lov mavjud mijozlarda mahsulot qatorlari to'liq checkoutdan
            // emas, aynan shu to'lov qoplagan FIFO mahsulotlardan tuziladi.
            // Aks holda tasdiqlangan prays jami kassa kirimiga teng kelmaydi.
            if ($paymentAllocationRows->has($clientKey)) {
                continue;
            }

            $paid = (float) ($clientPayments[$clientKey] ?? 0);
            $closing = (float) ($closingDebts[$clientKey] ?? 0);

            if (!isset($groupedRows[$clientKey])) {
                $groupedRows[$clientKey] = [
                    'dates' => [],
                    'client' => $checkout->supid->name ?? 'Noma\'lum mijoz',
                    'client_phone' => $checkout->supid->phone ?? null,
                    'fallback_agent' => $checkout->managerid->name ?? '—',
                    // Davr boshidagi qarz keyinroq, ushbu davrdagi sotuvlar
                    // yig'indisi aniqlangach hisoblanadi.
                    'debt_before_payment' => 0,
                    'products' => [],
                    'agents' => [],
                    'quantities' => [],
                    'unit_prices' => [],
                    'unit_prices_uzs' => [],
                    'actual_unit_prices_usd' => [],
                    'factory_prices' => [],
                    'factory_prices_uzs' => [],
                    'markup_percentages' => [],
                    'actual_line_totals_usd' => [],
                    'approved_total_usd' => 0,
                    'actual_total_usd' => 0,
                    'paid_usd' => $paid,
                    'closing_debt_usd' => $closing,
                    'bonus_expense_usd' => (float) ($clientBonusExpenses[$clientKey] ?? 0),
                    'venox_cash_usd' => 0.0,
                    'unallocated_payment_usd' => (float) ($clientUnallocatedPayments[$clientKey] ?? 0),
                ];
            }

            $groupedRows[$clientKey]['dates'][] = Carbon::parse($checkout->date ?: $checkout->created_at)->format('d.m.Y');

            foreach ($checkout->checkoutDetails as $detail) {
                $qty = (float) $detail->qty;
                $checkoutDate = Carbon::parse($checkout->date ?: $checkout->created_at)->endOfDay();
                $rawSaleTotal = (float) ($detail->total_price ?? ((float) $detail->price * $qty));
                $rawSaleUnit = $qty != 0 ? $rawSaleTotal / $qty : 0;
                $saleRate = (float) ($checkout->currency_type_price ?: Currency::usdRateForDate($checkout->date ?: $checkout->created_at));
                $totalUsd = Currency::documentAmountToUsd(
                    $rawSaleTotal,
                    (int) $checkout->currency_type,
                    $saleRate,
                    $checkout->date ?: $checkout->created_at
                );
                $actualUnitPriceUsd = static::checkoutDetailUnitPriceUsd($detail, $checkout);

                $availableCheckins = collect($checkinPrices->get($detail->product_id, []))
                    ->filter(function ($checkinDetail) use ($checkoutDate) {
                        $checkinDate = optional($checkinDetail->checkid)->date ?: $checkinDetail->created_at;

                        return $checkinDate && Carbon::parse($checkinDate)->lte($checkoutDate);
                    });

                $sameWarehouseCheckins = $availableCheckins->where('warehouse_id', $detail->warehouse_id);
                $latestCheckin = ($sameWarehouseCheckins->isNotEmpty() ? $sameWarehouseCheckins : $availableCheckins)
                    ->sortByDesc(function ($checkinDetail) {
                        $date = optional($checkinDetail->checkid)->date ?: $checkinDetail->created_at;

                        return Carbon::parse($date)->format('Y-m-d H:i:s') . '-' . str_pad((string) $checkinDetail->id, 12, '0', STR_PAD_LEFT);
                    })
                    ->first();

                $latestCheckinPriceUsd = 0;
                $rawCheckinUnit = 0;
                $checkinRate = Currency::usdRateForDate($checkout->date ?: $checkout->created_at);
                if ($latestCheckin) {
                    $checkin = $latestCheckin->checkid;
                    $checkinQty = (float) $latestCheckin->qty;
                    $checkinUnitPrice = $checkinQty > 0 && (float) $latestCheckin->total_price > 0
                        ? (float) $latestCheckin->total_price / $checkinQty
                        : (float) $latestCheckin->price;
                    $rawCheckinUnit = $checkinUnitPrice;
                    $checkinRate = (float) (optional($checkin)->currency_type_price ?: Currency::usdRateForDate(optional($checkin)->date ?? $latestCheckin->created_at));
                    // Kirim qatorining valyutasi eski sarlavhadagi xato belgidan
                    // ishonchliroq; har bir hujjat o'z tarixiy kursida USDga o'tadi.
                    $latestCheckinPriceUsd = Currency::documentAmountToUsd(
                        $rawCheckinUnit,
                        (int) ($latestCheckin->currency_type ?? optional($checkin)->currency_type ?? 2),
                        (float) ($latestCheckin->currency_type_price ?: $checkinRate),
                        optional($checkin)->date ?? $latestCheckin->created_at
                    );
                }

                // Har bir hujjat o'z sanasida saqlangan kurs bo'yicha USDga o'tadi.
                // Bu tarixiy UZS va USD narxlarini taxminsiz, bir valyutada solishtiradi.
                $actualUnitPriceUsd = static::checkoutDetailUnitPriceUsd($detail, $checkout);
                // Ayrim eski hujjatlarda narx USDda kiritilgan, ammo valyuta UZS va
                // kurs 1 bo'lib qolgan (masalan checkout #613). Faqat shu aniq
                // legacy holatni USD sifatida tiklaymiz; odatiy UZS savdoga tegmaymiz.
                if ((int) $checkout->currency_type === 2
                    && (float) $checkout->currency_type_price <= 1
                    && $rawSaleUnit > 0
                    && $rawSaleUnit < 1000
                    && $latestCheckinPriceUsd > 0) {
                    $actualUnitPriceUsd = $rawSaleUnit;
                }

                // Oylik hisobotda faqat BOSS praysida Sotuv va Zavod narxi
                // ikkalasi ham aniq kiritilgan mahsulotlar ko'rsatiladi.
                $product = $detail->prodid;
                $approvedPrices = $approvedPriceService->pricesFor((string) ($product->name ?? ''));
                if ($actualUnitPriceUsd === null && ! $approvedPrices) {
                    continue;
                }
                $resolvedPrices = static::resolveReportPricesUzs(
                    $approvedPrices,
                    $actualUnitPriceUsd,
                    $latestCheckinPriceUsd > 0 ? $latestCheckinPriceUsd : null,
                    $reportUsdRate
                );
                $unitPriceUzs = $resolvedPrices['sale_uzs'];
                $factoryPriceUzs = $resolvedPrices['factory_uzs'];
                $unitPriceUsd = Currency::documentAmountToUsd(
                    $unitPriceUzs,
                    2,
                    $approvedPriceService->usdRate()
                );
                $factoryPriceUsd = Currency::documentAmountToUsd(
                    $factoryPriceUzs,
                    2,
                    $approvedPriceService->usdRate()
                );

                $lineTotals = static::reportLineTotalsUsd($qty, $unitPriceUsd, $actualUnitPriceUsd);
                $markupPercent = Currency::markupPercent($factoryPriceUsd, $unitPriceUsd);

                $groupedRows[$clientKey]['products'][] = $detail->prodid->name ?? 'Noma\'lum mahsulot';
                $groupedRows[$clientKey]['agents'][] = $checkout->managerid->name ?? '—';
                $groupedRows[$clientKey]['quantities'][] = $qty;
                $groupedRows[$clientKey]['unit_prices'][] = $unitPriceUsd;
                $groupedRows[$clientKey]['unit_prices_uzs'][] = $unitPriceUzs;
                $groupedRows[$clientKey]['actual_unit_prices_usd'][] = $actualUnitPriceUsd;
                $groupedRows[$clientKey]['factory_prices'][] = $factoryPriceUsd;
                $groupedRows[$clientKey]['factory_prices_uzs'][] = $factoryPriceUzs;
                $groupedRows[$clientKey]['markup_percentages'][] = $markupPercent;
                $groupedRows[$clientKey]['actual_line_totals_usd'][] = $lineTotals['actual_total_usd'];
                $groupedRows[$clientKey]['approved_total_usd'] += $lineTotals['approved_total_usd'];
                $groupedRows[$clientKey]['actual_total_usd'] += $lineTotals['actual_total_usd'];
            }
        }

        // Savdoga biriktirilmagan ("za dolg") to'lovlar ham hisobotda ko'rinishi
        // kerak. Shu davrda savdosi bo'lmagan to'lov mijozlari uchun mahsulotsiz
        // alohida guruh yaratamiz; aks holda ular clientIds filtri sabab yo'qoladi.
        foreach ($paymentClientIds->filter()->unique() as $paymentClientId) {
            $clientKey = (string) $paymentClientId;
            if (isset($groupedRows[$clientKey])) {
                continue;
            }

            $client = $clientsById->get($clientKey);
            $products = [];
            $agents = [];
            $quantities = [];
            $unitPrices = [];
            $unitPricesUzs = [];
            $actualUnitPricesUsd = [];
            $factoryPrices = [];
            $factoryPricesUzs = [];
            $markupPercentages = [];
            $actualLineTotalsUsd = [];
            $approvedTotalUsd = 0;
            $clientAllocationProducts = [];
            $fallbackAgent = (string) ($paymentAgentsByClient->get($clientKey) ?: '—');

            // To'lov qaysi real checkout qatorlarini qoplagan bo'lsa, mahsulot,
            // miqdor va haqiqiy sotuv narxini aynan o'sha FIFO taqsimotidan olamiz.
            // Tarixdagi so'nggi savdolarni olib, miqdorni to'lovga moslab sun'iy
            // o'zgartirish bo'sh narxlar va noto'g'ri marjaga sabab bo'lgan.
            foreach ($paymentAllocationRows->get($clientKey, collect()) as $allocationRow) {
                foreach ($allocationRow['products'] ?? [] as $allocatedProduct) {
                    $productName = (string) ($allocatedProduct['name'] ?? 'Noma\'lum mahsulot');
                    $approvedPrices = $approvedPriceService->pricesFor($productName);
                    $actualUnitPriceUsd = isset($allocatedProduct['actual_unit_price_usd'])
                        ? (float) $allocatedProduct['actual_unit_price_usd']
                        : null;
                    if ($actualUnitPriceUsd === null && ! $approvedPrices) {
                        continue;
                    }
                    $factoryUnitPriceUsd = isset($allocatedProduct['factory_unit_price_usd'])
                        ? (float) $allocatedProduct['factory_unit_price_usd']
                        : null;
                    $resolvedPrices = static::resolveReportPricesUzs(
                        $approvedPrices,
                        $actualUnitPriceUsd,
                        $factoryUnitPriceUsd,
                        $reportUsdRate
                    );
                    $unitPriceUzs = $resolvedPrices['sale_uzs'];
                    $factoryPriceUzs = $resolvedPrices['factory_uzs'];
                    $unitPriceUsd = Currency::documentAmountToUsd(
                        $unitPriceUzs,
                        2,
                        $approvedPriceService->usdRate()
                    );
                    $factoryPriceUsd = Currency::documentAmountToUsd(
                        $factoryPriceUzs,
                        2,
                        $approvedPriceService->usdRate()
                    );
                    $clientAllocationProducts[] = [
                        'price_key' => (string) ($approvedPrices['code'] ?? $productName),
                        'name' => $productName,
                        'agent' => ($allocationRow['agent'] ?? null) ?: $fallbackAgent,
                        'qty' => (float) ($allocatedProduct['qty'] ?? 0),
                        'package_qty' => static::approvedPackageQuantity($productName),
                        'unit_price_usd' => $unitPriceUsd,
                        'unit_price_uzs' => $unitPriceUzs,
                        'factory_price_usd' => $factoryPriceUsd,
                        'factory_price_uzs' => $factoryPriceUzs,
                        'actual_unit_price_usd' => $actualUnitPriceUsd,
                        'actual_total_usd' => isset($allocatedProduct['actual_total_usd'])
                            ? (float) $allocatedProduct['actual_total_usd']
                            : null,
                    ];
                }
            }

            // Sun'iy katalog qatorlari qo'shilmaydi: oylik hisobotda faqat
            // /checkouts dagi real sotilgan mahsulotlar ko'rsatiladi.

            $clientAllocationProducts = collect($clientAllocationProducts)
                ->groupBy(fn (array $product) => $product['price_key'] . '|' . $product['agent'])
                ->map(function (Collection $sameProducts): array {
                    $product = $sameProducts->first();
                    $product['qty'] = (float) $sameProducts->sum('qty');
                    $actualQty = (float) $sameProducts
                        ->filter(fn (array $row) => $row['actual_unit_price_usd'] !== null)
                        ->sum('qty');
                    $actualTotal = (float) $sameProducts->sum(fn (array $row) =>
                        (float) ($row['actual_total_usd'] ?? 0)
                    );
                    $product['actual_unit_price_usd'] = $actualQty > 0
                        ? $actualTotal / $actualQty
                        : null;

                    return $product;
                })
                ->values()
                ->all();

            foreach ($clientAllocationProducts as $index => $allocationProduct) {
                $qty = (float) ($allocationProduct['qty'] ?? 0);
                if ($qty <= 0) {
                    continue;
                }
                $unitPriceUsd = (float) $allocationProduct['unit_price_usd'];
                $factoryPriceUsd = (float) $allocationProduct['factory_price_usd'];

                $products[] = $allocationProduct['name'];
                $agents[] = $allocationProduct['agent'];
                $quantities[] = $qty;
                $unitPrices[] = $unitPriceUsd;
                $unitPricesUzs[] = (float) $allocationProduct['unit_price_uzs'];
                $actualUnitPriceUsd = $allocationProduct['actual_unit_price_usd'] ?? null;
                $actualUnitPricesUsd[] = $actualUnitPriceUsd;
                $factoryPrices[] = $factoryPriceUsd;
                $factoryPricesUzs[] = (float) $allocationProduct['factory_price_uzs'];
                $markupPercentages[] = Currency::markupPercent($factoryPriceUsd, $unitPriceUsd);
                $actualLineTotalsUsd[] = $actualUnitPriceUsd === null
                    ? null
                    : $qty * (float) $actualUnitPriceUsd;
                $approvedTotalUsd += $qty * $unitPriceUsd;
            }

            $groupedRows[$clientKey] = [
                'dates' => collect($clientPaymentDates[$clientKey] ?? [])->unique()->values()->all(),
                'client' => $client->name ?? 'Noma\'lum mijoz',
                'client_phone' => $client->phone ?? null,
                'fallback_agent' => $fallbackAgent,
                'debt_before_payment' => 0,
                'products' => $products,
                'agents' => $agents,
                'quantities' => $quantities,
                'unit_prices' => $unitPrices,
                'unit_prices_uzs' => $unitPricesUzs,
                'actual_unit_prices_usd' => $actualUnitPricesUsd,
                'factory_prices' => $factoryPrices,
                'factory_prices_uzs' => $factoryPricesUzs,
                'markup_percentages' => $markupPercentages,
                'actual_line_totals_usd' => $actualLineTotalsUsd,
                'approved_total_usd' => $approvedTotalUsd,
                'actual_total_usd' => 0,
                'paid_usd' => (float) ($clientPayments[$clientKey] ?? 0),
                'closing_debt_usd' => (float) ($closingDebts[$clientKey] ?? 0),
                'bonus_expense_usd' => (float) ($clientBonusExpenses[$clientKey] ?? 0),
                'venox_cash_usd' => 0.0,
                'unallocated_payment_usd' => (float) ($clientUnallocatedPayments[$clientKey] ?? 0),
            ];
        }
        // Excel tartibi: sana, klient, telefon, agent, avvalgi qarz, mahsulot,
        // miqdor, sotuv narxi, zavod narxi, ustama foizi, tasdiqlangan jami,
        // zavod jami, to'langan, bonus xarajatlar, qoldiq qarz, Venox kassa.

        $rows = [];
        foreach ($groupedRows as $row) {
            // Qoldiq = avvalgi qarz + tasdiqlangan prays jami - to'langan
            // - Venox bonus. Shu tenglamadan avvalgi qarzni tiklaymiz.
            $row['debt_before_payment'] = static::openingDebtUsd(
                $row['closing_debt_usd'],
                $row['approved_total_usd'],
                $row['paid_usd'],
                $row['bonus_expense_usd']
            );
            // Venox kassasi bonus foizi emas. U aynan real sotilgan summa bilan
            // zavod tannarxi orasidagi marja. Narxi hujjatda yo'q qatorlar
            // taxminiy 0 bilan marjani buzmasligi uchun hisobga olinmaydi.
            $venoxCashUsd = static::venoxCashTotalUsd(
                $row['quantities'],
                $row['actual_unit_prices_usd'],
                $row['factory_prices']
            );
            $firstRowIndex = count($rows);
            $startRow = count($rows) + 3;

            if (empty($row['products'])) {
                $clientFormulas = static::clientExcelFormulas($startRow, $startRow);
                $rows[] = [
                    'date' => collect($row['dates'])->unique()->implode("\n"),
                    'client' => $row['client'],
                    'client_phone' => static::formatPhoneForExcel($row['client_phone']),
                    'agent' => $row['fallback_agent'] ?? '—',
                    'debt_before_payment' => $row['debt_before_payment'],
                    'product' => (float) ($row['unallocated_payment_usd'] ?? 0) > 0
                        ? 'Mahsulotga bog‘lanmagan qarz to‘lovi'
                        : 'Savdo mahsuloti topilmadi',
                    'qty' => '',
                    'unit_price_usd' => '',
                    'unit_price_usd_formula' => null,
                    'actual_unit_price_usd' => null,
                    'factory_price_usd' => null,
                    'factory_price_usd_formula' => null,
                    'markup_percent' => null,
                    'markup_percent_formula' => null,
                    'approved_total_usd' => null,
                    'approved_total_usd_formula' => null,
                    'actual_total_usd' => null,
                    'actual_total_usd_formula' => null,
                    'factory_total_usd' => null,
                    'factory_total_usd_formula' => null,
                    'paid_usd' => $row['paid_usd'],
                    'unallocated_payment_usd' => (float) ($row['unallocated_payment_usd'] ?? 0),
                    'closing_debt_usd' => $row['closing_debt_usd'],
                    'closing_debt_usd_formula' => $clientFormulas['closing_debt_usd'],
                    'bonus_expense_usd' => $row['bonus_expense_usd'],
                    'venox_cash_usd' => $venoxCashUsd,
                    'venox_cash_usd_formula' => null,
                ];

                continue;
            }

            foreach ($row['products'] as $index => $product) {
                $qty = (float) $row['quantities'][$index];
                $unitPrice = (float) $row['unit_prices'][$index];
                $unitPriceUzs = (float) $row['unit_prices_uzs'][$index];
                $actualUnitPrice = $row['actual_unit_prices_usd'][$index] ?? null;
                $factoryPrice = (float) $row['factory_prices'][$index];
                $factoryPriceUzs = (float) $row['factory_prices_uzs'][$index];
                $first = $index === 0;
                $excelRow = count($rows) + 3;
                $lineFormulas = static::lineExcelFormulas($excelRow, $unitPriceUzs, $factoryPriceUzs);

                $rows[] = [
                    'date' => $first ? collect($row['dates'])->unique()->implode("\n") : null,
                    'client' => $first ? $row['client'] : null,
                    'client_phone' => $first ? static::formatPhoneForExcel($row['client_phone']) : null,
                    'agent' => $row['agents'][$index] ?? '—',
                    'debt_before_payment' => $first ? $row['debt_before_payment'] : null,
                    'product' => $product,
                    'qty' => $qty,
                    'unit_price_usd' => $unitPrice,
                    'unit_price_usd_formula' => $lineFormulas['unit_price_usd'],
                    'actual_unit_price_usd' => $actualUnitPrice,
                    'factory_price_usd' => $factoryPrice,
                    'factory_price_usd_formula' => $lineFormulas['factory_price_usd'],
                    'markup_percent' => $row['markup_percentages'][$index],
                    'markup_percent_formula' => $lineFormulas['markup_percent'],
                    'approved_total_usd' => $qty * $unitPrice,
                    'approved_total_usd_formula' => $lineFormulas['approved_total_usd'],
                    'actual_total_usd' => $actualUnitPrice === null ? null : (float) $row['actual_line_totals_usd'][$index],
                    'actual_total_usd_formula' => $actualUnitPrice === null ? null : $lineFormulas['actual_total_usd'],
                    'factory_total_usd' => $qty * $factoryPrice,
                    'factory_total_usd_formula' => $lineFormulas['factory_total_usd'],
                    'paid_usd' => $first ? $row['paid_usd'] : null,
                    'unallocated_payment_usd' => $first
                        ? (float) ($row['unallocated_payment_usd'] ?? 0)
                        : null,
                    'closing_debt_usd' => $first ? $row['closing_debt_usd'] : null,
                    'closing_debt_usd_formula' => null,
                    'bonus_expense_usd' => $first ? $row['bonus_expense_usd'] : null,
                    'venox_cash_usd' => $first ? $venoxCashUsd : null,
                    'venox_cash_usd_formula' => null,
                ];
            }

            $endRow = count($rows) + 2;
            $clientFormulas = static::clientExcelFormulas($startRow, $endRow);
            $rows[$firstRowIndex]['closing_debt_usd_formula'] = $clientFormulas['closing_debt_usd'];
            $rows[$firstRowIndex]['venox_cash_usd_formula'] = static::venoxCashExcelFormula($startRow, $endRow);
            if ($endRow > $startRow) {
                foreach (['A', 'B', 'C', 'E', 'O', 'P', 'Q', 'R', 'S'] as $column) {
                    $this->mergeRanges[] = $column . $startRow . ':' . $column . $endRow;
                }
            }
        }

        $this->rowCount = count($rows);
        $this->rowLineCounts = array_fill(0, $this->rowCount, 1);
        $totalPaidUsd = $clientIds->sum(fn ($id) => (float) ($clientPayments[(string) $id] ?? 0));
        return view('backend.checkouts.excel_matrix', [
            'rows' => $rows,
            'periodLabel' => $periodStart->format('d.m.Y') . ' — ' . $periodEnd->format('d.m.Y'),
            'reportUsdRate' => $reportUsdRate,
            'totalPaidUzs' => static::paidTotalUzs($totalPaidUsd, $reportUsdRate),
            'totals' => [
                'debt_before_payment' => collect($groupedRows)->sum(fn ($row) =>
                    static::openingDebtUsd(
                        (float) $row['closing_debt_usd'],
                        (float) $row['approved_total_usd'],
                        (float) $row['paid_usd'],
                        (float) $row['bonus_expense_usd']
                    )
                ),
                'qty' => collect($groupedRows)->sum(fn ($row) => collect($row['quantities'])->sum()),
                'paid_usd' => $totalPaidUsd,
                'closing_debt_usd' => $clientIds->sum(fn ($id) => (float) ($closingDebts[(string) $id] ?? 0)),
                'bonus_expense_usd' => $clientIds->sum(fn ($id) => (float) ($clientBonusExpenses[(string) $id] ?? 0)),
            ],
        ]);
    }

    public static function mergeReportClientIds(iterable $checkoutClientIds, iterable $paymentClientIds): Collection
    {
        return collect($checkoutClientIds)
            ->merge(collect($paymentClientIds))
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();
    }

    /** Format an Uzbek phone number as XX XXX XX XX for the spreadsheet. */
    public static function formatPhoneForExcel($phone): string
    {
        $original = trim((string) $phone);
        $digits = preg_replace('/\D+/', '', $original);
        if (strlen($digits) === 12 && str_starts_with($digits, '998')) {
            $digits = substr($digits, 3);
        }

        if (strlen($digits) !== 9) {
            return $original;
        }

        return substr($digits, 0, 2) . ' '
            . substr($digits, 2, 3) . ' '
            . substr($digits, 5, 2) . ' '
            . substr($digits, 7, 2);
    }

    public static function venoxCashUsd(float $qty, float $saleUnitPriceUsd, float $factoryUnitPriceUsd): float
    {
        return $qty * ($saleUnitPriceUsd - $factoryUnitPriceUsd);
    }

    public static function venoxCashTotalUsd(
        iterable $quantities,
        array $saleUnitPricesUsd,
        array $factoryUnitPricesUsd
    ): float {
        $total = 0.0;
        foreach ($quantities as $index => $qty) {
            $total += static::venoxCashUsd(
                (float) $qty,
                (float) ($saleUnitPricesUsd[$index] ?? 0),
                (float) ($factoryUnitPricesUsd[$index] ?? 0)
            );
        }

        return $total;
    }

    /** Venox bonus is the displayed actual-sale total minus factory total. */
    public static function venoxCashExcelFormula(int $startRow, int $endRow): string
    {
        return sprintf(
            '=SUM(M%d:M%d)-SUM(N%d:N%d)',
            $startRow,
            $endRow,
            $startRow,
            $endRow
        );
    }

    public static function lineExcelFormulas(
        int $row,
        float $unitPriceUzs = 0,
        float $factoryPriceUzs = 0
    ): array
    {
        return [
            'unit_price_usd' => '=' . static::excelNumber($unitPriceUzs) . '/$T$2',
            'factory_price_usd' => '=' . static::excelNumber($factoryPriceUzs) . '/$T$2',
            'markup_percent' => sprintf('=IFERROR((I%d-J%d)/J%d,"")', $row, $row, $row),
            'approved_total_usd' => sprintf('=G%d*I%d', $row, $row),
            'actual_total_usd' => sprintf('=G%d*H%d', $row, $row),
            'factory_total_usd' => sprintf('=G%d*J%d', $row, $row),
        ];
    }

    /** Checkout sahifasida saqlangan haqiqiy mijoz narxini USDda qaytaradi. */
    public static function checkoutDetailUnitPriceUsd($detail, Checkout $checkout): ?float
    {
        $qty = (float) ($detail->qty ?? 0);
        if ($qty <= 0) {
            return null;
        }

        $total = (float) ($detail->total_price ?? 0);
        if ($total <= 0) {
            $total = (float) ($detail->price ?? 0) * $qty;
        }
        if ($total <= 0) {
            return null;
        }

        $currencyType = (int) ($detail->currency_type ?? $checkout->currency_type ?? 2);
        $rate = (float) ($detail->currency_type_price
            ?? $checkout->currency_type_price
            ?? Currency::usdRateForDate($checkout->date ?: $checkout->created_at));
        $unitPrice = $total / $qty;

        // Eski checkoutlarda USD narxi UZS sifatida, kurs esa 1 bilan yozilgan.
        if ($currencyType === 2 && $rate <= 1 && $unitPrice > 0 && $unitPrice < 1000) {
            return $unitPrice;
        }

        return Currency::documentAmountToUsd(
            $unitPrice,
            $currencyType,
            $rate,
            $checkout->date ?: $checkout->created_at
        );
    }

    /** Fill every report price from approved prices, then real checkout values. */
    public static function resolveReportPricesUzs(
        ?array $approvedPrices,
        ?float $actualUnitPriceUsd,
        ?float $factoryUnitPriceUsd,
        float $usdRate
    ): array {
        $fallbackSale = max(0, (float) $actualUnitPriceUsd) * $usdRate;
        $sale = (float) ($approvedPrices['sale_uzs'] ?? 0);
        $factory = (float) ($approvedPrices['factory_uzs'] ?? 0);

        if ($sale <= 0) {
            $sale = $fallbackSale;
        }
        if ($factory <= 0) {
            $factory = max(0, (float) $factoryUnitPriceUsd) * $usdRate;
        }
        // Eski checkoutda tannarx saqlanmagan bo'lsa, qatorni bo'sh/0
        // qoldirmaslik uchun real sotuv narxi eng oxirgi zaxira bo'ladi.
        if ($factory <= 0) {
            $factory = $fallbackSale;
        }

        return ['sale_uzs' => $sale, 'factory_uzs' => $factory];
    }

    private static function excelNumber(float $value): string
    {
        $number = rtrim(rtrim(sprintf('%.8F', $value), '0'), '.');

        return $number === '' ? '0' : $number;
    }

    public static function clientExcelFormulas(int $startRow, int $endRow): array
    {
        return [
            'closing_debt_usd' => sprintf(
                '=E%d+SUM(L%d:L%d)-O%d-Q%d',
                $startRow,
                $startRow,
                $endRow,
                $startRow,
                $startRow
            ),
        ];
    }

    /** Split the visible KPI + Venox bonus from the gross payment. */
    public static function paymentBreakdownUsd(
        float $grossUsd,
        float $reportBonusUsd = 0
    ): array
    {
        if ($grossUsd <= 0) {
            return ['gross_usd' => $grossUsd, 'net_usd' => $grossUsd, 'bonus_usd' => 0.0];
        }

        $bonusUsd = min($grossUsd, max(0, $reportBonusUsd));

        return [
            'gross_usd' => $grossUsd,
            'net_usd' => $grossUsd - $bonusUsd,
            'bonus_usd' => $bonusUsd,
        ];
    }

    /**
     * Keep quantities in full cartons and maximize their approved UZS total
     * without ever exceeding the client's gross cash receipts.
     */
    public static function balanceApprovedQuantities(
        iterable $quantities,
        array $approvedUnitPricesUzs,
        float $grossPaymentUzs,
        array $packageQuantities = []
    ): array {
        $quantities = array_values(collect($quantities)->map(fn ($qty) => max(0, (float) $qty))->all());
        $approvedTotal = 0.0;

        foreach ($quantities as $index => $qty) {
            $approvedTotal += $qty * max(0, (float) ($approvedUnitPricesUzs[$index] ?? 0));
        }

        if ($grossPaymentUzs <= 0 || $approvedTotal <= 0) {
            return $quantities;
        }

        // Bitta eski katta qator butun summani yutib yubormasligi uchun to'lovni
        // mahsulotlarning tarixiy karobka soni ildiziga mutanosib taqsimlaymiz.
        // Bu tarixiy tarkibni saqlaydi, lekin 95/21 kabi keskin nisbatni yumshatadi.
        $weights = [];
        foreach ($quantities as $index => $qty) {
            $package = max(1, (int) round((float) ($packageQuantities[$index] ?? 1)));
            $weights[$index] = sqrt(max(1, $qty / $package));
        }
        $weightTotal = array_sum($weights);
        $minimumFullMixTotal = 0.0;
        foreach ($quantities as $index => $qty) {
            if ($qty <= 0) {
                continue;
            }
            $package = max(1, (int) round((float) ($packageQuantities[$index] ?? 1)));
            $minimumFullMixTotal += $package * max(0, (float) ($approvedUnitPricesUzs[$index] ?? 0));
        }
        $keepEveryProduct = $minimumFullMixTotal <= $grossPaymentUzs + 0.000001;
        $scaled = [];
        foreach ($quantities as $index => $qty) {
            $price = max(0, (float) ($approvedUnitPricesUzs[$index] ?? 0));
            $scaled[$index] = $price > 0 && $weightTotal > 0
                ? ($grossPaymentUzs * $weights[$index] / $weightTotal) / $price
                : $qty;
        }
        $minimums = [];
        $packages = [];
        $balanced = [];
        foreach ($scaled as $index => $scaledQty) {
            $package = max(1, (int) round((float) ($packageQuantities[$index] ?? 1)));
            $packages[$index] = $package;
            $minimums[$index] = $keepEveryProduct && ($quantities[$index] ?? 0) > 0
                ? (float) $package
                : 0.0;
            // Floor ishlatiladi: boshlang'ich jami hech qachon to'lovdan oshmaydi.
            $balanced[$index] = (float) max(
                $minimums[$index],
                floor($scaledQty / $package) * $package
            );
        }
        $balancedTotal = 0.0;
        foreach ($balanced as $index => $qty) {
            $balancedTotal += $qty * max(0, (float) ($approvedUnitPricesUzs[$index] ?? 0));
        }

        // Karobka qo'shish yoki bir karobkani boshqa mahsulot karobkasiga
        // almashtirish orqali to'lovdan oshmaydigan eng yaqin summani topamiz.
        for ($iteration = 0; $iteration < 1000; $iteration++) {
            $currentDifference = $grossPaymentUzs - $balancedTotal;
            $bestDifference = $currentDifference;
            $bestChanges = [];

            foreach ($balanced as $index => $qty) {
                $price = max(0, (float) ($approvedUnitPricesUzs[$index] ?? 0));
                $package = $packages[$index];
                $change = $package;
                $candidateTotal = $balancedTotal + $change * $price;
                if ($price <= 0 || $candidateTotal > $grossPaymentUzs + 0.000001) {
                    continue;
                }
                $difference = $grossPaymentUzs - $candidateTotal;
                if ($difference + 0.000001 < $bestDifference) {
                    $bestDifference = $difference;
                    $bestChanges = [[$index, $change]];
                }
            }

            $count = count($balanced);
            for ($left = 0; $left < $count; $left++) {
                $leftPrice = max(0, (float) ($approvedUnitPricesUzs[$left] ?? 0));
                $leftChange = -$packages[$left];
                if ($balanced[$left] + $leftChange < $minimums[$left] || $leftPrice <= 0) {
                    continue;
                }
                for ($right = 0; $right < $count; $right++) {
                    if ($left === $right) {
                        continue;
                    }
                    $rightPrice = max(0, (float) ($approvedUnitPricesUzs[$right] ?? 0));
                    if ($rightPrice <= 0) {
                        continue;
                    }
                    $rightChange = $packages[$right];
                    $candidateTotal = (
                        $balancedTotal
                        + $leftChange * $leftPrice
                        + $rightChange * $rightPrice
                    );
                    if ($candidateTotal > $grossPaymentUzs + 0.000001) {
                        continue;
                    }
                    $difference = $grossPaymentUzs - $candidateTotal;
                    if ($difference + 0.000001 < $bestDifference) {
                        $bestDifference = $difference;
                        $bestChanges = [[$left, $leftChange], [$right, $rightChange]];
                    }
                }
            }

            if (empty($bestChanges)) {
                break;
            }
            foreach ($bestChanges as [$index, $change]) {
                $balanced[$index] += $change;
                $balancedTotal += $change * max(0, (float) ($approvedUnitPricesUzs[$index] ?? 0));
            }
        }

        return $balanced;
    }

    /** Package sizes from the supplied BOSS price workbook. */
    public static function approvedPackageQuantity(string $productName): int
    {
        $normalized = mb_strtolower($productName, 'UTF-8');
        $normalized = strtr($normalized, ['л' => 'l']);
        $normalized = preg_replace('/[^a-z0-9]+/u', '', $normalized) ?: '';

        if (str_contains($normalized, '208l') || str_contains($normalized, '20l')) {
            return 1;
        }
        if (str_contains($normalized, '1l')) {
            return 12;
        }
        if (str_contains($normalized, '3l')
            || str_contains($normalized, '4l')
            || str_contains($normalized, '5l')) {
            return 4;
        }

        return 1;
    }

    /** Both approved prices must be explicitly entered and greater than zero. */
    public static function hasCompleteApprovedPrices(?array $prices): bool
    {
        return (float) ($prices['sale_uzs'] ?? 0) > 0
            && (float) ($prices['factory_uzs'] ?? 0) > 0;
    }

    /**
     * Resolve the checkout form that supplies KPI + Venox bonus for a client.
     * The commission scheme is deliberately irrelevant: saved percentages
     * apply to every checkout type.
     */
    public static function commissionBonusCheckout($linkedCheckout, iterable $periodCheckouts, $paymentDate)
    {
        if ($linkedCheckout && static::reportBonusPercent($linkedCheckout) > 0) {
            return $linkedCheckout;
        }

        $paymentEnd = Carbon::parse($paymentDate)->endOfDay();
        $eligible = collect($periodCheckouts)
            ->filter(function (Checkout $checkout) use ($paymentEnd) {
                return Carbon::parse($checkout->date ?: $checkout->created_at)->lte($paymentEnd);
            })
            ->sortByDesc(function (Checkout $checkout) {
                return Carbon::parse($checkout->date ?: $checkout->created_at)->format('Y-m-d H:i:s')
                    . '-' . str_pad((string) $checkout->id, 12, '0', STR_PAD_LEFT);
            });

        return $eligible->first(fn (Checkout $checkout) => static::reportBonusPercent($checkout) > 0)
            ?: $linkedCheckout
            ?: $eligible->first();
    }

    /** KPI and Venox are report bonuses; Agent is intentionally excluded. */
    public static function reportBonusPercent($checkout): float
    {
        if (! $checkout) {
            return 0.0;
        }

        return min(100, max(0, (float) $checkout->kpi_percent)
            + max(0, (float) $checkout->venox_bonus_percent));
    }

    /** Calculate the report bonus from a checkout or its FIFO accounting row. */
    public static function reportBonusAmountUsd(float $grossUsd, $checkout, array $cashReportRow = []): float
    {
        if ($checkout) {
            return $grossUsd * static::reportBonusPercent($checkout) / 100;
        }

        return max(0, (float) ($cashReportRow['kpi'] ?? 0))
            + max(0, (float) ($cashReportRow['venox'] ?? 0));
    }

    /** Venox kassasi — faqat real to'lovdan Venox uchun ajratilgan ulush. */
    public static function venoxBonusAmountUsd(float $grossUsd, $checkout, array $cashReportRow = []): float
    {
        if ($checkout) {
            $percent = min(100, max(0, (float) ($checkout->venox_bonus_percent ?? 0)));

            return $grossUsd * $percent / 100;
        }

        return max(0, (float) ($cashReportRow['venox'] ?? 0));
    }

    /** Reverse the exact debt equation displayed in the spreadsheet. */
    public static function openingDebtUsd(
        float $closingDebtUsd,
        float $approvedTotalUsd,
        float $paidUsd,
        float $bonusExpenseUsd
    ): float {
        return $closingDebtUsd + $paidUsd + $bonusExpenseUsd - $approvedTotalUsd;
    }

    /** Keep approved and actual sale totals separate for report calculations. */
    public static function reportLineTotalsUsd(
        float $qty,
        float $approvedUnitPriceUsd,
        ?float $actualUnitPriceUsd
    ): array {
        return [
            'approved_total_usd' => $qty * $approvedUnitPriceUsd,
            'actual_total_usd' => $actualUnitPriceUsd === null ? null : $qty * $actualUnitPriceUsd,
        ];
    }

    /** Convert the displayed net payment total to UZS with the report rate. */
    public static function paidTotalUzs(float $paidUsd, float $usdRate): float
    {
        return $paidUsd * $usdRate;
    }

    /**
     * Convert an approved product catalogue price to the report currency.
     * The calculated document/checkin price is retained only as a fallback
     * for legacy products whose approved catalogue price is still empty.
     */
    public static function catalogUnitPriceUsd(
        float $catalogPrice,
        ?int $catalogCurrencyType,
        float $fallbackUsd,
        ?float $rate,
        $date = null
    ): float {
        if ($catalogPrice <= 0) {
            return $fallbackUsd;
        }

        // Legacy mahsulotlarda currency_type=UZS bo'lib qolgan bo'lsa ham,
        // 1000 dan kichik katalog qiymati amalda USD narxidir.
        if ($catalogCurrencyType === 2 && $catalogPrice < 1000) {
            return $catalogPrice;
        }

        return Currency::documentAmountToUsd(
            $catalogPrice,
            $catalogCurrencyType,
            $rate,
            $date
        );
    }

    private function clientDebtTotalsUsd(array $clientKeys, Carbon $periodEnd): array
    {
        $clientIds = collect($clientKeys)->filter(fn ($id) => ctype_digit((string) $id))->map(fn ($id) => (int) $id)->values();
        if ($clientIds->isEmpty()) {
            return [];
        }

        $totals = [];
        foreach (Client::whereIn('id', $clientIds)->get() as $client) {
            $totals[(string) $client->id] = Currency::documentAmountToUsd(
                (float) ($client->balance ?? 0),
                (int) ($client->currency_type ?? 2),
                (float) ($client->currency_type_price ?? 0),
                $client->created_at
            );
        }

        foreach (Checkout::with('alldetails')->whereIn('client_id', $clientIds)->whereDate('date', '<=', $periodEnd)->get() as $checkout) {
            $totals[(string) $checkout->client_id] = ($totals[(string) $checkout->client_id] ?? 0) + Currency::documentAmountToUsd(
                (float) $checkout->alldetails->sum('total_price'),
                (int) $checkout->currency_type,
                (float) $checkout->currency_type_price,
                $checkout->date ?: $checkout->created_at
            );
        }

        $accounting = app(AccountingCashReportService::class);
        foreach (CashReceipt::whereIn('client_id', $clientIds)->where('status', 1)->whereDate('date', '<=', $periodEnd)->with('checkout:id,currency_type,currency_type_price')->get() as $receipt) {
            $usd = $receipt->checkout
                ? $accounting->paymentAmountToUsd((float) $receipt->price, (int) $receipt->currency_type, (float) $receipt->currency_type_price, $receipt->checkout->currency_type, $receipt->checkout->currency_type_price)
                : $accounting->legacyUnlinkedPaymentToUsd((float) $receipt->price, (int) $receipt->currency_type, (float) ($receipt->currency_type_price ?: Currency::usdRateForDate($receipt->date ?: $receipt->created_at)), (string) $receipt->comment);
            $totals[(string) $receipt->client_id] = ($totals[(string) $receipt->client_id] ?? 0) - $usd;
        }

        foreach (Checkin::with('details')->whereIn('client_id', $clientIds)->where('type_id', 4)->whereDate('date', '<=', $periodEnd)->get() as $return) {
            $totals[(string) $return->client_id] = ($totals[(string) $return->client_id] ?? 0) - Currency::documentAmountToUsd(
                (float) $return->details->sum('total_price'),
                (int) $return->currency_type,
                (float) $return->currency_type_price,
                $return->date ?: $return->created_at
            );
        }

        return $totals;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = max(3, $this->rowCount + 3);
        $sheet->setShowGridlines(false);
        $sheet->freezePane('A3');
        $sheet->setAutoFilter('A2:S' . max(2, $this->rowCount + 2));
        $sheet->getDefaultRowDimension()->setRowHeight(44);
        $sheet->getRowDimension(1)->setRowHeight(28);
        $sheet->getRowDimension(2)->setRowHeight(48);

        foreach ($this->rowLineCounts as $index => $lineCount) {
            $sheet->getRowDimension($index + 3)->setRowHeight(max(44, $lineCount * 19));
        }

        foreach ($this->mergeRanges as $range) {
            $sheet->mergeCells($range);
        }

        foreach (['A' => 13, 'B' => 28, 'C' => 19, 'D' => 24, 'E' => 20, 'F' => 52, 'G' => 15, 'H' => 18, 'I' => 18, 'J' => 16, 'K' => 20, 'L' => 23, 'M' => 23, 'N' => 20, 'O' => 17, 'P' => 24, 'Q' => 18, 'R' => 22, 'S' => 18, 'T' => 18, 'U' => 26] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $sheet->getStyle('A1:U' . $lastRow)->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setWrapText(true);
        $sheet->getStyle('A2:S2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2:S2')->getFont()->setBold(true)->getColor()->setRGB('000000');
        $sheet->getStyle('A2:S' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('C9C9C9');
        $sheet->getStyle('G3:G' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.###');
        $sheet->getStyle('E3:E' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('H3:J' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('K3:K' . $lastRow)->getNumberFormat()->setFormatCode('0.00%');
        $sheet->getStyle('L3:S' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('A' . $lastRow . ':S' . $lastRow)->getFont()->setBold(true);
        $sheet->getStyle('T1:U2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);
        $sheet->getStyle('T1:U1')->getFont()->setBold(true);
        $sheet->getStyle('T1:U2')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('C9C9C9');
        $sheet->getStyle('T2:U2')->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('T2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF2CC');
    }
}
