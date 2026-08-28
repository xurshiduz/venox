<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client as GClient;
use Illuminate\Http\Request;

use App\Models\WarehouseStock;
use App\Models\CheckoutDetail;
use App\Models\TransferDetail;
use App\Models\Warehouse;
use App\Models\Transfer;
use App\Models\Checkout;
use App\Models\Product;
use App\Models\History;

use Carbon\Carbon;
use Excel;
use Auth;
use Str;

class TransferController extends Controller
{
    public function index()
    { 
        $data = Transfer::orderBy('id', 'desc')->paginate(20); 
        $keyword = NULL; 
        return view('backend.transfers.index', compact('data', 'keyword'));
    }

    public function form($id = null)
    {
        $item = null;
        $warehouses = Warehouse::whereNull('factory_id')->where('status', 1)->get();

        if($id) {
            $item = Transfer::where('code', $id)->first();
        }
        
        return view('backend.transfers.form', compact('item', 'warehouses'));
    }

    public function save(Request $request, $id = null)
    {
        if($request->product_id){
            $chprid = $request->product_id;
        } else {
            $chprid = $request->modal_product;
        }
        
        if(Product::where('barcode', $chprid)->orWhere('barcode', '0'. $chprid)->orWhere('name', $request->product_id)->orWhere('fullname', $request->product_id)->count()) {
            
            $year = Carbon::now()->format('Y');
            $data['date'] = Carbon::parse($request->date)->format('Y-m-d');
            $data['reference'] = $request->reference;
            $data['warehouse_out'] = $request->warehouse_out;
            $data['warehouse_in'] = $request->warehouse_in;
            $pid = Product::where('barcode', $chprid)->orWhere('barcode', '0'. $chprid)->orWhere('name', $request->product_id)->orWhere('fullname', $request->product_id)->first();
            if ($id) {
                $item = Transfer::where('code', $id)->first();
                if($item) {
                    $item->update($data);
                    $request->session()->flash('success', trans('backend.post_update'));
                }
            } else {
                $data['user_id'] = Auth::id();
                $data['code'] = Str::uuid();
                $item = Transfer::create($data);
                $request->session()->flash('success', trans('backend.post_create'));
            }
            //Productni qushish
            if(TransferDetail::where('warehouse_out', $item->warehouse_out)->where('warehouse_in', $item->warehouse_in)->where('transfer_id', $item->id)->where('product_id', $pid->id)->count()){
                $ditem = TransferDetail::where('warehouse_out', $item->warehouse_out)->where('warehouse_in', $item->warehouse_in)->where('product_id', $pid->id)->first();
                $udata['qty'] = $ditem->qty + 1;
                $ditem->update($udata);
                $request->session()->flash('error', trans('backend.old_added_product'));
            } else {
                $pr = $pid->currency_type != 1 ? ($pid->currencyid->currencyid->first()->price * $pid->price) : $pid->price;
                $cdata['transfer_id'] = $item->id;
                $cdata['warehouse_out'] = $request->warehouse_out;
                $cdata['warehouse_in'] = $request->warehouse_in;
                $cdata['product_id'] = $pid->id;
                $cdata['qty'] = 1;
                $cdata['unit_id'] = $pid->unit_id;
                $cdata['code'] = Str::uuid();
                
                TransferDetail::create($cdata);
                
                if(WarehouseStock::where('warehouse_id', $item->warehouse_out)->where('product_id', $pid->id)->count()){
                    $wsid = WarehouseStock::where('warehouse_id', $item->warehouse_out)->where('product_id', $pid->id)->first();
                    $stc = ($wsid->stock - 1);
                    $wsid->update([
                        'stock' => $stc,
                        'checkin_price' => $pid->checkindetails()->max('price'),
                        'checkout_price' => $pid->price ? $pid->price : $pid->checkoutdetails()->max('price'),
                        'checkin_total_price' => $stc > 0 ? ($pid->checkindetails()->max('price') * $stc) : 0,
                        'checkout_total_price' => $stc > 0 ? (($pid->price ? $pid->price : $pid->checkoutdetails()->max('price')) * $stc) : 0
                    ]);
                } 
                
                if(WarehouseStock::where('warehouse_id', $item->warehouse_in)->where('product_id', $pid->id)->count()){
                    $wsid = WarehouseStock::where('warehouse_id', $item->warehouse_in)->where('product_id', $pid->id)->first();
                    $stc = ($wsid->stock + 1);
                    $wsid->update([
                        'stock' => $stc,
                        'checkin_price' => $pid->checkindetails()->max('price'),
                        'checkout_price' => $pid->price ? $pid->price : $pid->checkoutdetails()->max('price'),
                        'checkin_total_price' => $stc > 0 ? ($pid->checkindetails()->max('price') * $stc) : 0,
                        'checkout_total_price' => $stc > 0 ? (($pid->price ? $pid->price : $pid->checkoutdetails()->max('price')) * $stc) : 0
                    ]);
                    
                } else {
                    WarehouseStock::create([
                        'warehouse_id' => $item->warehouse_in, 
                        'product_id' => $pid->id, 
                        'stock' => 1,
                        'checkin_price' => $pid->checkindetails()->max('price'),
                        'checkout_price' => $pid->price ? $pid->price : $pid->checkoutdetails()->max('price'),
                        'checkin_total_price' => $pid->checkindetails()->max('price') * 1,
                        'checkout_total_price' => ($pid->price ? $pid->price : $pid->checkoutdetails()->max('price')) * 1
                    ]);
                }
            }
            return redirect()->route('transfer_form', ['id' => $item->code]);
        } else {
            //Agar product topilmasa javob qaytarish
            $request->session()->flash('error', trans('backend.no_product'));
            return back();
        }

        return redirect()->action('Backend\TransferController@index');
    }
    
