<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckoutDetail extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function prodid()
    {
        return $this->belongsTo('App\Models\Product', 'product_id');
    }

    public function checkid()
    {
        return $this->belongsTo('App\Models\Checkout', 'checkout_id');
    }
    
    public function currencytypeid()
    {
        return $this->belongsTo('App\Models\CurrencyType', 'currency_type');
    }
    
    public function warehouseid()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id');
    }
    
    public function stockid()
    {
        return $this->hasMany('App\Models\WarehouseStock', 'product_id', 'product_id');
    }
}
