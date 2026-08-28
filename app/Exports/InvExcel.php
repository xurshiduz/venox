<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

use App\Models\InventoryDetail;
class InvExcel implements FromView
{ 
    
    function __construct($id) {
        $this->id     = $id;
    }
        
    public function view(): View
    {
        $data = InventoryDetail::all();
        return view('backend.inventories.excel', compact('data'));
    }
}