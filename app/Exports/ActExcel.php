<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

use App\Models\CashReceipt;
use App\Models\Checkout;
use App\Models\Checkin;
use App\Models\CashExpenditure;
use App\Models\Setting;
use App\Models\Client;

class ActExcel implements FromView, WithEvents
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

        // Excel va PDF bir xil boshlang'ich saldo bilan ishlashi kerak.
        // Tanlangan sanadan oldingi barcha harakatlar mijozning dastlabki
        // balansiga qo'shiladi.
        $previousCheckouts = Checkout::where('client_id', $this->clientid)
            ->where('date', '<', $from)
            ->get();
        $previousCashReceipts = CashReceipt::where('status', 1)
            ->where('client_id', $this->clientid)
            ->where('date', '<', $from)
            ->get();
        $previousCheckins = Checkin::where('status', 1)
            ->where('client_id', $this->clientid)
            ->where('date', '<', $from)
            ->get();
        $previousCashExpenditures = CashExpenditure::where('supplier_id', $this->clientid)
            ->where('cash_expenditure_types', 8)
            ->where('date', '<', $from)
            ->get();

        $previousDebits = $previousCheckouts->sum(function ($item) {
            return $item->sumtotal();
        }) + $previousCashExpenditures->sum('price');
        $previousCredits = $previousCashReceipts->sum('price')
            + $previousCheckins->sum(function ($item) {
                return $item->sumtotal();
            });
        $startSaldo = (float) ($client->balance ?? 0) + $previousDebits - $previousCredits;
        
        $checkouts = Checkout::with('checktypeid')->where('client_id', $this->clientid)->whereBetween('date', [$this->from, $this->to])->get();
        $cashs = CashReceipt::with('tname')->where('status', 1)->where('client_id', $this->clientid)->whereBetween('date', [$this->from, $this->to])->get();
        $checkins = Checkin::with('typeid')->where('status', 1)->where('client_id', $this->clientid)->whereBetween('date', [$this->from, $this->to])->get();
        $cashExpenditures = CashExpenditure::with('cename')->where('supplier_id', $this->clientid)
            ->where('cash_expenditure_types', 8)
            ->whereBetween('date', [$this->from, $this->to])
            ->get();

        $data = $checkouts
            ->concat($cashs)
            ->concat($checkins)
            ->concat($cashExpenditures)
            ->sortBy('date');
        
        return view('backend.reconciliation_act.excel_v2', compact(
            'data',
            'from',
            'to',
            'client',
            'comp',
            'startSaldo'
        ));
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $range = 'A1:' . $sheet->getHighestColumn() . $sheet->getHighestRow();

                $sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);

                $sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);
            },
        ];
    }
}
