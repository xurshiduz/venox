<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class CheckinFilterExport implements FromView, ShouldAutoSize, WithTitle
{
    public function __construct($data, $fromdate, $todate)
    {
        $this->data = $data;
        $this->fromdate = $fromdate;
        $this->todate = $todate;
    }

    public function view(): View
    {
        return view('backend.filter.excel', [
            'data' => $this->data,
            'fromdate' => $this->fromdate,
            'todate' => $this->todate,
        ]);
    }

    public function title(): string
    {
        return 'Kirim';
    }
}
