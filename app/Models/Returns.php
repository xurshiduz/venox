<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Returns extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    
    public function checkdetid()
    {
        return $this->belongsTo('App\Models\CheckoutDetail', 'checkout_detail_id');
    }
    
    public function prodid()
    {
        return $this->belongsTo('App\Models\Product', 'product_id');
    }
    
    public function warehouseid()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id');
    }
    
    public function sellerid()
    {
        return $this->belongsTo('App\Models\User', 'seller_id');
    }
    
    public function userid()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}
