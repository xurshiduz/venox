<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adjustment extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function warid()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id');
    }

    public function userid()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function prodid()
    {
        return $this->belongsTo('App\Models\Product', 'product_id');
    }
}
