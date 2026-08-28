<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller; 
use App\Models\CashExpenditure;
use App\Models\CashExpenditureType;
use Illuminate\Http\Request; 
use App\Models\Checkout;
use App\Models\Client;
use App\Models\Setting;
use App\Models\CashReceiptType;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Warehouse;
use Auth;
use Str;
use Carbon\Carbon;
use App\Exports\Export;
use Excel;

class CashExController extends Controller
{
    public function index()
    { 
        if(Auth::user()->hasAnyRole('admin|report')){
            $data = CashExpenditure::orderBy('id', 'desc')->paginate(20); 
        } else{
            $data = CashExpenditure::where('user_id', Auth::id())->orderBy('id', 'desc')->paginate(20); 
        }
        $contracts = CashExpenditureType::where('type', 1)->get();
        $stores = Warehouse::all();
        
        return view('backend.cash_expenditures.index', compact('data', 'contracts', 'stores'));
    }
    
    public function category_select($id)
    { 
        $selectid = CashExpenditureType::where('code', $id)->first();
        if(Auth::user()->hasAnyRole('admin|report')){
            $data = CashExpenditure::where('cash_expenditure_types', $selectid->id)->orderBy('id', 'desc')->paginate(40); 
        } else{
            $data = CashExpenditure::where('cash_expenditure_types', $selectid->id)->where('user_id', Auth::id())->orderBy('id', 'desc')->paginate(40); 
        }
        
        return view('backend.cash_expenditures.category_select', compact('data', 'selectid'));
    }
    
    public function search(Request $request)
    { 
        $contracts = CashExpenditureType::where('type', 1)->get();
        $stores = Warehouse::all();
        $type = NULL;
        $ot = Carbon::parse($request->input('date_ot'));
        $do = Carbon::parse($request->input('date_do'));
        $magazin = $request->input('store');
            
        if($request->input('type')){
            $type = $request->input('type');
            
            if(Auth::user()->hasAnyRole('admin|report')){
                if($magazin){
                    $data = CashExpenditure::orderBy('id', 'desc')->where('store_id', $magazin)->whereBetween('date', [$ot, $do])->where('cash_expenditure_types', $type)->get();
                } else {
                   $data = CashExpenditure::orderBy('id', 'desc')->whereBetween('date', [$ot, $do])->where('cash_expenditure_types', $type)->get(); 
                }
                
            } else{
                $data = CashExpenditure::where('user_id', Auth::id())->orderBy('id', 'desc')->whereBetween('date', [$ot, $do])->where('cash_expenditure_types', $type)->get();
            }
            
            $tname = CashExpenditureType::find($type);
            return view('backend.cash_expenditures.search_type', compact('data', 'type', 'contracts', 'tname', 'stores', 'magazin', 'ot', 'do'));
        } else {
            if(Auth::user()->hasAnyRole('admin|report')){
                if($magazin){
                    $data = CashExpenditure::orderBy('id', 'desc')->where('store_id', $magazin)->whereBetween('date', [$ot, $do])->get();
                } else {
                   $data = CashExpenditure::orderBy('id', 'desc')->whereBetween('date', [$ot, $do])->get(); 
                }
            } else{
                $data = CashExpenditure::where('user_id', Auth::id())->orderBy('id', 'desc')->whereBetween('date', [$ot, $do])->get();
            }
            
            return view('backend.cash_expenditures.search', compact('data', 'type', 'contracts', 'stores', 'magazin', 'ot', 'do'));
        }
        

        
    }

    public function form($id = null)
    {
        $item = null;
        $contracts = CashExpenditureType::where('type', 1)->get();
        $types = CashReceiptType::all();
        $suppliers = Client::whereNotNull('is_supplier')->get();
        
        if(Auth::user()->hasAnyRole('admin|report')){
            $employees = User::orderBy('id', 'desc')->get(); 
        } else{
            $employees = User::where('store_id', Auth::user()->store_id)->orderBy('id', 'desc')->get(); 
        }
        
        if($id) {
            $item = CashExpenditure::where('code', $id)->first();
        }
        
        return view('backend.cash_expenditures.form', compact('item', 'contracts', 'types', 'suppliers', 'employees'));
    }

    public function save(Request $request, $id = null)
    {
        $data = $request->all();
        $data['date'] = Carbon::parse($request->date)->format('Y-m-d');

        if ($id) {
            $item = CashExpenditure::where('code', $id)->first();
            if($item) {
                $item->update($data);
                $request->session()->flash('update_cash', trans('backend.post_update'));
            }
        } else {
            $data['code'] = Str::uuid();
            $data['user_id'] = Auth::id();
            $data['store_id'] = Auth::user()->store_id;
            
            $item = CashExpenditure::create($data);
            $request->session()->flash('success_cash', trans('backend.post_create'));
        }

        return redirect()->action('Backend\CashExController@index');
    }

    public function delete(Request $request, $id = null)
    {

        if ($id) {
            $item = CashExpenditure::where('code', $id)->first();
            if($item) {
                $item->delete();
                $request->session()->flash('success', 'o`chirildi');
            }
        } 

        return back();
    }
    
    public function excel()
    {
        return Excel::download(new Export(), 'export- ' . Carbon::now()->format('Ymd') . '.xlsx');
    }
    
    ///
    public function types_index()
    { 
        $data = CashExpenditureType::where('type', 1)->orderBy('id', 'desc')->paginate(20); 
        $keyword = NULL; 
        return view('backend.cash_expenditure_types.index', compact('data', 'keyword'));
    }
    
    public function types_form($id = null)
    {
        $item = null;
        
        if($id) {
            $item = CashExpenditureType::where('code', $id)->first();
        }
        
        return view('backend.cash_expenditure_types.form', compact('item'));
    }

    public function types_save(Request $request, $id = null)
    {
        $data = $request->all();

        if ($id) {
            $item = CashExpenditureType::where('code', $id)->first();

            if($item) {
                $item->update($data);
                $request->session()->flash('update_cash', trans('backend.post_update'));
            }
        } else {
            $data['code'] = Str::uuid();
            $data['user_id'] = Auth::id();

            $item = CashExpenditureType::create($data);
            $request->session()->flash('success_cash', trans('backend.post_create'));
        }

        return redirect()->action('Backend\CashExController@types_index');
    }
}
