<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class CheckinFilterExport implements FromView, ShouldAutoSize, WithTitle
{
    public function __construct($data, $totals, $fromdate, $todate)
    {
        $this->data = $data;
        $this->totals = $totals;
        $this->fromdate = $fromdate;
        $this->todate = $todate;
    }

    public function view(): View
    {
        return view('backend.filter.excel', [
            'data' => $this->data,
            'totals' => $this->totals,
            'fromdate' => $this->fromdate,
            'todate' => $this->todate,
        ]);
    }

    public function title(): string
    {
        return 'Kirim-chiqim';
    }
}
