<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checkin extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function details()
    {
        return $this->hasMany('App\Models\CheckinDetail', 'checkin_id');
    }
    
    public function sumtotal()
    {
        return $this->hasMany('App\Models\CheckinDetail', 'checkin_id')->sum('total_price');
    }

    public function warid()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id');
    }

    public function supid()
    {
        return $this->belongsTo('App\Models\Client', 'client_id');
    }

    public function userid()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function typeid()
    {
        return $this->belongsTo('App\Models\CheckType', 'type_id');
    }
    
    public function currencytypeid()
    {
        return $this->belongsTo('App\Models\CurrencyType', 'currency_type');
    }
}
