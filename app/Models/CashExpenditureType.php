<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashExpenditureType extends Model
{
    use HasFactory; 

    protected $guarded = [];

    public function details()
    { 
        return $this->hasMany('App\Models\CashExpenditure', 'cash_expenditure_types');
    }
    
    public function revdetails()
    { 
        return $this->hasMany('App\Models\CashRevenue', 'cash_expenditure_types');
    }
    
    public function tname()
    {
        return $this->belongsTo('App\Models\CashReceiptType', 'cash_receipt_type');
    }
    
    public function uname()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
    
    public function contracktname()
    {
        return $this->belongsTo('App\Models\Checkout', 'checkout_id');
    }
    
    public function bname()
    {
        return $this->belongsTo('App\Models\ProductBrand', 'brand_id');
    }
    
    public function countout()
    {
        return $this->hasMany('App\Models\CheckoutDetail', 'product_id');
    }
}
