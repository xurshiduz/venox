<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    
    
    public function details()
    {
        return $this->hasMany('App\Models\TransferDetail', 'transfer_id');
    }
    
    public function warehouseoutid()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_out');
    }
    
    public function warehouseinid()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_in');
    }
    
    public function managerid()
    {
        return $this->belongsTo('App\Models\User', 'manager_id');
    }

    public function userid()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}
