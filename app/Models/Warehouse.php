<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $guarded = [];
    

    public function dealerid()
    {
        return $this->belongsTo('App\Models\Dealer', 'dealer_id');
    }

    public function warehouseall()
    {
        return $this->hasMany('App\Models\WarehouseCell', 'warehouse_id');
    }

    public function stockall()
    {
        return $this->hasMany('App\Models\WarehouseStock', 'warehouse_id');
    }
    
    public function checkoutsdetail()
    {
        return $this->hasMany('App\Models\CheckoutDetail', 'warehouse_id');
    }

    public function blocks()
    {
        return $this->hasMany('App\Models\WarehouseBlock', 'warehouse_id');
    }
}
