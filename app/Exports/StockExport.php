<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use App\Models\Warehouse;

class StockExport implements FromView, WithEvents, WithTitle
{ 
    
    function __construct($id, $usdRate = null) {
        $this->id = $id;
        $this->usdRate = (float) $usdRate;
    }
        
    public function view(): View
    {
        $wareid = Warehouse::where('code', $this->id)->first();
        
        $usdRate = $this->usdRate;

        return view('backend.warehouses.excel', compact('wareid', 'usdRate'));
    }

    public function title(): string
    {
        return 'Лист1';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                $widths = [
                    'A' => 5.28515625,
                    'B' => 6.42578125,
                    'C' => 45,
                    'D' => 16.42578125,
                    'E' => 17,
                    'F' => 18.7109375,
                    'G' => 13.7109375,
                    'H' => 14.140625,
                    'I' => 23.85546875,
                ];

                foreach ($widths as $column => $width) {
                    $sheet->getColumnDimension($column)->setWidth($width);
                }

                $sheet->getRowDimension(1)->setRowHeight(15.75);
                $sheet->getRowDimension(2)->setRowHeight(72);
                for ($row = 3; $row <= $lastRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(33);
                }

                if ($lastRow < 2) {
                    return;
                }

                $tableRange = 'B2:I' . $lastRow;
                $sheet->getStyle($tableRange)->applyFromArray([
                    'font' => [
                        'name' => 'Calibri',
                        'size' => 11,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                        'outline' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);

                $sheet->getStyle('B2:I2')->applyFromArray([
                    'font' => [
                        'name' => 'Calibri',
                        'size' => 14,
                        'bold' => true,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);
            },
        ];
    }
}
