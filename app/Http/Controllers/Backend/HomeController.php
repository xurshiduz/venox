<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Exports\ActExcel;
use App\Exports\InvExcel;

use App\Models\ManufacturerFactoryType;
use App\Models\ManufacturerRawMaterial;
use App\Models\WarehouseBlockProduct;
use App\Models\WarehouseProduct;
use App\Models\AktSverkaHistory;
use App\Models\WarehouseBlockCell;
use App\Models\InventoryPriceDetail;
use App\Models\InventoryDetail;
use App\Models\ProductCategory;
use App\Models\WarehouseBlock;
use App\Models\CheckoutDetail;
use App\Models\CashExpenditure;
use App\Models\CheckinDetail;
use App\Models\Checkin;
use App\Models\HistoryModul;
use App\Models\CurrencyType;
use App\Models\DisplayOrder;
use App\Models\CashReceipt;
use App\Models\Warehouse;
use App\Models\Checkout;
use App\Models\Currency;
use App\Models\History;
use App\Models\Product;
use App\Models\Setting;
use App\Models\KpiPlan;
use App\Models\Client;
use App\Models\User;

use Carbon\Carbon;
use Excel;
use Auth;
use Str;
use DB;

class HomeController extends Controller
{
    
    public function inv_excel($id = null)
    { 
        return Excel::download(new InvExcel($id), 'ostatok.xlsx');
    }

    public function index()
    {
        $warehouses = Warehouse::all();
        $topprod = Product::where('factory_id', 1)->paginate(5);
        return view('backend.index', compact('warehouses', 'topprod'));
    }
    
    public function display()
    {
        return view('backend.display.search');
    }
    
    public function accounting_month_report()
    {
        return view('backend.reports.month_report_index');
    }
    
    public function display_send()
    {
        $datacid = request()->datacid; //ID Keladi
        $item = DisplayOrder::findOrFail(1);
        $data['barcode'] = $datacid;
        $item->update($data);
        return response()->json(['status' => 1]);
    }
    
    public function display_refresh()
    {
        $data = DisplayOrder::findOrFail(1);
        return response()->json($data);
    }

    public function disp()
    {
        $item = DisplayOrder::findOrFail(1);
        $pr = $item->barcode;
        $item = Product::orWhere('barcode', $pr)->orWhere('barcode', '0'. $pr)->first();
        return view('backend.display.display', compact('item'));
    }

    public function warehouse_filter()
    {
        $item = null;
        $warehouses = Warehouse::whereNull('factory_id')->get();
        return view('backend.warehouse_filter.index', compact('warehouses', 'item'));
    }
    
    public function warehouse_filter_post(Request $request)
    {
        $from = Carbon::parse($request->from)->format('Y-m-d');
        $to = Carbon::parse($request->to)->format('Y-m-d');
        
        $client = Warehouse::find($request->warehouse_id);
        $comp = Setting::find(1)->value;
        $clientid = $request->warehouse_id;
        
        $data = CheckinDetail::where('warehouse_id', $request->warehouse_id)->get()->groupBy('product_id');
        
        if($request->type == 'pdf'){
            return view('backend.warehouse_filter.print', compact('data', 'from', 'to', 'client', 'comp'));
        } else {
            return Excel::download(new ActExcel($from, $to, $clientid), 'export.xlsx');
        }
        
    }

    public function top_client_filter()
    {
        return view('backend.top_client.index');
    }
    
    public function top_client_filter_post(Request $request)
    {
        $from = Carbon::parse($request->from)->format('Y-m-d');
        $to = Carbon::parse($request->to)->format('Y-m-d');
        
        $data = DB::table('checkouts')
        ->select('client_id', DB::raw('SUM(total_price_payme) as umumiy_sotuv_summasi'))
        ->whereBetween('date', [$from, $to])->where('client_id', '!=', 1)
        ->groupBy('client_id')
        ->orderByDesc('umumiy_sotuv_summasi')
        ->limit(20)
        ->get();
        
        $data = $data->map(function ($mijoz) {
            $mijozModel = Client::find($mijoz->client_id); // Mijoz modelini olish
            $mijoz->mijoz_malumotlari = $mijozModel; // Mijoz ma'lumotlarini qo'shish
            return $mijoz;
        });
        
        return view('backend.top_client.result', compact('data', 'from', 'to'));
        
    }

