<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Models\Checkout;
use App\Models\Setting;

class CheckoutOne implements FromView
{ 
    
    function __construct($id) {
        $this->id = $id;
    }
        
    public function view(): View
    {
        $comp = Setting::all();
        $item = Checkout::where('code', $this->id)->first();
        
        return view('backend.checkouts.print_excel', compact('item', 'comp'));
    }
}