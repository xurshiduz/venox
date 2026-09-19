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
        $request->validate([
            'fromdate' => ['required', 'date_format:d.m.Y'],
            'todate' => ['required', 'date_format:d.m.Y', 'after_or_equal:fromdate'],
            'manager' => ['nullable'],
            'warehouse' => ['nullable'],
            'client_id' => ['nullable'],
            'barcode' => ['nullable', 'string', 'max:255'],
        ]);

        $managers = User::role('sale')->get();
        $types = CashReceiptType::all();
        $keyword = $request->input('search');
        
        $fromdate       = $request->fromdate;
        $todate         = $request->todate;
        
        $shipment       = $request->shipment;
        $finish         = $request->finish;
        $selmanager     = $request->input('manager', 'all');
        $warehouse      = $request->input('warehouse', 'all');
        $barcode        = trim((string) $request->barcode);
        $client_id      = $request->input('client_id', 'all');
        
        $result = CheckinDetail::query()
            ->with([
                'prodid:id,name,barcode',
                'warehouseid:id,name',
                'checkid:id,date,client_id,reference,user_id,code',
                'checkid.supid:id,name',
                'checkid.userid:id,name',
            ])
            ->join('checkins', 'checkin_details.checkin_id', '=', 'checkins.id')
            ->join('products', 'checkin_details.product_id', '=', 'products.id')
            ->select('checkin_details.*', 'products.barcode as product_barcode')
            ->whereBetween('checkins.date', [
                Carbon::createFromFormat('d.m.Y', $fromdate)->format('Y-m-d'),
                Carbon::createFromFormat('d.m.Y', $todate)->format('Y-m-d'),
            ]);

        if($shipment){
            $result = $result->where('checkins.status', 1);
        }

        if($finish){
            $result = $result->where('checkins.status', 1);
        }

        if($selmanager != 'all'){
            $result = $result->where('checkins.user_id', $selmanager);
        }

        if($client_id != 'all'){
            $result = $result->where('checkins.client_id', $client_id);
        }

        if($barcode){
            $prid = Product::where('barcode', $barcode)->first();

            if ($prid) {
                $result->where(function ($query) use ($prid, $barcode) {
                    $query->where('checkin_details.product_id', $prid->id)
                        ->orWhere('checkin_details.product_barcode', $barcode)
                        ->orWhere('products.barcode', $barcode);
                });
            } else {
                $result->where(function ($query) use ($barcode) {
                    $query->where('checkin_details.product_barcode', $barcode)
                        ->orWhere('products.barcode', $barcode);
                });
            }
        }

        if($warehouse != 'all'){
            $result->where('checkin_details.warehouse_id', $warehouse);
        }

        $data = $result
            ->orderBy('checkins.date', 'desc')
            ->orderBy('checkin_details.id', 'desc')
            ->paginate(20)
            ->appends($request->all());

        return view('backend.filter.filter', compact('data', 'keyword', 'types', 'managers', 'shipment', 'finish', 'selmanager', 'fromdate', 'todate'));
    }
    
    
}
