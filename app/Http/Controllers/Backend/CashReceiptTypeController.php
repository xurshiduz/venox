<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\CashReceiptType;
use Illuminate\Http\Request;
use Auth;
use Str;

class CashReceiptTypeController extends Controller
{
    public function index()
    { 
        $data = CashReceiptType::where('status', 1)->orderBy('id', 'desc')->paginate(20);
        $keyword = NULL; 
        return view('backend.cash_receipt_types.index', compact('data', 'keyword'));
    }

    public function form($id = null)
    {
        $item = null;

        if($id) {
            $item = CashReceiptType::where('status', 1)->where('code', $id)->first();
        }
        
        return view('backend.cash_receipt_types.form', compact('item'));
    }

    public function save(Request $request, $id = null)
    {

        $data = $request->all();

        if ($id) {
            $item = CashReceiptType::where('status', 1)->where('code', $id)->first();

            if($item) {
                $item->update($data);
                $request->session()->flash('success', trans('backend.post_update'));
            }
        } else {
            $data['code'] = Str::uuid();

            $item = CashReceiptType::create($data);
            $request->session()->flash('success', trans('backend.post_create'));
        }

        return redirect()->action('Backend\CashReceiptTypeController@index');
    }

    public function status($id = null)
    {
            $data['status'] = 0;
        $item = CashReceiptType::where('code', $id)->first();
                $item->update($data);
        
        return back();
    }
}
