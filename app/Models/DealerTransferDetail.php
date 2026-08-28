<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DealerTransferDetail extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function prodid()
    {
        return $this->belongsTo('App\Models\Product', 'product_id');
    }

    public function deltransid()
    {
        return $this->belongsTo('App\Models\DealerTransfer', 'dealer_transfer_id');
    }
    
    public function currencytypeid()
    {
        return $this->belongsTo('App\Models\CurrencyType', 'currency_type');
    }
    
    public function warehouseid()
    {
        return $this->belongsTo('App\Models\Warehouse', 'warehouse_id');
    }
}
