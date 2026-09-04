<?php

namespace App\Http\Controllers\Backend;

use App\Exports\CashReceiptsExport;
use App\Http\Controllers\Controller; 
use GuzzleHttp\Client as GClient;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\CashReceipt;
use App\Models\History;
use App\Models\Checkout;
use App\Models\CashReceiptType;
use App\Models\Client;
use Auth;
use Str;
use Carbon\Carbon;

class CashReceiptController extends Controller
{
    public function index()
    { 
        if(Auth::user()->hasAnyRole('admin|cashier')){
            $data = CashReceipt::where('status', 1)->orderBy('id', 'desc')->paginate(40);
        } elseif(Auth::user()->hasAnyRole('diler_admin')) {
            $data = CashReceipt::where('dealer_id', Auth::user()->dealer_id)->where('status', 1)->orderBy('id', 'desc')->paginate(40);
        } else {
            $data = CashReceipt::where('user_id', Auth::id())->where('status', 1)->orderBy('id', 'desc')->paginate(40);
        }
        $keyword = NULL; 
        return view('backend.cash_receipts.index', compact('data', 'keyword'));
    }
    public function index_his()
    { 
        if(Auth::user()->hasAnyRole('admin|cashier')){
            $data = CashReceipt::where('status', 0)->orderBy('id', 'desc')->paginate(40);
        } elseif(Auth::user()->hasAnyRole('diler_admin')) {
            $data = CashReceipt::where('dealer_id', Auth::user()->dealer_id)->where('status', 0)->orderBy('id', 'desc')->paginate(40);
        } else {
            $data = CashReceipt::where('user_id', Auth::id())->where('status', 0)->orderBy('id', 'desc')->paginate(40);
        }
        $keyword = NULL; 
        return view('backend.cash_receipts.index', compact('data', 'keyword'));
    }

    public function excel()
    {
        $query = CashReceipt::query()->where('status', 1);

        if (Auth::user()->hasAnyRole('dealer_admin|diler_admin')) {
            $query->where('dealer_id', Auth::user()->dealer_id);
        } elseif (! Auth::user()->hasAnyRole('admin|cashier')) {
            $query->where('user_id', Auth::id());
        }

        $receipts = $query
            ->with(['clientname', 'tname'])
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        return Excel::download(
            new CashReceiptsExport($receipts),
            'kassa-kirimlari-' . Carbon::now()->format('Y-m-d') . '.xlsx'
        );
    }
    
    public function portfolio()
    { 
        $data = Checkout::where('status', 1)->whereNotNull('number_work')->orderBy('id', 'desc')->get(); 
        $keyword = NULL; 
        return view('backend.portfolio.index', compact('data', 'keyword'));
    }
    
    public function get_change_client_id()
    { 
        $data = CashReceipt::whereNotNull('checkout_id')->get(); 
        
        foreach($data as $aaa){
            if($aaa->client_id != $aaa->contracktname->client_id){
                dd($aaa->id);
            }
        }
    }

    public function form($id = null, $page = null, $checkout = null)
    {
        $item = null;
        $clients = Client::all();
        
        $types = CashReceiptType::where('status', 1)->get();
    
        if($id) {
            $item = CashReceipt::where('code', $id)->first();
        }
        
        return view('backend.cash_receipts.form', compact('item', 'clients', 'types', 'checkout', 'page'));
    }

    public function save(Request $request, $id = null, $page = null, $checkout = null)
    {
        $data['date'] = Carbon::parse($request->date)->format('Y-m-d');
        $data['client_id'] = $request->client_id;
        $data['cash_receipt_type'] = $request->cash_receipt_type;
        $data['price'] = Str::replace(',', '.', Str::replace(' ', '', $request->price));
        $data['comment'] = $request->comment;
        
        if ($id) {
            $item = CashReceipt::where('code', $id)->first();
            if($item) {
                $item->update($data);
                if($item->contracktname){
                    $chitem = Checkout::find($item->checkout_id);
                    $oplata = $chitem->payments()->where('status', 1)->sum('price');
                    $chitem->update(['total_price_payme' => $oplata, 'total_price_debt' => $chitem->total_price - $oplata]);
                }
                $request->session()->flash('update_cash', trans('backend.post_update'));
            }
        } else {
            $data['code'] = Str::uuid();
            $data['user_id'] = Auth::id();
            $item = CashReceipt::create($data);
            $request->session()->flash('success_cash', trans('backend.post_create'));
        }
        
        if($checkout){
            return redirect()->to('/checkouts?page='. $page);
            
        }

        return redirect()->action('Backend\CashReceiptController@index');
    }
    
