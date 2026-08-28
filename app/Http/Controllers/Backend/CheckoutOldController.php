<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client as GClient;
use Illuminate\Http\Request;
use App\Exports\CheckoutOne;
use App\Exports\CheckoutOneNull;
use App\Exports\DayExcel;

use App\Models\InventoryDetail;
use App\Models\ProductCategory;
use App\Models\CashReceiptType;
use App\Models\CheckoutDetail;
use App\Models\WarehouseStock;
use App\Models\CheckinDetail;
use App\Models\CheckoutType;
use App\Models\CurrencyType;
use App\Models\CashReceipt;
use App\Models\Warehouse;
use App\Models\Currency;
use App\Models\Checkout;
use App\Models\Checkin;
use App\Models\Setting;
use App\Models\History;
use App\Models\Product;
use App\Models\Client;
use App\Models\User;

use Carbon\Carbon;
use Excel;
use Auth;
use Str;
use DB;

class CheckoutController extends Controller
{
    public function index()
    { 
        
        $managers = User::role('sale')->get();
        if(Auth::user()->hasAnyRole('admin|cashier|select_manager')){
            $data = Checkout::where('type_id', 1)->orderBy('id', 'desc')->paginate(35);
        } elseif(Auth::user()->hasAnyRole('dealer_admin')) {
            $data = Checkout::where('dealer_id', Auth::user()->dealer_id)->where('type_id', 1)->orderBy('id', 'desc')->paginate(35);
        } else {
            $data = Checkout::where('manager_id', Auth::id())->where('type_id', 1)->orderBy('id', 'desc')->paginate(35);
        }
         
        $types          = CashReceiptType::where('status', 1)->get();
        $keyword        = NULL; 
        $shipment       = NULL;
        $finish         = NULL;
        $selmanager     = NULL;
        $clientselect   = NULL;
        $draft          = NULL;
        $sdata          = NULL;
        $fromdate       = Carbon::now()->subDay()->format('d.m.Y');
        $todate         = Carbon::now()->format('d.m.Y');

        return view('backend.checkouts.index', compact('data', 'keyword', 'types', 'managers', 'shipment', 'finish', 'selmanager', 'fromdate', 'todate', 'clientselect', 'draft', 'sdata'));
    }
    public function refresh_total()
    { 
        foreach(Checkout::all() as $item){
            $sum = CashReceipt::where('status', 1)->where('checkout_id',$item->id)->sum('price');
            $item->update(['total_price' => $item->details()->sum('total_price'), 'total_price_payme' => $sum, 'total_price_debt' => $item->details()->sum('total_price') - $sum]);
        }
        
        dd('success');
    }
    
    public function debtors()
    { 
        
        $managers = User::role('sale')->get();
        if(Auth::user()->hasAnyRole('admin|cashier|select_manager')){
            $data = Checkout::where('total_price_debt', '!=', 0)->where('type_id', 1)->orderBy('id', 'desc')->paginate(35);
        } elseif(Auth::user()->hasAnyRole('dealer_admin')) {
            $data = Checkout::where('total_price_debt', '!=', 0)->where('dealer_id', Auth::user()->dealer_id)->where('type_id', 1)->orderBy('id', 'desc')->paginate(35);
        } else {
            $data = Checkout::where('total_price_debt', '!=', 0)->where('manager_id', Auth::id())->where('type_id', 1)->orderBy('id', 'desc')->paginate(35);
        }
         
        $types          = CashReceiptType::where('status', 1)->get();
        $keyword        = NULL; 
        $shipment       = NULL;
        $finish         = NULL;
        $selmanager     = NULL;
        $clientselect   = NULL;
        $draft          = NULL;
        $sdata          = NULL;
        $fromdate       = Carbon::now()->subDay()->format('d.m.Y');
        $todate         = Carbon::now()->format('d.m.Y');

        return view('backend.checkouts.index', compact('data', 'keyword', 'types', 'managers', 'shipment', 'finish', 'selmanager', 'fromdate', 'todate', 'clientselect', 'draft', 'sdata'));
    }
    
    public function in_price()
    { 
        $from       = Carbon::parse('01.12.2024')->format('d.m.Y');
        $to         = Carbon::now()->format('d.m.Y');
        
        $data = CheckoutDetail::whereBetween('created_at', [Carbon::parse($from)->startOfDay()->format('Y-m-d H:i:s'), Carbon::parse($to)->endOfDay()->format('Y-m-d H:i:s')])->get()->groupBy('product_id');

        return view('backend.checkouts.inprice_form', compact('data'));
    }
    
