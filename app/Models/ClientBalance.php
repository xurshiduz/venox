<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model; 

class ClientBalance extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    public function uname()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
    
    public function clientname()
    {
        return $this->belongsTo('App\Models\Client', 'client_id');
    }
    
    public function tname()
    {
        return $this->belongsTo('App\Models\CashReceiptType', 'cash_receipt_type');
    }
}
