<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use GuzzleHttp\Client as GClient;

use App\Models\CashReceiptType;
use App\Models\ProductCategory;
use App\Models\CashReceipt;
use App\Models\Warehouse;
use App\Models\Checkout;
use App\Models\History;
use App\Models\Checkin;
use App\Models\Client;
use App\Models\Dealer;
use App\Models\User;

use Carbon\Carbon;
use Auth;
use Str;

class ClientController extends Controller
{
    public function index()
    { 
        if(Auth::user()->hasAnyRole('admin|select_manager')){
            $data = Client::orderBy('id', 'desc')->paginate(30); 
        } elseif(Auth::user()->hasAnyRole('manufacturer_admin|manufacturer_gp')) {
            $data = Client::where('factory_client', 1)->orderBy('id', 'desc')->paginate(30); 
        } else {
            $data = Client::where('user_id', Auth::id())->orderBy('id', 'desc')->paginate(30); 
        } 
        
        $keyword = NULL;
        return view('backend.clients.index', compact('data', 'keyword'));
    }
    public function api_clients()
    { 
        $data = Client::select('id', 'name')->get();
        return response()->json($data);
    }
    
    public function index_search(Request $request)
    { 
        $keyword = $request->input('search');
        
        $data = Client::where(function ($query) use($keyword) {
                $query->where('name', 'like', '%' . $keyword . '%')
                   ->orWhere('address', 'like', '%' . $keyword . '%')
                   ->orWhere('phone', 'like', '%' . $keyword . '%')
                   ->orWhere('schet', 'like', '%' . $keyword . '%');
              })
        ->paginate(20);
        
        $data->appends($request->all());
        return view('backend.clients.index', compact('data', 'keyword'));
    }
    
    public function checkins($id)
    { 
        
        $cid = Client::where('code',$id)->first();
        
        if(Auth::user()->hasAnyRole('admin|cashier')){
            $data = Checkin::where('client_id', $cid->id)->where('type_id', 1)->orderBy('id', 'desc')->paginate(20);
        } else {
            $data = Checkin::where('client_id', $cid->id)->where('user_id', Auth::id())->where('type_id', 1)->orderBy('id', 'desc')->paginate(20);
        }
         
        $keyword = NULL; 
        return view('backend.checkins.index', compact('data', 'keyword'));
        
    }
    
    public function checkouts($id)
    { 
        
        $cid = Client::where('code',$id)->first();
        $fromdate       = Carbon::parse(Checkout::orderBy('id', 'asc')->first()->created_at)->format('d.m.Y');
        $todate         = Carbon::now()->format('d.m.Y');
        $managers = User::role('sale')->get();
        $clientselect   = $cid->name;
        $sdata          = NULL;
        $draft          = NULL;
        $shipment       = NULL;
        $finish         = NULL;
        $selmanager = $cid->id;
        
        if(Auth::user()->hasAnyRole('admin|cashier')){
            $data = Checkout::where('client_id', $selmanager)->where('type_id', 1)->orderBy('id', 'desc')->paginate(20);
        } else {
            $data = Checkout::where('client_id', $selmanager)->where('user_id', Auth::id())->where('type_id', 1)->orderBy('id', 'desc')->paginate(20);
        }
         
        $types = CashReceiptType::all();
        $keyword = NULL; 
        return view('backend.checkouts.index', compact('data', 'keyword', 'types', 'managers', 'fromdate', 'todate', 'selmanager', 'shipment', 'finish', 'clientselect', 'sdata', 'draft'));
        
    }

    public function form($id = null)
    {
        $item = null;

        if($id) {
            $item = Client::where('code',$id)->first();
        }
        
        return view('backend.clients.form', compact('item'));
    }

    public function delete(Request $request, $id = null)
    {
        $item = Client::where('code',$id)->first();
        if($item->checkouts->count() == 0 && $item->checkins->count() == 0){
            $item->delete();
        }
        
        return back();
    }

