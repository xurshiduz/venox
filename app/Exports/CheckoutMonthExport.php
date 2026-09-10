<?php

namespace App\Exports;

use App\Models\CashReceipt;
use App\Models\Checkin;
use App\Models\CheckinDetail;
use App\Models\Checkout;
use App\Models\Client;
use App\Models\ContractBonusTransaction;
use App\Models\Currency;
use App\Services\AccountingCashReportService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
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

        $clientIds = $checkouts->pluck('client_id')->filter()->unique()->values();
        $accounting = app(AccountingCashReportService::class);
        $clientPayments = [];

        $payments = CashReceipt::query()
            ->where('status', 1)
            ->whereIn('client_id', $clientIds)
            ->whereBetween('date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->with('checkout:id,currency_type,currency_type_price')
            ->get();

        foreach ($payments as $payment) {
            $key = (string) $payment->client_id;
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
            $clientPayments[$key] = ($clientPayments[$key] ?? 0) + $usd;
        }

        $closingDebts = $this->clientDebtTotalsUsd($clientIds->map(fn ($id) => (string) $id)->all(), $periodEnd);
        $clientBonusExpenses = ContractBonusTransaction::query()
            ->where('status', true)
            ->where('direction', 'debit')
            ->whereIn('client_id', $clientIds)
            ->whereDate('transaction_date', '>=', $periodStart->toDateString())
            ->whereDate('transaction_date', '<=', $periodEnd->toDateString())
            ->selectRaw('client_id, SUM(amount_usd) as total_amount')
            ->groupBy('client_id')
            ->pluck('total_amount', 'client_id');
        $groupedRows = [];

        foreach ($checkouts as $checkout) {
            $clientKey = (string) $checkout->client_id;
            $paid = (float) ($clientPayments[$clientKey] ?? 0);
            $closing = (float) ($closingDebts[$clientKey] ?? 0);

            if (!isset($groupedRows[$clientKey])) {
                $groupedRows[$clientKey] = [
                    'dates' => [],
                    'client' => $checkout->supid->name ?? 'Noma\'lum mijoz',
                    'client_phone' => $checkout->supid->phone ?? null,
                    'debt_before_payment' => $closing + $paid,
                    'products' => [],
                    'agents' => [],
                    'quantities' => [],
                    'unit_prices' => [],
                    'factory_prices' => [],
                    'markup_percentages' => [],
                    'total_usd' => 0,
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
                $unitPriceUsd = $qty != 0 ? $totalUsd / $qty : 0;

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

                $factoryPriceUsd = 0;
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
                    $factoryPriceUsd = Currency::documentAmountToUsd(
                        $rawCheckinUnit,
                        (int) ($latestCheckin->currency_type ?? optional($checkin)->currency_type ?? 2),
                        (float) ($latestCheckin->currency_type_price ?: $checkinRate),
                        optional($checkin)->date ?? $latestCheckin->created_at
                    );
                }

                // Har bir hujjat o'z sanasida saqlangan kurs bo'yicha USDga o'tadi.
                // Bu tarixiy UZS va USD narxlarini taxminsiz, bir valyutada solishtiradi.
                $unitPriceUsd = Currency::documentAmountToUsd(
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
                    && $factoryPriceUsd > 0) {
                    $unitPriceUsd = $rawSaleUnit;
                }
                $totalUsd = $unitPriceUsd * $qty;
                $markupPercent = Currency::markupPercent($factoryPriceUsd, $unitPriceUsd);

                $groupedRows[$clientKey]['products'][] = $detail->prodid->name ?? 'Noma\'lum mahsulot';
                $groupedRows[$clientKey]['agents'][] = $checkout->managerid->name ?? '—';
                $groupedRows[$clientKey]['quantities'][] = $qty;
                $groupedRows[$clientKey]['unit_prices'][] = $unitPriceUsd;
                $groupedRows[$clientKey]['factory_prices'][] = $factoryPriceUsd;
                $groupedRows[$clientKey]['markup_percentages'][] = $markupPercent;
                $groupedRows[$clientKey]['total_usd'] += $totalUsd;
            }
        }

        $rows = [];
        foreach ($groupedRows as $row) {
            $startRow = count($rows) + 3;
            foreach ($row['products'] as $index => $product) {
                $qty = (float) $row['quantities'][$index];
                $unitPrice = (float) $row['unit_prices'][$index];
                $factoryPrice = (float) $row['factory_prices'][$index];
                $first = $index === 0;

                $rows[] = [
                    'date' => $first ? collect($row['dates'])->unique()->implode("\n") : null,
                    'client' => $first ? $row['client'] : null,
                    'client_phone' => $first ? $row['client_phone'] : null,
                    'agent' => $row['agents'][$index] ?? '—',
                    'debt_before_payment' => $first ? $row['debt_before_payment'] : null,
                    'product' => $product,
                    'qty' => $qty,
                    'unit_price_usd' => $unitPrice,
                    'factory_price_usd' => $factoryPrice,
                    'markup_percent' => $row['markup_percentages'][$index],
                    'total_usd' => $qty * $unitPrice,
                    'paid_usd' => $first ? $row['paid_usd'] : null,
                    'closing_debt_usd' => $first ? $row['closing_debt_usd'] : null,
                    'bonus_expense_usd' => $first ? $row['bonus_expense_usd'] : null,
                ];
            }

            $endRow = count($rows) + 2;
            if ($endRow > $startRow) {
                foreach (['A', 'B', 'C', 'E', 'L', 'M', 'N'] as $column) {
                    $this->mergeRanges[] = $column . $startRow . ':' . $column . $endRow;
                }
            }
        }

        $this->rowCount = count($rows);
        $this->rowLineCounts = array_fill(0, $this->rowCount, 1);

        return view('backend.checkouts.excel_matrix', [
            'rows' => $rows,
            'periodLabel' => $periodStart->format('d.m.Y') . ' — ' . $periodEnd->format('d.m.Y'),
            'totals' => [
                'debt_before_payment' => $clientIds->sum(fn ($id) => (float) ($closingDebts[(string) $id] ?? 0) + (float) ($clientPayments[(string) $id] ?? 0)),
                'qty' => collect($groupedRows)->sum(fn ($row) => collect($row['quantities'])->sum()),
                'paid_usd' => $clientIds->sum(fn ($id) => (float) ($clientPayments[(string) $id] ?? 0)),
                'closing_debt_usd' => $clientIds->sum(fn ($id) => (float) ($closingDebts[(string) $id] ?? 0)),
                'bonus_expense_usd' => $clientIds->sum(fn ($id) => (float) ($clientBonusExpenses[(string) $id] ?? 0)),
            ],
        ]);
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
        $sheet->setAutoFilter('A2:N' . max(2, $this->rowCount + 2));
        $sheet->getDefaultRowDimension()->setRowHeight(44);
        $sheet->getRowDimension(1)->setRowHeight(28);
        $sheet->getRowDimension(2)->setRowHeight(48);

        foreach ($this->rowLineCounts as $index => $lineCount) {
            $sheet->getRowDimension($index + 3)->setRowHeight(max(44, $lineCount * 19));
        }

        foreach ($this->mergeRanges as $range) {
            $sheet->mergeCells($range);
        }

        foreach (['A' => 13, 'B' => 28, 'C' => 19, 'D' => 24, 'E' => 20, 'F' => 52, 'G' => 15, 'H' => 16, 'I' => 16, 'J' => 20, 'K' => 18, 'L' => 17, 'M' => 18, 'N' => 22] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $sheet->getStyle('A1:N' . $lastRow)->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setWrapText(true);
        $sheet->getStyle('A2:N2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2:N2')->getFont()->setBold(true)->getColor()->setRGB('000000');
        $sheet->getStyle('A2:N' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('C9C9C9');
        $sheet->getStyle('G3:G' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.###');
        $sheet->getStyle('E3:E' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('H3:I' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('J3:J' . $lastRow)->getNumberFormat()->setFormatCode('0.00%');
        $sheet->getStyle('K3:N' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('A' . $lastRow . ':N' . $lastRow)->getFont()->setBold(true);
    }
}
