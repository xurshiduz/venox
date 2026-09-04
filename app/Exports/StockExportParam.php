<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use App\Models\Warehouse;

class StockExportParam extends StockExport
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
        
        return view('backend.warehouses.excel', compact('wareid', 'take', 'pag'));
    }
}
