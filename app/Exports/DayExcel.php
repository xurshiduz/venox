<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class DayExcel implements FromView
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
        
        return view('backend.checkouts.day_excel_all', compact('data', 'fromdate', 'todate', 'checkouttip'));
    }
}