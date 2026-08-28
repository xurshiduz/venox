<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\CheckoutDetail;
use App\Models\WarehouseStock;
use App\Models\Checkout;
use App\Models\Returns;

use Auth;
use Str;

class ReturnController extends Controller
{
    public function index()
    {
        if(Auth::user()->hasAnyRole('admin|cashier')){
            $data = Returns::orderBy('id', 'desc')->paginate(20);
        } elseif(Auth::user()->hasAnyRole('diler_admin')) {
            $data = Returns::where('dealer_id', Auth::user()->dealer_id)->orderBy('id', 'desc')->paginate(20);
        } else {
            $data = Returns::where('user_id', Auth::id())->orderBy('id', 'desc')->paginate(20);
        }
        
        $keyword = NULL;
        return view('backend.return_product.index', compact('data', 'keyword'));
    }

    public function form()
    {
        $keyword = NULL;
        $item = NULL;
        return view('backend.return_product.form', compact('item', 'keyword'));
    }

    public function save(Request $request)
    {
        if (Checkout::where('transaction', $request->part_number)->orWhere('number_work', $request->part_number)->count()) {
            $keyword = $request->part_number;
            $item = Checkout::where('transaction', $request->part_number)->orWhere('number_work', $request->part_number)->first();
            return view('backend.return_product.form', compact('item', 'keyword'));
        }
        
        $request->session()->flash('error', 'Чек со штрих-кодом ' . $request->part_number . ' не найден');
        return redirect()->back();
    }
    
    public function return_post(Request $request, $code)
    {
        $item = CheckoutDetail::where('code', $code)->first();
        
        
        if($item) {
            $data['user_id'] = Auth::id();
            $data['seller_id'] = $item->checkid->user_id;
            $data['checkout_id'] = $item->checkout_id;
            $data['checkout_detail_id'] = $item->id;
            $data['product_id'] = $item->product_id;
            $data['qty'] = $request->qty;
            $data['price'] = $item->price;
            $data['code'] = Str::uuid();
            
            $data['number_doc'] = $item->checkid->number_work;
            $data['warehouse_id'] = $item->warehouse_id;
            
            Returns::create($data);
            
            $item->update(['qty' => ($item->qty - $request->qty), 'total_price' => (($item->qty - $request->qty) * $item->price), 'return_qty' => $request->qty]);
            
            if(WarehouseStock::where('warehouse_id', $item->warehouse_id)->where('product_id', $item->product_id)->count()){
                $wsid = WarehouseStock::where('warehouse_id', $item->warehouse_id)->where('product_id', $item->product_id)->first();
                $st = ($wsid->stock + $request->qty);
                $wsid->update([
                    'stock' => $st,
                    'checkin_price' => $item->prodid->checkindetails()->max('price'),
                    'checkout_price' => $item->prodid->price ? $item->prodid->price : $item->prodid->checkoutdetails()->max('price'),
                    'checkin_total_price' => $st > 0 ? ($item->prodid->checkindetails()->max('price') * $st) : 0,
                    'checkout_total_price' => $st > 0 ? (($item->prodid->price ? $item->prodid->price : $item->prodid->checkoutdetails()->max('price')) * $st) : 0
                    
                    ]);
            } 
        
            $request->session()->flash('success', 'Успешно');
            return redirect()->back();
        }
        
        $request->session()->flash('error', 'Чек со штрих-кодом ' . $request->part_number . ' не найден');
        return redirect()->back();
    }
}