    public function index_stiock_del()
    { 
        foreach(InventoryDetail::where('checkin_status', 0)->where('warehouse_id', 4)->get() as $dd){
            if($dd->prodid){
                if(CheckinDetail::where('checkin_id', 181)->where('product_id', $dd->prodid->id)->where('warehouse_cell_id', $dd->warehouse_cell_id)->count()){
                    $ddd = CheckinDetail::where('checkin_id', 181)->where('product_id', $dd->prodid->id)->where('warehouse_cell_id', $dd->warehouse_cell_id)->first();
                    $ddd->update(['qty' => $ddd->qty + $dd->qty]);
                } else {
                    $detdata['checkin_id'] = 181;
                    $detdata['product_id'] = $dd->prodid->id;
                    $detdata['warehouse_id'] = 4;
                    $detdata['warehouse_cell_id'] = $dd->warehouse_cell_id;
                    $detdata['warehouse_block_id'] = $dd->warehouse_block_id;
                    $detdata['category_id'] = 1;
                    $detdata['qty'] = $dd->qty;
                    $detdata['currency_type'] = 1;
                    $detdata['currency_type_price'] = 0;
                    $detdata['price'] = 0;
                    $detdata['status'] = 1;
                    $detdata['code'] = Str::uuid();
                    $detdata['barcode'] = mt_rand(1000,9999) . time() . mt_rand(1000,9999);
                    $detitem = CheckinDetail::create($detdata);
                }
                $dd->update(['checkin_status' => 1]);
            }
        }
        dd('s');
        //Inventarizatisya
        
        foreach(InventoryDetail::where('warehouse_id', 2)->get() as $dd){
            if($dd->prodid){
                if(CheckinDetail::where('checkin_id', 169)->where('product_id', $dd->prodid->id)->where('warehouse_cell_id', $dd->warehouse_cell_id)->count()){
                    $ddd = CheckinDetail::where('checkin_id', 169)->where('product_id', $dd->prodid->id)->where('warehouse_cell_id', $dd->warehouse_cell_id)->first();
                    $ddd->update(['qty' => $ddd->qty + $dd->qty]);
                } else {
                    $detdata['checkin_id'] = 169;
                    $detdata['product_id'] = $dd->prodid->id;
                    $detdata['warehouse_id'] = 2;
                    $detdata['warehouse_cell_id'] = $dd->warehouse_cell_id;
                    $detdata['warehouse_block_id'] = $dd->warehouse_block_id;
                    $detdata['category_id'] = 1;
                    $detdata['qty'] = $dd->qty;
                    $detdata['currency_type'] = 1;
                    $detdata['currency_type_price'] = 0;
                    $detdata['price'] = 0;
                    $detdata['status'] = 1;
                    $detdata['code'] = Str::uuid();
                    $detdata['barcode'] = mt_rand(1000,9999) . time() . mt_rand(1000,9999);
                    $detitem = CheckinDetail::create($detdata);
                }
                $dd->update(['checkin_status' => 1]);
            }
            
            
        }
        dd('s');
        
        //Prodajani bilan 0 qilish
        foreach(CheckoutDetail::where('warehouse_id', 2)->get() as $dd){
            if(CheckinDetail::where('checkin_id', 2)->where('product_id', $dd->product_id)->count()){
                $ddd = CheckinDetail::where('checkin_id', 2)->where('product_id', $dd->product_id)->first();
                $ddd->update(['qty' => $ddd->qty + $dd->qty]);
            } else {
                $detdata['checkin_id'] = 2;
                $detdata['product_id'] = $dd->product_id;
                $detdata['warehouse_id'] = 2;
                $detdata['category_id'] = 1;
                $detdata['qty'] = $dd->qty;
                $detdata['currency_type'] = 1;
                $detdata['currency_type_price'] = 0;
                $detdata['price'] = 0;
                $detdata['status'] = 1;
                $detdata['code'] = Str::uuid();
                $detdata['barcode'] = mt_rand(1000,9999) . time() . mt_rand(1000,9999);
                $detitem = CheckinDetail::create($detdata);
            }
            
        }
        dd('s');
        
        //
        $checkout = CheckinDetail::whereIn('checkin_id', [1,2])->get();
        
        foreach($checkout as $s){
            $s->delete();
        }
        dd('s');
        
        
        //data buyicha
        $from = date('2024-02-24');
        $to = date('2024-09-30');
        
        $checkout = Checkout::whereBetween('created_at', [$from, $to])->get();
        
        foreach($checkout as $s){
            foreach($s->details() as $det){
                $det->delete();
            }
            $s->delete();
        }
        dd('s');
        
        dd($checkout->count());

        //Prodajani bilan 0 qilish
        foreach(CheckoutDetail::where('warehouse_id', 2)->get() as $dd){
            if(CheckinDetail::where('checkin_id', 2)->where('product_id', $dd->product_id)->count()){
                $ddd = CheckinDetail::where('checkin_id', 2)->where('product_id', $dd->product_id)->first();
                $ddd->update(['qty' => $ddd->qty + $dd->qty]);
            } else {
                $detdata['checkin_id'] = 2;
                $detdata['product_id'] = $dd->product_id;
                $detdata['warehouse_id'] = 2;
                $detdata['category_id'] = 1;
                $detdata['qty'] = $dd->qty;
                $detdata['currency_type'] = 1;
                $detdata['currency_type_price'] = 0;
                $detdata['price'] = 0;
                $detdata['status'] = 1;
                $detdata['code'] = Str::uuid();
                $detdata['barcode'] = mt_rand(1000,9999) . time() . mt_rand(1000,9999);
                $detitem = CheckinDetail::create($detdata);
            }
            
        }
        dd('s');
        
        //
        foreach(Checkin::where('warehouse_id', 2)->get() as $s){
            foreach($s->details as $det){
                $det->delete();
            }
            $s->delete();
        }
        dd('s');
        
        
        //s
        $data = Checkout::all();
        
        foreach($data as $s){
            if($s->details()->count()){
                
            } else {
                $s->delete();
            }
        }
        dd('s');
        
        
        //tan narxlarni kiritish
        $checks = CheckoutDetail::where('tan_price', '>=', 100)->get()->groupBy('product_id');
        
        foreach($checks as $ch){
            foreach(CheckoutDetail::where('product_id', $ch->first()->product_id)->get() as $det){
                $det->update(['tan_price' => $ch->first()->tan_price, 'total_tan_price' => ($ch->first()->qty * $ch->first()->tan_price)]);
            }
        }
        dd('success');
    }
    
    public function index_report()
    { 
        
        $managers = User::role('sale')->get();
        $data = Checkout::where('type_id', 1)->orderBy('id', 'desc')->paginate(40);
         
        $types = CashReceiptType::where('status', 1)->get();
        $keyword = NULL; 
        $shipment       = NULL;
        $finish         = NULL;
        $selmanager     = NULL;
        $fromdate       = Carbon::parse('21.02.2024')->format('d.m.Y');
        $todate         = Carbon::now()->format('d.m.Y');

        return view('backend.checkouts.report_index', compact('data', 'keyword', 'types', 'managers', 'shipment', 'finish', 'selmanager', 'fromdate', 'todate'));
    }
    
    public function all_done_status()
    {
        $adata = Checkout::where('shipment_status', 0)->paginate(200);
        
        foreach($adata as $item){
            $data['shipment_status'] = 1;
            $item->update($data);
        }
        
        dd('success');
        
        $adata = Checkout::whereNull('number_order')->paginate(50);
        
        foreach($adata as $item){
            $year = Carbon::now()->format('Y');
            
            $data['transaction'] = $year . time();
            $data['status'] = 1;
            $data['step'] = 2;
            
            
            if(Checkout::whereYear('date', '=', $year)->count()){
                $slice = Checkout::whereYear('date', '=', $year)->max('number_order');
                $data['number_order'] = Str::padLeft(($slice + 1), 6, '0');
                $data['number_work'] = Carbon::now()->format('y') . $data['number_order'];
            } else {
                $data['number_order'] = '000001';
                $data['number_work'] = Carbon::now()->format('y') . $data['number_order'];
            }
            
            $item->update($data);
            
            foreach($item->details()->get() as $det){
                $det->update(['status' => 1]);
            }
            
        }
        
        
        dd('success');
    }
    
