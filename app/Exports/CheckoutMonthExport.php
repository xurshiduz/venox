<?php

namespace App\Exports;

use App\Models\Checkout;
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

        foreach ($checkouts as $checkout) {
            $clientName = $checkout->supid->name ?? 'Noma\'lum mijoz';
            
            // Valyuta tipi va kursi (Agar 1 bo'lsa kursini olamiz, yo'qsa 0)
            $cType = $checkout->currency_type;
            $cRate = $checkout->currency_type_price ?? 0;

            if (!isset($matrixData[$clientName])) {
                $matrixData[$clientName] = [];
                $clientTotalUzs[$clientName] = 0; 
                $clientTotalUsd[$clientName] = 0; 
            }

            foreach ($checkout->checkoutDetails as $detail) {
                $productName = $detail->prodid->name ?? 'Noma\'lum mahsulot';
                
                $productsList[$productName] = $productName;

                // 1. Mijoz va tovar kesishmasida MIQDOR (qty)
                if (!isset($matrixData[$clientName][$productName])) {
                    $matrixData[$clientName][$productName] = 0;
                }
                $matrixData[$clientName][$productName] += $detail->qty;

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
                $clientTotalUzs[$clientName] += $uzs;
                $clientTotalUsd[$clientName] += $usd;

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

        ksort($productsList);

        return view('backend.checkouts.excel_matrix', [
            'productsList'    => $productsList,
            'matrixData'      => $matrixData,
            'productTotalUzs' => $productTotalUzs,
            'productTotalUsd' => $productTotalUsd,
            'clientTotalUzs'  => $clientTotalUzs,
            'clientTotalUsd'  => $clientTotalUsd,
            'grandTotalUzs'   => $grandTotalUzs,
            'grandTotalUsd'   => $grandTotalUsd,
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