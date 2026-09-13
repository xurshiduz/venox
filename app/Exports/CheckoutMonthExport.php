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
        $periodCheckoutsByClient = $checkouts->groupBy(fn (Checkout $checkout) => (string) $checkout->client_id);

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

        $payments = CashReceipt::query()
            ->where('status', 1)
            ->whereDate('date', '>=', $periodStart->toDateString())
            ->whereDate('date', '<=', $periodEnd->toDateString())
            ->with('checkout:id,client_id,currency_type,currency_type_price,venox_bonus_percent')
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $paymentClientIds = $payments->map(function ($payment) {
            return $payment->client_id ?: optional($payment->checkout)->client_id;
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
                'include_purchase_cost' => false,
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
            $commissionCheckout = static::venoxBonusCheckout(
                $payment->checkout,
                $periodCheckoutsByClient->get($key, []),
                $payment->date ?: $payment->created_at
            );

            // Bog'lanmagan "za dolg" to'lovi uchun ham shu davrdagi eng yaqin
            // checkout formasidagi Venox foizi FIFO tarixidan ustun turadi.
            $venoxBonusUsd = $commissionCheckout
                ? $usd * (float) $commissionCheckout->venox_bonus_percent / 100
                : (float) ($cashReportRow['venox'] ?? 0);
            $paymentBreakdown = static::paymentBreakdownUsd(
                $usd,
                $venoxBonusUsd
            );
            $clientPayments[$key] = ($clientPayments[$key] ?? 0) + $paymentBreakdown['net_usd'];
            $paymentBonusExpensesByClient[$key] = ($paymentBonusExpensesByClient[$key] ?? 0) + $paymentBreakdown['bonus_usd'];
            $clientPaymentDates[$key][] = Carbon::parse($payment->date ?: $payment->created_at)->format('d.m.Y');
        }

        $closingDebts = $this->clientDebtTotalsUsd($clientIds->map(fn ($id) => (string) $id)->all(), $periodEnd);
        // Bu hisobotdagi bonus xarajatlari faqat checkout formasida saqlangan
        // Venox bonus foizidan olinadi. KPI va Agent bu ustunga kirmaydi.
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
                    // Davr boshidagi qarz keyinroq, ushbu davrdagi sotuvlar
                    // yig'indisi aniqlangach hisoblanadi.
                    'debt_before_payment' => 0,
                    'products' => [],
                    'agents' => [],
                    'quantities' => [],
                    'unit_prices' => [],
                    'unit_prices_uzs' => [],
                    'factory_prices' => [],
                    'factory_prices_uzs' => [],
                    'markup_percentages' => [],
                    'actual_line_totals_usd' => [],
                    'approved_total_usd' => 0,
                    'actual_total_usd' => 0,
                    'paid_usd' => $paid,
                    'closing_debt_usd' => $closing,
                    'bonus_expense_usd' => (float) ($clientBonusExpenses[$clientKey] ?? 0),
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
                $actualUnitPriceUsd = $qty != 0 ? $totalUsd / $qty : 0;

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
                $actualUnitPriceUsd = Currency::documentAmountToUsd(
                    $rawSaleUnit,
                    (int) $checkout->currency_type,
                    $saleRate,
                    $checkout->date ?: $checkout->created_at
                );
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
                if (! static::hasCompleteApprovedPrices($approvedPrices)) {
                    continue;
                }
                $unitPriceUzs = (float) $approvedPrices['sale_uzs'];
                $factoryPriceUzs = (float) $approvedPrices['factory_uzs'];
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
            $factoryPrices = [];
            $factoryPricesUzs = [];
            $markupPercentages = [];
            $actualLineTotalsUsd = [];
            $approvedTotalUsd = 0;
            $clientAllocationProducts = [];

            foreach ($paymentAllocationRows->get($clientKey, collect()) as $allocationRow) {
                foreach ($allocationRow['products'] ?? [] as $allocatedProduct) {
                    $productName = (string) ($allocatedProduct['name'] ?? 'Noma\'lum mahsulot');
                    $approvedPrices = $approvedPriceService->pricesFor($productName);
                    if (! static::hasCompleteApprovedPrices($approvedPrices)) {
                        continue;
                    }
                    $unitPriceUzs = (float) $approvedPrices['sale_uzs'];
                    $factoryPriceUzs = (float) $approvedPrices['factory_uzs'];
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
                        'name' => $productName,
                        'agent' => $allocationRow['agent'] ?? '—',
                        'qty' => (float) ($allocatedProduct['qty'] ?? 0),
                        'unit_price_usd' => $unitPriceUsd,
                        'unit_price_uzs' => $unitPriceUzs,
                        'factory_price_usd' => $factoryPriceUsd,
                        'factory_price_uzs' => $factoryPriceUzs,
                    ];
                }
            }

            // Har bir mijoz kesimida tasdiqlangan jami gross to'lovga eng yaqin
            // bo'lsin. Gross = Excelda ko'rinadigan net to'lov + Venox bonus.
            $grossClientPaymentUzs = (
                (float) ($clientPayments[$clientKey] ?? 0)
                + (float) ($clientBonusExpenses[$clientKey] ?? 0)
            ) * $reportUsdRate;
            $balancedQuantities = static::balanceApprovedQuantities(
                collect($clientAllocationProducts)->pluck('qty')->all(),
                collect($clientAllocationProducts)->pluck('unit_price_uzs')->all(),
                $grossClientPaymentUzs
            );

            foreach ($clientAllocationProducts as $index => $allocationProduct) {
                $qty = (float) ($balancedQuantities[$index] ?? 0);
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
                $factoryPrices[] = $factoryPriceUsd;
                $factoryPricesUzs[] = (float) $allocationProduct['factory_price_uzs'];
                $markupPercentages[] = Currency::markupPercent($factoryPriceUsd, $unitPriceUsd);
                $actualLineTotalsUsd[] = null;
                $approvedTotalUsd += $qty * $unitPriceUsd;
            }

            $groupedRows[$clientKey] = [
                'dates' => collect($clientPaymentDates[$clientKey] ?? [])->unique()->values()->all(),
                'client' => $client->name ?? 'Noma\'lum mijoz',
                'client_phone' => $client->phone ?? null,
                'debt_before_payment' => 0,
                'products' => $products,
                'agents' => $agents,
                'quantities' => $quantities,
                'unit_prices' => $unitPrices,
                'unit_prices_uzs' => $unitPricesUzs,
                'factory_prices' => $factoryPrices,
                'factory_prices_uzs' => $factoryPricesUzs,
                'markup_percentages' => $markupPercentages,
                'actual_line_totals_usd' => $actualLineTotalsUsd,
                'approved_total_usd' => $approvedTotalUsd,
                'actual_total_usd' => 0,
                'paid_usd' => (float) ($clientPayments[$clientKey] ?? 0),
                'closing_debt_usd' => (float) ($closingDebts[$clientKey] ?? 0),
                'bonus_expense_usd' => (float) ($clientBonusExpenses[$clientKey] ?? 0),
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
            $venoxCashUsd = static::venoxCashTotalUsd(
                $row['quantities'],
                $row['unit_prices'],
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
                    'agent' => '—',
                    'debt_before_payment' => $row['debt_before_payment'],
                    'product' => '',
                    'qty' => '',
                    'unit_price_usd' => '',
                    'unit_price_usd_formula' => null,
                    'factory_price_usd' => null,
                    'factory_price_usd_formula' => null,
                    'markup_percent' => null,
                    'markup_percent_formula' => null,
                    'approved_total_usd' => null,
                    'approved_total_usd_formula' => null,
                    'factory_total_usd' => null,
                    'factory_total_usd_formula' => null,
                    'paid_usd' => $row['paid_usd'],
                    'closing_debt_usd' => $row['closing_debt_usd'],
                    'closing_debt_usd_formula' => $clientFormulas['closing_debt_usd'],
                    'bonus_expense_usd' => $row['bonus_expense_usd'],
                    'venox_cash_usd' => null,
                    'venox_cash_usd_formula' => null,
                ];

                continue;
            }

            foreach ($row['products'] as $index => $product) {
                $qty = (float) $row['quantities'][$index];
                $unitPrice = (float) $row['unit_prices'][$index];
                $unitPriceUzs = (float) $row['unit_prices_uzs'][$index];
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
                    'factory_price_usd' => $factoryPrice,
                    'factory_price_usd_formula' => $lineFormulas['factory_price_usd'],
                    'markup_percent' => $row['markup_percentages'][$index],
                    'markup_percent_formula' => $lineFormulas['markup_percent'],
                    'approved_total_usd' => $qty * $unitPrice,
                    'approved_total_usd_formula' => $lineFormulas['approved_total_usd'],
                    'factory_total_usd' => $qty * $factoryPrice,
                    'factory_total_usd_formula' => $lineFormulas['factory_total_usd'],
                    'paid_usd' => $first ? $row['paid_usd'] : null,
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
            $rows[$firstRowIndex]['venox_cash_usd_formula'] = $clientFormulas['venox_cash_usd'];
            if ($endRow > $startRow) {
                foreach (['A', 'B', 'C', 'E', 'M', 'N', 'O', 'P'] as $column) {
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

    public static function lineExcelFormulas(
        int $row,
        float $unitPriceUzs = 0,
        float $factoryPriceUzs = 0
    ): array
    {
        return [
            'unit_price_usd' => '=' . static::excelNumber($unitPriceUzs) . '/$Q$2',
            'factory_price_usd' => '=' . static::excelNumber($factoryPriceUzs) . '/$Q$2',
            'markup_percent' => sprintf('=IFERROR((H%d-I%d)/I%d,"")', $row, $row, $row),
            'approved_total_usd' => sprintf('=G%d*H%d', $row, $row),
            'factory_total_usd' => sprintf('=G%d*I%d', $row, $row),
        ];
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
                '=E%d+SUM(K%d:K%d)-M%d-N%d',
                $startRow,
                $startRow,
                $endRow,
                $startRow,
                $startRow
            ),
            'venox_cash_usd' => sprintf(
                '=SUM(K%d:K%d)-SUM(L%d:L%d)',
                $startRow,
                $endRow,
                $startRow,
                $endRow
            ),
        ];
    }

    /**
     * Only the checkout form's Venox bonus is shown as a bonus expense.
     * KPI and Agent commission are intentionally excluded.
     */
    public static function paymentBreakdownUsd(
        float $grossUsd,
        float $venoxBonusUsd = 0
    ): array
    {
        if ($grossUsd <= 0) {
            return ['gross_usd' => $grossUsd, 'net_usd' => $grossUsd, 'bonus_usd' => 0.0];
        }

        $bonusUsd = min($grossUsd, max(0, $venoxBonusUsd));

        return [
            'gross_usd' => $grossUsd,
            'net_usd' => $grossUsd - $bonusUsd,
            'bonus_usd' => $bonusUsd,
        ];
    }

    /**
     * Keep quantities as whole pieces and make their approved UZS total as
     * close as possible to the client's gross cash receipts.
     */
    public static function balanceApprovedQuantities(
        iterable $quantities,
        array $approvedUnitPricesUzs,
        float $grossPaymentUzs
    ): array {
        $quantities = array_values(collect($quantities)->map(fn ($qty) => max(0, (float) $qty))->all());
        $approvedTotal = 0.0;

        foreach ($quantities as $index => $qty) {
            $approvedTotal += $qty * max(0, (float) ($approvedUnitPricesUzs[$index] ?? 0));
        }

        if ($grossPaymentUzs <= 0 || $approvedTotal <= 0) {
            return $quantities;
        }

        $factor = $grossPaymentUzs / $approvedTotal;
        $scaled = array_map(fn (float $qty) => $qty * $factor, $quantities);
        $balanced = array_map(fn (float $qty) => (float) round($qty), $scaled);
        $balancedTotal = 0.0;
        foreach ($balanced as $index => $qty) {
            $balancedTotal += $qty * max(0, (float) ($approvedUnitPricesUzs[$index] ?? 0));
        }

        // Bir dona qo'shish/ayirish va ikki mahsulotni o'zaro almashtirish orqali
        // eng yaqin butun kombinatsiyani topamiz. Har qadam farqni kamaytiradi.
        for ($iteration = 0; $iteration < 1000; $iteration++) {
            $currentDifference = abs($grossPaymentUzs - $balancedTotal);
            $bestDifference = $currentDifference;
            $bestChanges = [];

            foreach ($balanced as $index => $qty) {
                $price = max(0, (float) ($approvedUnitPricesUzs[$index] ?? 0));
                foreach ([-1, 1] as $change) {
                    if ($price <= 0 || $qty + $change < 0) {
                        continue;
                    }
                    $difference = abs($grossPaymentUzs - ($balancedTotal + $change * $price));
                    if ($difference + 0.000001 < $bestDifference) {
                        $bestDifference = $difference;
                        $bestChanges = [[$index, $change]];
                    }
                }
            }

            $count = count($balanced);
            for ($left = 0; $left < $count; $left++) {
                $leftPrice = max(0, (float) ($approvedUnitPricesUzs[$left] ?? 0));
                if ($balanced[$left] <= 0 || $leftPrice <= 0) {
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
                    $difference = abs($grossPaymentUzs - ($balancedTotal - $leftPrice + $rightPrice));
                    if ($difference + 0.000001 < $bestDifference) {
                        $bestDifference = $difference;
                        $bestChanges = [[$left, -1], [$right, 1]];
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

    /** Both approved prices must be explicitly entered and greater than zero. */
    public static function hasCompleteApprovedPrices(?array $prices): bool
    {
        return (float) ($prices['sale_uzs'] ?? 0) > 0
            && (float) ($prices['factory_uzs'] ?? 0) > 0;
    }

    /**
     * Resolve the checkout form that supplies Venox bonus for any client.
     * A saved positive bonus in the selected period takes precedence over a
     * linked legacy checkout whose bonus is still zero.
     */
    public static function venoxBonusCheckout($linkedCheckout, iterable $periodCheckouts, $paymentDate)
    {
        if ($linkedCheckout && (float) $linkedCheckout->venox_bonus_percent > 0) {
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

        return $eligible->first(fn (Checkout $checkout) => (float) $checkout->venox_bonus_percent > 0)
            ?: $linkedCheckout
            ?: $eligible->first();
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
        $sheet->setAutoFilter('A2:P' . max(2, $this->rowCount + 2));
        $sheet->getDefaultRowDimension()->setRowHeight(44);
        $sheet->getRowDimension(1)->setRowHeight(28);
        $sheet->getRowDimension(2)->setRowHeight(48);

        foreach ($this->rowLineCounts as $index => $lineCount) {
            $sheet->getRowDimension($index + 3)->setRowHeight(max(44, $lineCount * 19));
        }

        foreach ($this->mergeRanges as $range) {
            $sheet->mergeCells($range);
        }

        foreach (['A' => 13, 'B' => 28, 'C' => 19, 'D' => 24, 'E' => 20, 'F' => 52, 'G' => 15, 'H' => 16, 'I' => 16, 'J' => 20, 'K' => 23, 'L' => 20, 'M' => 17, 'N' => 18, 'O' => 22, 'P' => 18, 'Q' => 18, 'R' => 26] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $sheet->getStyle('A1:R' . $lastRow)->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setWrapText(true);
        $sheet->getStyle('A2:P2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2:P2')->getFont()->setBold(true)->getColor()->setRGB('000000');
        $sheet->getStyle('A2:P' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('C9C9C9');
        $sheet->getStyle('G3:G' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.###');
        $sheet->getStyle('E3:E' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('H3:I' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('J3:J' . $lastRow)->getNumberFormat()->setFormatCode('0.00%');
        $sheet->getStyle('K3:P' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('A' . $lastRow . ':P' . $lastRow)->getFont()->setBold(true);
        $sheet->getStyle('Q1:R2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);
        $sheet->getStyle('Q1:R1')->getFont()->setBold(true);
        $sheet->getStyle('Q1:R2')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('C9C9C9');
        $sheet->getStyle('Q2:R2')->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('Q2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF2CC');
    }
}
