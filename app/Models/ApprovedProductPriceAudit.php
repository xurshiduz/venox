<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovedProductPriceAudit extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'old_sale_price_uzs' => 'decimal:2',
        'new_sale_price_uzs' => 'decimal:2',
        'old_factory_price_uzs' => 'decimal:2',
        'new_factory_price_uzs' => 'decimal:2',
    ];

    public function price()
    {
        return $this->belongsTo(ApprovedProductPrice::class, 'approved_product_price_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
