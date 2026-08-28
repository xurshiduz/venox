<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client as GClient;
use Illuminate\Http\Request;
use App\Exports\DealerTransferOne;
use App\Exports\DealerTransferOneNull;

use App\Models\ProductCategory;
use App\Models\CashReceiptType;
use App\Models\DealerTransferDetail;
use App\Models\CheckinDetail;
use App\Models\CurrencyType;
use App\Models\CashReceipt;
use App\Models\Warehouse;
use App\Models\Currency;
use App\Models\DealerTransfer;
use App\Models\Setting;
use App\Models\History;
use App\Models\Product;
use App\Models\Dealer;
use App\Models\User;

use Carbon\Carbon;
use Excel;
use Auth;
use Str;

class DealerTransferController extends Controller
{
    public function index()
    { 
        
        $managers = User::role('sale')->get();
        if(Auth::user()->hasAnyRole('admin|cashier')){
            $data = DealerTransfer::where('type_id', 1)->orderBy('id', 'desc')->paginate(20);
        } elseif(Auth::user()->hasAnyRole('dealer_admin')) {
            $data = DealerTransfer::where('dealer_id', Auth::user()->dealer_id)->where('type_id', 1)->orderBy('id', 'desc')->paginate(20);
        } else {
            $data = DealerTransfer::where('user_id', Auth::id())->where('type_id', 1)->orderBy('id', 'desc')->paginate(20);
        }
         
        $types = CashReceiptType::all();
        $keyword = NULL; 
        $shipment       = NULL;
        $finish         = NULL;
        $selmanager     = NULL;
        $fromdate       = Carbon::parse('21.02.2024')->format('d.m.Y');
        $todate         = Carbon::now()->format('d.m.Y');

        return view('backend.dealer_transfer.index', compact('data', 'keyword', 'types', 'managers', 'shipment', 'finish', 'selmanager', 'fromdate', 'todate'));
    }
    
    public function checkout_filter(Request $request)
    { 
        $managers = User::role('sale')->get();
        $types = CashReceiptType::all();
        $keyword = $request->input('search');
        
        $shipment       = $request->shipment;
        $finish         = $request->finish;
        $selmanager     = $request->manager;
        $fromdate       = $request->fromdate;
        $todate         = $request->todate;
        
        $result = DealerTransfer::query()->whereBetween('created_at', [Carbon::parse($fromdate)->startOfDay()->format('Y-m-d H:i:s'), Carbon::parse($todate)->endOfDay()->format('Y-m-d H:i:s')])->orderBy('id', 'desc');
        
        if($shipment){
            $result = $result->where('shipment_status', 1);
        }
        
        if($finish){
            $result = $result->where('status', 1);
        }
        
        
        if($selmanager != 'all'){
            $result = $result->where('manager_id', $request->manager);
        }
        
        $data = $result->paginate(20)->appends($request->all());
        return view('backend.dealer_transfer.index', compact('data', 'keyword', 'types', 'managers', 'shipment', 'finish', 'selmanager', 'fromdate', 'todate'));
    }
    
    public function payment_check(Request $request,$id)
    { 
        $item = DealerTransfer::where('code', $id)->first();
        $data = $request->all();
        $sum = Str::replace(',', '.', Str::replace(' ', '', $request->price));
        
        $data['price'] = $sum;
        $data['dealer_transfer_id'] = $item->id;
        $data['dealer_id'] = $item->dealer_id;
        $data['user_id'] = Auth::id();
        $data['date'] = Carbon::now()->format('Y-m-d');
        $data['currency_type'] = 1;
        $data['currency'] = 1;
        $data['code'] = Str::uuid();
        
        CashReceipt::create($data);
        
        if($item->details()->sum('total_price') == $item->payments()->sum('price')){
            $item->update(['step' => 3]);
        }
        
        $request->session()->flash('success', 'Успешно');
        return back();
    }

    public function form($id = null, $page = null)
    {
        $item = null;
        
        if(Auth::user()->hasAnyRole('admin|cashier|sale')){
            $warehouses = Warehouse::where('status', 1)->get();
        } else {
            $warehouses = Warehouse::where('dealer_id', Auth::user()->dealer_id)->where('status', 1)->get();
        } 
        
        $clients = Dealer::all();
        $managers = User::role('sale')->get();
        if($id) {
            $item = DealerTransfer::where('code', $id)->first();
        }
        return view('backend.dealer_transfer.form', compact('item', 'warehouses', 'clients', 'managers', 'page'));
    }

