<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller; 
use Illuminate\Http\Request;
use App\Models\ProductBrand;
use App\Models\Currency;
use App\Models\CurrencyType;
use App\Models\Tax;
use Auth;
use Str;
use GuzzleHttp\Client as GClient;

class CurrencyController extends Controller
{
    
    //Brand
    public function brand_index()
    { 
        $data = ProductBrand::orderBy('id', 'desc')->paginate(20); 
        $keyword = NULL;
        
        return view('backend.brands.index', compact('data', 'keyword'));
    }

    public function brand_form($id = null)
    {
        $item = null;
        $categories = ProductCategory::all();

        if($id) {
            $item = ProductBrand::where('code', $id)->first();
        }
        
        return view('backend.brands.form', compact('item','categories'));
    }

    public function brand_save(Request $request, $id = null)
    {
        
        if ($id) {
             $request->validate([
                    'category_id'  => 'required|integer',
                    'name'         => 'required|min:3',
                    'plotnost'     => 'required|min:2',
            ]);
         
            $data = $request->only([
                    'category_id',
                    'name',
                    'plotnost'
            ]);
            
            
            $item = ProductBrand::where('code', $id)->first();

            if($item) {
                
                $data['name'] =  "BASALTWOOL " .$request->name;
                $item->update($data);
                
                $request->session()->flash('brand_update', trans('backend.post_update'));
            }
            
            foreach($item->parametr()->get() as $p){
                $p->delete();
            }
            
        } else {
            
             $request->validate([
                    'category_id'  => 'required|integer',
                    'name'         => 'required|min:3|unique:product_brands,name',
                    'plotnost'     => 'required|min:2',
            ]);
         
            $data = $request->only([
                    'category_id',
                    'name',
                    'plotnost'
            ]);
            
            $data['code'] = Str::uuid();
            $data['name'] =  "BASALTWOOL " .$request->name;
            $item = ProductBrand::create($data);
            
            $client = new GClient([
            "base_uri" => "https://api.telegram.org",
            ]);
            
            $valyuta = $item->name;
            $pname =  $item->plotnost;
            $start_at = $item->created_at;
            $cat = $item->products->name;
            
            $men3 = 'добален_новый_марка';
            $bot_token = "5642867560:AAEvNhtPco1gf1V9iPbdIuwBpvKyeW53pKQ";
            $chat_id = "-1001611367540";
            $message = "<b>#$men3</b> \n<b>Наименование:</b> $valyuta\n<b>Плотность:</b> $pname\n<b>Категории:</b> $cat\n<b>Время:</b> $start_at";
            $response = $client->request("GET", "/bot$bot_token/sendMessage", [
                "query" => [
                    "chat_id" => $chat_id,
                    "text" => $message,
                    "parse_mode" => "html"
                ]
            ]);
            
            $request->session()->flash('brand_success', trans('backend.post_create'));
        }
        
        foreach ($request->input('tolshina') as $key => $value) {
        	$bdata['tolshina'] = data_get($request->input('tolshina'), $key);
        	$bdata['pcs'] = data_get($request->input('pcs'), $key);
        	$bdata['brand_id'] = $item->id;
        	ProductParametr::create($bdata);
        }

        return redirect()->action('Backend\CategoryController@brand_index');
    }
    //End mark
    
    //Currency
    public function index()
    { 
        $data = CurrencyType::where('id', '!=', 2)->orderBy('id', 'desc')->paginate(20); 
        $keyword = NULL;
        
        return view('backend.currencies.index', compact('data', 'keyword'));
    }

    public function form($id = null)
    {
        $item = null;
        $cc = CurrencyType::where('belgi', $id)->first(); 

        if($id) {
            $item = Currency::orderBy('id', 'desc')->where('type_id', $cc->id)->first();
        }
        
        return view('backend.currencies.form', compact('item'));
    }

    public function save(Request $request, $id = null)
    {
        if(Currency::where('type_id', CurrencyType::where('belgi', $id)->first()->id)->orderBy('id', 'desc')->count()){
            $oldv = Currency::where('type_id', CurrencyType::where('belgi', $id)->first()->id)->orderBy('id', 'desc')->first()->price;
        }
        
        
        $data['price'] = $request->price;
        $data['code'] = Str::uuid();
        $data['user_id']   = Auth::id();
        $data['type_id']   = CurrencyType::where('belgi', $id)->first()->id;
        
        $item = Currency::create($data);
        $request->session()->flash('success', trans('backend.post_create'));

        return redirect()->action('Backend\CurrencyController@index');
    }
    //End Currency

    public function currency_type_form($id = null)
    {
        $item = null;
        if($id) {
            $item = CurrencyType::where('code', $id)->first();
        }
        return view('backend.currency_type.form', compact('item'));
    }

    public function currency_type_save(Request $request, $id = null)
    {
        $data = $request->all();
        if ($id) {
            $item = CurrencyType::where('code', $id)->first();
            if($item) {
                $item->update($data);
                $request->session()->flash('success', trans('backend.post_update'));
            }
        } else {
            $data['user_id'] = Auth::id();
            $item = CurrencyType::create($data);
            Currency::create(['user_id' => Auth::id(), 'type_id' => $item->id, 'price' => 0, 'code' => Str::uuid()]);
            $request->session()->flash('success', trans('backend.post_create'));
        }
        return redirect()->action('Backend\CategoryController@currency_index');
    }
    
    //Tax
    public function tax_index()
    { 
        $data = Tax::orderBy('id', 'desc')->paginate(20); 
        $keyword = NULL;
        
        return view('backend.taxes.index', compact('data', 'keyword'));
    }

    public function tax_form($id = null)
    {
        $item = null;
        if($id) {
            $item = Tax::where('code', $id)->first();
        }
        return view('backend.taxes.form', compact('item'));
    }

    public function tax_save(Request $request, $id = null)
    {
        $data = $request->all();
        if ($id) {
            $item = Tax::where('code', $id)->first();
            if($item) {
                $item->update($data);
                $request->session()->flash('success', trans('backend.post_update'));
            }
        } else {
            $data['user_id']   = Auth::id();
            $item = Tax::create($data);
            $request->session()->flash('success', trans('backend.post_create'));
        }
        return redirect()->action('Backend\CategoryController@tax_index');
    }
    //End Tax
}
