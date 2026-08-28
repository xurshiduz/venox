<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithDefaultStyles;

use App\Models\ManufacturerFinishedDetail;
use App\Models\ManufacturerFactoryType;
use App\Models\ManufacturerFinished;
use App\Models\Setting;

class MFinishedExcel implements FromView, WithDefaultStyles 
{ 
    
    function __construct($id) {
        $this->id = $id;
    }
        
    public function view(): View
    {
        $item = ManufacturerFinished::find($this->id);
        $factn = ManufacturerFactoryType::find($item->m_factory_type);
        $details = ManufacturerFinishedDetail::where('m_finished_id', $item->id)->orderBy('id', 'desc')->get();
        return view('backend.manufacturer.finished.excel_finished', compact('item', 'factn', 'details'));
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