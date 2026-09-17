<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Role;
use App\Models\Warehouse;
use App\Models\Dealer;
use App\Models\ProductCategory;

use Illuminate\Http\Request;
use Str;
use Auth;

class UserController extends Controller
{
    public function index(Request $request)
    {
        return $this->userList($request, false);
    }

    public function noactive(Request $request)
    {
        return $this->userList($request, true);
    }

    private function userList(Request $request, bool $archived)
    {
        $keyword = trim((string) $request->query('search', ''));
        $selectedRole = trim((string) $request->query('role', ''));
        $query = $archived
            ? User::onlyArchived()
            : User::query();

        $query->with(['dealerid', 'uroles.rolenameid']);

        if (Auth::user()->hasAnyRole('admin')) {
            $query->where('username', '!=', 'esengul');
        } else {
            $query->where('dealer_id', Auth::user()->dealer_id);
        }

        if ($keyword !== '') {
            $query->where(function ($builder) use ($keyword) {
                $builder->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('username', 'like', '%' . $keyword . '%')
                    ->orWhere('phone', 'like', '%' . $keyword . '%')
                    ->orWhere('email', 'like', '%' . $keyword . '%');
            });
        }

        if ($selectedRole !== '') {
            $query->role($selectedRole);
        }

        $data = $query->orderByDesc('id')->paginate(20)->withQueryString();
        $roles = Role::where('status', 1)->orderBy('name_full')->get();

        return view('backend.user.index', compact(
            'data',
            'keyword',
            'selectedRole',
            'roles',
            'archived'
        ));
    }
    
    public function checkouts($id)
    {
        $user = User::where('code', $id)->firstOrFail();

        // Savdolar sahifasining barcha filtrlari va view ma'lumotlari bitta
        // controllerda shakllansin. Aks holda bu eski endpoint yangi viewga
        // yetishmaydigan o'zgaruvchilarni yuborib, sahifani xatoga tushiradi.
        return redirect()->route('checkouts_index', [
            'agent_id' => $user->id,
        ]);
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

    public function archive(Request $request, $id)
    {
        $user = User::withArchived()->where('code', $id)->firstOrFail();

        if ((int) $user->id === (int) Auth::id() || $user->hasRole('admin')) {
            return back()->with('error', trans('backend.ui.user_archive_forbidden'));
        }

        $user->update(['status' => 0]);

        return redirect()->route('users_index')->with('success', trans('backend.ui.user_archived'));
    }

    public function restore(Request $request, $id)
    {
        $user = User::withArchived()->where('code', $id)->firstOrFail();
        $user->update(['status' => 1]);

        return redirect()->route('users_noactive')->with('success', trans('backend.ui.user_restored'));
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