    public function histories()
    {
        $types = HistoryModul::all();
        $data = History::orderBy('id', 'DESC')->paginate(20);
        return view('backend.history', compact('data', 'types'));
    }
    
    public function iscompact()
    {
        if(Auth::user()->iscompact == 1){
            User::find(Auth::id())->update(['iscompact' => 0]);
            $compact = 0;
        } else {
            User::find(Auth::id())->update(['iscompact' => 1]);
            $compact = 1;
        }
        
        return response()->json(['compact' => $compact]);
        
    }
    
    public function factory_id($id)
    {
        if($id != 0){
            User::find(Auth::id())->update(['m_factory_type' => ManufacturerFactoryType::where('code', $id)->first()->id]);
        } else {
            User::find(Auth::id())->update(['m_factory_type' => null]);
        }
        return back();
    }
    
    public function reconciliation_act()
    {
        $item = null;
        if(Auth::user()->hasAnyRole('admin')){
            $clients = Client::orderBy('id', 'desc')->where('status', 1)->get();
        } else {
            $clients = Client::where('user_id', Auth::id())->where('status', 1)->get(); 
        } 
        
        return view('backend.reconciliation_act.index', compact('item', 'clients'));
    }
    
    public function reconciliation_act_post(Request $request)
{
    $from = Carbon::parse($request->from)->format('Y-m-d');
    $to = Carbon::parse($request->to)->format('Y-m-d');
    
    $client = Client::find($request->client_id);
    $comp = Setting::find(1)->value;
    $clientid = $request->client_id;
    
    // 1. O'tgan davr uchun ma'lumotlar
    $prev_checkouts = Checkout::where('client_id', $clientid)->where('date', '<', $from)->get();
    $prev_cashs = CashReceipt::where('status', 1)->where('client_id', $clientid)->where('date', '<', $from)->get();
    $prev_checkins = Checkin::where('status', 1)->where('client_id', $clientid)->where('date', '<', $from)->get();
    
    // O'tgan davr uchun pul chiqimlari (CashExpenditure)
    $prev_cash_expenditures = CashExpenditure::where('supplier_id', $clientid)
        ->where('cash_expenditure_types', 8)
        ->where('date', '<', $from)
        ->get();
    
    // Summalarni hisoblash
    // Sotuvlar va mijozga berilgan pullar (Debet)
    $sum_prev_debets = $prev_checkouts->sum(function($item) { return $item->sumtotal(); }) 
                     + $prev_cash_expenditures->sum('price'); 

    // To'lovlar va vozvratlar (Kredit)
    $sum_prev_credits = $prev_cashs->sum('price') 
                      + $prev_checkins->sum(function($item) { return $item->sumtotal(); }); 
    
    // --- YANGI QO'SHILGAN QISM: Boshlang'ich qarz ---
    $initial_balance = $client->balance ? (float)$client->balance : 0;

    // Boshlang'ich Saldo: (Boshlang'ich qarz + Debet - Kredit)
    $start_saldo = $initial_balance + $sum_prev_debets - $sum_prev_credits; 

    // 2. Tanlangan oraliqdagi operatsiyalar
    $checkouts = Checkout::where('client_id', $clientid)->whereBetween('date', [$from, $to])->get();
    $cashs = CashReceipt::where('status', 1)->where('client_id', $clientid)->whereBetween('date', [$from, $to])->get();
    $checkins = Checkin::where('status', 1)->where('client_id', $clientid)->whereBetween('date', [$from, $to])->get();
    
    // Tanlangan oraliq uchun pul chiqimlari
    $cash_expenditures = CashExpenditure::where('supplier_id', $clientid)
        ->where('cash_expenditure_types', 8)
        ->whereBetween('date', [$from, $to])
        ->get();
    
    // Barcha ma'lumotlarni birlashtirish va sana bo'yicha tartiblash
    $data = $checkouts->concat($cashs)
                      ->concat($checkins)
                      ->concat($cash_expenditures)
                      ->sortBy('date');
    
    // Tarixga yozish
    $cdata['from_date'] = $from;
    $cdata['to_date'] = $to;
    $cdata['client_id'] = $clientid;
    $cdata['user_id'] = Auth::id();
    $cdata['code']= Str::uuid();
    
    AktSverkaHistory::create($cdata);
    
    if($request->type == 'pdf'){
        // start_saldo ni viewga qo'shib yuboramiz
        return view('backend.reconciliation_act.print', compact('data', 'from', 'to', 'client', 'comp', 'start_saldo'));
    } else {
        return Excel::download(new ActExcel($from, $to, $clientid), 'АКТ сверки ' . $client->name . ' от ' . Carbon::now()->format('Y-m-d') . '.xlsx');
    }
}

