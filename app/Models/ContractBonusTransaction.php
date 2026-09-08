<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractBonusTransaction extends Model
{
    protected $guarded = [];

    protected $casts = [
        'amount_usd' => 'float',
        'meta' => 'array',
        'status' => 'boolean',
        'transaction_date' => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function receipt()
    {
        return $this->belongsTo(CashReceipt::class, 'cash_receipt_id');
    }

    public function checkout()
    {
        return $this->belongsTo(Checkout::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
