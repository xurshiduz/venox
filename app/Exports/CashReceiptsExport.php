<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CashReceiptsExport implements FromArray, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithStyles
{
    private Collection $receipts;

    public function __construct(Collection $receipts)
    {
        $this->receipts = $receipts;
    }

    public function array(): array
    {
        $rows = $this->receipts->map(function ($receipt) {
            $paymentType = $receipt->tname;
            $isClick = $paymentType && strcasecmp((string) $paymentType->name_ru, 'Click') === 0;
            $checkout = $receipt->checkout;
            $amount = (float) $receipt->price;
            $kpiPercent = $checkout ? (float) $checkout->kpi_percent : 0;
            $agentPercent = $checkout ? (float) $checkout->agent_percent : 0;
            $venoxPercent = $checkout ? (float) $checkout->venox_bonus_percent : 0;
            $hasScheme = $checkout && $checkout->commission_scheme;
            $schemeLabels = [
                'special' => 'Spes',
                'contract' => 'Shartnoma',
                'venox_10' => 'Venox bonus 10%',
                'venox_15' => 'Venox bonus 15%',
                'venox_20' => 'Venox bonus 20%',
                'venox_25' => 'Venox bonus 25%',
            ];

            $kpiAmount = $hasScheme ? $amount * $kpiPercent / 100 : null;
            $agentAmount = $hasScheme ? $amount * $agentPercent / 100 : null;
            $venoxAmount = $hasScheme ? $amount * $venoxPercent / 100 : null;
            $factoryAmount = $hasScheme ? $amount - $kpiAmount - $agentAmount - $venoxAmount : null;

            return [
                $receipt->date,
                optional($receipt->clientname)->name,
                $hasScheme ? ($schemeLabels[$checkout->commission_scheme] ?? $checkout->commission_scheme) : 'Belgilanmagan',
                $amount,
                $isClick ? $amount : null,
                $kpiAmount,
                $agentAmount,
                $venoxAmount,
                $factoryAmount,
            ];
        });

        $rows->push([
            'ЖАМИ',
            null,
            null,
            (float) $this->receipts->sum('price'),
            (float) $this->receipts->filter(function ($receipt) {
                return $receipt->tname
                    && strcasecmp((string) $receipt->tname->name_ru, 'Click') === 0;
            })->sum('price'),
            (float) $rows->sum(5),
            (float) $rows->sum(6),
            (float) $rows->sum(7),
            (float) $rows->sum(8),
        ]);

        return $rows->all();
    }

    public function headings(): array
    {
        return [
            'Сана',
            'Клент',
            'Bonus / bez bonus',
            'Pastupleniya summa USD',
            'Сумма USD click',
            'KPI kassa',
            '8% Fiksa agent',
            'Venox bonus kassa',
            'Zavod kassa',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => '#,##0.00', 'E' => '#,##0.00', 'F' => '#,##0.00',
            'G' => '#,##0.00', 'H' => '#,##0.00', 'I' => '#,##0.00',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $this->receipts->count() + 2;
        $sheet->freezePane('A2');
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
        $sheet->getStyle("A{$lastRow}:I{$lastRow}")->getFont()->setBold(true);

        return [];
    }
}
