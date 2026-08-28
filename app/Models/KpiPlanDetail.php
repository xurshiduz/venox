<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiPlanDetail extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function planid()
    {
        return $this->belongsTo('App\Models\KpiPlan', 'plan_id');
    }

    public function managerid()
    {
        return $this->belongsTo('App\Models\User', 'manager_id');
    }
}
