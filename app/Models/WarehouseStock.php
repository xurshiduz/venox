<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseStock extends Model
{
    use HasFactory;

    protected $guarded = []; 
    
    public function wareid()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id');
    }
    
    public function productid()
    {
        return $this->belongsTo('App\Models\Product', 'product_id');
    }
}
