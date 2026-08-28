<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client as GClient;
use Illuminate\Http\Request;

use App\Models\Supplier;
use App\Models\History;
use Auth;
use Str;

class SupplierController extends Controller
{
    public function index()
    { 
        $data = Supplier::orderBy('id', 'desc')->paginate(20); 
        $keyword = NULL;
        return view('backend.suppliers.index', compact('data', 'keyword'));
    }

    public function form($id = null)
    {
        $item = null;

        if($id) {
            $item = Supplier::where('code', $id)->first();
        }
        
        return view('backend.suppliers.form', compact('item'));
    }

    public function save(Request $request, $id = null)
    {

        $data = $request->all();

        if ($id) {
            $item = Supplier::where('code', $id)->first();
            
            $oldname        = $item->name;
            $olddirector    = $item->director;
            $oldphone       = $item->phone;
            $oldaddress     = $item->address;
            $oldinn         = $item->inn;
            $oldregion      = $item->region;
            $oldschet       = $item->schet;
            $oldmfo         = $item->mfo;
            $oldoked        = $item->oked;
            $oldcomment     = $item->comment;

            if($item) {
                $item->update($data);
                $request->session()->flash('success', trans('backend.post_update'));
            }
            
            if($oldname != $item->name || $olddirector != $item->director || $oldphone != $item->phone || $oldaddress != $item->address || $oldinn != $item->inn || 
            $oldregion != $item->region || $oldschet != $item->schet || $oldmfo != $item->mfo || $oldoked != $item->oked || $oldcomment != $item->comment){
                
                $hdata['dealer_id'] = Auth::user()->dealerid ? Auth::user()->dealerid->name : null;
                $hdata['user_id'] = Auth::id();
                $hdata['code'] = Str::uuid();
                $hdata['name'] = 4;
                $hdata['database'] = 'suppliers';
                $hdata['ip_address'] = $request->ip();
                $hdata['agent'] = $request->server('HTTP_USER_AGENT');
                $hdata['comment'] = $oldname != $item->name ? ('Наименование с <b>' . ($oldname ? $oldname : 'NULL') . '</b> на ' . ($item->name ? $item->name  : 'NULL') . '</b>' ) : null
                . ($olddirector != $item->director ? ('Имя директора с <b>' . ($olddirector ? $olddirector : 'NULL') . '</b> на ' . ($item->director ? $item->director : 'NULL') . '</b>' ) : null)
                . ($oldaddress != $item->address ? ('Адресс с <b>' . ($oldaddress ? $oldaddress : 'NULL') . '</b> на <b>' . ($item->address ? $item->address : 'NULL') . '</b>' ) : null)
                . ($oldphone != $item->phone ? ('Контакты с <b>' . ($oldphone ? $oldphone : 'NULL') . '</b> на <b>' . ($item->phone ? $item->phone : 'NULL') . '</b>' ) : null)
                . ($oldschet != $item->schet ? ('Р/сч с <b>' . ($oldschet ? $oldschet : 'NULL') . '</b> на <b>' . ($item->schet ? $item->schet : 'NULL') . '</b>' ) : null)
                . ($oldregion != $item->region ? ('Город с <b>' . ($oldregion ? $oldregion : 'NULL') . ' на <b>' . ($item->region ? $item->region : 'NULL') . '</b>' ) : null)
                . ($oldmfo != $item->mfo ? ('МФО с <b>' . ($oldmfo ? $oldmfo : 'NULL') . '</b> на <b>' . ($item->mfo ? $item->mfo : 'NULL') . '</b>' ) : null)
                . ($oldinn != $item->inn ? ('ИНН с <b>' . ($oldinn ? $oldinn : 'NULL') . '</b> на <b>' . ($item->inn ? $item->inn : 'NULL') . '</b>' ) : null)
                . ($oldoked != $item->oked ? ('ОКЭД с <b>' . ($oldoked ? $oldoked : 'NULL') . '</b> на <b>' . ($item->oked ? $item->oked : 'NULL') . '</b>' ) : null)
                . ($oldcomment != $item->comment ? ('Примечание с <b>' . ($oldcomment ? $oldcomment : 'NULL') . '</b> на <b>' . ($item->comment ? $item->comment : 'NULL') . '</b>' ) : null);
                
                $history = History::create($hdata);
                
                $client = new GClient([
                    "base_uri" => "https://api.telegram.org",
                ]);
                
                $clientid       = $item->name;
                $ip             = $request->ip();
                $dealer         = Auth::user()->dealerid ? Auth::user()->dealerid->name : null;
                $user           = Auth::user()->name;
                $hid            = $history->id;
                $comment        = $hdata['comment'];
                $date           = Carbon::now()->format('Y-m-d H:i:s');
                
                $bot_token = "7335500759:AAFQCsaM-8jLefkgmNEW3phJxmn_gQIBSQw";
                $chat_id = "-1003627640983";
                $message = "ID# $hid\n<b><u>⚠️ Модуль: Поставщик</u></b>\n <b>🤵‍♂️ Поставщик:</b> $clientid \n\n<b>💠 Филиал:</b> $dealer \n<b>👨‍💻Пользователь:</b> $user \n<b>📝 Примечание:</b> $comment \n<b>📍 ИП адрес:</b> $ip \n<b>⏱ Дата :</b> $date";
                $response = $client->request("GET", "/bot$bot_token/sendMessage", [
                    
                    "query" => [
                        "chat_id" => $chat_id,
                        "text" => $message,
                        "parse_mode" => "html"
                    ]
                ]);
                
            }
            
        } else {
            $data['user_id'] = Auth::id();
            $data['code'] = Str::uuid();
            
            $item = Supplier::create($data);
            $request->session()->flash('success', trans('backend.post_create'));
        }

        return redirect()->action('Backend\SupplierController@index');
    }
}

