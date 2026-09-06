<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AccountingCashReportExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    private Collection $rows;

    public function __construct(Collection $rows) { $this->rows = $rows; }

    public function array(): array
    {
        $schemeLabels = ['special' => 'Spes', 'contract' => 'Shartnoma', 'venox_bonus' => 'Venox bonus'];
        $data = $this->rows->values()->map(function ($row, $index) use ($schemeLabels) {
            return [
                $index + 1,
                $row['date'],
                $row['agent'],
                collect($row['products'])->map(fn ($p) => $p['name'].' — '.number_format($p['qty'], 3, '.', ' ').' '.$p['unit'])->implode("\n"),
                $row['client'],
                $schemeLabels[$row['scheme']] ?? $row['scheme'],
                $row['purchase_cost_usd'], $row['payment_usd'], $row['kpi'], $row['agent_amount'], $row['venox'], $row['factory'],
            ];
        })->all();

        $data[] = ['', '', '', 'JAMI', '', '',
            $this->rows->sum('purchase_cost_usd'), $this->rows->sum('payment_usd'),
            $this->rows->sum('kpi'), $this->rows->sum('agent_amount'),
            $this->rows->sum('venox'), $this->rows->sum('factory'),
        ];

        return $data;
    }

    public function headings(): array
    {
        return ['№', 'Sana', 'Agent', 'Tovar', 'Klient', 'Bonus / bez bonus', 'Prihod summa (USD)', 'Summa USD', 'KPI', 'Fiksa agent', 'Venox bonus kassa', 'Zavod kassa'];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->freezePane('A2');
        $sheet->getStyle('A1:L1')->getFont()->setBold(true);
        $lastRow = $this->rows->count() + 2;
        $sheet->getStyle('A1:L'.$lastRow)->getAlignment()->setWrapText(true)->setVertical('center');
        $sheet->getStyle('A'.$lastRow.':L'.$lastRow)->getFont()->setBold(true);
        $sheet->getStyle('G2:L'.$lastRow)->getNumberFormat()->setFormatCode('#,##0.00');
        return [];
    }
}
