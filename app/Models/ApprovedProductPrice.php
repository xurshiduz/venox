<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovedProductPrice extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'match_tokens' => 'array',
        'sale_price_uzs' => 'decimal:2',
        'factory_price_uzs' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function audits()
    {
        return $this->hasMany(ApprovedProductPriceAudit::class);
    }
}