    //API Qty
    public function qty()
    {
        $brid = request()->brid; //qty Keladi
        $cid = request()->cid; //ID keladi
        $item = DealerTransferDetail::findOrFail($cid);
        $oldqty = $item->qty;
        $item->update(['qty' => $brid, 'total_price' => ($brid * $item->price)]);
        
        $citem = DealerTransfer::findOrFail($item->dealer_transfer_id);
        
        if($citem->status == 1){
            $hdata['dealer_id'] = $citem->dealer_id;
            $hdata['user_id'] = Auth::id();
            $hdata['code'] = Str::uuid();
            $hdata['name'] = 8;
            $hdata['database'] = 'dealer_transfer_details';
            $hdata['ip_address'] = request()->ip();
            $hdata['agent'] = request()->server('HTTP_USER_AGENT');
            $hdata['comment'] = 'В контракте "' . ($citem->number_work ? '№ ' . $citem->number_work : 'Черновик #' . $citem->id) . '" от ' . $citem->date . ' изменилось количество запчастей «<u>' . $item->prodid->name . '</u>» с ' . $oldqty . ' на ' . $brid;
            
            $history = History::create($hdata);
            
            $client = new GClient([
                "base_uri" => "https://api.telegram.org",
            ]);
                    
            $clientid       = $citem->number_work ? $citem->number_work : 'Черновик ID#' . $citem->id;
            $ip             = request()->ip();
            $dealer         = $item->warehouseid->dealerid->name;
            $warehouse      = $item->warehouseid->name;
            $barcode        = $item->prodid->barcode;
            $hid            = $history->id;
            $user           = Auth::user()->name . ' Тел: ' . Auth::user()->phone;
            $comment        = $hdata['comment'];
            $date           = Carbon::now()->format('Y-m-d H:i:s');
            
            $bot_token = "7335500759:AAFQCsaM-8jLefkgmNEW3phJxmn_gQIBSQw";
            $chat_id = "-1003627640983";
            $message = "ID#$hid\n<b><u>⚠️ Модуль: Продажа (Позиция-Количество)</u></b>\n<b>🧾 Договор:</b> $clientid \n\n<b>🏬 Дилер:</b> $dealer \n<b>🏭 Склад:</b> $warehouse \n<b>📦 Баркод:</b> $barcode \n<b>👨‍💻 Пользователь:</b> $user \n<b>📝 Примечание:</b> $comment \n<b>📍 ИП адрес:</b> $ip \n<b>⏱ Дата :</b> $date";
            $response = $client->request("GET", "/bot$bot_token/sendMessage", [
                "query" => [
                    "chat_id" => $chat_id,
                    "text" => $message,
                    "parse_mode" => "html"
                ]
            ]);
        }
        
        
        $citem->update(['total_price' => null]);
        return response()->json(['qty' => $item->qty]);
    }
    
    public function send_success()
    {
        $datacid = request()->datacid; //ID Keladi
        
        $item = DealerTransfer::findOrFail($datacid);
        $data['shipment_status'] = 1;
        $item->update($data);
        
        return response()->json(['status' => 1]);
    }
    
    public function select_warehouse()
    {
        $brid = request()->brid; //qty Keladi
        $cid = request()->cid; //ID keladi
        $item = DealerTransfer::findOrFail($cid);
        $item->update(['warehouse_id' => $brid]);
        return response()->json(['status' => 'success']);
    }
    
    public function full_price()
    {
        $gid = request()->gid; //ID keladi
        $itemid = request()->itemid; //ID keladi
        $item = DealerTransfer::findOrFail($itemid);
        $pr = Currency::where('type_id', $gid)->orderBy('id', 'desc')->first()->price;
        
        return response()->json(['full_price' => $item->details()->sum('total_price'), 'curr' => $pr]);
    }

