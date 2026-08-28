<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Models\Product;
use App\Models\Warehouse;

class StockExport implements FromView
{ 
    
    function __construct($id) {
        $this->id = $id;
    }
        
    public function view(): View
    {
        $wareid = Warehouse::where('code', $this->id)->first();
        
        return view('backend.warehouses.excel', compact('wareid'));
    }
}