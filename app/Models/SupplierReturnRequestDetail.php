<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierReturnRequestDetail extends Model
{
    protected $guarded = [];
    public function product() { return $this->belongsTo(Product::class); }
    public function request() { return $this->belongsTo(SupplierReturnRequest::class, 'supplier_return_request_id'); }
}
