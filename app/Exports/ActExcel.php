<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

use App\Models\CashReceipt;
use App\Models\Checkout;
use App\Models\Checkin;
use App\Models\Setting;
use App\Models\Client;

class ActExcel implements FromView
{ 
    
    function __construct($from, $to, $clientid) {
        $this->from     = $from;
        $this->to       = $to;
        $this->clientid = $clientid;
    }
        
    public function view(): View
    {
        $from = $this->from;
        $to = $this->to;
        $comp = Setting::find(1)->value;
        $client = Client::find($this->clientid);
        
        $checkouts = Checkout::where('client_id', $this->clientid)->whereBetween('date', [$this->from, $this->to])->get();
        $cashs = CashReceipt::where('status', 1)->where('client_id', $this->clientid)->whereBetween('date', [$this->from, $this->to])->get();
        $checkins = Checkin::where('status', 1)->where('client_id', $this->clientid)->whereBetween('date', [$this->from, $this->to])->get();
        $data = $checkouts->concat($cashs)->concat($checkins)->sortBy('date');
        
        $data = $checkouts->concat($cashs)->concat($checkins)->sortBy('date');
        
        return view('backend.reconciliation_act.excel', compact('data', 'from', 'to', 'client', 'comp'));
    }
}