<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client as GClient;
use Illuminate\Http\Request;

use App\Models\WarehouseStock;
use App\Models\Adjustment;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\History;

use Carbon\Carbon;
use Excel;
use Auth;
use Str;

class AdjustmentController extends Controller
{
    public function index()
    { 
        $data = Adjustment::orderBy('id', 'desc')->paginate(20); 
        $keyword = NULL; 
        return view('backend.adjustments.index', compact('data', 'keyword'));
    }

    public function save(Request $request)
    {
        $pid = Product::where('code', $request->product_id)->first();
        $wid = Warehouse::where('code', $request->warehouse_id)->first();
        $reqstock = Str::replace(' ', '', Str::replace(' ', '', $request->stock));
        $reqoldtock =  Str::replace(' ', '', Str::replace(' ', '', $request->old_stock));
        if($pid && $wid){
            Adjustment::create([
                'user_id' => 1,
                'product_id' => $pid->id,
                'warehouse_id' => $wid->id,
                'user_id' => Auth::id(),
                'qty_minus' => $reqstock < $reqoldtock ? $reqoldtock - $reqstock : null,
                'qty_plus' => $reqstock > $reqoldtock ? $reqstock - ($reqoldtock): null,
                'qty_old' => $reqoldtock,
                'qty_new' => $reqstock,
                'comment' => $request->comment,
                'ip_address' => $request->ip(),
                'code' => Str::uuid(),
            ]);
            
            if($reqoldtock != $reqstock){
                if(WarehouseStock::where('warehouse_id', $wid->id)->where('product_id', $pid->id)->count()){
                    $wsid = WarehouseStock::where('warehouse_id', $wid->id)->where('product_id', $pid->id)->first();
                    $wsid->update(['stock' => $reqstock]);
                } else {
                    WarehouseStock::create(['warehouse_id' => $wid->id, 'product_id' => $pid->id, 'stock' => $reqstock]);
                }
            }
            
            return redirect()->route('product_barcode');
        }
        
        return back();
    }
}