    public function status(Request $request, $id = null, $page = null, $checkout = null)
    {
        $item = CashReceipt::where('code',$id)->first();
        if($item->status == 1){
            $data['status'] = 0;
        } else {
            $data['status'] = 1;
        }
        
        $hdata['dealer_id'] = $item->dealer_id;
        $hdata['user_id'] = Auth::id();
        $hdata['code'] = Str::uuid();
        $hdata['name'] = 5;
        $hdata['database'] = 'cash_receipts';
        $hdata['ip_address'] = $request->ip();
        $hdata['agent'] = $request->server('HTTP_USER_AGENT');
        $hdata['comment'] = ($item->checkout_id ? 'Номер документа ' . ($item->contracktname ? $item->contracktname->number_work : null). ' ' : NULL)  .  ($data['status'] == 1 ? 'ID #' . $item->id . ' активирован':  'ID #' . $item->id . ' аннулирован');
        
        $history = History::create($hdata);
        
        $client = new GClient([
            "base_uri" => "https://api.telegram.org",
        ]);
        
        $ip             = $request->ip();
        $dealer         = Auth::user()->dealerid ? Auth::user()->dealerid->name : null;
        $user           = Auth::user()->name;
        $hid            = $history->id;
        $comment        = $hdata['comment'];
        $summ           = $item->price . ' сум';
        $date           = Carbon::now()->format('Y-m-d H:i:s');
        
        $bot_token = "7335500759:AAFQCsaM-8jLefkgmNEW3phJxmn_gQIBSQw";
        $chat_id = "-1003627640983";
        $message = "ID#$hid\n<b><u>⚠️ Модуль: Поступление и Затраты</u></b>\n\n<b>💠 Филиал:</b> $dealer \n<b>💵 Сумма:</b> $summ \n<b>👨‍💻Пользователь:</b> $user \n<b>📝 Примечание:</b> $comment \n<b>📍 ИП адрес:</b> $ip \n<b>⏱ Дата :</b> $date";
        $response = $client->request("GET", "/bot$bot_token/sendMessage", [
            
            "query" => [
                "chat_id" => $chat_id,
                "text" => $message,
                "parse_mode" => "html"
            ]
        ]);
            
        $item->update($data);
        
        if($item->contracktname){
            $chitem = Checkout::find($item->checkout_id);
            $oplata = $chitem->payments()->where('status', 1)->sum('price');
            $chitem->update(['total_price_payme' => $oplata, 'total_price_debt' => $chitem->total_price - $oplata]);
        }
        
        if($checkout){
            return redirect()->to('/checkouts?page='. $page);
        }
        return back();
    }
    
    public function refresh_status(Request $request, $id = null)
    {
        $item = Checkout::where('code', $id)->first();
        $sum = CashReceipt::where('status', 1)->where('checkout_id',$item->id)->sum('price');
        $item->update(['total_price_payme' => $sum, 'total_price_debt' => $item->total_price - $sum]);
        
        return response()->json(['total_price_payme' => $sum, 'total_price_debt' => $item->total_price - $sum]);
        return back();
    }

    public function import_save(Request $request)
    {
        $data = $request->all();
        $data['code'] = Str::uuid();

        $item = ProductCategory::create($data);
        $request->session()->flash('success', trans('backend.post_create'));

        return redirect()->action('Backend\CategoryController@index');
    }
}
