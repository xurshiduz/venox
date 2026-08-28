<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Price;
use App\Models\Product;

class PriceController extends Controller
{
    public function index()
    { 
        $data = Product::orderBy('id', 'desc')->paginate(20); 
        $keyword = NULL;
        return view('backend.prices.index', compact('data', 'keyword'));
    }

    public function api_index()
    {
        $model = request()->model;

        $data = Price::where(function ($query) use($model) {
                $query->where('name', 'like', '%' . $model . '%')
                   ->orWhere('part_number', 'like', '%' . $model . '%');
              })
        ->get();

        return response()->json($data);
    }

    public function form($id = null)
    {
        $item = null;
        $products = Product::all();
        $units = Unit::all();

        if($id) {
            $item = Price::findOrFail($id);
        }
        
        return view('backend.prices.form', compact('item', 'products', 'units'));
    }

    public function save(Request $request, $id = null)
    {

        $data = $request->all();

        

        if ($id) {
            $item = Price::find($id);
            if($item) {
                $item->update($data);
                $request->session()->flash('success', trans('backend.post_update'));
            }
        } else {
            $data['user_id'] = Auth::id();
            $data['code'] = Str::uuid();

            $item = Price::create($data);
            $request->session()->flash('success', trans('backend.post_create'));
        }

        return redirect()->action('Backend\PriceController@index');
    }

    public function search(Request $request)
    { 
        $keyword = $request->input('search');

        $data = Price::where(function ($query) use($keyword) {
                $query->where('name', 'like', '%' . $keyword . '%')
                   ->orWhere('onec_code', 'like', '%' . $keyword . '%')
                   ->orWhere('part_number', 'like', '%' . $keyword . '%');
              })
        ->get();

        return view('backend.prices.index', compact('data'));
    }
}
