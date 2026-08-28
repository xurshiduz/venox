<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Role;
use App\Models\Warehouse;
use App\Models\Dealer;
use App\Models\ProductCategory;
use App\Models\Checkout;
use App\Models\CashReceiptType;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Str;
use Auth;

class UserController extends Controller
{
    public function index()
    {
        $keyword = NULL;
        $data = User::orderBy('id', 'desc')->paginate(20);
        
        if(Auth::user()->hasAnyRole('admin')){
            $data = User::where('username', '!=','esengul')->orderBy('id', 'desc')->paginate(20);
        } else {
            $data = User::where('dealer_id', Auth::user()->dealer_id)->orderBy('id', 'desc')->paginate(20);
        }
        return view('backend.user.index', compact('data', 'keyword'));
    }
    
    public function checkouts($id)
    { 
        $cid = User::where('code',$id)->first();
        $fromdate       = Carbon::parse(Checkout::orderBy('id', 'asc')->first()->created_at)->format('d.m.Y');
        $todate         = Carbon::now()->format('d.m.Y');
        $managers = User::role('sale')->get();
        
        $shipment       = NULL;
        $finish         = NULL;
        $selmanager = $cid->id;
        
        if(Auth::user()->hasAnyRole('admin|cashier')){
            $data = Checkout::where('manager_id', $selmanager)->where('type_id', 1)->orderBy('id', 'desc')->paginate(20);
        } else {
            $data = Checkout::where('manager_id', $selmanager)->where('user_id', Auth::id())->where('type_id', 1)->orderBy('id', 'desc')->paginate(20);
        }
         
        $types = CashReceiptType::all();
        $keyword = NULL; 
        return view('backend.checkouts.index', compact('data', 'keyword', 'types', 'managers', 'fromdate', 'todate', 'selmanager', 'shipment', 'finish'));
    }

    public function role()
    {
        $data = Role::all();
        return view('backend.user.role', compact('data'));
    }

    public function form($id = null)
    {
        $item = null;
        $categories = Warehouse::where('status', 1)->get();
        $dealers = Dealer::where('status', 1)->get();
        if ($id) {
            $item = User::where('code', $id)->first();
        }
        return view('backend.user.form', compact('item', 'categories', 'dealers'));
    }

    public function save(Request $request, $id = null)
    {
        $data['name'] = $request->name;
        $data['phone'] = $request->phone;
        
        if(Auth::user()->hasAnyRole('admin')){
            $data['dealer_id'] = $request->dealer_id;
        } else {
            $data['dealer_id'] = Auth::user()->dealer_id;
        }
        
        if($request->warehouse_id != 0){
            $data['warehouse_id'] = $request->warehouse_id;
        }
        
        if($request->password){
            $data['password'] = bcrypt($request->password);
            $data['text_password'] = $request->password;
        }
        
        if($id) {
            $item =  User::where('code', $id)->first();
            if ($item) {
                $item->roles()->detach();
                $item->update($data);
                foreach($request->type as $sorder){
                    $item->assignRole($sorder);
                }
                $request->session()->flash('success_update', 'Пользователь обновлен!');
            }
        } else {
            $data['username'] = $request->username;
             
            $data['code'] = Str::uuid();
            $data['email'] = $request->username . '@azaliya.uz';
            $item = User::create($data);
            foreach($request->type as $sorder){
                $item->assignRole($sorder);
            }
            session()->flash('success', 'Пользователь успешно создано');
        }

        return redirect()->action('Backend\UserController@index');
    }

    //Parol
    public function p_form()
    {
        $item = User::find(Auth::id());
        return view('backend.myprofile.p_form', compact('item'));
    }

    public function p_save(Request $request, $id = null)
    {
        $this->validate($request, [
             'password'    => ['required','confirmed', 'min:6',    
                                'regex:/[a-z]/',      
                                'regex:/[A-Z]/',      
                                'regex:/[0-9]/'
                            ]
        ],
        [
            'password.confirmed'  => 'Пароли не совпадают! Пароли должны быть написаны одинаково!',
            'password.min'        => 'Пароль должен содержать не менее 6 символов!',
            'password.regex'      => 'В целях безопасности при установке пароля используйте прописные и строчные буквы и специальные символы! Ваш пароль должен содержать не менее 6 символов!'
            
        ]);

        $data = $request->only([
            'password',
        ]);

        $data['text_password'] = $data['password'];
        $data['password'] = bcrypt($data['password']);
        
        $item = User::find(Auth::id());
        
        if ($item) {
            $item->update($data);
        }
        
        $request->session()->flash('success_password', 'Пароль успешно обновлен!');
        return redirect()->route('home');
    }

    public function lock_user($id)
    {
        User::where('code', $id)->update(['status' => 0]);
        return back();
    }
    
    public function unlock_user($id)
    {
        User::where('code', $id)->update(['status' => 1]);
        return back();
    }

    public function block(Request $request, $id)
    {
        $item = User::where('code', $id)->first();
        if ($item) {
            if ($item->status == 1) {
                User::where('id', $id)->update(['status' => 0]);
                $request->session()->flash('success', 'Пользователь  заблокирован!');
            }  else {
                User::where('id', $id)->update(['status' => 1]);
                $request->session()->flash('success', 'Пользователь был разблокирован!');
            }
        }  else {
                $request->session()->flash('error', 'Пользователь не найден!');
        }
        return back();
    }

    public function m_form()
    {
        $item = User::find(Auth::id());
        return view('backend.myprofile.form', compact('item'));
    }

    public function m_save(Request $request)
    {

        $data = $request->only([
            'name',
            'phone',
        ]);

        $item = User::find(Auth::id());
        if ($item) {
            $item->update($data);
            $request->session()->flash('success', 'Обновлено!');
        }

        $request->session()->flash('success', trans('backend.submit.success'));
        return redirect()->route('home');
    }
    
    public function theme($id)
    {
        $data = User::where('code', $id)->first();
        $data->update(['dark_mode' => $data->dark_mode ? 0 : 1]); 
        return back();
    }
    
}
