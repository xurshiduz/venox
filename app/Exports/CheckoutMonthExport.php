<?php

namespace App\Exports;

use App\Models\Checkout;
use App\Models\CashReceipt;
use App\Models\Currency;
use App\Models\Client;
use App\Models\Checkin;
use App\Services\AccountingCashReportService;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Carbon\Carbon;

class CheckoutMonthExport implements FromView, WithStyles
{
    protected $monthYear;
    protected $productCount = 0;
    protected $clientCount = 0;

    public function __construct($monthYear)
    {
        $this->monthYear = $monthYear;
    }

    public function view(): View
    {
        $date = Carbon::parse($this->monthYear);
        $year = $date->year;
        $month = $date->month;

        $periodStart = $date->copy()->startOfMonth();
        $periodEnd = $date->copy()->endOfMonth();

        $checkouts = Checkout::with(['supid', 'checkoutDetails.prodid'])
            ->whereYear('date', $year) 
            ->whereMonth('date', $month)
            ->get();

        $productsList = []; 
        $matrixData = [];   
        
        $productTotalUsd = [];
        $clientTotalUsd = [];

        // Kassa kirimlari shu oyning sanasi va mijoz ID-si bo'yicha hisoblanadi.
        // status=0 bo'lgan (bekor qilingan) to'lovlar hisobotga kiritilmaydi.
        $payments = CashReceipt::query()
            ->where('status', 1)
            ->whereBetween('date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->with('checkout:id,currency_type,currency_type_price')
            ->get(['id', 'checkout_id', 'client_id', 'price', 'currency_type', 'currency_type_price', 'comment', 'date', 'created_at']);

        $accounting = app(AccountingCashReportService::class);
        $clientPayments = [];
        foreach ($payments as $payment) {
            $clientKey = (string) $payment->client_id;
            $paymentUsd = $payment->checkout
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
            $clientPayments[$clientKey] = ($clientPayments[$clientKey] ?? 0) + $paymentUsd;
        }

        $clientNames = [];
        foreach ($checkouts as $checkout) {
            $clientName = $checkout->supid->name ?? 'Noma\'lum mijoz';
            $clientKey = (string) ($checkout->client_id ?? 'unknown-' . $clientName);
            $clientNames[$clientKey] = $clientName;
            
            // Valyuta tipi va kursi (Agar 1 bo'lsa kursini olamiz, yo'qsa 0)
            $cType = $checkout->currency_type;
            $cRate = $checkout->currency_type_price ?? 0;

            if (!isset($matrixData[$clientKey])) {
                $matrixData[$clientKey] = [];
                $clientTotalUsd[$clientKey] = 0;
            }

            foreach ($checkout->checkoutDetails as $detail) {
                $productName = $detail->prodid->name ?? 'Noma\'lum mahsulot';
                
                $productsList[$productName] = $productName;

                // 1. Mijoz va tovar kesishmasida MIQDOR (qty)
                if (!isset($matrixData[$clientKey][$productName])) {
                    $matrixData[$clientKey][$productName] = 0;
                }
                $matrixData[$clientKey][$productName] += $detail->qty;

                // 2. Narxni hisoblash (Asosiy narxni aniqlaymiz)
                $basePriceTotal = $detail->price_total ?? ($detail->price * $detail->qty);

                $usd = Currency::documentAmountToUsd(
                    (float) $basePriceTotal,
                    (int) $cType,
                    (float) $cRate,
                    $checkout->date ?: $checkout->created_at
                );

                // Qator bo'yicha summa (Mijozning jami So'm va Dollari)
                $clientTotalUsd[$clientKey] += $usd;

                // Ustun bo'yicha summa (Tovarning jami So'm va Dollari)
                if (!isset($productTotalUsd[$productName])) {
                    $productTotalUsd[$productName] = 0;
                }
                $productTotalUsd[$productName] += $usd;
            }
        }

        $clientPaidTotals = [];
        $clientDebtTotals = $this->clientDebtTotalsUsd(array_keys($matrixData), $periodEnd);
        foreach (array_keys($matrixData) as $clientKey) {
            $clientPaidTotals[$clientKey] = (float) ($clientPayments[(string) $clientKey] ?? 0);
            $clientDebtTotals[$clientKey] = (float) ($clientDebtTotals[(string) $clientKey] ?? 0);
        }

        ksort($productsList);
        $this->productCount = count($productsList);
        $this->clientCount = count($matrixData);

        $firstDataRow = 3;
        $lastDataRow = $firstDataRow + $this->clientCount - 1;
        $debtColumn = Coordinate::stringFromColumnIndex(3);
        $salesColumn = Coordinate::stringFromColumnIndex($this->productCount + 4);
        $paidColumn = Coordinate::stringFromColumnIndex($this->productCount + 5);
        $productTotalFormulas = [];
        foreach (array_keys($productsList) as $index => $productName) {
            $productColumn = Coordinate::stringFromColumnIndex($index + 4);
            $productTotalFormulas[$productName] = $this->clientCount > 0
                ? '=SUM(' . $productColumn . $firstDataRow . ':' . $productColumn . $lastDataRow . ')'
                : 0;
        }
        $grandSalesFormula = $this->clientCount > 0
            ? '=SUM(' . $salesColumn . $firstDataRow . ':' . $salesColumn . $lastDataRow . ')'
            : 0;
        $grandPaidFormula = $this->clientCount > 0
            ? '=SUM(' . $paidColumn . $firstDataRow . ':' . $paidColumn . $lastDataRow . ')'
            : 0;
        $grandDebtFormula = $this->clientCount > 0
            ? '=SUM(' . $debtColumn . $firstDataRow . ':' . $debtColumn . $lastDataRow . ')'
            : 0;

        return view('backend.checkouts.excel_matrix', [
            'productsList'    => $productsList,
            'matrixData'      => $matrixData,
            'clientNames'     => $clientNames,
            'productTotalUsd' => $productTotalUsd,
            'productTotalFormulas' => $productTotalFormulas,
            'clientTotalUsd'  => $clientTotalUsd,
            'clientPaidTotals'=> $clientPaidTotals,
            'clientDebtTotals'=> $clientDebtTotals,
            'grandDebtFormula' => $grandDebtFormula,
            'grandSalesFormula' => $grandSalesFormula,
            'grandPaidFormula' => $grandPaidFormula,
            'monthYear'       => $this->monthYear
        ]);
    }

    /**
     * Tanlangan oy oxiridagi mijoz qarzini barcha hujjatlarni USDga keltirib hisoblaydi.
     */
    private function clientDebtTotalsUsd(array $clientKeys, Carbon $periodEnd): array
    {
        $clientIds = collect($clientKeys)->filter(fn ($id) => ctype_digit((string) $id))->map(fn ($id) => (int) $id)->values();

        if ($clientIds->isEmpty()) {
            return [];
        }

        $clients = Client::whereIn('id', $clientIds)->get()->keyBy('id');
        $totals = [];

        foreach ($clients as $client) {
            $totals[(string) $client->id] = Currency::documentAmountToUsd(
                (float) ($client->balance ?? 0),
                (int) ($client->currency_type ?? 2),
                (float) ($client->currency_type_price ?? 0),
                $client->created_at
            );
        }

        $sales = Checkout::with('alldetails')
            ->whereIn('client_id', $clientIds)
            ->whereDate('date', '<=', $periodEnd->toDateString())
            ->get();

        foreach ($sales as $checkout) {
            $amount = (float) $checkout->alldetails->sum('total_price');
            $totals[(string) $checkout->client_id] += Currency::documentAmountToUsd(
                $amount,
                (int) $checkout->currency_type,
                (float) $checkout->currency_type_price,
                $checkout->date ?: $checkout->created_at
            );
        }

        $receipts = CashReceipt::whereIn('client_id', $clientIds)
            ->where('status', 1)
            ->whereDate('date', '<=', $periodEnd->toDateString())
            ->with('checkout:id,currency_type,currency_type_price')
            ->get();

        $accounting = app(AccountingCashReportService::class);
        foreach ($receipts as $receipt) {
            $receiptUsd = $receipt->checkout
                ? $accounting->paymentAmountToUsd(
                    (float) $receipt->price,
                    (int) $receipt->currency_type,
                    (float) $receipt->currency_type_price,
                    $receipt->checkout->currency_type !== null ? (int) $receipt->checkout->currency_type : null,
                    $receipt->checkout->currency_type_price !== null ? (float) $receipt->checkout->currency_type_price : null
                )
                : $accounting->legacyUnlinkedPaymentToUsd(
                    (float) $receipt->price,
                    (int) $receipt->currency_type,
                    (float) ($receipt->currency_type_price ?: Currency::usdRateForDate($receipt->date ?: $receipt->created_at)),
                    (string) $receipt->comment
                );
            $totals[(string) $receipt->client_id] -= $receiptUsd;
        }

        $returns = Checkin::with('details')
            ->whereIn('client_id', $clientIds)
            ->where('type_id', 4)
            ->whereDate('date', '<=', $periodEnd->toDateString())
            ->get();

        foreach ($returns as $return) {
            $amount = (float) $return->details->sum('total_price');
            $totals[(string) $return->client_id] -= Currency::documentAmountToUsd(
                $amount,
                (int) $return->currency_type,
                (float) $return->currency_type_price,
                $return->date ?: $return->created_at
            );
        }

        return $totals;
    }

    public function styles(Worksheet $sheet)
    {
        $highestColumn = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow(); // Jadvaldagi eng oxirgi qatorni aniqlaymiz

        // 1. BUTUN jadvalni vertikal bo'yicha markazga joylash
        $sheet->getStyle('A1:' . $highestColumn . $highestRow)
              ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // 2. BUTUN jadvalda matn sig'masa pastga tushirish (Wrap Text)
        $sheet->getStyle('A1:' . $highestColumn . $highestRow)
              ->getAlignment()->setWrapText(true);

        // 3. Faqat sarlavha (1 va 2-qator)larni gorizontal markazga joylash
        $sheet->getStyle('A1:' . $highestColumn . '2')
              ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $summaryRow = $this->clientCount + 3;
        $debtColumn = Coordinate::stringFromColumnIndex(3);
        $salesColumn = Coordinate::stringFromColumnIndex($this->productCount + 4);
        $paidColumn = Coordinate::stringFromColumnIndex($this->productCount + 5);

        $sheet->getStyle($debtColumn . '3:' . $debtColumn . $summaryRow)
            ->getNumberFormat()->setFormatCode('$#,##0.00');
        $sheet->getStyle($salesColumn . '3:' . $paidColumn . $summaryRow)
            ->getNumberFormat()->setFormatCode('$#,##0.00');

        if ($this->productCount > 0) {
            $firstProductColumn = Coordinate::stringFromColumnIndex(4);
            $lastProductColumn = Coordinate::stringFromColumnIndex($this->productCount + 3);
            $sheet->getStyle($firstProductColumn . $summaryRow . ':' . $lastProductColumn . $summaryRow)
                ->getNumberFormat()->setFormatCode('$#,##0.00');
        }
    }
}