    public function checkout_filter(Request $request)
    { 
        $managers = User::role('sale')->get();
        $types = CashReceiptType::where('status', 1)->get();
        $keyword = $request->input('search');
        
        $shipment       = $request->shipment;
        $finish         = $request->finish;
        $selmanager     = $request->manager;
        $fromdate       = $request->fromdate;
        $todate         = $request->todate;
        $clientselect         = $request->clientselect;
        $draft         = $request->draft;
        $sdata         = $request->sdata;
        
        $result = Checkout::query()->orderBy('id', 'desc');
        
        
        if($sdata){
            $result = $result->whereBetween('created_at', [Carbon::parse($fromdate)->startOfDay()->format('Y-m-d H:i:s'), Carbon::parse($todate)->endOfDay()->format('Y-m-d H:i:s')]);
        }
        
        if($shipment){
            $result = $result->where('shipment_status', 1);
        }
        
        if($finish){
            $result = $result->where('status', 1);
        }
        
        if($selmanager != 'all'){
            $result = $result->where('manager_id', $request->manager);
        }
        
        if($clientselect != null){
            if(Client::where('name', $clientselect)->count()){
                $idcl = Client::where('name', $clientselect)->first();
            
                $result = $result->where('client_id', $idcl->id);
            }
        }
        
        if($draft){
            $result = $result->whereNull('number_order');
        }
        
        $data = $result->paginate(20)->appends($request->all());
        return view('backend.checkouts.index', compact('data', 'keyword', 'types', 'managers', 'shipment', 'finish', 'selmanager', 'fromdate', 'todate', 'clientselect', 'draft', 'sdata'));
    }
    
