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

            return [
                $receipt->date,
                optional($receipt->clientname)->name,
                $paymentType ? $paymentType->name_ru : null,
                (float) $receipt->price,
                $isClick ? (float) $receipt->price : null,
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
        ]);

        return $rows->all();
    }

    public function headings(): array
    {
        return [
            'Сана',
            'Клент',
            'Bonus/ bez bonus',
            'Pastupleniya summa USD',
            'Сумма USD click',
        ];
    }

    public function columnFormats(): array
    {
        return ['D' => '#,##0.00', 'E' => '#,##0.00'];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $this->receipts->count() + 2;
        $sheet->freezePane('A2');
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        $sheet->getStyle("A{$lastRow}:E{$lastRow}")->getFont()->setBold(true);

        return [];
    }
}
