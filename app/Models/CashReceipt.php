<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashReceipt extends Model 
{
    use HasFactory;

    protected $guarded = [];

    public function cname()
    {
        return $this->belongsTo('App\Models\Checkout', 'checkout_id');
    }
    
    public function tname()
    {
        return $this->belongsTo('App\Models\CashReceiptType', 'cash_receipt_type');
    }
    
    public function clientname()
    {
        return $this->belongsTo('App\Models\Client', 'client_id');
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
    
    public function checkout()
    {
        return $this->belongsTo(Checkout::class, 'checkout_id');
    }
}
