<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseBlockCell extends Model
{
    use HasFactory;

    protected $guarded = []; 

    public function warehouseid()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id');
    }

    public function warehouseblockid()
    {
        return $this->belongsTo('App\Models\WarehouseBlock', 'warehouse_block_id');
    }
}