    public function save(Request $request, $id = null)
    {

        $data = $request->all();
        $data['is_customer'] = $request->is_customer ? 1 : null;
        $data['is_supplier'] = $request->is_supplier ? 1 : null;
        
        if($request->balance){
            $dprice = str_replace(' ', '', $request->balance);
            $data['balance'] = $dprice;
        }
        
        if(Auth::user()->hasAnyRole('manufacturer_admin|manufacturer_gp')){
            $data['factory_client'] = 1;
            $data['factory_type'] = Auth::user()->m_factory_type;
        }
        $data['dealer_id'] = Auth::user()->dealer_id ? Auth::user()->dealer_id : 1;

        if ($id) {
            $item = Client::where('code',$id)->first();
            
            $oldname        = $item->name;
            $olddirector    = $item->director;
            $oldphone       = $item->phone;
            $oldaddress     = $item->address;
            $oldinn         = $item->inn;
            $oldregion      = $item->region;
            $oldschet       = $item->schet;
            $oldmfo         = $item->mfo;
            $oldoked        = $item->oked;
            $oldcomment     = $item->comment;
            
            if($item) {
                $item->update($data);
                $request->session()->flash('success', trans('backend.post_update'));
            }
            
            if($oldname != $item->name || $olddirector != $item->director || $oldphone != $item->phone || $oldaddress != $item->address || $oldinn != $item->inn || 
            $oldregion != $item->region || $oldschet != $item->schet || $oldmfo != $item->mfo || $oldoked != $item->oked || $oldcomment != $item->comment){
                
                $hdata['dealer_id'] = $item->dealer_id;
                $hdata['user_id'] = Auth::id();
                $hdata['code'] = Str::uuid();
                $hdata['name'] = 1;
                $hdata['database'] = 'clients';
                $hdata['ip_address'] = $request->ip();
                $hdata['agent'] = $request->server('HTTP_USER_AGENT');
                $hdata['comment'] = $oldname != $item->name ? ('Имя (Фирма) с <b>' . ($oldname ? $oldname : null) . '</b> на <b>' . ($item->name ? $item->name  : null) . '</b>' ) : null
                . ($olddirector != $item->director ? ('Имя директора с <b>' . ($olddirector ? $olddirector : null) . '</b> на <b>' . ($item->director ? $item->director : null) . '</b>' ) : null)
                . ($oldaddress != $item->address ? ('Адресс с <b>' . ($oldaddress ? $oldaddress : null) . '</b> на <b>' . ($item->address ? $item->address : null) . '</b>' ) : null)
                . ($oldphone != $item->phone ? ('Контакты с <b>' . ($oldphone ? $oldphone : null) . '</b> на <b>' . ($item->phone ? $item->phone : null) . '</b>' ) : null)
                . ($oldschet != $item->schet ? ('Р/сч с <b>' . ($oldschet ? $oldschet : null) . '</b> на <b>' . ($item->schet ? $item->schet : null) . '</b>' ) : null)
                . ($oldregion != $item->region ? ('Город с <b>' . ($oldregion ? $oldregion : null) . '</b> на <b>' . ($item->region ? $item->region : null) . '</b>' ) : null)
                . ($oldmfo != $item->mfo ? ('МФО с <b>' . ($oldmfo ? $oldmfo : null) . '</b> на <b>' . ($item->mfo ? $item->mfo : null) . '</b>' ) : null)
                . ($oldinn != $item->inn ? ('ИНН с <b>' . ($oldinn ? $oldinn : null) . '</b> на <b>' . ($item->inn ? $item->inn : null) . '</b>' ) : null)
                . ($oldoked != $item->oked ? ('ОКЭД с <b>' . ($oldoked ? $oldoked : null) . '</b> на <b>' . ($item->oked ? $item->oked : null) . '</b>' ) : null)
                . ($oldcomment != $item->comment ? ('Примечание с <b>' . ($oldcomment ? $oldcomment : null) . '</b> на <b>' . ($item->comment ? $item->comment : null) . '</b>' ) : null);
                
                $history = History::create($hdata);
                
                $client = new GClient([
                    "base_uri" => "https://api.telegram.org",
                ]);
                
                $clientid       = $item->name;
                $ip             = $request->ip();
                $hid            = $history->id;
                $dealer         = Auth::user()->dealerid ? Auth::user()->dealerid->name : null;
                $user           = Auth::user()->name;
                $comment        = $hdata['comment'];
                $date           = Carbon::now()->format('Y-m-d H:i:s');
                
                $bot_token = "7335500759:AAFQCsaM-8jLefkgmNEW3phJxmn_gQIBSQw";
                $chat_id = "-1003627640983";
                $message = "ID#$hid\n<b><u>⚠️ Модуль: Клиент</u></b>\n <b>🤵‍♂️ Клиент:</b> $clientid \n\n<b>💠 Филиал:</b> $dealer \n<b>👨‍💻Пользователь:</b> $user \n<b>📝 Примечание:</b> $comment \n<b>📍 ИП адрес:</b> $ip \n<b>⏱ Дата :</b> $date";
                $response = $client->request("GET", "/bot$bot_token/sendMessage", [
                    
                    "query" => [
                        "chat_id" => $chat_id,
                        "text" => $message,
                        "parse_mode" => "html"
                    ]
                ]);
                
            }


        } else {
            $data['user_id'] = Auth::id();
            $data['code'] = Str::uuid();
            
            $item = Client::create($data);
            $request->session()->flash('success', trans('backend.post_create'));
        }

        return redirect()->action('Backend\ClientController@index');
    }
    
