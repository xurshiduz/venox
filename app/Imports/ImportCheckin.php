<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Collection;

use App\Models\WarehouseStock;
use App\Models\CheckinDetail;
use App\Models\Product;
use Auth;
use Str;
 
class ImportCheckin implements ToCollection 
{
    function __construct($part, $wareid) {
        $this->part = $part;
        $this->wareid = $wareid;
    }

    public function collection(Collection $rows)
    {
        $part = $this->part;
        $wareid = $this->wareid;
        
        foreach ($rows as $key => $row){
            if($key != 0){
                $pid = Product::where('barcode', $row[0])->first();
                if($pid){
            
                    $item = CheckinDetail::create([
                        'checkin_id' => $part,
                        'warehouse_id' => $wareid,
                        'category_id' => 1,
                        'product_id' => $pid->id,
                        'currency_type' => 1,
                        'currency_type_price' => 0,
                        'qty' => Str::replace(' ', '', Str::replace(' ', '', $row[1])),
                        'price' => Str::replace(' ', '', Str::replace(' ', '', $row[2])),
                        'total_price' => (Str::replace(' ', '', Str::replace(' ', '', $row[1])) * Str::replace(' ', '', Str::replace(' ', '', $row[2]))),
                        'code' => Str::uuid(),
                        'barcode' => mt_rand(100,999) . time() . mt_rand(10,99)
                    ]); 
                    
                    if(WarehouseStock::where('warehouse_id', $item->warehouse_id)->where('product_id', $item->product_id)->count()){
                        $wsid = WarehouseStock::where('warehouse_id', $item->warehouse_id)->where('product_id', $item->product_id)->first();
                        $wsid->update(['stock' => ($wsid->stock + $item->qty)]);
                    } else {
                        WarehouseStock::create(['warehouse_id' => $item->warehouse_id, 'product_id' => $item->product_id, 'stock' => $item->qty]);
                    }
                }
            }
        }
    }
    
    public function headingRow(): int
    {
        return 2;
    }
}