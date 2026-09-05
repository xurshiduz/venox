<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client as GClient;
use Illuminate\Http\Request;

use App\Exports\WarehouseProductList;
use App\Exports\StockExport;
use App\Exports\StockExportParam;

use App\Models\WarehouseBlockProduct;
use App\Models\DealerTransferDetail;
use App\Models\WarehouseProduct;
use App\Models\WarehouseBlock;
use App\Models\WarehouseBlockCell;
use App\Models\WarehouseStock;
use App\Models\CheckinDetail;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\History;
use App\Models\Dealer;
use App\Models\Currency;

use Carbon\Carbon;
use Session;
use Excel;
use Auth;
use Str;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        
        if(Auth::user()->hasAnyRole('admin|cashier')){
            $data = Warehouse::orderBy('id', 'desc')->paginate(20); 
        } elseif(Auth::user()->hasAnyRole('dealer_admin')) {
            $data = Warehouse::orderBy('id', 'desc')->where('dealer_id', Auth::user()->dealer_id)->paginate(20); 
        } 
        $keyword = NULL; 
        $usdRate = Currency::usdRate();

        return view('backend.warehouses.index', compact('data', 'keyword', 'usdRate'));
        
        //
        
        $data = CheckinDetail::whereNotNull('warehouse_cell_id')->get();
        foreach($data as $item){
            if(WarehouseBlockProduct::where('product_id', $item->product_id)->where('warehouse_cell_id', $item->warehouse_cell_id)->count()){
                
            } else {
                WarehouseBlockProduct::create(['product_id' => $item->product_id, 'warehouse_id' =>  $item->warehouse_id, 'warehouse_block_id' =>  $item->warehouse_block_id, 'warehouse_cell_id' =>  $item->warehouse_cell_id, 'code' => Str::uuid()]);
            }
        }
        
        dd('success');
        //
        
        $data = CheckinDetail::whereNotNull('warehouse_cell_id')->get();
        foreach($data as $item){
            if(WarehouseProduct::where('product_id', $item->product_id)->where('warehouse_id', $item->warehouse_id)->count()){
                
            } else {
                WarehouseProduct::create(['product_id' => $item->product_id, 'warehouse_id' =>  $item->warehouse_id]);
            }
        }
        
        dd('success');
    }
    
    public function warehouse_inventory($id)
    { 
        $keyword = NULL;
        $ware = Warehouse::where('code', $id)->first(); 
        if(Auth::user()->hasAnyRole('admin|cashier')){
            $data = \App\Models\WarehouseStock::where('warehouse_id', $ware->id)
                ->where('stock', '>', 0) // Agar faqat bor narsalar kerak bo'lsa (ixtiyoriy)
                ->with(['productid' => function($query) {
                    $query->where('status', 1); // Faqat aktiv productlar
                }])
                ->whereHas('productid', function($query) {
                    $query->where('status', 1); // Product o'chirilmagan bo'lishi kerak
                })
                ->get();
            return view('backend.warehouses.inventory', compact('data', 'keyword', 'ware'));
            
            $data = CheckinDetail::where('warehouse_id', $ware->id)->get()->groupBy('product_id');
        } elseif(Auth::user()->hasAnyRole('dealer_admin')) {
            $data = DealerTransferDetail::where('warehouse_id', $ware->id)->get()->groupBy('product_id'); 
            return view('backend.warehouses.inventory_null_dealer', compact('data', 'keyword', 'ware'));
        }  
    }
    
    public function warehouse_blocks($id)
    { 
        $ware = Warehouse::where('code', $id)->first(); 
        $data = WarehouseBlock::where('warehouse_id', $ware->id)->get();
        $keyword = NULL; 
        return view('backend.warehouses.blocks', compact('data', 'keyword', 'ware'));
    }
    
    public function warehouse_blocks_save(Request $request, $id)
    { 
        $ware = Warehouse::where('code', $id)->first(); 
        if(WarehouseBlock::where('warehouse_id', $ware->id)->where('row', $request->row)->count()){
            $request->session()->flash('success', trans('backend.block_dublicate'));
        } else {
            WarehouseBlock::create(['warehouse_id' => $ware->id, 'row' => $request->row]);
        }
        return back();
    }
    
    public function excel_product_list($id)
    { 
        return Excel::download(new WarehouseProductList($id), 'export- ' . $id . '.xlsx');
    }
    
    public function warehouse_stock(Request $request, $id)
    { 
        $usdRate = Currency::usdRate();

        return Excel::download(new StockExport($id, $usdRate), 'export- ' . $id . '.xlsx');
    }
    
    public function warehouse_stock_param($id, $take, $pag)
    { 
        return Excel::download(new StockExportParam($id, $take, $pag), 'export- ' . $id . '.xlsx');
    }
    
    public function warehouse_stock_input(Request $request)
    { 
        $wareid = Warehouse::where('code', $request->id)->first()->name;
        
        $id = $request->id;
        $take = $request->take;
        $pag = $request->pag;
        $usdRate = Currency::usdRate();

        return Excel::download(new StockExportParam($id, $take, $pag, $usdRate), 'filter- ' . $wareid . '-' . $take . '-' . $pag . '.xlsx');
    }

    public function warehouse_stock_refresh($id)
    { 
        $wareid = Warehouse::where('code', $id)->first();
        $productIds = CheckinDetail::distinct()->pluck('product_id');

        // Shu ID larga tegishli mahsulotlarni olamiz
        $products = Product::whereIn('id', $productIds)->get();
        foreach ($products as $product) {
            $stock = ($product->checkindetails()->where('warehouse_id', $wareid->id)->sum('qty') + $product->transferdetails()->where('warehouse_in', $wareid->id)->sum('qty')) - ($product->checkoutdetails()->where('warehouse_id', $wareid->id)->sum('bonus') + $product->checkoutdetails()->where('warehouse_id', $wareid->id)->sum('qty') + $product->transferdetails()->where('warehouse_out', $wareid->id)->sum('qty'));
            
            if(WarehouseStock::where('warehouse_id', $wareid->id)->where('product_id', $product->id)->count()){
                $pws = WarehouseStock::where('warehouse_id', $wareid->id)->where('product_id', $product->id)->first();
                $pws->update([
                    'stock' => $stock,
                    'checkin_price' => $product->checkindetails()->max('price'),
                    'checkout_price' => $product->price ? $product->price : null,
                    'checkin_total_price' => $stock > 0 ? ($product->checkindetails()->max('price') * $stock) : null,
                    'checkout_total_price' => $stock > 0 ? ($product->price ? $product->price  * $stock : null) : null,
                ]);
            } else {
                WarehouseStock::create([
                    'warehouse_id' => $wareid->id, 
                    'product_id' => $product->id, 
                    'stock' => $stock,
                    'checkin_price' => $product->checkindetails()->max('price'),
                    'checkout_price' => $product->price ? $product->price : null,
                    'checkin_total_price' => $stock > 0 ? ($product->checkindetails()->max('price') * $stock) : null,
                    'checkout_total_price' => $stock > 0 ? ($product->price ? $product->price * $stock : null) : null,
                ]);
            }
        }
        dd('success');
    }
    
    public function warehouse_stock_old($id)
    { 
        return Excel::download(new StockExport($id), 'export- ' . $id . '.xlsx');
        
        $wareid = Warehouse::where('code', $id)->first()->id;
        
        $keyword = NULL; 
       
        $products = Product::lazy();
       
        foreach ($products as $product) {
            $stock = $product->checkindetails()->where('warehouse_id', 4)->where('status', 1)->sum('qty') - $product->checkoutdetails()->where('warehouse_id', 4)->where('status', 1)->sum('qty');
            if($stock > 0){
                WarehouseStock::create(['session_id' => Session::getId(), 'warehouse_id' => 4, 'product_id' => $product->id, 'stock' => $stock]);
            }
        }
       dd('success');
        
        Product::select('id', 'barcode', 'name')->chunk(100, function($products) {
            foreach ($products as $product) {
                $stock = $product->checkindetails()->where('warehouse_id', 4)->where('status', 1)->sum('qty') - $product->checkoutdetails()->where('warehouse_id', 4)->where('status', 1)->sum('qty');
                if($stock > 0){
                    WarehouseStock::create(['session_id' => Session::getId(), 'warehouse_id' => 4, 'product_id' => $product->id, 'stock' => $stock]);
                }
            }
        });
        
        dd('success');
        return view('backend.warehouses.index', compact('data', 'keyword'));
    }

    public function warehouse_select($warehouse = null, $block = null, $row = null, $column = null, $floor = null)
    { 
        $keyword = NULL; 
        if($warehouse){
            $item = Warehouse::where('code', $warehouse)->first(); 
            return view('backend.warehouses.warehouse', compact('item', 'keyword'));
        }
        
        return view('backend.warehouses.index', compact('data', 'keyword'));
    }

    public function form($id = null)
    {
        $item = null;
        $dealers = Dealer::all();

        if($id) {
            $item = Warehouse::where('code', $id)->first();
        }
        
        return view('backend.warehouses.form', compact('item', 'dealers'));
    }

    public function save(Request $request, $id = null)
    {

        $data = $request->all();
        
        if($request->dealer_id){
            $data['dealer_id'] = $request->dealer_id;
        } else {
            $data['dealer_id'] = Auth::user()->dealer_id;
        }

        if ($id) {
            $item = Warehouse::where('code', $id)->first();
            
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
                
                $hdata['dealer_id'] = Auth::user()->dealer_id;
                $hdata['user_id'] = Auth::id();
                $hdata['code'] = Str::uuid();
                $hdata['name'] = 3;
                $hdata['database'] = 'warehouses';
                $hdata['ip_address'] = $request->ip();
                $hdata['agent'] = $request->server('HTTP_USER_AGENT');
                $hdata['comment'] = $oldname != $item->name ? ('Наименование с <b>' . ($oldname ? $oldname : 'NULL') . '</b> на ' . ($item->name ? $item->name  : 'NULL') . '</b>' ) : null
                . ($olddirector != $item->director ? ('Имя директора с <b>' . ($olddirector ? $olddirector : 'NULL') . '</b> на ' . ($item->director ? $item->director : 'NULL') . '</b>' ) : null)
                . ($oldaddress != $item->address ? ('Адресс с <b>' . ($oldaddress ? $oldaddress : 'NULL') . '</b> на <b>' . ($item->address ? $item->address : 'NULL') . '</b>' ) : null)
                . ($oldphone != $item->phone ? ('Контакты с <b>' . ($oldphone ? $oldphone : 'NULL') . '</b> на <b>' . ($item->phone ? $item->phone : 'NULL') . '</b>' ) : null)
                . ($oldschet != $item->schet ? ('Р/сч с <b>' . ($oldschet ? $oldschet : 'NULL') . '</b> на <b>' . ($item->schet ? $item->schet : 'NULL') . '</b>' ) : null)
                . ($oldregion != $item->region ? ('Город с <b>' . ($oldregion ? $oldregion : 'NULL') . ' на <b>' . ($item->region ? $item->region : 'NULL') . '</b>' ) : null)
                . ($oldmfo != $item->mfo ? ('МФО с <b>' . ($oldmfo ? $oldmfo : 'NULL') . '</b> на <b>' . ($item->mfo ? $item->mfo : 'NULL') . '</b>' ) : null)
                . ($oldinn != $item->inn ? ('ИНН с <b>' . ($oldinn ? $oldinn : 'NULL') . '</b> на <b>' . ($item->inn ? $item->inn : 'NULL') . '</b>' ) : null)
                . ($oldoked != $item->oked ? ('ОКЭД с <b>' . ($oldoked ? $oldoked : 'NULL') . '</b> на <b>' . ($item->oked ? $item->oked : 'NULL') . '</b>' ) : null)
                . ($oldcomment != $item->comment ? ('Примечание с <b>' . ($oldcomment ? $oldcomment : 'NULL') . '</b> на <b>' . ($item->comment ? $item->comment : 'NULL') . '</b>' ) : null);
                
                $history = History::create($hdata);
                
                $client = new GClient([
                    "base_uri" => "https://api.telegram.org",
                ]);
                
                $clientid       = $item->name;
                $ip             = $request->ip();
                $dealer         = Auth::user()->dealerid ? Auth::user()->dealerid->name : null;
                $user           = Auth::user()->name;
                $hid            = $history->id;
                $comment        = 'Есть изменения';
                $date           = Carbon::now()->format('Y-m-d H:i:s');
                
                $bot_token = "7335500759:AAFQCsaM-8jLefkgmNEW3phJxmn_gQIBSQw";
                $chat_id = "-1003627640983";
                $message = "ID# $hid\n<b><u>⚠️ Модуль: Склад</u></b>\n <b>🤵‍♂️ Склад:</b> $clientid \n\n<b>💠 Филиал:</b> $dealer \n<b>👨‍💻Пользователь:</b> $user \n<b>📝 Примечание:</b> $comment \n<b>📍 ИП адрес:</b> $ip \n<b>⏱ Дата :</b> $date";
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

            $item = Warehouse::create($data);
            $request->session()->flash('success', trans('backend.post_create'));
        }

        return redirect()->action('Backend\WarehouseController@index');
    }
    
    public function block_api()
    {
        $model = request()->model;
        $data = WarehouseBlock::where('warehouse_id', $model)->get();
        return response()->json($data);
    }
    
    public function block_cell_api()
    {
        $model = request()->model;
        $data = WarehouseBlockCell::where('warehouse_block_id', $model)->get();
        return response()->json($data);
    }
}