    public function payment_check_old(Request $request,$id)
    { 
        $item = Checkout::where('code', $id)->first();
        $data = $request->all();
        $sum = Str::replace(',', '.', Str::replace(' ', '', $request->price));
        
        $data['price'] = $sum;
        $data['checkout_id'] = $item->id;
        $data['client_id'] = $item->client_id;
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
    
    public function payment_check(Request $request,$id)
    { 
        $item = Checkout::where('code', $id)->first();
        $sum = Str::replace(',', '.', Str::replace(' ', '', $request->price));
        
        $tsum = $item->total_price_payme + $sum;
        if($tsum > $item->total_price){
            $request->session()->flash('error', 'Оплата больше суммы контракта невозможна.');
        } else {
            $data = $request->all();
            
            $data['price'] = $sum;
            $data['checkout_id'] = $item->id;
            $data['client_id'] = $item->client_id;
            $data['user_id'] = Auth::id();
            $data['date'] = Carbon::now()->format('Y-m-d');
            $data['currency_type'] = 1;
            $data['currency'] = 1;
            $data['code'] = Str::uuid();
            
            CashReceipt::create($data);
            
            $item->update(['total_price_payme' => $tsum, 'total_price_debt' => $item->total_price_debt - $sum]);
            $request->session()->flash('success', 'Успешно');
        }
        
        return back();
    }

    public function form($id = null, $page = null)
    {
        $item = null;
        
        if(Auth::user()->hasAnyRole('admin|cashier|sale')){
            $warehouses = Warehouse::whereNull('factory_id')->where('status', 1)->get();
        } else {
            $warehouses = Warehouse::whereNull('factory_id')->where('dealer_id', Auth::user()->dealer_id)->where('status', 1)->get();
        } 
       
        $managers = User::role('sale')->where('status', 1)->get();
        $types = CheckoutType::all();
        
        if(Auth::user()->hasAnyRole('dealer_admin|dealer_seller')){
            $clients = Client::whereIn('dealer_id', [Auth::user()->dealer_id, 0])->get();
        } else {
            $clients = Client::all();
        }
        
        if($id) {
            $item = Checkout::where('code', $id)->first();
        }
        
        if(Auth::user()->hasAnyRole('tan_report')){
            return view('backend.checkouts.report_form', compact('item', 'warehouses', 'clients', 'managers', 'page', 'types'));
        } else {
            return view('backend.checkouts.form', compact('item', 'warehouses', 'clients', 'managers', 'page', 'types'));
        }
    }

    //API Qty
    public function qty()
    {
        $brid = request()->brid; //qty Keladi
        $cid = request()->cid; //ID keladi
        $item = CheckoutDetail::findOrFail($cid);
        $oldqty = $item->qty;
        $item->update(['qty' => $brid, 'total_price' => ($brid * $item->price), 'total_tan_price' => $item->tan_price >=0 ? ($brid * $item->tan_price) : null]);
        
        if($oldqty != $item->qty){
            if(WarehouseStock::where('warehouse_id', $item->warehouse_id)->where('product_id', $item->product_id)->count()){
                $wsid = WarehouseStock::where('warehouse_id', $item->warehouse_id)->where('product_id', $item->product_id)->first();
                if($oldqty < $item->qty){
                    $stc = ($wsid->stock - ($item->qty - $oldqty));
                } else {
                    $stc = ($wsid->stock + ($oldqty - $item->qty));
                }
                $wsid->update([
                    'stock' => $stc,
                    'checkin_price' => $item->prodid->checkindetails()->max('price'),
                    'checkout_price' => $item->prodid->price ? $item->prodid->price : $item->prodid->checkoutdetails()->max('price'),
                    'checkin_total_price' => $item->prodid->checkindetails()->max('price') * $stc,
                    'checkout_total_price' => ($item->prodid->price ? $item->prodid->price : $item->prodid->checkoutdetails()->max('price')) * $stc
                ]);
            } else {
                WarehouseStock::create(['warehouse_id' => $item->warehouse_id, 'product_id' => $item->product_id, 'stock' => (0 - $item->qty)]);
            }
        }
        
        
        $citem = Checkout::findOrFail($item->checkout_id);
        
        if($citem->status == 1){
            $hdata['dealer_id'] = $citem->dealer_id;
            $hdata['user_id'] = Auth::id();
            $hdata['code'] = Str::uuid();
            $hdata['name'] = 8;
            $hdata['database'] = 'checkout_details';
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
            $chat_id = "-1002409027082";
            $message = "ID#$hid\n<b><u>⚠️ Модуль: Продажа (Позиция-Количество)</u></b>\n<b>🧾 Договор:</b> $clientid \n\n<b>🏬 Дилер:</b> $dealer \n<b>🏭 Склад:</b> $warehouse \n<b>📦 Баркод:</b> $barcode \n<b>👨‍💻 Пользователь:</b> $user \n<b>📝 Примечание:</b> $comment \n<b>📍 ИП адрес:</b> $ip \n<b>⏱ Дата :</b> $date";
            $response = $client->request("GET", "/bot$bot_token/sendMessage", [
                "query" => [
                    "chat_id" => $chat_id,
                    "text" => $message,
                    "parse_mode" => "html"
                ]
            ]);
        }
        
        
        $citem->update(['total_price' => $citem->details()->sum('total_price'), 'total_price_debt' => $citem->total_price_payme ? ($citem->details()->sum('total_price') - $citem->total_price_payme) : $citem->details()->sum('total_price')]);
        return response()->json(['qty' => $item->qty]);
    }
    
    
    public function today_send()
    {
        
        $client = new GClient([
            "base_uri" => "https://api.telegram.org",
        ]);
        
        $result = "";
        $result_pay = "";
        
        foreach(CheckoutDetail::whereDate('created_at', Carbon::today())->get()->groupBy('product_id') as $saa){ 
            $result .= $saa->first()->prodid->name . ' - ' . $saa->sum('qty') . ' ' . $saa->first()->prodid->unitid->name. "\n";
        }
        foreach(CashReceipt::where('status', 1)->whereDate('date', Carbon::today())->get()->groupBy('cash_receipt_type') as $pay){ 
            $result_pay .= $pay->first()->tname->name . ': ' . number_format($pay->sum('price'), 0, '.', ' ') . "\n";
        }
        
        $products = $result = rtrim($result, "\n");
        $pay_list = $result_pay = rtrim($result_pay, "\n");
        $ip             = request()->ip();
        $user           = Auth::user()->name . ' Тел: ' . Auth::user()->phone;
        $summa          = number_format(CashReceipt::where('status', 1)->whereDate('date', Carbon::today())->sum('price'), 0, '.', ' ') ;
        $summa_dolg     = number_format(Checkout::where('status', 1)->whereDate('date', Carbon::today())->sum('total_price_debt'), 0, '.', ' ') ;
        $qtyparts       = number_format(CheckoutDetail::join('checkouts', 'checkout_details.checkout_id', '=', 'checkouts.id')->whereDate('checkouts.date', Carbon::today())->sum(DB::raw('checkout_details.qty')), 0, '.', ' ') ;
        $vidparts       = number_format(CheckoutDetail::join('checkouts', 'checkout_details.checkout_id', '=', 'checkouts.id')->whereDate('checkouts.date', Carbon::today())->count(DB::raw('checkout_details.product_id')), 0, '.', ' ') ;
        $stock_wtm      = number_format(WarehouseStock::where('warehouse_id', 1)->where('stock', '>', 0)->sum('stock'), 0, '.', ' ');
        $stock_mtm      = number_format(WarehouseStock::where('warehouse_id', 2)->where('stock', '>', 0)->sum('stock'), 0, '.', ' ');
        $date           = Carbon::now()->format('Y-m-d H:i:s');
        $ddate           = Carbon::now()->format('d.m.Y');
        
        $bot_token = "7335500759:AAFQCsaM-8jLefkgmNEW3phJxmn_gQIBSQw";
        $chat_id = "-1002409027082";
        $message = "<b><u>🛒 Продажа на $ddate</u></b>\n<b>💵 Продажа:</b> $summa\n<b>📑 Оплата:</b> \n$pay_list \n<b>💵 Продажа (долг):</b> $summa_dolg \n<b>🛠 Кол-во зап.частей:</b> $qtyparts \n<b>📦 Вид зап.частей:</b> $vidparts \n\n<b>👨‍💻 Пользователь:</b> $user \n<b>📍 ИП адрес:</b> $ip \n<b>⏱ Дата :</b> $date \n\n\n<b>📑 Список зачастей:</b> \n$products";
        $response = $client->request("GET", "/bot$bot_token/sendMessage", [
            "query" => [
                "chat_id" => $chat_id,
                "text" => $message,
                "parse_mode" => "html"
            ]
        ]);
        
        return back();
        $message = "<b><u>🛒 Cегодняшняя продажа</u></b>\n<b>💵 Касса:</b> $summa\n<b>💵 Продажа (долг):</b> $summa_dolg \n<b>🛠 Кол-во зап.частей:</b> $qtyparts \n<b>📦 Вид зап.частей:</b> $vidparts \n\n<b>📦 Остаток на `Склад TM` :</b> $stock_wtm \n<b>📦 Остаток на `Магазин TM` :</b> $stock_mtm \n<b>👨‍💻 Пользователь:</b> $user \n<b>📍 ИП адрес:</b> $ip \n<b>⏱ Дата :</b> $date \n\n\n<b>📑 Список зачастей:</b> \n$products";
    }
    
    public function yesterday_send()
    {
        
        $client = new GClient([
            "base_uri" => "https://api.telegram.org",
        ]);
        
        $result = "";
        
        foreach(CheckoutDetail::whereDate('created_at', Carbon::yesterday())->get()->groupBy('product_id') as $saa){ 
            $result .= $saa->first()->prodid->name . ' - ' . $saa->sum('qty') . ' ' . $saa->first()->prodid->unitid->name. "\n";
        }
        
        $products = $result = rtrim($result, "\n");
        
        $ip             = request()->ip();
        $user           = Auth::user()->name . ' Тел: ' . Auth::user()->phone;
        $summa          = number_format(CashReceipt::where('status', 1)->whereDate('date', Carbon::yesterday())->sum('price'), 0, '.', ' ') ;
        $summa_dolg     = number_format(Checkout::where('status', 1)->whereDate('date', Carbon::yesterday())->sum('total_price_debt'), 0, '.', ' ') ;
        $qtyparts       = number_format(CheckoutDetail::join('checkouts', 'checkout_details.checkout_id', '=', 'checkouts.id')->whereDate('checkouts.date', Carbon::yesterday())->sum(DB::raw('checkout_details.qty')), 0, '.', ' ') ;
        $vidparts       = number_format(CheckoutDetail::join('checkouts', 'checkout_details.checkout_id', '=', 'checkouts.id')->whereDate('checkouts.date', Carbon::yesterday())->count(DB::raw('checkout_details.product_id')), 0, '.', ' ') ;
        $stock_wtm      = number_format(WarehouseStock::where('warehouse_id', 1)->where('stock', '>', 0)->sum('stock'), 0, '.', ' ');
        $stock_mtm      = number_format(WarehouseStock::where('warehouse_id', 2)->where('stock', '>', 0)->sum('stock'), 0, '.', ' ');
        $date           = Carbon::yesterday();
        $ddate          = Carbon::yesterday()->format('d.m.Y');
        
        $bot_token = "7335500759:AAFQCsaM-8jLefkgmNEW3phJxmn_gQIBSQw";
        $chat_id = "-1002409027082";
        $message = "<b><u>🛒 Продажа на $ddate</u></b>\n<b>💵 Касса:</b> $summa\n<b>💵 Продажа (долг):</b> $summa_dolg \n<b>🛠 Кол-во зап.частей:</b> $qtyparts \n<b>📦 Вид зап.частей:</b> $vidparts \n\n<b>👨‍💻 Пользователь:</b> $user \n<b>📍 ИП адрес:</b> $ip \n<b>⏱ Дата :</b> $date \n\n\n<b>📑 Список зачастей:</b> \n$products";
        $response = $client->request("GET", "/bot$bot_token/sendMessage", [
            "query" => [
                "chat_id" => $chat_id,
                "text" => $message,
                "parse_mode" => "html"
            ]
        ]);
        
        return back();
        $message = "<b><u>🛒 Вчерашняя продажа</u></b>\n<b>💵 Продажа:</b> $summa\n<b>💵 Продажа (долг):</b> $summa_dolg \n<b>🛠 Кол-во зап.частей:</b> $qtyparts \n<b>📦 Вид зап.частей:</b> $vidparts \n\n<b>📦 Остаток на `Склад TM` :</b> $stock_wtm \n<b>📦 Остаток на `Магазин TM` :</b> $stock_mtm \n<b>👨‍💻 Пользователь:</b> $user \n<b>📍 ИП адрес:</b> $ip \n<b>⏱ Дата :</b> $date \n\n\n<b>📑 Список зачастей:</b> \n$products";
    }
    
    public function send_success()
    {
        $datacid = request()->datacid; //ID Keladi
        
        $item = Checkout::findOrFail($datacid);
        $data['shipment_status'] = 1;
        $item->update($data);
        
        return response()->json(['status' => 1]);
    }
    
    public function select_warehouse()
    {
        $brid = request()->brid; //qty Keladi
        $cid = request()->cid; //ID keladi
        $item = Checkout::findOrFail($cid);
        $item->update(['warehouse_id' => $brid]);
        return response()->json(['status' => 'success']);
    }
    
    public function select_checkout_type()
    {
        $typeid = request()->typeid; //qty Keladi
        $cid = request()->cid; //ID keladi
        $item = Checkout::findOrFail($cid);
        $item->update(['checkout_tip_id' => $typeid]);
        return response()->json(['status' => 'success']);
    }
    
    public function select_checkout_date()
    {
        $typeid = request()->typeid; //qty Keladi
        $dat = Carbon::parse($typeid)->format('Y-m-d');
        $cid = request()->cid; //ID keladi
        $item = Checkout::findOrFail($cid);
        $item->update(['date' => $dat]);
        return response()->json(['status' => 'success']);
    }
    
    public function client_change()
    {
        $clientid = request()->clientid; //qty Keladi
        $cid = request()->cid; //ID keladi
        $item = Checkout::findOrFail($cid);
        $item->update(['client_id' => $clientid]);
        
        if(CashReceipt::where('checkout_id', $cid)->count()){
            foreach(CashReceipt::where('checkout_id', $cid)->get() as $crep){
                    $crep->update(['client_id' => $clientid]);
            }
        }
        return response()->json(['status' => 'success']);
    }
    
    public function full_price()
    {
        $gid = request()->gid; //ID keladi
        $itemid = request()->itemid; //ID keladi
        $item = Checkout::findOrFail($itemid);
        $pr = Currency::where('type_id', $gid)->orderBy('id', 'desc')->first()->price;
        
        return response()->json(['full_price' => $item->details()->sum('total_price'), 'curr' => $pr]);
    }

    //API Price
    public function price()
    {
        $pbrid = request()->pbrid; //Price keladi
        $pbrid = str_replace(' ', '', $pbrid);
        $pcid = request()->pcid; //ID keladi
        $item = CheckoutDetail::findOrFail($pcid);
        $oldprice = $item->price;
        $item->update(['price' => $pbrid, 'total_price' => ($item->qty * $pbrid)]);
        
        $citem = Checkout::findOrFail($item->checkout_id);
        
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
            $chat_id = "-1002409027082";
            $message = "ID#$hid\n<b><u>⚠️ Модуль: Продажа (Позиция-Цена)</u></b>\n<b>🧾 Договор:</b> $clientid \n\n<b>🏬 Дилер:</b> $dealer \n<b>🏭 Склад:</b> $warehouse \n<b>📦 Баркод:</b> $barcode \n<b>👨‍💻 Пользователь:</b> $user \n<b>📝 Примечание:</b> $comment \n<b>📍 ИП адрес:</b> $ip \n<b>⏱ Дата :</b> $date";
            $response = $client->request("GET", "/bot$bot_token/sendMessage", [
                "query" => [
                    "chat_id" => $chat_id,
                    "text" => $message,
                    "parse_mode" => "html"
                ]
            ]);
        }
        
        $citem->update(['total_price' => $citem->details()->sum('total_price'), 'total_price_debt' => $citem->total_price_payme ? ($citem->details()->sum('total_price') - $citem->total_price_payme) : $citem->details()->sum('total_price')]);
        $onetotalp = number_format($item->total_price, 2, '.', ' ');
        $ctotalp = number_format($citem->details()->sum('total_price'), 2, '.', ' ');
        return response()->json(['one_total_price' => $onetotalp, 'total_price' => $ctotalp, 'price' => $item->price]);
    }
    