    //API Price
    public function price()
    {
        $pbrid = request()->pbrid; //Price keladi
        $pbrid = str_replace(' ', '', $pbrid);
        $pcid = request()->pcid; //ID keladi
        $item = DealerTransferDetail::findOrFail($pcid);
        $oldprice = $item->price;
        $item->update(['price' => $pbrid, 'total_price' => ($item->qty * $pbrid)]);
        
        $citem = DealerTransfer::findOrFail($item->dealer_transfer_id);
        
        if($citem->status == 1){
            $hdata['dealer_id'] = $citem->dealer_id;
            $hdata['user_id'] = Auth::id();
            $hdata['code'] = Str::uuid();
            $hdata['name'] = 9;
            $hdata['database'] = 'checkout_details';
            $hdata['ip_address'] = request()->ip();
            $hdata['agent'] = request()->server('HTTP_USER_AGENT');
            $hdata['comment'] = 'В контракте "' . ($citem->number_work ? '№ ' . $citem->number_work : 'Черновик #' . $citem->id) . '" от ' . $citem->date . ' изменилось цена запчастей «<u>' . $item->prodid->name . '</u>» с ' . $oldprice . ' на ' . $pbrid;
            
            $history = History::create($hdata);
            
            $client = new GClient([
                "base_uri" => "https://api.telegram.org",
            ]);
                    
            $clientid       = $citem->number_work ? $citem->number_work : 'Черновик ID#' . $citem->id;
            $ip             = request()->ip();
            $dealer         = $item->warehouseid->dealerid->name;
            $warehouse      = $item->warehouseid->name;
            $barcode        = $item->prodid->barcode;
            $hid            = $history->id;
            $user           = Auth::user()->name . ' Тел: ' . Auth::user()->phone;
            $comment        = $hdata['comment'];
            $date           = Carbon::now()->format('Y-m-d H:i:s');
            
            $bot_token = "7335500759:AAFQCsaM-8jLefkgmNEW3phJxmn_gQIBSQw";
            $chat_id = "-1003627640983";
            $message = "ID#$hid\n<b><u>⚠️ Модуль: Продажа (Позиция-Цена)</u></b>\n<b>🧾 Договор:</b> $clientid \n\n<b>🏬 Дилер:</b> $dealer \n<b>🏭 Склад:</b> $warehouse \n<b>📦 Баркод:</b> $barcode \n<b>👨‍💻 Пользователь:</b> $user \n<b>📝 Примечание:</b> $comment \n<b>📍 ИП адрес:</b> $ip \n<b>⏱ Дата :</b> $date";
            $response = $client->request("GET", "/bot$bot_token/sendMessage", [
                "query" => [
                    "chat_id" => $chat_id,
                    "text" => $message,
                    "parse_mode" => "html"
                ]
            ]);
        }
        
        $citem->update(['total_price' => null]);
        return response()->json(['price' => $item->price]);
    }
    
    public function price_total()
    {
        $pbrid = request()->pbrid; //Price keladi
        $pbrid = str_replace(' ', '', $pbrid);
        $pcid = request()->pcid; //ID keladi
        $item = DealerTransfer::findOrFail($pcid);
        $item->update(['total_price' => $pbrid]);
        return response()->json(['status' => 'success']);
    }

    public function currency()
    {
        $currency = request()->currency;
        $curcid = request()->curcid;
        $item = DealerTransferDetail::findOrFail($curcid);
        $item->update(['currency_type' => $currency, 'currency_type_price' => CurrencyType::find($currency)->currencyid->first()->price]);
        return response()->json(['price' => $item->price]);
    }
    
    public function currencies()
    {
        $currency = request()->currency;
        $curcid = request()->curcid;
        $item = DealerTransfer::findOrFail($curcid);
        $item->update(['currency_type' => $currency, 'currency_type_price' => CurrencyType::find($currency)->currencyid->first()->price]);
        return response()->json(['status' => 'success']);
    }

