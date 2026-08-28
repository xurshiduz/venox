<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Auth;
use Str;

class CategoryController extends Controller
{
    public function index()
    { 
        $keyword = NULL; 
        $data = ProductCategory::orderBy('id', 'desc')->paginate(20); 
        return view('backend.categories.index', compact('data', 'keyword'));
    }

    public function form($id = null)
    {
        $item = null;

        if($id) {
            $item = ProductCategory::where('code', $id)->first();
        }
        
        return view('backend.categories.form', compact('item'));
    }

    public function save(Request $request, $id = null)
    {

        $data = $request->all();

        if ($id) {
            $item = ProductCategory::where('code', $id)->first();

            if($item) {
                $item->update($data);
                $request->session()->flash('success', trans('backend.post_update'));
            }
        } else {
            $data['code'] = Str::uuid();

            $item = ProductCategory::create($data);
            $request->session()->flash('success', trans('backend.post_create'));
        }

        return redirect()->action('Backend\CategoryController@index');
    }

    public function import_form()
    { 
        return view('backend.categories.import');
    }

    public function import_save(Request $request)
    {

        $data = $request->all();

        $data['code'] = Str::uuid();

        $item = ProductCategory::create($data);
        $request->session()->flash('success', trans('backend.post_create'));

        return redirect()->action('Backend\CategoryController@index');
    }
}