    public function reconciliation_act_post_old(Request $request)
    {
        $from = Carbon::parse($request->from)->format('Y-m-d');
        $to = Carbon::parse($request->to)->format('Y-m-d');
        
        $client = Client::find($request->client_id);
        $comp = Setting::find(1)->value;
        $clientid = $request->client_id;
        
        
        $prev_checkouts = Checkout::where('client_id', $clientid)->where('date', '<', $from)->get();
        $prev_cashs = CashReceipt::where('status', 1)->where('client_id', $clientid)->where('date', '<', $from)->get();
        $prev_checkins = Checkin::where('status', 1)->where('client_id', $clientid)->where('date', '<', $from)->get();
        
        $sum_prev_debets = $prev_checkouts->sum(function($item) { return $item->sumtotal(); }); // Sotuvlar
        $sum_prev_credits = $prev_cashs->sum('price') + $prev_checkins->sum(function($item) { return $item->sumtotal(); }); // To'lovlar va vozvratlar
        
        // Boshlang'ich Saldo: (Sotuvlar - To'lovlar)
        $start_saldo = $sum_prev_debets - $sum_prev_credits; 
    
        // 2. Tanlangan oraliqdagi operatsiyalar
        $checkouts = Checkout::where('client_id', $request->client_id)->whereBetween('date', [$from, $to])->get();
        $cashs = CashReceipt::where('status', 1)->where('client_id', $request->client_id)->whereBetween('date', [$from, $to])->get();
        $checkins = Checkin::where('status', 1)->where('client_id', $request->client_id)->whereBetween('date', [$from, $to])->get();
        
        $data = $checkouts->concat($cashs)->concat($checkins)->sortBy('date');
        
        $cdata['from_date'] = $from;
        $cdata['to_date'] = $to;
        $cdata['client_id'] = $request->client_id;
        $cdata['user_id'] = Auth::id();
        $cdata['code']= Str::uuid();
        
        AktSverkaHistory::create($cdata);
        
        if($request->type == 'pdf'){
            // start_saldo ni viewga qo'shib yuboramiz
            return view('backend.reconciliation_act.print', compact('data', 'from', 'to', 'client', 'comp', 'start_saldo'));
        } else {
            return Excel::download(new ActExcel($from, $to, $clientid), 'АКТ сверки ' . $client->name . ' от ' . Carbon::now()->format('Y-m-d') . '.xlsx');
        }
    }

    

    public function activitie()
    {
        if(Currency::whereDate('updated_at', '>', Carbon::now()->subHour()->toDateTimeString())->count() == 0){
            
            $url= 'https://nbu.uz/en/exchange-rates/json/';

            $arrContextOptions=array(
                  "ssl"=>array(
                        "verify_peer"=>false,
                        "verify_peer_name"=>false,
                    ),
                );  
            
            $response = file_get_contents($url, false, stream_context_create($arrContextOptions));

            $json = json_decode($response, true);
            
            
            if($json){
                foreach($json as $s){
                    foreach(CurrencyType::where('belgi', $s['code'])->get() as $ctype){
                        if(Currency::whereDate('created_at', Carbon::now())->where('type_id', $ctype->id)->where('price', $s['nbu_cell_price'])->count() == 0){
                            Currency::create(['user_id' => 1,'code' => Str::uuid(), 'type_id' => $ctype->id, 'price' => $s['nbu_cell_price']]);
                        } else {
                            Currency::whereDate('created_at', Carbon::now())->where('type_id', $ctype->id)->where('price', $s['nbu_cell_price'])->first()->update(['updated_at' => Carbon::now()]);
                        }
                    }
                } 
            }
            
        }
        
        $warehouses = Warehouse::all();
        return view('backend.activitie', compact('warehouses'));
    }

