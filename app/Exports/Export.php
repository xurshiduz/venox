<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Models\Checkin;

class Export implements FromView
{ 
    
    function __construct($part) {
        $this->part = $part;
    }
        
    public function view(): View
    {
        return view('backend.checkins.excel', [
                'data' => Checkin::where('code', $this->part)->first()
        ]);
    }
}