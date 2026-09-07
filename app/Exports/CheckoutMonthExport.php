<?php

namespace App\Exports;

use App\Models\Checkout;
use App\Models\CashReceipt;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class CheckoutMonthExport implements FromView, WithStyles
{
    protected $monthYear;

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
        
        // Summalar uchun So'm va Dollar o'zgaruvchilari
        $productTotalUzs = []; 
        $productTotalUsd = []; 
        
        $clientTotalUzs = [];  
        $clientTotalUsd = [];  
        
        $grandTotalUzs = 0;          
        $grandTotalUsd = 0;          

        // Kassa kirimlari shu oyning sanasi va mijoz ID-si bo'yicha hisoblanadi.
        // status=0 bo'lgan (bekor qilingan) to'lovlar hisobotga kiritilmaydi.
        $clientPayments = CashReceipt::query()
            ->where('status', 1)
            ->whereBetween('date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->selectRaw('client_id, SUM(price) as total_paid')
            ->groupBy('client_id')
            ->pluck('total_paid', 'client_id');

        $clientNames = [];
        $grandTotalPaid = 0;

        foreach ($checkouts as $checkout) {
            $clientName = $checkout->supid->name ?? 'Noma\'lum mijoz';
            $clientKey = (string) ($checkout->client_id ?? 'unknown-' . $clientName);
            $clientNames[$clientKey] = $clientName;
            
            // Valyuta tipi va kursi (Agar 1 bo'lsa kursini olamiz, yo'qsa 0)
            $cType = $checkout->currency_type;
            $cRate = $checkout->currency_type_price ?? 0;

            if (!isset($matrixData[$clientKey])) {
                $matrixData[$clientKey] = [];
                $clientTotalUzs[$clientKey] = 0;
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

                $uzs = 0;
                $usd = 0;

                if ($cType == 1) {
                    // Agar valyuta $ (Dollar) bo'lsa
                    $usd = $basePriceTotal;
                    $uzs = $basePriceTotal * $cRate; // Kursga ko'paytirib So'mga aylantiramiz
                } else {
                    // Agar valyuta So'm bo'lsa (yoki boshqa)
                    $uzs = $basePriceTotal;
                }

                // Qator bo'yicha summa (Mijozning jami So'm va Dollari)
                $clientTotalUzs[$clientKey] += $uzs;
                $clientTotalUsd[$clientKey] += $usd;

                // Ustun bo'yicha summa (Tovarning jami So'm va Dollari)
                if (!isset($productTotalUzs[$productName])) {
                    $productTotalUzs[$productName] = 0;
                    $productTotalUsd[$productName] = 0;
                }
                $productTotalUzs[$productName] += $uzs;
                $productTotalUsd[$productName] += $usd;

                // Umumiy jami summa
                $grandTotalUzs += $uzs;
                $grandTotalUsd += $usd;
            }
        }

        $clientPaidTotals = [];
        foreach (array_keys($matrixData) as $clientKey) {
            $clientPaidTotals[$clientKey] = (float) ($clientPayments[$clientKey] ?? 0);
            $grandTotalPaid += $clientPaidTotals[$clientKey];
        }

        ksort($productsList);

        return view('backend.checkouts.excel_matrix', [
            'productsList'    => $productsList,
            'matrixData'      => $matrixData,
            'clientNames'     => $clientNames,
            'productTotalUzs' => $productTotalUzs,
            'productTotalUsd' => $productTotalUsd,
            'clientTotalUzs'  => $clientTotalUzs,
            'clientTotalUsd'  => $clientTotalUsd,
            'grandTotalUzs'   => $grandTotalUzs,
            'grandTotalUsd'   => $grandTotalUsd,
            'clientPaidTotals'=> $clientPaidTotals,
            'grandTotalPaid'  => $grandTotalPaid,
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
    }
}
