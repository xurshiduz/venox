<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function allusers()
    {
        return $this->hasMany('App\Models\ModelHasRole', 'role_id');
    }
    
    public function userRoleName()
    {
        return $this->hasMany('App\Models\ModelHasRole', 'model_id');
    }
}
