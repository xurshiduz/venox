<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class DayExcel implements FromView, ShouldAutoSize, WithColumnFormatting
{ 
    
    function __construct($data, $fromdate, $todate, $checkouttip) {
        $this->data = $data;
        $this->fromdate = $fromdate;
        $this->todate = $todate;
        $this->checkouttip = $checkouttip;
    }
        
    public function view(): View
    {
        $data           = $this->data;
        $fromdate       = $this->fromdate;
        $todate         = $this->todate;
        $checkouttip    = $this->checkouttip;

        // Kunlik sotuv hisoboti har doim yagona valyutada chiqariladi.
        // UZS hujjatlar eksport shablonida hujjat sanasidagi kurs bilan USD'ga aylantiriladi.
        $targetCurrencyType = 1;
        $targetCurrencyLabel = 'USD';
        
        return view('backend.checkouts.day_excel_all', compact(
            'data', 'fromdate', 'todate', 'checkouttip', 'targetCurrencyType', 'targetCurrencyLabel'
        ));
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_NUMBER_00,
            'D' => NumberFormat::FORMAT_NUMBER_00,
            'E' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }
}
