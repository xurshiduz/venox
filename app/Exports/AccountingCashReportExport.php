<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AccountingCashReportExport implements FromArray, WithHeadings, WithCustomStartCell, WithStyles
{
    private Collection $rows;
    private float $usdRate;
    private string $periodLabel;

    public function __construct(Collection $rows, float $usdRate, string $periodLabel)
    {
        $this->rows = $rows;
        $this->usdRate = $usdRate;
        $this->periodLabel = $periodLabel;
    }

    public function startCell(): string
    {
        return 'A2';
    }

    public function array(): array
    {
        $schemeLabels = ['' => 'Belgilanmagan', 'special' => 'Spes', 'contract' => 'Shartnoma', 'venox_bonus' => 'Venox bonus'];
        $data = $this->rows->values()->map(function ($row, $index) use ($schemeLabels) {
            return [
                $index + 1,
                $row['date'],
                $row['agent'],
                collect($row['products'])->map(fn ($p) => $p['name'].' — '.number_format($p['qty'], 3, '.', ' ').' '.$p['unit'])->implode("\n"),
                collect($row['products'])->map(fn ($p) => $p['factory_unit_price_usd'] !== null
                    ? number_format($p['factory_unit_price_usd'], 2, '.', '')
                    : '—')->implode("\n"),
                collect($row['products'])->map(fn ($p) => number_format($p['actual_total_usd'], 2, '.', ''))->implode("\n"),
                $row['client'],
                $schemeLabels[$row['scheme']] ?? $row['scheme'],
                $row['purchase_cost_usd'], $row['payment_usd'], $row['kpi'], $row['agent_amount'], $row['venox'], $row['factory'],
            ];
        })->all();

        $data[] = ['', '', '', 'JAMI', '', '', '', '',
            $this->rows->sum('purchase_cost_usd'), $this->rows->sum('payment_usd'),
            $this->rows->sum('kpi'), $this->rows->sum('agent_amount'),
            $this->rows->sum('venox'), $this->rows->sum('factory'),
        ];

        return $data;
    }

    public function headings(): array
    {
        return ['№', 'Sana', 'Agent', 'Tovar', 'Zavod narxi', 'Sotilish narxi', 'Klient', 'Bonus / bez bonus', 'Prihod summa (USD)', 'Summa USD', 'KPI', 'Fiksa agent', 'Venox bonus kassa', 'Zavod kassa'];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $this->rows->count() + 3;
        $sheet->setShowGridlines(false);
        $sheet->freezePane('A3');
        $sheet->setAutoFilter('A2:N2');
        $sheet->mergeCells('A1:N1');
        $sheet->setCellValue('A1', $this->periodLabel.' Kassa hisoboti (USD) — kursni T2 katakda o‘zgartiring');
        $sheet->setCellValue('T1', '1 USD (сўм)');
        $sheet->setCellValue('U1', 'Жами тўланган (сўм)');
        $sheet->setCellValue('T2', $this->usdRate);
        $sheet->setCellValue('U2', '=J'.$lastRow.'*$T$2');

        $sheet->getDefaultRowDimension()->setRowHeight(44);
        $sheet->getRowDimension(1)->setRowHeight(28);
        $sheet->getRowDimension(2)->setRowHeight(48);
        foreach ($this->rows->values() as $index => $row) {
            $lineCount = max(1, count($row['products'] ?? []));
            $sheet->getRowDimension($index + 3)->setRowHeight(max(44, $lineCount * 19));
        }

        foreach (['A' => 8, 'B' => 14, 'C' => 24, 'D' => 52, 'E' => 16, 'F' => 18,
            'G' => 28, 'H' => 20, 'I' => 20, 'J' => 18, 'K' => 14, 'L' => 16,
            'M' => 20, 'N' => 18, 'T' => 18, 'U' => 26] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $sheet->getStyle('A1:U'.$lastRow)->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setWrapText(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2:N2')->getFont()->setBold(true)->getColor()->setRGB('000000');
        $sheet->getStyle('A2:N'.$lastRow)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('C9C9C9');
        $sheet->getStyle('A'.$lastRow.':N'.$lastRow)->getFont()->setBold(true);
        $sheet->getStyle('I3:N'.$lastRow)->getNumberFormat()->setFormatCode('#,##0.00');

        $sheet->getStyle('T1:U2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);
        $sheet->getStyle('T1:U1')->getFont()->setBold(true);
        $sheet->getStyle('T1:U2')->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('C9C9C9');
        $sheet->getStyle('T2:U2')->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('T2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF2CC');

        return [];
    }
}
