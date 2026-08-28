<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Models\Warehouse;
use App\Models\CheckinDetail;

class WarehouseProductList implements FromView
{ 
    
    function __construct($id) {
        $this->id = $id;
    }
        
    public function view(): View
    {
        $wareid = Warehouse::where('code', $this->id)->first();
        $products = CheckinDetail::where('warehouse_id', $wareid->id)->get()->groupBy('product_id');
        return view('backend.warehouses.excel_product_list', compact('products'));
    }
}