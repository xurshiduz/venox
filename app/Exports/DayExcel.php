<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class DayExcel implements FromView, ShouldAutoSize, WithColumnFormatting, WithEvents
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

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $range = 'A1:' . $sheet->getHighestColumn() . $sheet->getHighestRow();

                $sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);

                $sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);
            },
        ];
    }
}