    public function chart()
    {
        $warehouses = Warehouse::where('dealer_id', 1)->get();
        $managers = User::role('sale')->get();
        $kpi_list = KpiPlan::all();
        return view('backend.chart', compact('warehouses', 'managers', 'kpi_list'));
    }
    
    public function report_unsold()
    {
        $data = DB::table('products')                 
          ->select('id','name', 'category_id', 'barcode')
          ->whereNotIn('id', DB::table('checkout_details')->pluck('product_id')->toArray())
          ->paginate(100);
         
        $title = '<span style="color: #ff0000">Не продаваемых запчасти</span> <span style="color: #42840a">за все время</span>';
        
        return view('backend.reports.unsold', compact('data', 'title'));
    }

    public function report_category()
    {
        $data = ProductCategory::addSelect(['balance' => CheckoutDetail::selectRaw('sum(qty) as total')
                 ->whereColumn('category_id', 'product_categories.id')
                 ->groupBy('category_id')
             ])
             ->orderBy('balance', 'DESC')
             ->paginate(100);

        $title = '<span style="color: #ff0000">Отчет</span> <span style="color: #42840a">по категориям</span>';
        return view('backend.reports.category', compact('data', 'title'));
    }

    public function report_top_all($id)
    {
        $date = Carbon::now();
        $date_old = Carbon::now()->subMonth();
        
        if ($id == 'all') {
            $data = Product::addSelect(['balance' => CheckoutDetail::where('status', 1)->selectRaw('sum(qty) as total')
                 ->whereColumn('product_id', 'products.id')
                 ->groupBy('product_id')
             ])
             ->orderBy('balance', 'DESC')
             ->paginate(100);
            $title = '<span style="color: #ff0000">Топ 100</span> <span style="color: #42840a">продаваемых запчасть за все время</span>';
        } elseif ($id == 'quarter') {
            $to = Carbon::now()->subMonth()->endOfMonth();
            $from = Carbon::now()->subMonths(4)->startOfMonth();
            $data = Product::addSelect(['balance' => CheckoutDetail::where('status', 1)->whereBetween('created_at', [$from, $to])->selectRaw('sum(qty) as total')
                 ->whereColumn('product_id', 'products.id')
                 ->groupBy('product_id')
             ])
             ->orderBy('balance', 'DESC')
             ->paginate(100);
            $title = '<span style="color: #ff0000">Топ 100</span> <span style="color: #42840a">продаваемых запчасть за ' . $date->locale('ru')->getTranslatedMonthName('MMMM YYYY') . '</span>';
        } elseif ($id == 'last_month') {
            $to = Carbon::now()->subMonth()->endOfMonth();
            $from = Carbon::now()->subMonth()->startOfMonth();
            $data = Product::addSelect(['balance' => CheckoutDetail::where('status', 1)->whereBetween('created_at', [$from, $to])->selectRaw('sum(qty) as total')
                 ->whereColumn('product_id', 'products.id')
                 ->groupBy('product_id')
             ])
             ->orderBy('balance', 'DESC')
             ->paginate(100);
            $title = '<span style="color: #ff0000">Топ 100</span> <span style="color: #42840a">продаваемых запчасть за ' . $date_old->locale('ru')->getTranslatedMonthName('MMMM YYYY') . '</span>';
        } else {
            abort(404);
        }
        
        return view('backend.reports.top_all', compact('data', 'title'));
    }

    
}
