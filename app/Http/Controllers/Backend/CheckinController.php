<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client as GClient;
use Illuminate\Http\Request;

use App\Imports\ImportCheckin;
use App\Exports\Export;
use App\Exports\CheckinDetailExport;

use App\Models\WarehouseBlockProduct;
use App\Models\WarehouseBlock;
use App\Models\WarehouseStock;
use App\Models\CheckinDetail;
use App\Models\CheckoutDetail;
use App\Models\InventoryDetail;
use App\Models\CurrencyType;
use App\Models\Warehouse;
use App\Models\Client;
use App\Models\CheckType;
use App\Models\Product;
use App\Models\Checkin;
use App\Models\History;

use Carbon\Carbon;
use Excel;
use Auth;
use Str;

class CheckinController extends Controller
{
    public function index()
    { 
        
        if(Auth::user()->hasAnyRole('admin|cashier')){
            $data = Checkin::orderBy('id', 'desc')->paginate(50);
        } elseif(Auth::user()->hasAnyRole('diler_admin')) {
            $data = Checkin::where('dealer_id', Auth::user()->dealer_id)->orderBy('id', 'desc')->paginate(50);
        } else {
            $data = Checkin::where('user_id', Auth::id())->orderBy('id', 'desc')->paginate(50);
        }
        
        $keyword = NULL; 
        return view('backend.checkins.index', compact('data', 'keyword'));
    }
    
    public function sverka_index()
    { 
        return view('backend.checkins.sverka');
    }
    
    public function sverka_excel(Request $request)
    {
        $fromdate       = $request->fromdate;
        $todate         = $request->todate;
        return Excel::download(new CheckinDetailExport($fromdate, $todate), 'Приход от' . $fromdate . 'до' . $todate . '.xlsx');
    }
    
    public function excel($part)
    {
        return Excel::download(new Export($part), 'export- ' . $part . '.xlsx');
    }

    public function form($id = null)
    {   
        $types = CheckType::where('status', 1)->get();
        $item = null;
        $details = null;
        
        if(Auth::user()->hasAnyRole('admin|cashier|sale')){
            $warehouses = Warehouse::whereNull('factory_id')->where('status', 1)->get();
        } else {
            $warehouses = Warehouse::whereNull('factory_id')->where('dealer_id', Auth::user()->dealer_id)->where('status', 1)->get();
        } 

        if($id) {
            $item = Checkin::where('code', $id)->first();
            $details = CheckinDetail::where('checkin_id', $item->id)->orderBy('id', 'desc')->paginate(500);
            
            // "Тип прихода" qiymati 1 ga teng bo'lsa is_supplier larni olamiz
            if ($item->type_id == 1) {
                $clients = Client::whereNotNull('is_supplier')->get();
            } else {
                $clients = Client::where('status', 1)->get();
            }
        } else {
            // Yangi qo'shilayotgan paytda (default holat)
            $clients = Client::whereNotNull('is_supplier')->get();
        }
        
        return view('backend.checkins.form', compact('item', 'warehouses', 'clients', 'details', 'types'));
    }
    
    public function change_comment(Request $request)
    {
        $item = Checkin::find($request->cid);
        if ($item) {
            $item->reference = $request->val;
            $item->save();
            return response()->json(['status' => true]);
        }
        return response()->json(['status' => false]);
    }

    public function type_change(Request $request)
    {
        // Agar forma tahrirlanayotgan bo'lsa (ID kelsa), turini saqlaymiz
        if ($request->cid) {
            $item = Checkin::find($request->cid);
            if ($item) {
                $item->type_id = $request->val;
                $item->save();
            }
        }

        // Turi 1 bo'lsa, yetkazib beruvchilarni, aks holda hamma aktiv mijozlarni olamiz
        if ($request->val == 1) {
            $clients = Client::whereNotNull('is_supplier')->get();
        } else {
            $clients = Client::where('status', 1)->get();
        }

        return response()->json(['status' => true, 'clients' => $clients]);
    }
    