    public function save(Request $request, $id = null)
    {
        //Birinchi productni qidirib kyin create qiladi
        if($request->product_id){
            $chprid = $request->product_id;
        } else {
            $chprid = $request->modal_product;
        }
        
        if(Product::where('barcode', $chprid)->orWhere('name', $request->product_id)->orWhere('fullname', $request->product_id)->count()) {
            
            $data['date'] = Carbon::parse($request->date)->format('Y-m-d');
            $data['reference'] = $request->reference;
            $data['type_id'] = 1;
            $data['warehouse_id'] = $request->warehouse_id;
            $data['dealer_id'] = $request->dealer_id;
            $data['manager_id'] = $request->manager_id ? $request->manager_id : Auth::id();
            
            $pid = Product::where('barcode', $chprid)->orWhere('name', $request->product_id)->orWhere('fullname', $request->product_id)->first();
            
            if ($id) {
                $item = DealerTransfer::where('code', $id)->first();
                if($item) {
                    $item->update($data);
                    $request->session()->flash('success', trans('backend.post_update'));
                }
            } else {
                $data['user_id'] = Auth::id();
                $data['code'] = Str::uuid();
                $data['currency_type'] = 1;
                $data['currency_type_price'] = 1;
            
                $item = DealerTransfer::create($data);
                $request->session()->flash('success', trans('backend.post_create'));
            }

            //Productni qushish
            if(DealerTransferDetail::where('warehouse_id', $item->warehouse_id)->where('dealer_transfer_id', $item->id)->where('product_id', $pid->id)->count()){
                //$ditem = DealerTransferDetail::where('warehouse_id', $item->warehouse_id)->where('product_id', $pid->id)->first();
                //$udata['qty'] = $ditem->qty + 1;
                //$ditem->update($udata);
                $request->session()->flash('error', trans('backend.old_added_product'));
            } else {
                $pr = $pid->currency_type != 1 ? ($pid->currencyid->currencyid->first()->price * $pid->price) : $pid->price;
                $cdata['dealer_transfer_id'] = $item->id;
                $cdata['warehouse_id'] = $item->warehouse_id;
                $cdata['product_id'] = $pid->id;
                $cdata['category_id'] = $pid->category_id;
                $cdata['qty'] = 1;
                $cdata['currency_type'] = 1;
                $cdata['currency_type_price'] = 1;
                $cdata['code'] = Str::uuid();
                $cdata['price'] = $pr;
                $cdata['total_price'] = $pr;
                $cdata['unit_id'] = $pid->unit_id;
                DealerTransferDetail::create($cdata);
            }
            return redirect()->route('dealer_transfer_form', ['id' => $item->code]);
        } else {
            //Agar product topilmasa javob qaytarish
            $request->session()->flash('error', trans('backend.no_product'));
            return back();
        }
    }

    public function delete(Request $request, $id = null)
    {
        $item = DealerTransferDetail::where('code',$id)->first();
        
        $hdata['dealer_id'] = $item->dealer_id;
        $hdata['user_id'] = Auth::id();
        $hdata['code'] = Str::uuid();
        $hdata['name'] = 6;
        $hdata['database'] = 'checkout_details';
        $hdata['ip_address'] = $request->ip();
        $hdata['agent'] = $request->server('HTTP_USER_AGENT');
        $hdata['comment'] = 'Запчасть "<u>' . $item->prodid->name . '</u>" исключена из договора "' . ($item->checkid->number_work ? '№ ' . $item->checkid->number_work : 'Черновик #' . $item->checkid->id) . '" от ' . $item->checkid->date;
        
        $history = History::create($hdata);
        
        $client = new GClient([
            "base_uri" => "https://api.telegram.org",
        ]);
                
        $clientid       = $item->checkid->number_work ? $item->checkid->number_work : 'Черновик ID#' . $item->checkid->id;
        $ip             = $request->ip();
        $dealer         = $item->warehouseid->dealerid->name;
        $warehouse      = $item->warehouseid->name;
        $barcode        = $item->prodid->barcode;
        $hid            = $history->id;
        $user           = Auth::user()->name . ' Тел: ' . Auth::user()->phone;
        $comment        = $hdata['comment'];
        $date           = Carbon::now()->format('Y-m-d H:i:s');
        
        $bot_token = "7335500759:AAFQCsaM-8jLefkgmNEW3phJxmn_gQIBSQw";
        $chat_id = "-1003627640983";
        $message = "ID#$hid\n<b><u>⚠️ Модуль: Продажа (Позиция)</u></b>\n<b>🧾 Договор:</b> $clientid \n\n<b>🏬 Дилер:</b> $dealer \n<b>🏭 Склад:</b> $warehouse \n<b>📦 Баркод:</b> $barcode \n<b>👨‍💻 Пользователь:</b> $user \n<b>📝 Примечание:</b> $comment \n<b>📍 ИП адрес:</b> $ip \n<b>⏱ Дата :</b> $date";
        $response = $client->request("GET", "/bot$bot_token/sendMessage", [
            "query" => [
                "chat_id" => $chat_id,
                "text" => $message,
                "parse_mode" => "html"
            ]
        ]);
        
        $item->delete();
        return back();
    }
    
