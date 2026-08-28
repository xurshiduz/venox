<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    public function modulid()
    {
        return $this->belongsTo('App\Models\HistoryModul', 'name');
    }

    public function userid()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}
