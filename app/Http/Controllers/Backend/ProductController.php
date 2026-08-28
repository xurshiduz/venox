<?php

namespace App\Http\Controllers\Backend;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client as GClient;
use Illuminate\Http\Request;
use App\Exports\TopProduct;
use App\Exports\StockProductAll;

use App\Models\WarehouseBlockProduct;
use App\Models\WarehouseBlockCell;
use App\Models\ProductCategory;
use App\Models\ProductBrand;
use App\Models\WarehouseBlock;
use App\Models\WarehouseStock;
use App\Models\CheckinDetail;
use App\Models\Warehouse;
use App\Models\Checkout;
use App\Models\Checkin;
use App\Models\History;
use App\Models\Product;
use App\Models\Dealer;
use App\Models\Unit;

use Carbon\Carbon;
use Storage;
use Excel;
use Http;
use Auth;
use Str;
use DB;

class ProductController extends Controller
{
    public function index_old($archive = null)
    { 
        
        if($archive == 'archive'){
            $data = Product::where('status', 0)->select('products.*', 
                                     DB::raw('(SELECT AVG(qty) FROM checkout_details WHERE product_id = products.id) as average_sales'))
                           ->orderBy('id', 'desc')->paginate(40);
            
        } else {
            $data = Product::where('status', 1)->select('products.*', 
                                     DB::raw('(SELECT AVG(qty) FROM checkout_details WHERE product_id = products.id) as average_sales'))
                           ->orderBy('id', 'desc')->paginate(40);
        }

        foreach ($data as $product) {
            if ($product->average_sales > 0) {
                $product->months_left = round($product->stockid->sum('stock') / $product->average_sales, 2);
            } else {
                $product->months_left = 'Cheksiz'; // Agar sotuv bo'lmasa
            }
        }
        
        $keyword = NULL;
        return view('backend.products.index', compact('data', 'keyword', 'archive'));
    }
    
    public function index($archive = null) // Function nomini o'zingizga moslaysiz
    {
        
         // Statusni aniqlash
        $status = ($archive == 'archive') ? 0 : 1;
    
        $data = Product::where('status', $status)
            ->orderBy('id', 'desc')
            ->paginate(40);
        
        $keyword = NULL;
        return view('backend.products.index', compact('data', 'keyword', 'archive'));
        
        
        $query = Product::where('status', 1);

        // Mantiqiy shart: Archive yoki Active
        if ($archive) {
            // AGAR ARCHIVE BO'LSA:
            // Stock 1 dan kichik (0) bo'lganlar YOKI WarehouseStock da umuman yozuvi yo'qlar
            $query->where(function($q) {
                $q->whereHas('warehouseStock', function($subQuery) {
                    $subQuery->where('stock', '<', 1);
                })->orWhereDoesntHave('warehouseStock');
            });
        } else {
            // AGAR ARCHIVE BO'LMASA (Odatiy holat):
            // Faqat stock 1 va undan yuqori bo'lganlar chiqadi
            $query->whereHas('warehouseStock', function($subQuery) {
                $subQuery->where('stock', '>=', 1);
            });
        }
    
        // Tartiblash va paginatsiya
        $data = $query->orderBy('id', 'desc')
                      ->paginate(40);
        
        $keyword = NULL;
        
        return view('backend.products.index', compact('data', 'keyword', 'archive'));
        
    }
    
    
    public function new_search()
    { 
        return view('backend.products.search');
    }
    
    public function stock_all()
    { 
        return Excel::download(new StockProductAll(), 'Остаток.xlsx');
    }
    
    public function stock_check()
    { 
        foreach(CheckinDetail::all() as $alpr){
            foreach(WarehouseStock::where('product_id', $alpr->product_id)->get() as $pst){
                $pst->update(['checkin_price' => $alpr->price, 'checkin_total_price' => $pst->stock > 0 ? $pst->stock * $alpr->price : 0]);
            }
            
        }
        dd('success');
        
        $wares = Warehouse::all();
        $dealers = Dealer::all();
        $data = Product::where('status', 1)->where('stock_status', 0)->paginate(800); 
        foreach($data as $item){
            foreach($dealers as $dealer){
                foreach($wares->where('dealer_id', $dealer->id) as $ware){
                    $pin = $item->checkindetails()->where('warehouse_id', $ware->id)->where('status', 1)->sum('qty');
                    $pout = $item->checkoutdetails()->where('warehouse_id', $ware->id)->where('status', 1)->sum('qty');
                    $st = ($pin - $pout);
                    $wst = WarehouseStock::where('warehouse_id', $ware->id)->where('product_id', $item->id)->first();
                    if($pin != 0 || $pout != 0){
                        if($wst){
                            $wst->update(['stock' => $st, 'checkin_qty' => $pin, 'checkout_qty' => $pout]);
                        } else {
                            WarehouseStock::create(['warehouse_id' => $ware->id, 'product_id' => $item->id, 'stock' => $st, 'checkin_qty' => $pin, 'checkout_qty' => $pout]);
                        }
                        
                    }
                }
            }
            $item->update(['stock_status' => 1]);
        }
        dd('success');
        return back();
    }

