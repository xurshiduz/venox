<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'id');
    }
    
    public function unitid()
    {
        return $this->belongsTo('App\Models\Unit', 'unit_id');
    }

    public function brands()
    {
        return $this->belongsToMany(ProductBrand::class, 'product_product_brand');
    }

    public function currencyid()
    {
        return $this->belongsTo('App\Models\CurrencyType', 'currency_type');
    }

    public function contryid()
    {
        return $this->belongsTo('App\Models\Country', 'country_id');
    }

    public function factid()
    {
        return $this->belongsTo('App\Models\ManufacturerFactoryType', 'm_factory_type');
    }
    
    public function blocksall()
    {
        return $this->hasMany('App\Models\WarehouseBlockProduct', 'product_id');
    }

    public function dealertransferdetails()
    {
        return $this->hasMany('App\Models\DealerTransferDetail', 'product_id');
    }

    public function transferdetails()
    {
        return $this->hasMany('App\Models\TransferDetail', 'product_id');
    }

    public function checkoutdetails()
    {
        return $this->hasMany('App\Models\CheckoutDetail', 'product_id');
    }

    public function checkindetails()
    {
        return $this->hasMany('App\Models\CheckinDetail', 'product_id');
    }

    public function catid()
    {
        return $this->belongsTo('App\Models\ProductCategory', 'category_id');
    }
    
    public function userid()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
    
    public function details()
    {
        return $this->hasMany('App\Models\ManufacturerDetail', 'm_product_id');
    }
    
    public function adjust_details()
    {
        return $this->hasMany('App\Models\ManufacturerAdjustment', 'm_product_id');
    }

    public function calcs()
    {
        return $this->hasMany('App\Models\ManufacturerCalculation', 'm_product_id');
    }
    
    public function trdetails()
    {
        return $this->hasMany('App\Models\ManufacturerFinishedDetail', 'm_product_id');
    }
    
    public function warehouseStock()
    {
        return $this->hasOne(WarehouseStock::class, 'product_id');
    }
    
    public function stockid()
    {
        return $this->hasMany('App\Models\WarehouseStock', 'product_id');
    }
}
