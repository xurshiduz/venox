<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseBlock extends Model
{
    use HasFactory;

    protected $guarded = []; 

    public function warehouseall()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id');
    }
    public function cellsall()
    {
        return $this->hasMany('App\Models\WarehouseBlockCell', 'warehouse_block_id');
    }
}