    public function stock()
    { 
        $data = Product::where('status', 1)->orderBy('id', 'desc')->paginate(30); 
        $keyword = NULL;
        return view('backend.products.stock', compact('data', 'keyword'));
    }
    
    public function api_test_index()
    {
        $model = request()->model;
        $data = Product::create(['name' => $model]);

        return response()->json('success');
    }

    public function api_index()
    {
        $model = request()->model;
        
         $rulat = [
            "А"=>"A","Б"=>"B","В"=>"V","Г"=>"G",
            "Д"=>"D","Е"=>"E","Ж"=>"J","З"=>"Z","И"=>"I",
            "Й"=>"Y","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N",
            "О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T",
            "У"=>"U","Ф"=>"F","Х"=>"H","Ц"=>"TS","Ч"=>"CH",
            "Ш"=>"SH","Щ"=>"SCH","Ъ"=>"","Ы"=>"YI","Ь"=>"",
            "Э"=>"E","Ю"=>"YU","Я"=>"YA","а"=>"a","б"=>"b",
            "в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"j",
            "з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
            "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
            "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
            "ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
            "ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya"
        ];
        
        $latru = [
            "A"=>"А","B"=>"Б","V"=>"В","G"=>"Г",
            "D"=>"Д","E"=>"Е","J"=>"Ж","Z"=>"З","I"=>"И",
            "Y"=>"Й","K"=>"К","L"=>"Л","M"=>"М","N"=>"Н",
            "O"=>"О","P"=>"П","R"=>"Р","S"=>"С","T"=>"Т",
            "U"=>"У","F"=>"Ф","H"=>"Х","TS"=>"Ц","CH"=>"Ч",
            "SH"=>"Ш","SCH"=>"Щ","YI"=>"Ы",
            "E"=>"Э","YU"=>"Ю","YA"=>"Я","a"=>"а","b"=>"б",
            "v"=>"в","g"=>"г","d"=>"д","e"=>"е","j"=>"ж",
            "z"=>"з","i"=>"и","y"=>"й","k"=>"к","l"=>"л",
            "m"=>"м","n"=>"н","o"=>"о","p"=>"п","r"=>"р",
            "s"=>"с","t"=>"т","u"=>"у","f"=>"ф","h"=>"х",
            "ts"=>"ц","ch"=>"ч","sh"=>"ш","sch"=>"щ","'"=>"ъ",
            "yi"=>"ы","e"=>"э","yu"=>"ю","ya"=>"я"
        ];
        $runame = strtr($model,$latru);

        $data = Product::select('status','name','barcode','fullname','image','code')->where(function ($query) use($model, $runame) {
                $query->where('status', 1)->where('name', 'like', '%' . $model . '%')
                   ->orWhere('name', 'like', '%' . $runame . '%')
                   ->orWhere('barcode', 'like', '%' . $model . '%')
                   ->orWhere('fullname', 'like', '%' . $model . '%');
              })
        ->get();

        return response()->json($data);
    }

    public function print($id = null, $hmm = null, $vmm = null)
    {
        $item = Product::where('code', $id)->first();
        return view('backend.products.print', compact('item', 'hmm', 'vmm'));
        $qrcode = QrCode::size(120)->generate($item->id);
    }

    public function form($id = null, $page = null)
    {
        if (!Auth::user()->hasRole('arrival') && !Auth::user()->hasRole('admin')) {
            session()->flash('error', 'Сиз ўзгартишиш хуқуқига эга эмассиз');
            return back();
        }
        
        $item = null;
        $categories = ProductCategory::all();
        $units = Unit::all();
        $brands = ProductBrand::where('status', 1)->get();

        if($id) {
            $item = Product::where('code', $id)->first();
        }
        
        return view('backend.products.form', compact('item', 'categories', 'units', 'brands'));
    }

