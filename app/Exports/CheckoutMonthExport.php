<?php

namespace App\Exports;

use App\Models\Checkout;
use App\Models\CashReceipt;
use App\Models\Currency;
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
            ->get(['client_id', 'price', 'currency_type', 'currency_type_price', 'date', 'created_at']);

        $clientPayments = [];
        foreach ($payments as $payment) {
            $clientKey = (string) $payment->client_id;
            $clientPayments[$clientKey] = ($clientPayments[$clientKey] ?? 0) + Currency::documentAmountToUsd(
                (float) $payment->price,
                (int) $payment->currency_type,
                (float) $payment->currency_type_price,
                $payment->date ?: $payment->created_at
            );
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
        foreach (array_keys($matrixData) as $clientKey) {
            $clientPaidTotals[$clientKey] = (float) ($clientPayments[(string) $clientKey] ?? 0);
        }

        ksort($productsList);
        $this->productCount = count($productsList);
        $this->clientCount = count($matrixData);

        $firstDataRow = 3;
        $lastDataRow = $firstDataRow + $this->clientCount - 1;
        $salesColumn = Coordinate::stringFromColumnIndex($this->productCount + 3);
        $paidColumn = Coordinate::stringFromColumnIndex($this->productCount + 4);
        $productTotalFormulas = [];
        foreach (array_keys($productsList) as $index => $productName) {
            $productColumn = Coordinate::stringFromColumnIndex($index + 3);
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

        return view('backend.checkouts.excel_matrix', [
            'productsList'    => $productsList,
            'matrixData'      => $matrixData,
            'clientNames'     => $clientNames,
            'productTotalUsd' => $productTotalUsd,
            'productTotalFormulas' => $productTotalFormulas,
            'clientTotalUsd'  => $clientTotalUsd,
            'clientPaidTotals'=> $clientPaidTotals,
            'grandSalesFormula' => $grandSalesFormula,
            'grandPaidFormula' => $grandPaidFormula,
            'monthYear'       => $this->monthYear
        ]);
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
        $salesColumn = Coordinate::stringFromColumnIndex($this->productCount + 3);
        $paidColumn = Coordinate::stringFromColumnIndex($this->productCount + 4);

        $sheet->getStyle($salesColumn . '3:' . $paidColumn . $summaryRow)
            ->getNumberFormat()->setFormatCode('$#,##0.00');

        if ($this->productCount > 0) {
            $firstProductColumn = Coordinate::stringFromColumnIndex(3);
            $lastProductColumn = Coordinate::stringFromColumnIndex($this->productCount + 2);
            $sheet->getStyle($firstProductColumn . $summaryRow . ':' . $lastProductColumn . $summaryRow)
                ->getNumberFormat()->setFormatCode('$#,##0.00');
        }
    }
}
