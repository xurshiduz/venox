<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
 
class CurrencyType extends Model
{
    use HasFactory; 
    protected $guarded = [];

    public function currencyid()
    {
        return $this->hasMany('App\Models\Currency', 'type_id')->orderBy('id', 'desc');
    } 
    
    public function clientid()
    {
        return $this->belongsTo('App\Models\Client', 'client_id');
    }
    
    public function userid()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
    
}