    public function product_report_form() {
        return view('backend.products.reports.index');
    }
    
    public function product_report_save(Request $request)
    {
        $fromDate = Carbon::parse($request->input('from_date'))->format('Y-m-d');
        $toDate = Carbon::parse($request->input('to_date'))->format('Y-m-d');
        if($request->type == 'excel'){
            return Excel::download(new TopProduct($fromDate, $toDate), 'Топ зап.часты от ' . $fromDate . ' до ' . $toDate . '.xlsx');
        } else {
            $mostSoldProducts = Checkout::whereBetween('date', [$fromDate, $toDate])
            ->join('checkout_details', 'checkouts.id', '=', 'checkout_details.checkout_id')
            ->join('products', 'checkout_details.product_id', '=', 'products.id')
            ->leftJoin('units', 'products.unit_id', '=', 'units.id') // Unit jadvalini qo'shish
            ->select(
                'products.name',
                'products.barcode',
                'units.name as unit_name', // Unit nomini olish
                DB::raw('SUM(checkout_details.qty) as total_qty')
            )
            ->groupBy('products.name', 'products.barcode', 'units.name') // Guruhlashni yangilash
            ->orderBy('total_qty', 'DESC')
            ->get();
    
            return view('backend.products.reports.print', compact('mostSoldProducts', 'fromDate', 'toDate'));
        }
    }
    
    public function delete($id, $page = null)
    {
        $item = Product::where('code', $id)->first();
        
        if($item->checkindetails->count() == 0 && $item->checkoutdetails->count() == 0 ){
            $item->delete();
        }
        
        if($page){
            return redirect()->to('/products?page='. $page);
        }

        return redirect()->action('Backend\ProductController@index');
    }
    
    public function status($id, $page = null)
    {
        if (!Auth::user()->hasRole('arrival') && !Auth::user()->hasRole('admin')) {
            session()->flash('error', 'Сиз архивга солиш хуқуқига эга эмассиз');
            return back();
        }
        
        $item = Product::where('code', $id)->first();
        $item->update(['status' => $item->status ? 0 : 1]);
        if($page){
            return redirect()->to('/products?page='. $page);
        }
        return redirect()->action('Backend\ProductController@index');
    }
    
    public function checkBarcode(Request $request)
    {
        $barcode = $request->barcode;
        $id = $request->id; // Tahrirlash rejimidagi ID
    
        // Agar barcode bo'sh bo'lsa, xato yo'q deb qaytarish
        if (!$barcode) {
            return response()->json(['exists' => false]);
        }
    
        // Bazadan izlash
        $query = Product::where('barcode', $barcode);
    
        // Agar tahrirlanayotgan bo'lsa, o'zining ID sini hisobga olmaslik kerak
        if ($id) {
            $query->where('id', '!=', $id);
        }
    
        $exists = $query->exists();
    
        return response()->json(['exists' => $exists]);
    }

