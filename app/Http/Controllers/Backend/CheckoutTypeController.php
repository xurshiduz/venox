<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\CheckoutType;
use Auth;
use Str;

class CheckoutTypeController extends Controller
{
    public function index()
    { 
        $data = CheckoutType::where('status', 1)->orderBy('id', 'desc')->paginate(20);
        $keyword = NULL; 
        return view('backend.checkout_types.index', compact('data', 'keyword'));
    }

    public function form($id = null)
    {
        $item = null;
        if($id) {
            $item = CheckoutType::where('status', 1)->where('code', $id)->first();
        }
        return view('backend.checkout_types.form', compact('item'));
    }

    public function save(Request $request, $id = null)
    {
        $data = $request->all();
        if ($id) {
            $item = CheckoutType::where('status', 1)->where('code', $id)->first();
            if($item) {
                $item->update($data);
                $request->session()->flash('success', trans('backend.post_update'));
            }
        } else {
            $data['code'] = Str::uuid();
            $item = CheckoutType::create($data);
            $request->session()->flash('success', trans('backend.post_create'));
        }
        return redirect()->action('Backend\CheckoutTypeController@index');
    }

    public function status($id = null)
    {
        $data['status'] = 0;
        $item = CheckoutType::where('code', $id)->first();
        $item->update($data);
        
        return back();
    }
}