    public function price_total()
    {
        $pbrid = request()->pbrid; //Price keladi
        $pbrid = str_replace(' ', '', $pbrid);
        $pcid = request()->pcid; //ID keladi
        $item = Checkout::findOrFail($pcid);
        $item->update(['total_price' => $pbrid]);
        return response()->json(['status' => 'success']);
    }
    
    public function price_total_detail()
    {
        $pbrid = request()->pbrid; //Price keladi
        $pbrid = str_replace(' ', '', $pbrid);
        $pcid = request()->pcid; //ID keladi
        $item = CheckoutDetail::findOrFail($pcid);
        $item->update(['total_price' => $pbrid, 'price' => ($pbrid / $item->qty)]);
        $citem = Checkout::findOrFail($item->checkout_id);
        $citem->update(['total_price' => $citem->details()->sum('total_price'), 'total_price_debt' => $citem->total_price_payme ? ($citem->details()->sum('total_price') - $citem->total_price_payme) : $citem->details()->sum('total_price')]);
        $ctotalp = number_format($citem->details()->sum('total_price'), 2, '.', ' ');
        $price = number_format($item->price, 2, '.', ' ');
        return response()->json(['total_price' => $ctotalp, 'price' => $price]);
    }
    
    public function tan_price()
    {
        $pbrid = request()->pbrid; //Price keladi
        $pbrid = str_replace(' ', '', $pbrid);
        $pcid = request()->pcid; //ID keladi
        $item = CheckoutDetail::findOrFail($pcid);
        
        $item->update(['tan_price' => $pbrid, 'total_tan_price' => $item->qty * $pbrid]);
        
        $data = CheckoutDetail::where('product_id', $item->product_id)->get();
        foreach($data as $ditem){
            $ditem->update(['tan_price' => $pbrid]);
        }
        
        $pitem = Product::findOrFail($item->product_id);
        
        $pitem->update(['tan_price' => $pbrid]);
        
        
        return response()->json(['status' => 'success']);
    }

