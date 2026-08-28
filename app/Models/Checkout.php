<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checkout extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function details()
    {
        return $this->hasMany('App\Models\CheckoutDetail', 'checkout_id');
    }
    
    public function checkoutDetails()
    {
        return $this->hasMany(CheckoutDetail::class, 'checkout_id');
    }

    public function alldetails()
    {
        return $this->hasMany('App\Models\CheckoutDetail', 'checkout_id');
    }
    
    public function sumtotal()
    {
        return $this->hasMany('App\Models\CheckoutDetail', 'checkout_id')->sum('total_price');
    }
    
    public function payments()
    {
        return $this->hasMany('App\Models\CashReceipt', 'checkout_id');
    }

    public function warid()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id');
    }

    public function checktypeid()
    {
        return $this->belongsTo('App\Models\CheckoutType', 'checkout_tip_id');
    }

    public function supid()
    {
        return $this->belongsTo('App\Models\Client', 'client_id');
    }
    
    public function managerid()
    {
        return $this->belongsTo('App\Models\User', 'manager_id');
    }
    
    public function currencytypeid()
    {
        return $this->belongsTo('App\Models\CurrencyType', 'currency_type');
    }
    
    public function payments_naqd()
    {
        return $this->hasMany('App\Models\CashReceipt', 'checkout_id')->where('cash_receipt_type','!=', 2)->sum('price');
    }
    
    public function payments_perech()
    {
        return $this->hasMany('App\Models\CashReceipt', 'checkout_id')->where('cash_receipt_type', 2)->sum('price');
    }
}
