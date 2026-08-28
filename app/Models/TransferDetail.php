<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransferDetail extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function prodid()
    {
        return $this->belongsTo('App\Models\Product', 'product_id');
    }

    public function transfid()
    {
        return $this->belongsTo('App\Models\Transfer', 'transfer_id');
    }
    
    public function warehouseoutid()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_out');
    }
    
    public function warehouseinid()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_in');
    }
}
