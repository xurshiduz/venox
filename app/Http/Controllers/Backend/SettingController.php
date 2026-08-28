<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Setting;
use Auth;
use Str;

class SettingController extends Controller
{
    public function index()
    { 
        $keyword = NULL; 
        $data = Setting::orderBy('id', 'desc')->paginate(20); 
        return view('backend.settings.index', compact('data', 'keyword'));
    }

    public function form($id = null)
    {
        $item = null;
        if($id) {
            $item = Setting::where('atribute', $id)->first();
        }
        return view('backend.settings.form', compact('item'));
    }

    public function save(Request $request, $id = null)
    {
        if(Setting::where('atribute', $request->atribute)->count()){
            $request->session()->flash('error', trans('backend.post_duplicate'));
            return back();
        }
        
        $data['value'] = $request->value;
        
        if ($id) {
            $item = Setting::where('atribute', $id)->first();
            if($item) {
                $item->update($data);
                $request->session()->flash('success', trans('backend.post_update'));
            }
            
        } else {
            $data['atribute'] = $request->atribute;
            $item = Setting::create($data);
            $request->session()->flash('success', trans('backend.post_create'));
        }
        return redirect()->action('Backend\SettingController@index');
    }
}
