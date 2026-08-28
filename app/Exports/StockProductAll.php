<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Models\WarehouseStock;

class StockProductAll implements FromView
{ 
        
    public function view(): View
    {
        $data = WarehouseStock::all()->groupBy('product_id');
        return view('backend.products.stock_all_excel', compact('data'));
    }
}