    public function one_qty(Request $request, $id = null)
    {
        $item = DealerTransferDetail::where('code',$id)->first();
        $pid = Product::find($item->product_id);
        $pr = $pid->currency_type != 1 ? ($pid->currencyid->currencyid->first()->price * $pid->price) : $pid->price;
        $item->update(['qty' => $request->qty_one, 'price' => $pr, 'total_price' => ($pr * $request->qty_one)]);
        return back();
    }
    
    public function done_status($id = null, $page = null)
    {
        $item = DealerTransfer::where('code',$id)->first();
        
        if($item->number_order == NULL){
            $year = Carbon::now()->format('Y');
            
            $data['transaction'] = $year . time();
            $data['status'] = 1;
            $data['step'] = 2;
            
        
            if(DealerTransfer::whereYear('date', '=', $year)->count()){
                $slice = DealerTransfer::whereYear('date', '=', $year)->max('number_order');
                $data['number_order'] = Str::padLeft(($slice + 1), 6, '0');
                $data['number_work'] = Carbon::now()->format('y') . $data['number_order'];
            } else {
                $data['number_order'] = '000001';
                $data['number_work'] = Carbon::now()->format('y') . $data['number_order'];
            }
            
            $item->update($data);
        }
        
        foreach($item->details()->get() as $det){
            $det->update(['status' => 1]);
        }
        
        if($page){
            return redirect()->to('/dealer_transfers?page='. $page);
        }
        
        return redirect()->route('dealer_transfers_index');
    }

    public function payment_status($id = null)
    {
        $item = DealerTransfer::where('code',$id)->first();
        $data['status'] = 1;
        $data['step'] = 3;
        $item->update($data);
        foreach($item->details()->get() as $det){
            $det->update(['status' => 1]);
        }
        
        $cdata['code'] = Str::uuid();
        $cdata['user_id'] = Auth::id();
        $cdata['price'] = $item->details()->sum('total_price');
        $cdata['date'] = Carbon::now()->format('Y-m-d');
        $cdata['dealer_transfer_id'] = $item->id;
        $cdata['dealer_id'] = $item->dealer_id;
        $cdata['currency_type'] = 1;
        $cdata['currency'] = 1;
        
        CashReceipt::create($cdata);
            
        return redirect()->action('Backend\DealerTransferController@index');
    }

    public function send_status($id = null)
    {
        $item = DealerTransfer::where('code',$id)->first();
        $data['shipment_status'] = 1;
        $item->update($data);
        return back();
    }
    
    public function cancel_status($id = null)
    {
        $item = DealerTransfer::where('code',$id)->first();
        $item->update(['status' => 2]);
        foreach($item->details as $det){
            $det->update(['status' => 2]);
        }
        return back();
    }
    
    public function delete_checkout(Request $request, $id = null)
    {
        $item = DealerTransfer::where('code',$id)->first();
        if($item->details()->count() == 0){
            $item->delete();
        } else {
            $request->session()->flash('error', trans('backend.checkout_count'));
        }
        
        
        return back();
    }

    public function search(Request $request)
    { 
        $keyword = $request->input('search');
        $warehouses = Warehouse::all();
        $categories = ProductCategory::all();
        
        $data = DealerTransfer::where(function ($query) use($keyword) {
                $query->where('number_order', 'like', '%' . $keyword . '%')
                   ->orWhere('number_work', 'like', '%' . $keyword . '%');
              })
        ->paginate(100);

        return view('backend.dealer_transfer.index', compact('data', 'keyword', 'warehouses', 'categories'));
    }

    public function check($id = null)
    {
        $item = null;
        if($id) {
            $item = DealerTransfer::where('code', $id)->first();
        }
        return view('backend.dealer_transfer.check', compact('item'));
    }

    public function print_doc($id = null, $view = null)
    {
        $comp = Setting::all();
        $item = null;
        if($id) {
            $item = DealerTransfer::where('code', $id)->first();
        }
        
        if(Setting::where('atribute', 'document_type')->first()->value == 1){
            return view('backend.dealer_transfer.print_doc_old', compact('item', 'comp', 'view'));
        }
        return view('backend.dealer_transfer.print_doc', compact('item', 'comp', 'view'));
    }
    
    public function checkout_excel($id)
    {
        return Excel::download(new DealerTransferOne($id), 'export- ' . $id . '.xlsx');
    }
    
    public function checkout_excel_null($id)
    {
        return Excel::download(new DealerTransferOneNull($id), 'export-null- ' . $id . '.xlsx');
    }
    
    
}