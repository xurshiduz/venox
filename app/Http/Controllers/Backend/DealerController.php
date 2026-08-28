<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client as GClient;
use App\Models\Dealer;
use App\Models\History;
use App\Models\Region;
use Illuminate\Http\Request;
use Auth;
use Str;
use Carbon\Carbon;

class DealerController extends Controller
{
    public function index()
    { 
        $data = Dealer::orderBy('id', 'desc')->paginate(20);
        $keyword = NULL; 
        return view('backend.dealers.index', compact('data', 'keyword'));
    }

    public function form($id = null)
    {
        $item = null;
        $regions = Region::all();
        if($id) {
            $item = Dealer::where('code', $id)->first();
        }
        
        return view('backend.dealers.form', compact('item', 'regions'));
    }

    public function save(Request $request, $id = null)
    {

        $data = $request->all();

        if ($id) {
            $item = Dealer::where('code', $id)->first();

            $oldname        = $item->name;
            $oldlocation    = $item->location;
            $oldphone       = $item->phone;
            $oldaddress     = $item->address;
            $oldregion_id   = $item->region_id;
            $oldcomment     = $item->comment;
            
            if($item) {
                $item->update($data);
                $request->session()->flash('success', trans('backend.post_update'));
            }
            
            if($oldname != $item->name || $oldlocation != $item->location || $oldphone != $item->phone || $oldaddress != $item->address || $oldcomment != $item->comment){
                
                $hdata['dealer_id'] = $item->dealer_id;
                $hdata['user_id'] = Auth::id();
                $hdata['code'] = Str::uuid();
                $hdata['name'] = 2;
                $hdata['database'] = 'dealers';
                $hdata['ip_address'] = $request->ip();
                $hdata['agent'] = $request->server('HTTP_USER_AGENT');
                $hdata['comment'] = $oldname != $item->name ? ('Наименование с <b>' . ($oldname ? $oldname : 'NULL') . '</b> на ' . ($item->name ? $item->name  : 'NULL') . '</b>' ) : null
                . ($oldlocation != $item->location ? ('Локация с <b>' . ($oldlocation ? $oldlocation : 'NULL') . '</b> на ' . ($item->location ? $item->location : 'NULL') . '</b>' ) : null)
                . ($oldaddress != $item->address ? ('Адресс с <b>' . ($oldaddress ? $oldaddress : 'NULL') . '</b> на <b>' . ($item->address ? $item->address : 'NULL') . '</b>' ) : null)
                . ($oldphone != $item->phone ? ('Контакты с <b>' . ($oldphone ? $oldphone : 'NULL') . '</b> на <b>' . ($item->phone ? $item->phone : 'NULL') . '</b>' ) : null)
                . ($oldcomment != $item->comment ? ('Примечание с <b>' . ($oldcomment ? $oldcomment : 'NULL') . '</b> на <b>' . ($item->comment ? $item->comment : 'NULL') . '</b>' ) : null);
                
                $history = History::create($hdata);
                
                $client = new GClient([
                    "base_uri" => "https://api.telegram.org",
                ]);
                
                $clientid       = $item->name;
                $ip             = $request->ip();
                $hid            = $history->id;
                $dealer         = Auth::user()->dealerid ? Auth::user()->dealerid->name : null;
                $user           = Auth::user()->name;
                $comment        = $hdata['comment'];
                $date           = Carbon::now()->format('Y-m-d H:i:s');
                
                $bot_token = "7335500759:AAFQCsaM-8jLefkgmNEW3phJxmn_gQIBSQw";
                $chat_id = "-1003627640983";
                $message = "ID# $hid\n<b><u>⚠️ Модуль: Дилер</u></b>\n <b>🤵‍♂️ Дилер:</b> $clientid \n\n<b>💠 Филиал:</b> $dealer \n<b>👨‍💻Пользователь:</b> $user \n<b>📝 Примечание:</b> $comment \n<b>📍 ИП адрес:</b> $ip \n<b>⏱ Дата :</b> $date";
                $response = $client->request("GET", "/bot$bot_token/sendMessage", [
                    
                    "query" => [
                        "chat_id" => $chat_id,
                        "text" => $message,
                        "parse_mode" => "html"
                    ]
                ]);
                
            }
        } else {
            $data['code'] = Str::uuid();

            $item = Dealer::create($data);
            $request->session()->flash('success', trans('backend.post_create'));
        }

        return redirect()->action('Backend\DealerController@index');
    }
}