    public function currency()
    {
        $currency = request()->currency;
        $curcid = request()->curcid;
        $item = CheckoutDetail::findOrFail($curcid);
        $item->update(['currency_type' => $currency, 'currency_type_price' => CurrencyType::find($currency)->currencyid->first()->price]);
        return response()->json(['price' => $item->price]);
    }
    
    public function currencies()
    {
        $currency = request()->currency;
        $curcid = request()->curcid;
        $item = Checkout::findOrFail($curcid);
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
        
        if(Product::where('barcode', $chprid)->orWhere('barcode', '0'. $chprid)->orWhere('name', $request->product_id)->orWhere('fullname', $request->product_id)->count()) {
            
            $year = Carbon::now()->format('Y');
            $data['date'] = Carbon::parse($request->date)->format('Y-m-d');
            $data['reference'] = $request->reference;
            $data['type_id'] = 1;
            $data['checkout_tip_id'] = $request->checkout_tip_id ? $request->checkout_tip_id : 1;
            $data['warehouse_id'] = $request->warehouse_id;
            $data['client_id'] = $request->client_id;
            if(Auth::user()->hasAnyRole('dealer_admin')) {
                $data['dealer_id'] = Auth::user()->dealer_id;
            }
            
            $pid = Product::where('barcode', $chprid)->orWhere('barcode', '0'. $chprid)->orWhere('name', $request->product_id)->orWhere('fullname', $request->product_id)->first();
            
            if ($id) {
                $item = Checkout::where('code', $id)->first();
                if($item) {
                    $item->update($data);
                    $request->session()->flash('success', trans('backend.post_update'));
                }
            } else {
                $data['transaction'] = $year . time();
                $data['manager_id'] = $request->manager_id ? $request->manager_id : Auth::id();
                $data['user_id'] = Auth::id();
                $data['code'] = Str::uuid();
                $data['currency_type'] = 1;
                $data['currency_type_price'] = 1;
            
                $item = Checkout::create($data);
                $queue = Checkout::where('date', Carbon::now()->format('Y-m-d'))->count() == 1 ? 1 : (Checkout::where('date', Carbon::now()->format('Y-m-d'))->max('queue') + 1 );
                $item->update(['queue' => $queue]);
                $request->session()->flash('success', trans('backend.post_create'));
            }

            //Productni qushish
            if(CheckoutDetail::where('warehouse_id', $item->warehouse_id)->where('checkout_id', $item->id)->where('product_id', $pid->id)->count()){
                //$ditem = CheckoutDetail::where('warehouse_id', $item->warehouse_id)->where('product_id', $pid->id)->first();
                //$udata['qty'] = $ditem->qty + 1;
                //$ditem->update($udata);
                $request->session()->flash('error', trans('backend.old_added_product'));
            } else {
                $pr = $pid->currency_type != 1 ? ($pid->currencyid->currencyid->first()->price * $pid->price) : $pid->price;
                $cdata['checkout_id'] = $item->id;
                $cdata['warehouse_id'] = $item->warehouse_id;
                $cdata['product_id'] = $pid->id;
                $cdata['category_id'] = $pid->category_id;
                $cdata['qty'] = 1;
                $cdata['currency_type'] = 1;
                $cdata['currency_type_price'] = 1;
                $cdata['code'] = Str::uuid();
                $cdata['price'] = $pr;
                $cdata['total_price'] = $pr;
                $cdata['tan_price'] = $pid->tan_price;
                $cdata['total_tan_price'] = $pid->tan_price >= 0 ? $pid->tan_price : null;
                $cdata['unit_id'] = $pid->unit_id;
                $cdata['user_id'] = Auth::id();
                if(Auth::user()->hasAnyRole('dealer_admin')) {
                    $cdata['dealer_id'] = Auth::user()->dealer_id;
                }
                CheckoutDetail::create($cdata);
                if(WarehouseStock::where('warehouse_id', $item->warehouse_id)->where('product_id', $pid->id)->count()){
                    $wsid = WarehouseStock::where('warehouse_id', $item->warehouse_id)->where('product_id', $pid->id)->first();
                    $st = ($wsid->stock - 1);
                    $wsid->update([
                        'stock' => ($wsid->stock - 1),
                        'checkin_price' => $pid->checkindetails()->max('price'),
                        'checkout_price' => $pid->price ? $pid->price : $pid->checkoutdetails()->max('price'),
                        'checkin_total_price' => $st > 0 ? ($pid->checkindetails()->max('price') * $st) : 0,
                        'checkout_total_price' => $st > 0 ? (($pid->price ? $pid->price : $pid->checkoutdetails()->max('price')) * $st) : 0
                    ]);
                } else {
                    WarehouseStock::create(['warehouse_id' => $item->warehouse_id, 'product_id' => $pid->id, 'stock' => -1]);
                }
            }
            
            $item->update(['total_price' => $item->details()->sum('total_price'), 'total_price_debt' => $item->total_price_payme ? ($item->details()->sum('total_price') - $item->total_price_payme) : $item->details()->sum('total_price')]);
            return redirect()->route('checkout_form', ['id' => $item->code]);
        } else {
            //Agar product topilmasa javob qaytarish
            $request->session()->flash('error', trans('backend.no_product'));
            return back();
        }
    }

