<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dealer extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function regionid()
    {
        return $this->belongsTo('App\Models\Region', 'region_id');
    }
}
