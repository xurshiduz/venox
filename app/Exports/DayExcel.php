<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class DayExcel implements FromView, ShouldAutoSize, WithColumnFormatting, WithEvents
{
    private Collection $rows;
    private string $fromdate;
    private string $todate;

    public function __construct(Collection $rows, string $fromdate, string $todate)
    {
        $this->rows = $rows;
        $this->fromdate = $fromdate;
        $this->todate = $todate;
    }
        
    public function view(): View
    {
        $rows = $this->rows;
        $fromdate = $this->fromdate;
        $todate = $this->todate;
        
        return view('backend.checkouts.day_excel_all', compact('rows', 'fromdate', 'todate'));
    }

    public function columnFormats(): array
    {
        return [
            'C' => '#,##0.###',
            'D' => NumberFormat::FORMAT_NUMBER_00,
            'E' => NumberFormat::FORMAT_NUMBER_00,
            'F' => NumberFormat::FORMAT_NUMBER_00,
            'H' => NumberFormat::FORMAT_NUMBER_00,
            'I' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $range = 'A1:' . $sheet->getHighestColumn() . $sheet->getHighestRow();

                $sheet->setShowGridlines(false);
                $sheet->freezePane('A3');
                $sheet->setAutoFilter('A2:N2');
                $sheet->getDefaultRowDimension()->setRowHeight(44);
                $sheet->getRowDimension(1)->setRowHeight(28);
                $sheet->getRowDimension(2)->setRowHeight(48);
                $sheet->getStyle($range)->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setWrapText(true);
                $sheet->getStyle('A1:N1')->getFont()->setBold(true);
                $sheet->getStyle('A2:N2')->getFont()->setBold(true);
                $sheet->getStyle('A' . $sheet->getHighestRow() . ':N' . $sheet->getHighestRow())
                    ->getFont()->setBold(true);

                foreach (['A' => 42, 'B' => 14, 'C' => 12, 'D' => 18, 'E' => 18, 'F' => 22,
                    'G' => 26, 'H' => 24, 'I' => 26, 'J' => 14, 'K' => 22, 'L' => 18,
                    'M' => 14, 'N' => 20] as $column => $width) {
                    $sheet->getColumnDimension($column)->setWidth($width);
                }

                $sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFC9C9C9'],
                        ],
                    ],
                ]);
            },
        ];
    }
}
