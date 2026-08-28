<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiPlan extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function details()
    {
        return $this->hasMany('App\Models\KpiPlanDetail', 'plan_id');
    }

    public function userid()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}
