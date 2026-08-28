<?php

namespace App\Models; 

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $guarded = [];
 
    public function details()
    {
        return $this->hasMany('App\Models\LogisticDetail', 'logistic_id');
    }

    public function trasportid()
    {
        return $this->belongsTo('App\Models\Transport', 'transport_id');
    }
  
    public function firstid()
    {
        return $this->belongsTo('App\Models\Transport', 'transport_id');
    }

    public function steps()
    {
        return $this->belongsTo('App\Models\LogisticStep', 'logistic_step_id');
    }
    
    
}
