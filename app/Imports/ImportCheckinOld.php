<?php

namespace App\Imports;

use App\Models\CheckinDetail;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
Use PhpOffice\PhpSpreadsheet\Shared\Date;
use Auth;
use Str;
 
class ImportCheckin implements ToCollection
{
    function __construct($part) {
        $this->part = $part;
    }

    public function collection(Collection $rows)
    {
        $part = $this->part;
        
        foreach ($rows as $key => $row) 
        {
            if($key != 0){
                
                $pid = Product::where('name', $row[0])->orWhere('fullname', $row[0])->orWhere('barcode', $row[0])->first();
                $ware = Warehouse::where('name', $row[1])->orWhere('id', $row[1])->first();

                CheckinDetail::create([
                    'checkin_id' => $part,
                    'product_id' => $pid->id,
                    'warehouse_block_id' => $ware->id,
                    'category_id' => $pid->category_id,
                    'qty' => $row[2],
                    'currency_type' => 1,
                    'currency_type_price' => 0,
                    'price' => str_replace(' ', '', $row[3]),
                    'total_price' => ($row[2] * $row[3]),
                    'code' => Str::uuid(),
                    'barcode' => mt_rand(10,99) . time()
                ]); 
                
            }
            
        }
    }

    
    
    public function headingRow(): int
    {
        return 2;
    }
}