<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithDefaultStyles;
use App\Models\ManufacturerFactoryType;
use App\Models\Product;
use App\Models\Warehouse;

class MStock implements FromView, WithDefaultStyles 
{ 
    
    function __construct($start, $finish, $factoryid) {
        $this->start = $start;
        $this->finish = $finish;
        $this->factoryid = $factoryid;
    }
        
    public function view(): View
    {
        $start = $this->start;
        $finish = $this->finish;
        $factoryid = $this->factoryid;
        
        $factn = ManufacturerFactoryType::find($factoryid);
        $data = Product::where('factory_id', 1)->where('m_factory_type', $factoryid)->orderBy('name', 'asc')->get();
        
        return view('backend.manufacturer.product_stocks.excel', compact('data', 'start', 'finish', 'factn'));
    }
    
    public function defaultStyles($defaultStyle): array
    {
        return [
            'alignment' => [
                'wrapText' => true
            ],
        ];
    }
}