    public function save(Request $request, $id = null, $page = null)
    {
    
        $data = [
            'description' => $request->input('description'),
            'name' => $request->input('name'),
            'price' => $request->input('price'),
            'wholesale_price' => $request->input('wholesale_price'),
            'unit_id' => $request->input('unit_id'),
            'barcode' => $request->input('barcode'),
            'category_id' => $request->input('category_id'),
            'country_id' => $request->input('country_id'),
            'currency_type' => $request->input('currency_type'),
            'notification_qty' => $request->input('notification_qty')
        ];
        
        $data['dealer_id'] = Auth::user()->dealer_id ? Auth::user()->dealer_id : 1;
        
        if($request->price){
            $data['price'] = str_replace(' ', '', $request->price);
        }
        
        if($request->wholesale_price){
            $data['wholesale_price'] = str_replace(' ', '', $request->wholesale_price);
        }
        
        if ($request->hasFile('image')) {
            if($id) {
                $item = Product::where('code', $id)->first();
                if (Storage::exists('upload/product_image/'. $item->image)) {                        
                    Storage::delete('upload/product_image/'. $item->image);
                }
            }
            
            $extension = $request->file('image')->getClientOriginalExtension();
            $fileNameToStore = time().'.'.$extension;
            $path = $request->file('image')->move('upload/product_image/',$fileNameToStore);
            $data['image'] = $fileNameToStore;
        }
        
        if ($request->hasFile('image_2')) {
            if($id) {
                $item = Product::where('code', $id)->first();
                if (Storage::exists('upload/product_image/'. $item->image_2)) {                        
                    Storage::delete('upload/product_image/'. $item->image_2);
                }
            }
            
            $extension = $request->file('image_2')->getClientOriginalExtension();
            $fileNameToStore = time().'2.'.$extension;
            $path = $request->file('image_2')->move('upload/product_image/',$fileNameToStore);
            $data['image_2'] = $fileNameToStore;
        }
        
        if ($request->hasFile('image_3')) {
            if($id) {
                $item = Product::where('code', $id)->first();
                if (Storage::exists('upload/product_image/'. $item->image_3)) {                        
                    Storage::delete('upload/product_image/'. $item->image_3);
                }
            }
            
            $extension = $request->file('image_3')->getClientOriginalExtension();
            $fileNameToStore = time().'3.'.$extension;
            $path = $request->file('image_3')->move('upload/product_image/',$fileNameToStore);
            $data['image_3'] = $fileNameToStore;
        }
        
        $data['fullname'] = $request->name .' ('. $request->barcode .')';
        if ($id) {
            $item = Product::where('code', $id)->first();
            
            $oldname                = $item->name;
            $oldunit_id             = $item->unit_id;
            $oldunitname            = $item->unitid->name;
            $oldcategory_id         = $item->category_id;
            $oldimage               = $item->image;
            $oldprice               = $item->price;
            $oldnotification_qty    = $item->notification_qty;
            $oldcountry_id          = $item->country_id;
            $oldcurrency_type       = $item->currency_type;
            $oldstatus              = $item->status;
            
            
            
            if($item) {
                $item->update($data);
            $item->brands()->attach($request->input('brands'));
                $request->session()->flash('success', trans('backend.post_update'));
            }
            
            if($oldname != $item->name || $oldunit_id != $item->unit_id || $oldcategory_id != $item->category_id || $oldimage != $item->image || $oldprice != $item->price ||
            $oldnotification_qty != $item->notification_qty || $oldcountry_id != $item->country_id || $oldcurrency_type != $item->currency_type || $oldstatus != $item->status){
                
                $hdata['dealer_id'] = Auth::user()->dealer_id;
                $hdata['user_id'] = Auth::id();
                $hdata['code'] = Str::uuid();
                $hdata['name'] = 10; 
                $hdata['database'] = 'products';
                $hdata['ip_address'] = $request->ip();
                $hdata['agent'] = $request->server('HTTP_USER_AGENT');
                $hdata['comment'] = ($oldname != $item->name ? ('Наименование с <b>' . ($oldname ? $oldname : 'NULL') . '</b> на <b>' . ($item->name ? $item->name  : 'NULL') . '</b> ' ) : null)
                . ($oldunit_id != $item->unit_id ? ('Ед. измерения с <b>' . ($oldunitname ? $oldunitname : 'NULL') . '</b> на <b>' . ($item->unitid->name ? $item->unitid->name : 'NULL') . '</b> ' ) : null)
                . ($oldcategory_id != $item->category_id ? ('Категория с <b>' . ($oldcategory_id ? $oldcategory_id : 'NULL') . '</b> на <b>' . ($item->category_id ? $item->category_id : 'NULL') . '</b> ' ) : null)
                . ($oldimage != $item->image ? 'Изображение было заменено ' : null)
                . ($oldprice != $item->price ? ('Цена с <b>' . ($oldprice ? $oldprice : 'NULL') . '</b> на <b>' . ($item->price != null ? $item->price : 'NULL') . '</b> ' ) : null)
                . ($oldnotification_qty != $item->notification_qty ? ('Мин. запас с <b>' . ($oldnotification_qty ? $oldnotification_qty : 'NULL') . '</b> на <b>' . ($item->notification_qty ? $item->notification_qty : 'NULL') . '</b> ' ) : null)
                . ($oldcountry_id != $item->country_id ? ('Страна с <b>' . ($oldcountry_id ? $oldcountry_id : 'NULL') . '</b> на <b>' . ($item->country_id ? $item->country_id : 'NULL') . '</b> ' ) : null)
                . ($oldcurrency_type != $item->currency_type ? ('Валюта с <b>' . ($oldcurrency_type ? $oldcurrency_type : 'NULL') . '</b> на <b>' . ($item->currency_type ? $item->currency_type : 'NULL') . '</b> ' ) : null)
                . ($oldstatus != $item->status ? ('Статус с <b>' . ($oldstatus ? $oldstatus : 'NULL') . '</b> на <b>' . ($item->status ? $item->status : 'NULL') . '</b> ' ) : null);
                
                $history = History::create($hdata);
                
                $client = new GClient([
                    "base_uri" => "https://api.telegram.org",
                ]);
                
                $clientid       = $item->name;
                $ip             = $request->ip();
                $dealer         = Auth::user()->dealerid ? Auth::user()->dealerid->name : null;
                $user           = Auth::user()->name;
                $barcode        = $item->barcode;
                $hid            = $history->id;
                $comment        = $hdata['comment'];
                $date           = Carbon::now()->format('Y-m-d H:i:s');
                
                $bot_token = "7335500759:AAFQCsaM-8jLefkgmNEW3phJxmn_gQIBSQw";
                $chat_id = "-1003627640983";
                $message = "ID#$hid\n<b><u>⚠️ Модуль: Продукты (Запчасти)</u></b>\n<b>🧾 Продукт (Запчасти):</b> $clientid \n\n<b>🏬 Дилер:</b> $dealer \n<b>📦 Баркод:</b> $barcode \n<b>👨‍💻 Пользователь:</b> $user \n<b>📝 Примечание:</b> $comment \n<b>📍 ИП адрес:</b> $ip \n<b>⏱ Дата :</b> $date";
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

            $item = Product::create($data);
            $item->brands()->attach($request->input('brands'));
            $request->session()->flash('success', trans('backend.post_create'));
        }
        
        if(WarehouseStock::where('product_id', $item->id)->count() && $item->price){
            foreach(WarehouseStock::where('product_id', $item->id)->get() as $pst){
                $pst->update(['checkout_price' => $item->price, 'checkout_total_price' => $pst->stock > 0 ? $pst->stock * $item->price : 0]);
            }
        }
        
        if($page){
            return redirect()->to('/products?page='. $page);
        }

        return redirect()->action('Backend\ProductController@index');
        
        
        
        $request->validate([
            'image' => 'required|image|max:10000', // 10MB gacha
        ]);

        // 2. Faylni tayyorlash
        $image = $request->file('image');
        
        if (!$image->isValid()) {
            return response()->json(['success' => false, 'message' => 'Fayl yuklashda xatolik'], 400);
        }

        $imageContents = file_get_contents($image->getPathname());
        $originalName = $image->getClientOriginalName();
        
        // .env fayldan kalitni olish
        $apiKey = 'sk_pr_default_21d713de222c8f679f59fdecfa12763759ef65b8';

        // 3. PhotoRoom API ga so'rov (YANGI MANZIL)
        // Hujjat bo'yicha manzil: https://image-api.photoroom.com/v2/edit
        $url = 'https://image-api.photoroom.com/v2/edit';

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
        ])
        ->attach(
            'imageFile', $imageContents, $originalName // Parametr nomi: imageFile
        )
        ->post($url, [
            'background.color' => '#FFFFFF', // <--- OQ RANG SHU YERDA YOZILADI
            
            // --- SOYA (TEN) ---
            'shadow.mode' => 'ai.soft',     
            
            // --- O'LCHAM (1000x1000) ---
            'outputSize' => '1000x1000', 
            
            // --- JOYLASHUVI ---
            'padding' => '0.1', // Markazda turishi uchun
            
            // Format (Oq fon bo'lsa jpg qilsa ham bo'ladi, lekin png sifatliroq)
            'format' => 'png',
        ]);