    //
    public function balance_index()
    { 
        $data = CashReceipt::where('balance_client', 1)->orderBy('id', 'desc')->paginate(30); 
        
        $keyword = NULL;
        return view('backend.clients.balance_index', compact('data', 'keyword'));
        
    }
    
    public function balance_hisall_index()
    { 
        $data = Client::whereNotNull('balance')->orderBy('id', 'desc')->paginate(30); 
        
        $keyword = NULL;
        return view('backend.clients.balance_client', compact('data', 'keyword'));
    }
    
    public function balance_history_index($id)
    { 
        $citem = Client::where('code', $id)->first();
        $data = CashReceipt::where('balance_client', 1)->where('client_id', $citem->id)->orderBy('id', 'desc')->paginate(30); 
        
        $keyword = NULL;
        return view('backend.clients.balance_index', compact('data', 'keyword'));
    }

    public function balance_form($id = null)
    {
        $item = null;
        $clients = Client::all();
        $types = CashReceiptType::where('status', 1)->get();

        if($id) {
            $item = CashReceipt::where('balance_client', 1)->where('code',$id)->first();
        }
        
        return view('backend.clients.balance_form', compact('item', 'clients', 'types'));
    }

    public function balance_save(Request $request, $id = null)
    {
        $data['date'] = Carbon::parse($request->date)->format('Y-m-d');
        $data['cash_receipt_type'] = $request->cash_receipt_type;
        $data['balance_client'] = 1;
        
        
        if($request->clientselect){
            if(Client::where('name', $request->clientselect)->count()){
                $idcl = Client::where('name', $request->clientselect)->first();
                $data['client_id'] = $idcl->id;
            } else {
                
            $request->session()->flash('error', 'Mijoz topilmadi');
            return back();
            }
        } else {
            $data['client_id'] = $request->client_id;
        }
        
        if($request->price){
            $dprice = str_replace(' ', '', $request->price);
            $data['price'] = $dprice;
        }

        if ($id) {
            $item = CashReceipt::where('code',$id)->first();
            if($item) {
                $item->update($data);
                $request->session()->flash('success', trans('backend.post_update'));
            }
        } else {
            $data['user_id'] = Auth::id();
            $data['code'] = Str::uuid();
            
            $item = CashReceipt::create($data);
            $item->clientname->update(['balance' => $item->clientname->balance + $dprice]);
            $request->session()->flash('success', 'Balansga qo`shildi');
        }
        
        if($request->clientselect){
            return back();
        } else {
            return redirect()->action('Backend\ClientController@balance_index');
        }
    }

    public function balance_status(Request $request, $id)
    {
        $item = CashReceipt::where('code',$id)->first();
        if($item) {
            $item->update(['status' => 0]);
            $item->clientname->update(['balance' => $item->clientname->balance - $item->price]);
            $request->session()->flash('success', trans('backend.post_update'));
        }
        return back();
    }
}
