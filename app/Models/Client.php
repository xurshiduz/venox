<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model; 

class Client extends Model
{
    use HasFactory;

    protected $guarded = [];
    
    public function checkouts()
    {
        return $this->hasMany('App\Models\Checkout', 'client_id');
    }
    
    public function checkins()
    {
        return $this->hasMany('App\Models\Checkin', 'client_id');
    }
    
    public function balances()
    {
        return $this->hasMany('App\Models\ClientBalance', 'client_id');
    }
    
    public function cashReceipts()
    {
        return $this->hasMany('App\Models\CashReceipt', 'client_id');
    }
    
    public function activeDebts()
    {
        return $this->hasMany(Checkout::class, 'client_id', 'id')
                    ->where('total_price_debt', '>=', 1);
    }

    // Mijozning eng oxirgi to'lovi (status = 1)
    public function lastPayment()
    {
        return $this->hasOne(CashReceipt::class, 'client_id', 'id')
                    ->where('status', 1)
                    ->latest('date'); // Yoki created_at, qaysi sana ustuni bo'lsa
    }
}
