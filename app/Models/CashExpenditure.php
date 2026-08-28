<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashExpenditure extends Model
{
    use HasFactory; 

    protected $guarded = [];

    public function cname()
    {
        return $this->belongsTo('App\Models\Checkout', 'checkout_id');
    }
    
    public function typename()
    {
        return $this->belongsTo('App\Models\CashReceiptType', 'cash_receipt_type_id');
    }
    
    public function tname()
    {
        return $this->belongsTo('App\Models\CashReceiptType', 'cash_receipt_type');
    }
    
    public function cename()
    {
        return $this->belongsTo('App\Models\CashExpenditureType', 'cash_expenditure_types');
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
    
    public function supplier()
    {
        return $this->belongsTo('App\Models\Client', 'supplier_id');
    }
    
    public function countout()
    {
        return $this->hasMany('App\Models\CheckoutDetail', 'product_id');
    }
}