    public function qty()
    {
        $brid = request()->brid; //qty Keladi
        $cid = request()->cid; //ID keladi
        $item = TransferDetail::findOrFail($cid);
        $oldqty = $item->qty;
        $item->update(['qty' => $brid]);
        $pid = Product::find($item->product_id);
        if($oldqty != $item->qty){
            
            if(WarehouseStock::where('warehouse_id', $item->warehouse_out)->where('product_id', $item->product_id)->count()){
                $wsid = WarehouseStock::where('warehouse_id', $item->warehouse_out)->where('product_id', $item->product_id)->first();
                if($oldqty < $item->qty){
                    $stcout = ($wsid->stock - ($item->qty - $oldqty));
                } else {
                    $stcout = ($wsid->stock + ($oldqty - $item->qty));
                }
                
                $wsid->update([
                    'stock' => $stcout,
                    'checkin_price' => $pid->checkindetails()->max('price'),
                    'checkout_price' => $pid->price ? $pid->price : $pid->checkoutdetails()->max('price'),
                    'checkin_total_price' => $stcout > 0 ? ($pid->checkindetails()->max('price') * $stcout) : 0,
                    'checkout_total_price' => $stcout > 0 ? (($pid->price ? $pid->price : $pid->checkoutdetails()->max('price')) * $stcout) : 0
                ]);
                    
            } 
            
            if(WarehouseStock::where('warehouse_id', $item->warehouse_in)->where('product_id', $item->product_id)->count()){
                $inwsid = WarehouseStock::where('warehouse_id', $item->warehouse_in)->where('product_id', $item->product_id)->first();
                if($oldqty < $item->qty){
                    $stcin = ($inwsid->stock + ($item->qty - $oldqty));
                } else {
                    $stcin = ($inwsid->stock - ($oldqty - $item->qty));
                }
                $inwsid->update([
                    'stock' => $stcin,
                    'checkin_price' => $pid->checkindetails()->max('price'),
                    'checkout_price' => $pid->price ? $pid->price : $pid->checkoutdetails()->max('price'),
                    'checkin_total_price' => $stcin > 0 ? ($pid->checkindetails()->max('price') * $stcin) : 0,
                    'checkout_total_price' => $stcin > 0 ? (($pid->price ? $pid->price : $pid->checkoutdetails()->max('price')) * $stcin) : 0
                ]);
            } 
        }
        
        
        $citem = Transfer::findOrFail($item->transfer_id);
        
        if($citem->status == 1){
            $hdata['dealer_id'] = $citem->dealer_id;
            $hdata['user_id'] = Auth::id();
            $hdata['code'] = Str::uuid();
            $hdata['name'] = 8;
            $hdata['database'] = 'transfer_details';
            $hdata['ip_address'] = request()->ip();
            $hdata['agent'] = request()->server('HTTP_USER_AGENT');
            $hdata['comment'] = 'В перемещение между складами "' . ($citem->number_work ? '№ ' . $citem->number_work : 'Черновик #' . $citem->id) . '" от ' . $citem->date . ' изменилось количество запчастей «<u>' . $item->prodid->name . '</u>» с ' . $oldqty . ' на ' . $brid;
            
            $history = History::create($hdata);
            
            $client = new GClient([
                "base_uri" => "https://api.telegram.org",
            ]);
                    
            $clientid       = $citem->number_work ? $citem->number_work : 'Черновик ID#' . $citem->id;
            $ip             = request()->ip();
            $dealer         = $item->warehouseinid->dealerid->name;
            $warehousein    = $item->warehouseinid->name;
            $warehouseout   = $item->warehouseoutid->name;
            $barcode        = $item->prodid->barcode;
            $hid            = $history->id;
            $user           = Auth::user()->name . ' Тел: ' . Auth::user()->phone;
            $comment        = $hdata['comment'];
            $date           = Carbon::now()->format('Y-m-d H:i:s');
            
            $bot_token = "7335500759:AAFQCsaM-8jLefkgmNEW3phJxmn_gQIBSQw";
            $chat_id = "-1003627640983";
            $message = "ID#$hid\n<b><u>⚠️ Модуль: Перемещения (Позиция-Количество)</u></b>\n<b>🧾 Номер жокумента:</b> $clientid \n\n<b>🏬 Дилер:</b> $dealer \n<b>🏭 С склада:</b> $warehouseout\n<b>🏭 на склад:</b> $warehousein \n<b>📦 Баркод:</b> $barcode \n<b>👨‍💻 Пользователь:</b> $user \n<b>📝 Примечание:</b> $comment \n<b>📍 ИП адрес:</b> $ip \n<b>⏱ Дата :</b> $date";
            $response = $client->request("GET", "/bot$bot_token/sendMessage", [
                "query" => [
                    "chat_id" => $chat_id,
                    "text" => $message,
                    "parse_mode" => "html"
                ]
            ]);
        }
        
        
        return response()->json(['qty' => $item->qty]);
    }
    