    public function form_excel($id = null)
    {
        $item = null;
        $details = null;
        if(Auth::user()->hasAnyRole('admin|cashier|sale')){
            $warehouses = Warehouse::whereNull('factory_id')->where('status', 1)->get();
        } else {
            $warehouses = Warehouse::whereNull('factory_id')->where('dealer_id', Auth::user()->dealer_id)->where('status', 1)->get();
        } 
        $clients = Client::whereNotNull('is_supplier')->get();

        if($id) {
            $item = Checkin::where('code', $id)->first();
            $details = CheckinDetail::where('checkin_id', $item->id)->orderBy('id', 'desc')->paginate(200);
        }
        
        return view('backend.checkins.excel_form', compact('item', 'warehouses', 'clients', 'details'));
    }
    
    public function warehouse_block()
    {
        $pbrid = request()->pbrid;
        $pcid = request()->pcid;
        $item = CheckinDetail::findOrFail($pcid);
        
        if($pbrid){
            if(WarehouseBlock::where('warehouse_id', $item->warehouse_id)->where('row', $pbrid)->count()){
                $wb = WarehouseBlock::where('warehouse_id', $item->warehouse_id)->where('row', $pbrid)->first();
            } else {
                $wb = WarehouseBlock::create(['warehouse_id' => $item->warehouse_id, 'row' => $pbrid]);
            }
            
            if(WarehouseBlockProduct::where('warehouse_id', $item->warehouse_id)->where('warehouse_block_id', $wb->id)->where('product_id', $item->product_id)->count()){
                $wbp = WarehouseBlockProduct::where('warehouse_id', $item->warehouse_id)->where('warehouse_block_id', $wb->id)->where('product_id', $item->product_id)->first();
            } else {
                $wbp = WarehouseBlockProduct::create(['warehouse_id' => $item->warehouse_id, 'warehouse_block_id' => $wb->id, 'product_id' => $item->product_id, 'code' => Str::uuid(), 'checkin_detail_id' => $item->id]);
            }
        }
        
        
        $item->update(['warehouse_block_id' => $pbrid]);
        return response()->json(['warehouse_block_id' => $item->warehouse_block_id]);
    }

