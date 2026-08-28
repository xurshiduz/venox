<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductBrand extends Model
{
    use HasFactory;
    protected $guarded = [];
    
    public function productsid()
    {
        return $this->hasMany('App\Models\Product', 'brand_id');
    }
    
    public function userid()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}
