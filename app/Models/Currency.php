<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
  
class Currency extends Model
{
    use HasFactory; 
    protected $guarded = [];

    public function products()
    {
        return $this->hasMany('App\Models\Product', 'category_id');
    } 
    
    public function clientid()
    {
        return $this->belongsTo('App\Models\Client', 'client_id');
    }
    
    public function cname()
    {
        return $this->belongsTo('App\Models\CurrencyType', 'type_id');
    }
    
    public function userid()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
    
}
