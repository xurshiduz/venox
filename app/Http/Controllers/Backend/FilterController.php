<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ProductCategory;
use App\Models\CashReceiptType;
use App\Models\CheckoutDetail;
use App\Models\CheckinDetail;
use App\Models\CurrencyType;
use App\Models\CashReceipt;
use App\Models\Warehouse;
use App\Models\Checkout;
use App\Models\Currency;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Client;
use App\Models\User;

use Carbon\Carbon;
use Auth;
use Str;

class FilterController extends Controller
{
    public function index()
    { 
        $fromdate       = Carbon::parse('21.02.2024')->format('d.m.Y');
        $todate     = Carbon::now()->format('d.m.Y');
        
        
        if(Auth::user()->hasAnyRole('admin')){
            $clients = Client::orderBy('id', 'desc')->where('status', 1)->get();
        } else {
            $clients = Client::where('user_id', Auth::id())->where('status', 1)->get(); 
        } 
        
            $managers   = User::role('sale')->get();
            $warehouses = Warehouse::all();
        
        
        return view('backend.filter.index', compact('managers', 'fromdate', 'todate', 'clients', 'warehouses'));
    }
    
    
    public function filter(Request $request)
    { 
        $managers = User::role('sale')->get();
        $types = CashReceiptType::all();
        $keyword = $request->input('search');
        
        $fromdate       = $request->fromdate;
        $todate         = $request->todate;
        
        $shipment       = $request->shipment;
        $finish         = $request->finish;
        $selmanager     = $request->manager;
        $warehouse      = $request->warehouse;
        $barcode        = $request->barcode;
        $client_id        = $request->client_id;
        
        $result = Checkout::query()->whereBetween('created_at', [Carbon::parse($fromdate)->startOfDay()->format('Y-m-d H:i:s'), Carbon::parse($todate)->endOfDay()->format('Y-m-d H:i:s')])->orderBy('id', 'desc');
        
        if($shipment){
            $result = $result->where('shipment_status', 1);
        }
        
        if($finish){
            $result = $result->where('status', 1);
        }
        
        if($selmanager != 'all'){
            $result = $result->where('manager_id', $selmanager);
        }
        
        if($client_id != 'all'){
            $result = $result->where('client_id', $client_id);
        }
        
        if($barcode){
            $prid = Product::where('barcode', $barcode)->first();
            
            $pid[] = NULL;
            
            foreach(CheckoutDetail::where('product_id', $prid->id)->get() as $mtseh){
                $pid[] = $mtseh->checkout_id;
            }
            
            $result = $result->whereIn('id', $pid);
        }
        
        if($warehouse != 'all'){
            $wid[] = NULL;
            
            foreach(CheckoutDetail::where('warehouse_id', $warehouse)->get() as $mtseh){
                $wid[] = $mtseh->checkout_id;
            }
            
            $result = $result->whereIn('id', $wid);
        }
        
        $data = $result->paginate(20)->appends($request->all());
        return view('backend.filter.filter', compact('data', 'keyword', 'types', 'managers', 'shipment', 'finish', 'selmanager', 'fromdate', 'todate'));
    }
    
    
}