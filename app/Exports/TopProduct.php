<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Models\Checkout;
use DB;

class TopProduct implements FromView
{ 
    
    function __construct($fromDate, $toDate) {
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
    }
        
    public function view(): View
    {
        $fromDate       = $this->fromDate;
        $toDate         = $this->toDate;
        
        $mostSoldProducts = Checkout::whereBetween('date', [$fromDate, $toDate])
            ->join('checkout_details', 'checkouts.id', '=', 'checkout_details.checkout_id')
            ->join('products', 'checkout_details.product_id', '=', 'products.id')
            ->leftJoin('units', 'products.unit_id', '=', 'units.id') // Unit jadvalini qo'shish
            ->select(
                'products.name',
                'products.barcode',
                'units.name as unit_name', // Unit nomini olish
                DB::raw('SUM(checkout_details.qty) as total_qty')
            )
            ->groupBy('products.name', 'products.barcode', 'units.name') // Guruhlashni yangilash
            ->orderBy('total_qty', 'DESC')
            ->get();
    
        return view('backend.products.reports.excel', compact('mostSoldProducts', 'fromDate', 'toDate'));
            
    }
}