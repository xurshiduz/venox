<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelHasRole extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    public function rolenameid()
    {
        return $this->belongsTo('App\Models\Role', 'role_id');
    }

    public function userinfo()
    {
        return $this->belongsTo('App\Models\User', 'model_id');
    }
}
