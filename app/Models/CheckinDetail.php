<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckinDetail extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function prodid()
    {
        return $this->belongsTo('App\Models\Product', 'product_id');
    }

    public function checkid()
    {
        return $this->belongsTo('App\Models\Checkin', 'checkin_id');
    }

    public function blockcellid()
    {
        return $this->belongsTo('App\Models\WarehouseBlockCell', 'warehouse_cell_id');
    }

    public function blockid()
    {
        return $this->belongsTo('App\Models\WarehouseBlock', 'warehouse_block_id');
    }
    
    public function warehouseid()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id');
    }
}
