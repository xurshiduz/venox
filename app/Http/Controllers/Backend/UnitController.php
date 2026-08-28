<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
use Auth;
use Str;

class UnitController extends Controller
{
    public function index()
    { 
        $data = Unit::orderBy('id', 'desc')->paginate(20);
        $keyword = NULL; 
        return view('backend.units.index', compact('data', 'keyword'));
    }

    public function form($id = null)
    {
        $item = null;

        if($id) {
            $item = Unit::where('code', $id)->first();
        }
        
        return view('backend.units.form', compact('item'));
    }

    public function save(Request $request, $id = null)
    {

        $data = $request->all();

        if ($id) {
            $item = Unit::where('code', $id)->first();

            if($item) {
                $item->update($data);
                $request->session()->flash('success', trans('backend.post_update'));
            }
        } else {
            $data['code'] = Str::uuid();

            $item = Unit::create($data);
            $request->session()->flash('success', trans('backend.post_create'));
        }

        return redirect()->action('Backend\UnitController@index');
    }
}