    public function delete(Request $request, $id = null)
    {
        $item = CheckoutDetail::where('code',$id)->first();
        
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
        $chat_id = "-1002409027082";
        $message = "ID#$hid\n<b><u>⚠️ Модуль: Продажа (Позиция)</u></b>\n<b>🧾 Договор:</b> $clientid \n\n<b>🏬 Дилер:</b> $dealer \n<b>🏭 Склад:</b> $warehouse \n<b>📦 Баркод:</b> $barcode \n<b>👨‍💻 Пользователь:</b> $user \n<b>📝 Примечание:</b> $comment \n<b>📍 ИП адрес:</b> $ip \n<b>⏱ Дата :</b> $date";
        $response = $client->request("GET", "/bot$bot_token/sendMessage", [
            "query" => [
                "chat_id" => $chat_id,
                "text" => $message,
                "parse_mode" => "html"
            ]
        ]);
        
        if(WarehouseStock::where('warehouse_id', $item->warehouse_id)->where('product_id', $item->product_id)->count()){
            $wsid = WarehouseStock::where('warehouse_id', $item->warehouse_id)->where('product_id', $item->product_id)->first();
            $st = ($wsid->stock + $item->qty);
            $wsid->update([
                'stock' => $st,
                'checkin_price' => $item->prodid->checkindetails()->max('price'),
                'checkout_price' => $item->prodid->price ? $item->prodid->price : $item->prodid->checkoutdetails()->max('price'),
                'checkin_total_price' => $st > 0 ? ($item->prodid->checkindetails()->max('price') * $st) : 0,
                'checkout_total_price' => $st > 0 ? (($item->prodid->price ? $item->prodid->price : $item->prodid->checkoutdetails()->max('price')) * $st) : 0
                
                ]);
        } else {
            WarehouseStock::create(['warehouse_id' => $item->warehouse_id, 'product_id' => $item->product_id, 'stock' => (0 - $item->qty)]);
        }
        $citem = Checkout::find($item->checkout_id);
        $item->delete();
        $citem->update(['total_price' => $citem->details()->sum('total_price'), 'total_price_debt' => $citem->total_price_payme ? ($citem->details()->sum('total_price') - $citem->total_price_payme) : $citem->details()->sum('total_price')]);
        return back();
    }
    
    public function one_qty(Request $request, $id = null)
    {
        $item = CheckoutDetail::where('code',$id)->first();
        $pid = Product::find($item->product_id);
        $oldqty = $item->qty;
        $pr = $pid->currency_type != 1 ? ($pid->currencyid->currencyid->first()->price * $pid->price) : $pid->price;
        $item->update(['qty' => $request->qty_one, 'price' => $pr, 'total_price' => ($pr * $request->qty_one)]);
        
        if($oldqty != $item->qty){
            
            if(WarehouseStock::where('warehouse_id', $item->warehouse_id)->where('product_id', $pid->id)->count()){
                $wsid = WarehouseStock::where('warehouse_id', $item->warehouse_id)->where('product_id', $pid->id)->first();
                if($oldqty < $item->qty){
                    $stc = ($wsid->stock - ($item->qty - $oldqty));
                } else {
                    $stc = ($wsid->stock + ($oldqty - $item->qty));
                }
                $wsid->update([
                    'stock' => $stc,
                    'checkin_price' => $pid->checkindetails()->max('price'),
                    'checkout_price' => $pid->price ? $pid->price : $pid->checkoutdetails()->max('price'),
                    'checkin_total_price' => $stc > 0 ? ($pid->checkindetails()->max('price') * $stc) : 0,
                    'checkout_total_price' => $stc > 0 ? (($pid->price ? $pid->price : $pid->checkoutdetails()->max('price')) * $stc) : 0
                ]);
            } else {
                WarehouseStock::create(['warehouse_id' => $item->warehouse_id, 'product_id' => $pid->id, 'stock' => (0 - $item->qty)]);
            }
            
        }
        $citem = Checkout::find($item->checkout_id);
        $citem->update(['total_price' => $citem->details()->sum('total_price'), 'total_price_debt' => $citem->total_price_payme ? ($citem->details()->sum('total_price') - $citem->total_price_payme) : $citem->details()->sum('total_price')]);
        return back();
    }
    
