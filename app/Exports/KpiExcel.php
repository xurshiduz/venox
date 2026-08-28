<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Models\KpiPlan;

class KpiExcel implements FromView
{ 
    
    function __construct($id) {
        $this->id = $id;
    }
        
    public function view(): View
    {
        $item = KpiPlan::where('code', $this->id)->first();
        
        return view('backend.kpi.excel', compact('item'));
    }
}