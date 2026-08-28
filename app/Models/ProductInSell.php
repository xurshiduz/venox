<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductInSell extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function prodid()
    {
        return $this->belongsTo('App\Models\Product', 'product_id');
    }
}
