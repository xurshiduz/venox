<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseBlockProduct extends Model
{
    use HasFactory;

    protected $guarded = []; 
    
    public function wareblockid()
    {
        return $this->belongsTo('App\Models\WarehouseBlock', 'warehouse_block_id');
    }
    
    public function wareblockcellid()
    {
        return $this->belongsTo('App\Models\WarehouseBlockCell', 'warehouse_cell_id');
    }
    
    public function wareid()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id');
    }
    
    public function productid()
    {
        return $this->belongsTo('App\Models\Product', 'product_id');
    }
}
