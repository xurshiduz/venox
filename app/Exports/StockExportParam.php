<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Models\Product;
use App\Models\Warehouse;

class StockExportParam implements FromView
{ 
    
    function __construct($id, $take, $pag) {
        $this->id = $id;
        $this->take = $take;
        $this->pag = $pag;
    }
        
    public function view(): View
    {
        $wareid = Warehouse::where('code', $this->id)->first();
        $take = $this->take;
        $pag = $this->pag;
        
        return view('backend.warehouses.excel_param', compact('wareid', 'take', 'pag'));
    }
}