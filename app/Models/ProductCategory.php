<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;
    protected $guarded = [];
    
    public function productsid()
    {
        return $this->hasMany('App\Models\Product', 'category_id');
    }

    public function checkoutdetails()
    {
        return $this->hasMany('App\Models\CheckoutDetail', 'category_id');
    }
    
    public function checkindetails()
    {
        return $this->hasMany('App\Models\CheckinDetail', 'category_id');
    }
}
