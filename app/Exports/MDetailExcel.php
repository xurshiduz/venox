<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithDefaultStyles;

use App\Models\ManufacturerDetailCalculation;
use App\Models\ManufacturerFactoryType;
use App\Models\ManufacturerDetail;
use App\Models\Setting;

class MDetailExcel implements FromView, WithDefaultStyles 
{ 
    
    function __construct($id) {
        $this->id = $id;
    }
        
    public function view(): View
    {
        $item = ManufacturerDetail::find($this->id);
        $factn = ManufacturerFactoryType::find($item->m_factory_type);
        $details = ManufacturerDetailCalculation::where('m_detail_id', $item->id)->orderBy('id', 'desc')->get();
        return view('backend.manufacturer.details.excel', compact('item', 'factn', 'details'));
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