    public function done_status($id = null, $page = null, $fromdate = null, $todate = null, $manager = null)
    {
        $item = Checkout::where('code',$id)->first();
        if($item->number_order == NULL){
            $year = Carbon::now()->format('Y');
            $data['transaction'] = $item->transaction ? $item->transaction : $year . time();
            $data['status'] = 1;
            $data['step'] = 2;
            if(Checkout::whereYear('date', '=', $year)->count()){
                $slice = Checkout::whereYear('date', '=', $year)->max('number_order');
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
        if($fromdate){
            if($page){
                return redirect()->to('/checkout_filter?fromdate=' . $fromdate . '&todate=' . $todate . '&manager='. $manager . '&page=' . $page);
            }
            return redirect()->to('/checkout_filter?fromdate=' . $fromdate . '&todate=' . $todate . '&manager='. $manager);
        }
        if($page){
            return redirect()->to('/checkouts?page='. $page);
        }
        return redirect()->route('checkouts_index');
    }

    public function payment_status($id = null)
    {
        $item = Checkout::where('code',$id)->first();
        $data['status'] = 1;
        $data['step'] = 3;
        $item->update($data);
        
        foreach($item->details()->get() as $det){
            $det->update(['status' => 1]);
        }
        $cdata['code'] = Str::uuid();
        $cdata['user_id'] = Auth::id();
        $cdata['price'] = $item->details()->sum('total_price');
        if($request->date){
            $cdata['date'] = Carbon::parse($request->date)->format('Y-m-d');
        } else {
            $cdata['date'] = Carbon::now()->format('Y-m-d');
        }
        $cdata['checkout_id'] = $item->id;
        $cdata['client_id'] = $item->client_id;
        $cdata['currency_type'] = 1;
        $cdata['currency'] = 1;
        
        CashReceipt::create($cdata);
            
        return redirect()->action('Backend\CheckoutController@index');
    }

    public function send_status($id = null)
    {
        $item = Checkout::where('code',$id)->first();
        $data['shipment_status'] = 1;
        $item->update($data);
        return back();
    }
    
    public function cancel_status($id = null)
    {
        $item = Checkout::where('code',$id)->first();
        $item->update(['status' => 2]);
        foreach($item->details as $det){
            $det->update(['status' => 2]);
        }
        return back();
    }
    
    public function delete_checkout(Request $request, $id = null)
    {
        $item = Checkout::where('code',$id)->first();
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
        $managers = User::role('sale')->get();
        $data = Checkout::where(function ($query) use($keyword) {
                $query->where('transaction', $keyword);
              })
        ->paginate(100);
         
        $types = CashReceiptType::where('status', 1)->get();
        $keyword = NULL; 
        $shipment       = NULL;
        $finish         = NULL;
        $selmanager     = NULL;
        $fromdate       = Carbon::parse('21.02.2024')->format('d.m.Y');
        $todate         = Carbon::now()->format('d.m.Y');
        $clientselect = NULL;
        $draft = NULL;
        $sdata = NULL;

        return view('backend.checkouts.index', compact('data', 'keyword', 'types', 'managers', 'shipment', 'finish', 'selmanager', 'fromdate', 'todate', 'clientselect', 'draft','sdata'));
    }

    public function check($id = null)
    {
        $item = null;
        if($id) {
            $item = Checkout::where('code', $id)->first();
        }
        return view('backend.checkouts.check', compact('item'));
    }

    public function print_doc($id = null, $view = null)
    {
        $comp = Setting::all();
        $item = null;
        if($id) {
            $item = Checkout::where('code', $id)->first();
        }
        
        if(Setting::where('atribute', 'document_type')->first()->value == 1){
            return view('backend.checkouts.print_doc_old', compact('item', 'comp', 'view'));
        }
        return view('backend.checkouts.print_doc', compact('item', 'comp', 'view'));
    }

    public function report_filter($id = null)
    {
        $managers = User::where('status', 1)->role('sale')->get();
        return view('backend.checkouts.report_filter_all', compact('managers'));
    }

    public function report_print_filter(Request $request)
    {
        $selmanager     = $request->manager_id;
        $fromdate       = $request->fromdate;
        $todate         = $request->todate;
        $result = Checkout::query()->whereBetween('created_at', [Carbon::parse($fromdate)->startOfDay()->format('Y-m-d H:i:s'), Carbon::parse($todate)->endOfDay()->format('Y-m-d H:i:s')])->orderBy('date', 'asc');
        
        if($selmanager != 'all'){
            $result = $result->where('manager_id', $selmanager);
        }
        
        $data = $result->get();
        
        return view('backend.checkouts.report_print_all', compact('data' ,'fromdate', 'todate'));
        
        if($shipment){
            $result = $result->where('shipment_status', 1);
        }
        
        if($finish){
            $result = $result->where('status', 1);
        }
    }

    public function day_filter($id = null)
    {
        $managers = User::where('status', 1)->role('sale')->get();
        $clients = Client::where('status', 1)->get();
        $types = CashReceiptType::where('status', 1)->get();
        $chtypes = CheckoutType::where('status', 1)->get();
        return view('backend.checkouts.day_filter_all', compact('managers', 'clients', 'types', 'chtypes'));
    }

    public function day_print_filter(Request $request)
    {
        $selmanager     = $request->manager_id;
        $selclient      = $request->client_id;
        $fromdate       = $request->fromdate;
        $todate         = $request->todate;
        $checkouttip    = $request->checkout_tip_id;
        $ch_tip_id      = $request->ch_tip_id;
        $result = Checkout::query()->whereBetween('date', [Carbon::parse($fromdate)->format('Y-m-d'), Carbon::parse($todate)->format('Y-m-d')])->orderBy('date', 'asc');
        
        if($selmanager != 'all'){
            $result = $result->where('manager_id', $selmanager);
        }
        
        if($selclient != 'all'){
            $result = $result->where('client_id', $selclient);
        }
        
        if($ch_tip_id != 'all'){
            $result = $result->where('checkout_tip_id', $ch_tip_id);
        }
        
        $data = $result->get();
        
        if($request->type == 'pdf'){
            return view('backend.checkouts.day_print_all', compact('data', 'fromdate', 'todate', 'checkouttip'));
        } 
        
        return Excel::download(new DayExcel($data, $fromdate, $todate, $checkouttip), 'Фильтр по продажам от ' . Carbon::parse($fromdate)->format('Y-m-d') . ' до ' . Carbon::parse($todate)->format('Y-m-d') . '.xlsx');
        
        if($finish){
            $result = $result->where('status', 1);
        }
        
        $result = Checkout::query()->whereBetween('created_at', [Carbon::parse($fromdate)->startOfDay()->format('Y-m-d H:i:s'), Carbon::parse($todate)->endOfDay()->format('Y-m-d H:i:s')])->orderBy('id', 'asc');
    }

    public function report_print($id = null, $view = null)
    {
        $comp = Setting::all();
        $item = null;
        if($id) {
            $item = Checkout::where('code', $id)->first();
        }
        
        return view('backend.checkouts.report_print', compact('item', 'comp', 'view'));
    }
    
    public function checkout_excel($id)
    {
        return Excel::download(new CheckoutOne($id), 'export- ' . $id . '.xlsx');
    }
    
    public function checkout_excel_null($id)
    {
        return Excel::download(new CheckoutOneNull($id), 'export-null- ' . $id . '.xlsx');
    }

    public function report_avg($id = null)
    {
        $managers = User::where('status', 1)->role('sale')->get();
        return view('backend.checkouts.report_avg_index', compact('managers'));
    }

    public function report_print_avg(Request $request)
    {
        $fromdate       = $request->fromdate;
        $todate         = $request->todate;
        $data = CheckoutDetail::whereBetween('created_at', [Carbon::parse($fromdate)->startOfDay()->format('Y-m-d H:i:s'), Carbon::parse($todate)->endOfDay()->format('Y-m-d H:i:s')])->get()->groupBy('product_id');
        
        return view('backend.checkouts.report_avg_print', compact('data' ,'fromdate', 'todate'));
        
        if($shipment){
            $result = $result->where('shipment_status', 1);
        }
        
        if($finish){
            $result = $result->where('status', 1);
        }
    }
    
    
}