    public function delete(Request $request, $id)
    {
        $item = TransferDetail::where('code',$id)->first();
        $pid = Product::find($item->product_id);
        if(WarehouseStock::where('warehouse_id', $item->warehouse_out)->where('product_id', $item->product_id)->count()){
            $wsid = WarehouseStock::where('warehouse_id', $item->warehouse_out)->where('product_id', $item->product_id)->first();
            $stcout = $wsid->stock + $item->qty;
            $wsid->update([
                'stock' => $stcout,
                'checkin_price' => $pid->checkindetails()->max('price'),
                'checkout_price' => $pid->price ? $pid->price : $pid->checkoutdetails()->max('price'),
                'checkin_total_price' => $stcout > 0 ? ($pid->checkindetails()->max('price') * $stcout) : 0,
                'checkout_total_price' => $stcout > 0 ? (($pid->price ? $pid->price : $pid->checkoutdetails()->max('price')) * $stcout) : 0
            ]);
        } 
        
        if(WarehouseStock::where('warehouse_id', $item->warehouse_in)->where('product_id', $item->product_id)->count()){
            $wsid = WarehouseStock::where('warehouse_id', $item->warehouse_in)->where('product_id', $item->product_id)->first();
            $stcin = $wsid->stock - $item->qty;
            $wsid->update([
                'stock' => $stcin,
                'checkin_price' => $pid->checkindetails()->max('price'),
                'checkout_price' => $pid->price ? $pid->price : $pid->checkoutdetails()->max('price'),
                'checkin_total_price' => $stcin > 0 ? ($pid->checkindetails()->max('price') * $stcin) : 0,
                'checkout_total_price' => $stcin > 0 ? (($pid->price ? $pid->price : $pid->checkoutdetails()->max('price')) * $stcin) : 0
            ]);
        } 
        
        $item->delete();
        return back();
    }
    
    public function delete_transfer(Request $request, $id)
    {
        $item = Transfer::where('code',$id)->first();
        if($item->details()->count() == 0){
            $item->delete();
        } else {
            $request->session()->flash('error', trans('backend.checkout_count'));
        }
        return back();
    }
    
    public function done_status($id = null, $page = null, $fromdate = null, $todate = null, $manager = null)
    {
        $item = Transfer::where('code',$id)->first();
        if($item->number_order == NULL){
            $year = Carbon::now()->format('Y');
            $data['transaction'] = $item->transaction ? $item->transaction : $year . time();
            if(Transfer::whereYear('date', '=', $year)->count()){
                $slice = Transfer::whereYear('date', '=', $year)->max('number_order');
                $data['number_order'] = Str::padLeft(($slice + 1), 6, '0');
                $data['number_work'] = Carbon::now()->format('y') . $data['number_order'];
            } else {
                $data['number_order'] = '000001';
                $data['number_work'] = Carbon::now()->format('y') . $data['number_order'];
            }
            $item->update($data);
        }
        
        if($fromdate){
            if($page){
                return redirect()->to('/checkout_filter?fromdate=' . $fromdate . '&todate=' . $todate . '&manager='. $manager . '&page=' . $page);
            }
            return redirect()->to('/checkout_filter?fromdate=' . $fromdate . '&todate=' . $todate . '&manager='. $manager);
        }
        if($page){
            return redirect()->to('/transfers?page='. $page);
        }
        return redirect()->route('transfers_index');
    }
}
