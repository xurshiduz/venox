<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use App\Models\Warehouse;

class StockExportParam extends StockExport
{ 
    
    function __construct($id, $take, $pag, $usdRate = null) {
        $this->id = $id;
        $this->take = $take;
        $this->pag = $pag;
        $this->usdRate = (float) $usdRate;
    }
        
    public function view(): View
    {
        $wareid = Warehouse::where('code', $this->id)->first();
        $take = $this->take;
        $pag = $this->pag;
        $usdRate = $this->usdRate;
        
        return view('backend.warehouses.excel', compact('wareid', 'take', 'pag', 'usdRate'));
    }
}