    public function qty()
    {
        $brid = request()->brid;
        $cid = request()->cid;
        $item = CheckinDetail::findOrFail($cid);
        $oldqty = $item->qty;
        $citem = Checkin::findOrFail($item->checkin_id);
        
        if($citem->status == 1){
            $hdata['dealer_id'] = $citem->dealer_id;
            $hdata['user_id'] = Auth::id();
            $hdata['code'] = Str::uuid();
            $hdata['name'] = 8;
            $hdata['database'] = 'checkin_details';
            $hdata['ip_address'] = request()->ip();
            $hdata['agent'] = request()->server('HTTP_USER_AGENT');
            $hdata['comment'] = 'В контракте "' . ($citem->number_work ? '№ ' . $citem->number_work : 'Черновик #' . $citem->id) . '" от ' . $citem->date . ' изменилось количество запчастей «<u>' . $item->prodid->name . '</u>» с ' . $oldqty . ' на ' . $brid;
            
            $history = History::create($hdata);
            
            $client = new GClient([
                "base_uri" => "https://api.telegram.org",
            ]);
                    
            $clientid       = $citem->number_work ? $citem->number_work : 'Черновик ID#' . $citem->id;
            $ip             = request()->ip();
            $dealer         = $citem->supid->name;
            $warehouse      = $item->warehouseid?->name;
            $barcode        = $item->prodid->barcode;
            $hid            = $history->id;
            $user           = Auth::user()->name . ' Тел: ' . Auth::user()->phone;
            $comment        = $hdata['comment'];
            $date           = Carbon::now()->format('Y-m-d H:i:s');
            
            $bot_token = "7335500759:AAFQCsaM-8jLefkgmNEW3phJxmn_gQIBSQw";
            $chat_id = "-1003627640983";
            $message = "ID#$hid\n<b><u>⚠️ Модуль: Приход (Позиция-Количество)</u></b>\n<b>🧾 Договор:</b> $clientid \n\n<b>🏬 Поставщик:</b> $dealer \n<b>🏭 Склад:</b> $warehouse \n<b>📦 Баркод:</b> $barcode \n<b>👨‍💻 Пользователь:</b> $user \n<b>📝 Примечание:</b> $comment \n<b>📍 ИП адрес:</b> $ip \n<b>⏱ Дата :</b> $date";
            $response = $client->request("GET", "/bot$bot_token/sendMessage", [
                "query" => [
                    "chat_id" => $chat_id,
                    "text" => $message,
                    "parse_mode" => "html"
                ]
            ]);
        }
        
        $item->update(['qty' => $brid, 'total_price' => ($brid * $item->price)]);
        
        if($oldqty != $item->qty){
            if(WarehouseStock::where('warehouse_id', $item->warehouse_id)->where('product_id', $item->product_id)->count()){
                $wsid = WarehouseStock::where('warehouse_id', $item->warehouse_id)->where('product_id', $item->product_id)->first();
                if($oldqty < $item->qty){
                    $stc = ($wsid->stock + ($item->qty - $oldqty));
                } else {
                    $stc = ($wsid->stock - ($oldqty - $item->qty));
                }
                $wsid->update([
                    'stock' => $stc,
                    'checkin_price' => $item->prodid->checkindetails()->max('price'),
                    'checkout_price' => $item->prodid->price ? $item->prodid->price : $item->prodid->checkoutdetails()->max('price'),
                    'checkin_total_price' => $stc > 0 ? ($item->prodid->checkindetails()->max('price') * $stc) : 0,
                    'checkout_total_price' => $stc > 0 ? (($item->prodid->price ? $item->prodid->price : $item->prodid->checkoutdetails()->max('price')) * $stc) : 0
                
                ]);
            } else {
                WarehouseStock::create([
                    'warehouse_id' => $item->warehouse_id, 
                    'product_id' => $item->product_id, 
                    'stock' => $item->qty,
                    'checkin_price' => $item->prodid->checkindetails()->max('price'),
                    'checkout_price' => $item->prodid->price ? $item->prodid->price : $item->prodid->checkoutdetails()->max('price'),
                    'checkin_total_price' => $item->qty > 0 ? ($item->prodid->checkindetails()->max('price') * $item->qty) : 0,
                    'checkout_total_price' => $item->qty > 0 ? (($item->prodid->price ? $item->prodid->price : $item->prodid->checkoutdetails()->max('price')) * $item->qty) : 0
                ]);
            }
        }
        
        return response()->json(['qty' => $item->qty]);
    }

    public function price()
    {
        $pbrid = request()->pbrid;
        $pcid = request()->pcid;
        $item = CheckinDetail::findOrFail($pcid);
        $item->update(['price' => $pbrid, 'total_price' => ($item->qty * $pbrid)]);
        
        if(WarehouseStock::where('warehouse_id', $item->warehouse_id)->where('product_id', $item->product_id)->count()){
            $wsid = WarehouseStock::where('warehouse_id', $item->warehouse_id)->where('product_id', $item->product_id)->first();
            $wsid->update(['checkin_price' => $item->price, 'checkin_total_price' => ($wsid->stock > 0 ? $wsid->stock * $item->price : 0)]);
        } 
            
        return response()->json(['price' => $item->price]);
    }
    
    public function currency_type_change(Request $request)
    {
        $checkout = Checkin::find($request->cid);
        if (!$checkout) {
            return response()->json(['status' => 'error']);
        }
    
        $currencyType = $request->currency_type;
    
        $checkout->currency_type = $currencyType;
        $checkout->save();

        $checkout->details()->update([
            'currency_type' => $currencyType,
            'currency_type_price' => (int) $currencyType === 1
                ? (float) $checkout->currency_type_price
                : 1,
        ]);
        
        return response()->json([
            'status' => 'success'
        ]);
    }
    