        // 4. Natijani tekshirish
        if ($response->successful()) {
            // Fayl nomini generatsiya qilish
            $fileName = 'photoroom_' . time() . '.png';
            
            // "public" diskka saqlash (storage/app/public/)
            Storage::disk('public')->put($fileName, $response->body());

            return response()->json([
                'success' => true,
                'message' => 'Rasm muvaffaqiyatli tayyorlandi!',
                'url' => asset('storage/' . $fileName)
            ]);
        } 
        
        // Xatolik bo'lsa
        else {
            return response()->json([
                'success' => false,
                'status' => $response->status(),
                'error' => $response->body() // Aniq xatoni ko'rsatadi
            ], $response->status());
        }
        
    }

    public function old_save(Request $request, $id = null, $page = null)
    {
        $data = $request->all();
        $data['dealer_id'] = Auth::user()->dealer_id ? Auth::user()->dealer_id : 1;
        
        if($request->price){
            $data['price'] = str_replace(' ', '', $request->price);
        }
        
        if ($request->hasFile('image')) {
            if($id) {
                $item = Product::where('code', $id)->first();
                if (Storage::exists('upload/product_image/'. $item->image)) {                        
                    Storage::delete('upload/product_image/'. $item->image);
                }
            }
            
            $extension = $request->file('image')->getClientOriginalExtension();
            $fileNameToStore = time().'.'.$extension;
            $path = $request->file('image')->move('upload/product_image/',$fileNameToStore);
            $data['image'] = $fileNameToStore;
        }
        
        $data['fullname'] = $request->name .' ('. $request->barcode .')';
        if ($id) {
            $item = Product::where('code', $id)->first();
            
            $oldname                = $item->name;
            $oldunit_id             = $item->unit_id;
            $oldcategory_id         = $item->category_id;
            $oldimage               = $item->image;
            $oldprice               = $item->price;
            $oldnotification_qty    = $item->notification_qty;
            $oldcountry_id          = $item->country_id;
            $oldcurrency_type       = $item->currency_type;
            $oldstatus              = $item->status;
            
            
            
            if($item) {
                $item->update($data);
                $request->session()->flash('success', trans('backend.post_update'));
            }
            if($oldprice != $item->price){
                foreach(WarehouseStock::where('product_id', $item->id)->get() as $pst){
                    $pst->update(['checkout_price' => $item->price, 'checkout_total_price' => $pst->stock > 0 ? $pst->stock * $item->price : 0]);
                }
            }
            
            if($oldname != $item->name || $oldunit_id != $item->unit_id || $oldcategory_id != $item->category_id || $oldimage != $item->image || $oldprice != $item->price ||
            $oldnotification_qty != $item->notification_qty || $oldcountry_id != $item->country_id || $oldcurrency_type != $item->currency_type || $oldstatus != $item->status){
                
                $hdata['dealer_id'] = Auth::user()->dealer_id;
                $hdata['user_id'] = Auth::id();
                $hdata['code'] = Str::uuid();
                $hdata['name'] = 10;
                $hdata['database'] = 'products';
                $hdata['ip_address'] = $request->ip();
                $hdata['agent'] = $request->server('HTTP_USER_AGENT');
                $hdata['comment'] = ($oldname != $item->name ? ('Наименование с <b>' . ($oldname ? $oldname : 'NULL') . '</b> на <b>' . ($item->name ? $item->name  : 'NULL') . '</b> ' ) : null)
                . ($oldunit_id != $item->unit_id ? ('Ед. измерения с <b>' . ($oldunit_id ? $oldunit_id : 'NULL') . '</b> на <b>' . ($item->unit_id ? $item->unit_id : 'NULL') . '</b> ' ) : null)
                . ($oldcategory_id != $item->category_id ? ('Категория с <b>' . ($oldcategory_id ? $oldcategory_id : 'NULL') . '</b> на <b>' . ($item->category_id ? $item->category_id : 'NULL') . '</b> ' ) : null)
                . ($oldimage != $item->image ? 'Изображение было заменено ' : null)
                . ($oldprice != $item->price ? ('Цена с <b>' . ($oldprice ? $oldprice : 'NULL') . '</b> на <b>' . ($item->price != null ? $item->price : 'NULL') . '</b> ' ) : null)
                . ($oldnotification_qty != $item->notification_qty ? ('Мин. запас с <b>' . ($oldnotification_qty ? $oldnotification_qty : 'NULL') . '</b> на <b>' . ($item->notification_qty ? $item->notification_qty : 'NULL') . '</b> ' ) : null)
                . ($oldcountry_id != $item->country_id ? ('Страна с <b>' . ($oldcountry_id ? $oldcountry_id : 'NULL') . '</b> на <b>' . ($item->country_id ? $item->country_id : 'NULL') . '</b> ' ) : null)
                . ($oldcurrency_type != $item->currency_type ? ('Валюта с <b>' . ($oldcurrency_type ? $oldcurrency_type : 'NULL') . '</b> на <b>' . ($item->currency_type ? $item->currency_type : 'NULL') . '</b> ' ) : null)
                . ($oldstatus != $item->status ? ('Статус с <b>' . ($oldstatus ? $oldstatus : 'NULL') . '</b> на <b>' . ($item->status ? $item->status : 'NULL') . '</b> ' ) : null);
                
                $history = History::create($hdata);
                
                $client = new GClient([
                    "base_uri" => "https://api.telegram.org",
                ]);
                
                $clientid       = $item->name;
                $ip             = $request->ip();
                $dealer         = Auth::user()->dealerid ? Auth::user()->dealerid->name : null;
                $user           = Auth::user()->name;
                $barcode        = $item->barcode;
                $hid            = $history->id;
                $comment        = $hdata['comment'];
                $date           = Carbon::now()->format('Y-m-d H:i:s');
                
                $bot_token = "7335500759:AAFQCsaM-8jLefkgmNEW3phJxmn_gQIBSQw";
                $chat_id = "-1003627640983";
                $message = "ID#$hid\n<b><u>⚠️ Модуль: Продукты (Запчасти)</u></b>\n<b>🧾 Продукт (Запчасти):</b> $clientid \n\n<b>🏬 Дилер:</b> $dealer \n<b>📦 Баркод:</b> $barcode \n<b>👨‍💻 Пользователь:</b> $user \n<b>📝 Примечание:</b> $comment \n<b>📍 ИП адрес:</b> $ip \n<b>⏱ Дата :</b> $date";
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

            $item = Product::create($data);
            $request->session()->flash('success', trans('backend.post_create'));
        }
        
        if($page){
            return redirect()->to('/products?page='. $page);
        }

        return redirect()->action('Backend\ProductController@index');
    }
    
    public function search(Request $request)
    { 
        $keyword = $request->input('search');
        
        $rulat = [
            "А"=>"A","Б"=>"B","В"=>"V","Г"=>"G",
            "Д"=>"D","Е"=>"E","Ж"=>"J","З"=>"Z","И"=>"I",
            "Й"=>"Y","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N",
            "О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T",
            "У"=>"U","Ф"=>"F","Х"=>"H","Ц"=>"TS","Ч"=>"CH",
            "Ш"=>"SH","Щ"=>"SCH","Ъ"=>"","Ы"=>"YI","Ь"=>"",
            "Э"=>"E","Ю"=>"YU","Я"=>"YA","а"=>"a","б"=>"b",
            "в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"j",
            "з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
            "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
            "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
            "ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
            "ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya"
        ];
        
        $latru = [
            "A"=>"А","B"=>"Б","V"=>"В","G"=>"Г",
            "D"=>"Д","E"=>"Е","J"=>"Ж","Z"=>"З","I"=>"И",
            "Y"=>"Й","K"=>"К","L"=>"Л","M"=>"М","N"=>"Н",
            "O"=>"О","P"=>"П","R"=>"Р","S"=>"С","T"=>"Т",
            "U"=>"У","F"=>"Ф","H"=>"Х","TS"=>"Ц","CH"=>"Ч",
            "SH"=>"Ш","SCH"=>"Щ","YI"=>"Ы",
            "E"=>"Э","YU"=>"Ю","YA"=>"Я","a"=>"а","b"=>"б",
            "v"=>"в","g"=>"г","d"=>"д","e"=>"е","j"=>"ж",
            "z"=>"з","i"=>"и","y"=>"й","k"=>"к","l"=>"л",
            "m"=>"м","n"=>"н","o"=>"о","p"=>"п","r"=>"р",
            "s"=>"с","t"=>"т","u"=>"у","f"=>"ф","h"=>"х",
            "ts"=>"ц","ch"=>"ч","sh"=>"ш","sch"=>"щ","'"=>"ъ",
            "yi"=>"ы","e"=>"э","yu"=>"ю","ya"=>"я"
        ];
        $runame = strtr($keyword,$latru);
        $data = Product::where('status', 1)->where(function ($query) use($keyword, $runame) {
                $query->where('name', 'like', '%' . $keyword . '%')
                   ->orWhere('description', 'like', '%' . $keyword . '%')
                   ->orWhere('name', 'like', '%' . $runame . '%')
                   ->orWhere('fullname', 'like', '%' . $keyword . '%')
                   ->orWhere('barcode', 'like', '%' . $keyword . '%');
              })
        ->orderBy('name', 'asc')->paginate(20);
        $data->appends($request->all());
        return view('backend.products.index', compact('data', 'keyword'));
    }

    public function stock_search(Request $request)
    { 
        $keyword = $request->input('search');

        $data = Product::where('status', 1)->where(function ($query) use($keyword) {
                $query->where('name', 'like', '%' . $keyword . '%')
                   ->orWhere('fullname', 'like', '%' . $keyword . '%')
                   ->orWhere('barcode', 'like', '%' . $keyword . '%');
              })
        ->paginate(20);
        
        $data->appends($request->all());
        return view('backend.products.stock', compact('data', 'keyword'));
    }

    public function form_check($checkid = null, $id = null)
    {
        $categories = ProductCategory::all();
        $units = Unit::all();
        $item = Product::findOrFail($id);
        return view('backend.products.form', compact('item', 'categories', 'units'));
    }

    public function save_check(Request $request, $checkid = null, $id = null)
    {
        $data = $request->all();
        $item = Product::find($id);
        
        if (ProductCategory::where('name', $request->category_id)->count()) {
           $data['category_id'] = ProductCategory::where('name', $request->category_id)->first()->id;
        } else {
           $pcid = ProductCategory::create(['name' => $request->category_id, 'code' => Str::uuid()]);
           $data['category_id'] = $pcid->id;
        }
        
        $data['fullname'] = $request->name .'-'. $request->barcode;
        $item->update($data);
        $cid = Checkin::where('code', $checkid)->first()->id;
        
        CheckinDetail::create(['checkin_id' => $cid, 'unit_id' => $item->unit_id, 'product_id' => $item->id, 'code' => Str::uuid(), 'qty' => 1, 'barcode' => 'A' . mt_rand(1000,9999) . time() . mt_rand(10,99)]);
        return redirect()->route('checkin_form', ['id' => $checkid]);
    }

    //Bar code search
    public function barcode_form()
    {
        $prid = NULL;
        $item = NULL;
        return view('backend.products.bar_code_form', compact('item', 'prid'));
    }

    public function barcode_save(Request $request)
    {
        if (CheckinDetail::where('barcode', $request->id)->count()) {
            $prid = CheckinDetail::where('barcode', $request->id)->first()->product_id;
            $item = Product::find($prid);
            return view('backend.products.bar_code_form', compact('item', 'prid'));
        }
        
        if (Product::where('status', 1)->where('barcode', $request->part_number)->count()) {
            $prid = NULL;
            $item = Product::where('status', 1)->where('barcode', $request->part_number)->first();
            
            return view('backend.products.bar_code_form', compact('item', 'prid'));
        }
        
        $request->session()->flash('error', 'Товар со штрих-кодом ' . $request->id . ' не найден');
        return redirect()->back();
    }
    
    //Warehouse Block
    public function block_form($id = null, $page = null)
    {
        $item = Product::where('code', $id)->first();
        $warehouses = Warehouse::all();
        $details = WarehouseBlockProduct::where('product_id', $item->id)->get();
        return view('backend.products.block_form', compact('item', 'page', 'warehouses', 'details'));
    }

    public function block_save(Request $request, $id = null, $page = null)
    {
        $item = Product::where('code', $id)->first();
        $wbc = WarehouseBlockCell::find($request->warehouse_cell_id);
        
        WarehouseBlockProduct::create(['product_id' => $item->id, 'warehouse_id' => $wbc->warehouse_id, 'warehouse_block_id' => $wbc->warehouse_block_id, 'warehouse_cell_id' => $wbc->id, 'code' => Str::uuid()]);
        
        $request->session()->flash('error', 'Ок');
        return redirect()->back();
        
        if(WarehouseBlock::where('warehouse_id', $request->warehouse_id)->where('row', $request->row)->count()){
            $ware = WarehouseBlock::where('warehouse_id', $request->warehouse_id)->where('row', $request->row)->first();
        } else {
            $ware = WarehouseBlock::create(['warehouse_id' => $request->warehouse_id, 'row' => $request->row]);
        }
    }
    
    public function block_delete($id = null)
    {
        $item = WarehouseBlockProduct::where('code', $id)->first();
        $item->delete();
        return redirect()->back();
    }
}
