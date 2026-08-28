<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exports\KpiExcel;


use App\Models\KpiPlan;
use App\Models\KpiPlanDetail;
use App\Models\User;
use App\Models\CashReceiptType;

use Carbon\Carbon;
use Excel;
use Auth;
use Str;

class KpiController extends Controller
{
    
    public function plan_index()
    { 
        $data   = KpiPlan::orderBy('id', 'desc')->paginate(20);
        
        return view('backend.kpi.plan', compact('data'));
    }
    
    public function plan_index_show(Request $request, $id = null)
    {
        $params = func_get_args();
		unset($params[0]);
		$alltypes = CashReceiptType::all();
		
		$ptypes = $this->filter($request);
		$inputs = $request->all();
        
        $item = KpiPlan::where('code', $id)->first();
        return view('backend.kpi.show', compact('item', 'ptypes', 'alltypes'));
    }
    
    public function postFilter(Request $request, $id = null)
	{		
		$cars = $this->filter($request);	
		$item = KpiPlan::where('code', $id)->first();
        return response()->json([
            'view' => view()->make('partials.cars', compact('cars', 'item'))->render(),
            'pagination' => $cars->appends(array_filter($request->all()))
                                ->withPath('')
                                ->links('partials.paginate')
                                ->toHtml(),
            'total' => $cars->total()
        ]);
	}

	public function filter($request)
	{
		$cars = CashReceiptType::when(!is_null($request->input('type_id')) && count($request->input('type_id')) > 0, function($query) use ($request) {
			return $query->whereIn('id', $request->input('type_id'));
		})->paginate(16);
		
		return $cars;
	}
    
    public function form($id = null)
    {
        $item = null;
        $managers = User::where('status', 1)->role('sale')->get();

        if($id) {
            $item = KpiPlan::where('code', $id)->first();
        }
        
        return view('backend.kpi.form', compact('item', 'managers'));
    }

    public function save(Request $request, $id = null)
    {
        $data['date'] = Carbon::parse($request->date . '-01')->format('Y-m-d');
        $data['comment'] = $request->comment;

        if ($id) {
            $item = KpiPlan::where('code', $id)->first();

            if($item) {
                $item->update($data);
                
                $item->details()->delete();
                
                foreach($request->managers as $key => $man){
                    KpiPlanDetail::create(['plan_id' => $item->id, 'manager_id' => $man, 'plan_sum' => Str::replace(' ', '', $request->plans[$key])]);
                }
            
                $request->session()->flash('success', trans('backend.post_update'));
            }
        } else {
            $data['code'] = Str::uuid();
            $data['user_id'] = Auth::id();
            
            if(KpiPlan::whereDate('date', $data['date'])->count()){
                $request->session()->flash('error', trans('backend.plan_count'));
                
                return back();
            }
            $item = KpiPlan::create($data);
            
            foreach($request->managers as $key => $man){
                KpiPlanDetail::create(['plan_id' => $item->id, 'manager_id' => $man, 'plan_sum' => Str::replace(' ', '', $request->plans[$key])]);
            }
            
            $request->session()->flash('success', trans('backend.post_create'));
        }

        return redirect()->action('Backend\KpiController@plan_index');
    }
    
    public function print($id = null)
    {
        $item = KpiPlan::where('code', $id)->first();
        return view('backend.kpi.print', compact('item'));
    }
    
    public function excel($id)
    {
        return Excel::download(new KpiExcel($id), 'kpi- ' . $id . '.xlsx');
    }
    
    
}