    public function currency_price_change(Request $request)
    {
        $checkout = Checkin::find($request->cid);
        if (!$checkout) {
            return response()->json(['status' => 'error']);
        }
    
        $price = (float) $request->price;
    
        // Asosiy chekda narxni saqlash
        $checkout->currency_type_price = $price;
        $checkout->save();

        $checkout->details()
            ->where('currency_type', 1)
            ->update(['currency_type_price' => $price]);
    
        return response()->json([
            'status' => 'success',
            'price'  => $price
        ]);
    }

    public function currency(Request $request)
    {
        $detail = CheckinDetail::with('checkid')->findOrFail($request->curcid);
        $currencyType = (int) $request->currency;
        $rate = $currencyType === 1
            ? (float) ($detail->checkid->currency_type_price ?? 0)
            : 1;

        $detail->update([
            'currency_type' => $currencyType,
            'currency_type_price' => $rate,
        ]);

        return response()->json(['status' => 'success']);
    }

    public function save(Request $request, $id = null)
    {
        // 1. Ma'lumotlarni tayyorlash
        $warehouseId = auth()->user()->hasAnyRole('admin') ? $request->warehouse_id : auth()->user()->warehouse_id;
    
        $data = [
            'date'         => Carbon::parse($request->date)->format('Y-m-d'),
            'client_id'    => $request->client_id,
            'reference'    => $request->reference,
            'type_id'      => $request->type_id,
            'warehouse_id' => $warehouseId,
            'currency_type_price' => $request->currency_type_price,
            'currency_type' => $request->currency_type,
        ];
    
        // 2. Checkin'ni yangilash yoki yaratish
        if ($id) {
            $item = Checkin::where('code', $id)->firstOrFail();
            $item->update($data);
        } else {
            $data['user_id'] = auth()->id();
            $data['code']    = Str::uuid();
            
            $item = Checkin::create($data);
            $request->session()->flash('success', trans('backend.post_create'));
        }
    
        $search = $request->product_id;
        $product = Product::where('status', 1)
            ->where(function ($query) use ($search) {
                $query->where('name', $search)
                      ->orWhere('fullname', $search)
                      ->orWhere('barcode', $search);
            })->first();
    
        // Agar mahsulot topilmasa, orqaga qaytarish
        if (!$product) {
            return redirect()->route('checkin_form', ['id' => $item->code])->with('error', trans('backend.old_added_product'));
        }
    
        // 4. Mahsulot avval qo'shilganligini tekshirish (count o'rniga exists)
        if (CheckinDetail::where('checkin_id', $item->id)->where('product_id', $product->id)->exists()) {
            return back()->with('error', trans('backend.old_added_product'));
        }
    
        // 5. Checkin Detail yaratish
        CheckinDetail::create([
            'checkin_id'          => $item->id,
            'product_id'          => $product->id,
            'warehouse_id'        => $item->warehouse_id,
            'category_id'         => $product->category_id,
            'qty'                 => 1,
            'currency_type'       => $item->currency_type,
            'currency_type_price' => $item->currency_type_price,
            'price'               => 1,
            'code'                => Str::uuid(),
            'barcode'             => mt_rand(10, 99) . time(),
        ]);
    
        // 6. Max narxlarni BIR MAROTABA hisoblash (Katta yuklamani oldini oladi)
        $maxCheckinPrice  = $product->checkindetails()->max('price') ?? 0;
        $maxCheckoutPrice = $product->price ?: ($product->checkoutdetails()->max('price') ?? 0);
    
        // 7. Ombor zaxirasini yangilash yoki yaratish
        $stock = WarehouseStock::firstOrNew([
            'warehouse_id' => $item->warehouse_id,
            'product_id'   => $product->id,
        ]);
    
        $currentStock = $stock->exists ? $stock->stock + 1 : 1;
    
        $stock->fill([
            'stock'                => $currentStock,
            'checkin_price'        => $maxCheckinPrice,
            'checkout_price'       => $maxCheckoutPrice,
            'checkin_total_price'  => $maxCheckinPrice * $currentStock,
            'checkout_total_price' => $maxCheckoutPrice * $currentStock,
        ])->save();
    
        return redirect()->route('checkin_form', ['id' => $item->code]);
    }
    
