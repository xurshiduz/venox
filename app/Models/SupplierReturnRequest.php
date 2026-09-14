<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierReturnRequest extends Model
{
    protected $guarded = [];
    protected $casts = ['date' => 'date', 'accepted_at' => 'datetime'];
    public function details() { return $this->hasMany(SupplierReturnRequestDetail::class); }
    public function checkin() { return $this->belongsTo(Checkin::class); }
}
