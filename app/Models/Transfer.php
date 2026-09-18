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

    public function clientoutid()
    {
        return $this->belongsTo(Client::class, 'client_out_id');
    }

    public function clientinid()
    {
        return $this->belongsTo(Client::class, 'client_in_id');
    }

    public function isClientTransfer(): bool
    {
        return $this->transfer_type === 'client';
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