    public function save_excel(Request $request, $id = null)
    {
        $data['date'] = Carbon::parse($request->date)->format('Y-m-d');
        $data['client_id'] = $request->client_id;
        
        $data['reference'] = $request->reference;
        $data['type_id'] = 1;
        $data['warehouse_id'] = $request->warehouse_id;
        
        if ($request->hasFile('file_excel')) {
            if($id) {
                $item = Checkin::where('code', $id)->first();
                if (Storage::exists('upload/import_excel/'. $item->file_excel)) {                        
                    Storage::delete('upload/import_excel/'. $item->file_excel);
                }
            }
            
            $extension = $request->file('file_excel')->getClientOriginalExtension();
            $fileNameToStore = time().'.'.$extension;
            $path = $request->file('file_excel')->move('upload/import_excel/',$fileNameToStore);
            $data['file_excel'] = $fileNameToStore;
        }

        if ($id) {
            $item = Checkin::where('code', $id)->first();

            if($item) {
                $item->update($data);
                $request->session()->flash('success', trans('backend.post_update'));
            }
        } else {
            $data['user_id'] = Auth::id();
            $data['code'] = Str::uuid();           
            
            $item = Checkin::create($data);
            $request->session()->flash('success', trans('backend.post_create'));
        }
        
        $part = $item->id;
        $wareid = $item->warehouse_id;
        
        Excel::import(new ImportCheckin($part,$wareid), public_path('upload/import_excel/' .$item->file_excel));

        return redirect()->route('checkin_form', ['id' => $item->code]);
    }
    
    public function change_currency()
    {
        $brid = request()->typeid; //qty Keladi
        $brid = str_replace(' ', '', $brid);
        $cid = request()->cid; //ID keladi
        $item = Checkin::findOrFail($cid);
        $item->update(['currency' => $brid]);
        return response()->json(['status' => 'success']);
    }
    
    public function select_warehouse()
    {
        $brid = request()->typeid; //qty Keladi
        $cid = request()->cid; //ID keladi
        $item = Checkin::findOrFail($cid);
        $item->update(['warehouse_id' => $brid]);
        return response()->json(['status' => 'success']);
    }
    
    public function select_supplier()
    {
        $brid = request()->typeid; //qty Keladi
        $cid = request()->cid; //ID keladi
        $item = Checkin::findOrFail($cid);
        $item->update(['client_id' => $brid]);
        return response()->json(['status' => 'success']);
    }

