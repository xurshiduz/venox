<?php

namespace App\Imports;

use App\Models\ManufacturerRawDetail;
use App\Models\ManufacturerRawMaterial;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
Use PhpOffice\PhpSpreadsheet\Shared\Date;
use Auth;
use Str;
 
class ImportRawDetail implements ToCollection
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
                
                $pid = ManufacturerRawMaterial::where('name', $row[0])->orWhere('id', $row[0])->orWhere('barcode', $row[0])->first();

                ManufacturerRawDetail::create([
                    'm_raw_purchase_id' => $part,
                    'm_raw_material_id' => $pid->id,
                    'category_id' => $pid->category_id,
                    'qty' => $row[1],
                    'price' => $row[2],
                    'total_price' => ($row[1] * $row[2]),
                    'code' => Str::uuid()
                ]); 
                
            }
            
        }
    }

    
    
    public function headingRow(): int
    {
        return 2;
    }
}