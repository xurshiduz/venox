<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

use App\Models\Checkout;

class CheckoutDebt implements FromView
{   
    public function view(): View
    {
        $data = Checkout::where('checkout_tip_id', 1)->where('total_price_debt', '!=', 0)->where('type_id', 1)->orderBy('id', 'desc')->get();
        return view('backend.checkouts.debtors_excel', compact('data'));
    }
}