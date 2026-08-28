<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

use App\Models\CheckinDetail;
use App\Models\Checkin;

use Carbon\Carbon;

class CheckinDetailExport implements FromView, ShouldAutoSize
{ 
    
    function __construct($fromdate, $todate) {
        $this->fromdate = $fromdate;
        $this->todate = $todate;
    }
        
    public function view(): View
    {
        $fromdate       = $this->fromdate;
        $todate         = $this->todate;
        
        $data = CheckinDetail::query()
            ->with([
                'prodid.unitid',
                'checkid.supid'
            ])
            ->whereHas('checkid', function ($query) use ($fromdate, $todate) {
                $query->orderBy('date', 'asc')->whereBetween('date', [Carbon::parse($fromdate)->format('Y-m-d'), Carbon::parse($todate)->format('Y-m-d')]);
            })
            ->join('products', 'checkin_details.product_id', '=', 'products.id')
            ->select('checkin_details.*')
            ->get();
            
        return view('backend.checkins.sverka_excel', compact('data', 'fromdate', 'todate'));
    }
}