    public function delete(Request $request, $id = null)
    {
        $item = CheckinDetail::where('code',$id)->first();
        
        $hdata['dealer_id'] = $item->dealer_id;
        $hdata['user_id'] = Auth::id();
        $hdata['code'] = Str::uuid();
        $hdata['name'] = 6;
        $hdata['database'] = 'checkin_details';
        $hdata['ip_address'] = $request->ip();
        $hdata['agent'] = $request->server('HTTP_USER_AGENT');
        $hdata['comment'] = 'Запчасть "<u>' . $item->prodid->name . '</u>" исключена из договора "' . ($item->checkid->number_work ? '№ ' . $item->checkid->number_work : 'Черновик #' . $item->checkid->id) . '" от ' . $item->checkid->date;
        
        $history = History::create($hdata);
        
        $client = new GClient([
            "base_uri" => "https://api.telegram.org",
        ]);
                
        $clientid       = $item->checkid->number_work ? $item->checkid->number_work : 'Черновик ID#' . $item->checkid->id;
        $ip             = $request->ip();
        $dealer         = $item->checkid->supid->name;
        $warehouse      = $item->warehouseid?->name;
        $barcode        = $item->prodid->barcode;
        $hid            = $history->id;
        $user           = Auth::user()->name . ' Тел: ' . Auth::user()->phone;
        $comment        = $hdata['comment'];
        $date           = Carbon::now()->format('Y-m-d H:i:s');
        
        $bot_token = "7335500759:AAFQCsaM-8jLefkgmNEW3phJxmn_gQIBSQw";
        $chat_id = "-1003627640983";
        $message = "ID#$hid\n<b><u>⚠️ Модуль: Приход (Позиция)</u></b>\n<b>🧾 Договор:</b> $clientid \n\n<b>🏬 Поставщик:</b> $dealer \n<b>🏭 Склад:</b> $warehouse \n<b>📦 Баркод:</b> $barcode \n<b>👨‍💻 Пользователь:</b> $user \n<b>📝 Примечание:</b> $comment \n<b>📍 ИП адрес:</b> $ip \n<b>⏱ Дата :</b> $date";
        $response = $client->request("GET", "/bot$bot_token/sendMessage", [
            "query" => [
                "chat_id" => $chat_id,
                "text" => $message,
                "parse_mode" => "html"
            ]
        ]);
        
        if(WarehouseStock::where('warehouse_id', $item->warehouse_id)->where('product_id', $item->product_id)->count()){
            $wsid = WarehouseStock::where('warehouse_id', $item->warehouse_id)->where('product_id', $item->product_id)->first();
            $stc = ($wsid->stock - $item->qty);
            $wsid->update([
                'stock' => $stc,
                'checkin_price' => $item->prodid->checkindetails()->max('price'),
                'checkout_price' => $item->prodid->price ? $item->prodid->price : $item->prodid->checkoutdetails()->max('price'),
                'checkin_total_price' => $stc > 0 ? ($item->prodid->checkindetails()->max('price') * $stc) : 0,
                'checkout_total_price' => $stc > 0 ? (($item->prodid->price ? $item->prodid->price : $item->prodid->checkoutdetails()->max('price')) * $stc) : 0
            ]);
        } 
        
        $item->delete();
        
        return back();
    }
    
    public function delete_checkin(Request $request, $id = null)
    {
        $item = Checkin::where('code',$id)->first();
        if($item->details()->count() == 0){
            $item->delete();
        } else {
            $request->session()->flash('error', trans('backend.checkin_count'));
        }
        
        
        return back();
    }
    
    public function done_status($id = null)
    {
        $item = Checkin::where('code',$id)->first();
        $data['status'] = 1;
        $year = Carbon::now()->format('Y');
        
        if($item->number_order == null){
            if(Checkin::whereYear('date', '=', $year)->count()){
                $slice = Checkin::whereYear('date', '=', $year)->max('number_order');
                $data['number_order'] = Str::padLeft(($slice + 1), 6, '0');
                $data['number_work'] = Carbon::now()->format('y') . $data['number_order'];
            } else {
                $data['number_order'] = '000001';
                $data['number_work'] = Carbon::now()->format('y') . $data['number_order'];
            }
            
            $item->update($data);
        }
        
        foreach($item->details as $det){
            $det->update(['status' => 1]);
        }
        

        return redirect()->action('Backend\CheckinController@index');
    }
    
    public function cancel_status($id = null)
    {
        $item = Checkin::where('code',$id)->first();
        $item->update(['status' => 2]);
        foreach($item->details as $det){
            $det->update(['status' => 2]);
        }
        return back();
    }
    
    public function select_date()
    {
        $typeid = request()->typeid; //qty Keladi
        $dat = Carbon::parse($typeid)->format('Y-m-d');
        $cid = request()->cid; //ID keladi
        $item = Checkin::findOrFail($cid);
        $item->update(['date' => $dat]);
        return response()->json(['status' => 'success']);
    }

    public function search(Request $request)
    { 
        $keyword = $request->input('search');

        $data = Checkin::where(function ($query) use($keyword) {
                $query->where('number_work', 'like', '%' . $keyword . '%');
              })
        ->orderBy('id', 'desc')->paginate(100);

        return view('backend.checkins.index', compact('data', 'keyword'));
    }

    public function print($id = null)
    {
        $item = null;
        if($id) {
            $item = Checkin::where('code', $id)->first();
        }
        return view('backend.checkins.print', compact('item'));
    }
}
