<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client as GClient;
use Illuminate\Http\Request;

use App\Models\WarehouseStock;
use App\Models\CheckoutDetail;
use App\Models\TransferDetail;
use App\Models\Warehouse;
use App\Models\Transfer;
use App\Models\Checkout;
use App\Models\Product;
use App\Models\History;
use App\Models\Client;

use Carbon\Carbon;
use Excel;
use Auth;
use Str;
use DB;

class TransferController extends Controller
{
    public function index()
    { 
        $data = Transfer::orderBy('id', 'desc')->paginate(20); 
        $keyword = NULL; 
        return view('backend.transfers.index', compact('data', 'keyword'));
    }

    public function form($id = null)
    {
        $item = null;
        $warehouses = Warehouse::whereNull('factory_id')->where('status', 1)->get();
        $clients = Client::where('status', 1)->orderBy('name')->get(['id', 'name', 'phone']);

        if($id) {
            $item = Transfer::where('code', $id)->first();
        }
        
        return view('backend.transfers.form', compact('item', 'warehouses', 'clients'));
    }

    public function save(Request $request, $id = null)
    {
        $transferType = $request->input('transfer_type', 'warehouse');
        if (!in_array($transferType, ['warehouse', 'client'], true)) {
            abort(422, 'O‘tkazma turi noto‘g‘ri.');
        }

        if ($transferType === 'client') {
            return $this->saveClientTransfer($request, $id);
        }

        if($request->product_id){
            $chprid = $request->product_id;
        } else {
            $chprid = $request->modal_product;
        }
        
        if(Product::where('barcode', $chprid)->orWhere('barcode', '0'. $chprid)->orWhere('name', $request->product_id)->orWhere('fullname', $request->product_id)->count()) {
            
            $year = Carbon::now()->format('Y');
            $data['date'] = Carbon::parse($request->date)->format('Y-m-d');
            $data['reference'] = $request->reference;
            $data['warehouse_out'] = $request->warehouse_out;
            $data['warehouse_in'] = $request->warehouse_in;
            $data['transfer_type'] = 'warehouse';
            $data['client_out_id'] = null;
            $data['client_in_id'] = null;
            $pid = Product::where('barcode', $chprid)->orWhere('barcode', '0'. $chprid)->orWhere('name', $request->product_id)->orWhere('fullname', $request->product_id)->first();
            if ($id) {
                $item = Transfer::where('code', $id)->first();
                if($item) {
                    $item->update($data);
                    $request->session()->flash('success', trans('backend.post_update'));
                }
            } else {
                $data['user_id'] = Auth::id();
                $data['code'] = Str::uuid();
                $item = Transfer::create($data);
                $request->session()->flash('success', trans('backend.post_create'));
            }
            //Productni qushish
            if(TransferDetail::where('warehouse_out', $item->warehouse_out)->where('warehouse_in', $item->warehouse_in)->where('transfer_id', $item->id)->where('product_id', $pid->id)->count()){
                $ditem = TransferDetail::where('warehouse_out', $item->warehouse_out)->where('warehouse_in', $item->warehouse_in)->where('product_id', $pid->id)->first();
                $udata['qty'] = $ditem->qty + 1;
                $ditem->update($udata);
                $request->session()->flash('error', trans('backend.old_added_product'));
            } else {
                $pr = $pid->currency_type != 1 ? ($pid->currencyid->currencyid->first()->price * $pid->price) : $pid->price;
                $cdata['transfer_id'] = $item->id;
                $cdata['warehouse_out'] = $request->warehouse_out;
                $cdata['warehouse_in'] = $request->warehouse_in;
                $cdata['product_id'] = $pid->id;
                $cdata['qty'] = 1;
                $cdata['unit_id'] = $pid->unit_id;
                $cdata['code'] = Str::uuid();
                
                TransferDetail::create($cdata);
                
                if(WarehouseStock::where('warehouse_id', $item->warehouse_out)->where('product_id', $pid->id)->count()){
                    $wsid = WarehouseStock::where('warehouse_id', $item->warehouse_out)->where('product_id', $pid->id)->first();
                    $stc = ($wsid->stock - 1);
                    $wsid->update([
                        'stock' => $stc,
                        'checkin_price' => $pid->checkindetails()->max('price'),
                        'checkout_price' => $pid->price ? $pid->price : $pid->checkoutdetails()->max('price'),
                        'checkin_total_price' => $stc > 0 ? ($pid->checkindetails()->max('price') * $stc) : 0,
                        'checkout_total_price' => $stc > 0 ? (($pid->price ? $pid->price : $pid->checkoutdetails()->max('price')) * $stc) : 0
                    ]);
                } 
                
                if(WarehouseStock::where('warehouse_id', $item->warehouse_in)->where('product_id', $pid->id)->count()){
                    $wsid = WarehouseStock::where('warehouse_id', $item->warehouse_in)->where('product_id', $pid->id)->first();
                    $stc = ($wsid->stock + 1);
                    $wsid->update([
                        'stock' => $stc,
                        'checkin_price' => $pid->checkindetails()->max('price'),
                        'checkout_price' => $pid->price ? $pid->price : $pid->checkoutdetails()->max('price'),
                        'checkin_total_price' => $stc > 0 ? ($pid->checkindetails()->max('price') * $stc) : 0,
                        'checkout_total_price' => $stc > 0 ? (($pid->price ? $pid->price : $pid->checkoutdetails()->max('price')) * $stc) : 0
                    ]);
                    
                } else {
                    WarehouseStock::create([
                        'warehouse_id' => $item->warehouse_in, 
                        'product_id' => $pid->id, 
                        'stock' => 1,
                        'checkin_price' => $pid->checkindetails()->max('price'),
                        'checkout_price' => $pid->price ? $pid->price : $pid->checkoutdetails()->max('price'),
                        'checkin_total_price' => $pid->checkindetails()->max('price') * 1,
                        'checkout_total_price' => ($pid->price ? $pid->price : $pid->checkoutdetails()->max('price')) * 1
                    ]);
                }
            }
            return redirect()->route('transfer_form', ['id' => $item->code]);
        } else {
            //Agar product topilmasa javob qaytarish
            $request->session()->flash('error', trans('backend.no_product'));
            return back();
        }

        return redirect()->action('Backend\TransferController@index');
    }
    
    public function qty()
    {
        $brid = request()->brid; //qty Keladi
        $cid = request()->cid; //ID keladi
        $item = TransferDetail::findOrFail($cid);
        $transfer = $item->transfid;
        $newQty = (float) $brid;

        if ($newQty <= 0) {
            return response()->json(['message' => 'Miqdor 0 dan katta bo‘lishi kerak.'], 422);
        }

        if ($transfer && $transfer->isClientTransfer()) {
            $available = $this->clientProductAvailableQty(
                $transfer->client_out_id,
                $item->product_id,
                $item->id
            );

            if ($newQty > $available) {
                return response()->json([
                    'message' => 'Manba mijozda yetarli mahsulot yo‘q. Mavjud: ' . $available,
                ], 422);
            }

            $item->update([
                'qty' => $newQty,
                'total_price' => $newQty * (float) $item->unit_price,
            ]);

            return response()->json([
                'qty' => $item->qty,
                'total_price' => $item->total_price,
            ]);
        }

        $oldqty = $item->qty;
        $item->update(['qty' => $brid]);
        $pid = Product::find($item->product_id);
        if($oldqty != $item->qty){
            
            if(WarehouseStock::where('warehouse_id', $item->warehouse_out)->where('product_id', $item->product_id)->count()){
                $wsid = WarehouseStock::where('warehouse_id', $item->warehouse_out)->where('product_id', $item->product_id)->first();
                if($oldqty < $item->qty){
                    $stcout = ($wsid->stock - ($item->qty - $oldqty));
                } else {
                    $stcout = ($wsid->stock + ($oldqty - $item->qty));
                }
                
                $wsid->update([
                    'stock' => $stcout,
                    'checkin_price' => $pid->checkindetails()->max('price'),
                    'checkout_price' => $pid->price ? $pid->price : $pid->checkoutdetails()->max('price'),
                    'checkin_total_price' => $stcout > 0 ? ($pid->checkindetails()->max('price') * $stcout) : 0,
                    'checkout_total_price' => $stcout > 0 ? (($pid->price ? $pid->price : $pid->checkoutdetails()->max('price')) * $stcout) : 0
                ]);
                    
            } 
            
            if(WarehouseStock::where('warehouse_id', $item->warehouse_in)->where('product_id', $item->product_id)->count()){
                $inwsid = WarehouseStock::where('warehouse_id', $item->warehouse_in)->where('product_id', $item->product_id)->first();
                if($oldqty < $item->qty){
                    $stcin = ($inwsid->stock + ($item->qty - $oldqty));
                } else {
                    $stcin = ($inwsid->stock - ($oldqty - $item->qty));
                }
                $inwsid->update([
                    'stock' => $stcin,
                    'checkin_price' => $pid->checkindetails()->max('price'),
                    'checkout_price' => $pid->price ? $pid->price : $pid->checkoutdetails()->max('price'),
                    'checkin_total_price' => $stcin > 0 ? ($pid->checkindetails()->max('price') * $stcin) : 0,
                    'checkout_total_price' => $stcin > 0 ? (($pid->price ? $pid->price : $pid->checkoutdetails()->max('price')) * $stcin) : 0
                ]);
            } 
        }
        
        
        $citem = Transfer::findOrFail($item->transfer_id);
        
        if($citem->status == 1){
            $hdata['dealer_id'] = $citem->dealer_id;
            $hdata['user_id'] = Auth::id();
            $hdata['code'] = Str::uuid();
            $hdata['name'] = 8;
            $hdata['database'] = 'transfer_details';
            $hdata['ip_address'] = request()->ip();
            $hdata['agent'] = request()->server('HTTP_USER_AGENT');
            $hdata['comment'] = 'В перемещение между складами "' . ($citem->number_work ? '№ ' . $citem->number_work : 'Черновик #' . $citem->id) . '" от ' . $citem->date . ' изменилось количество запчастей «<u>' . $item->prodid->name . '</u>» с ' . $oldqty . ' на ' . $brid;
            
            $history = History::create($hdata);
            
            $client = new GClient([
                "base_uri" => "https://api.telegram.org",
            ]);
                    
            $clientid       = $citem->number_work ? $citem->number_work : 'Черновик ID#' . $citem->id;
            $ip             = request()->ip();
            $dealer         = $item->warehouseinid->dealerid->name;
            $warehousein    = $item->warehouseinid->name;
            $warehouseout   = $item->warehouseoutid->name;
            $barcode        = $item->prodid->barcode;
            $hid            = $history->id;
            $user           = Auth::user()->name . ' Тел: ' . Auth::user()->phone;
            $comment        = $hdata['comment'];
            $date           = Carbon::now()->format('Y-m-d H:i:s');
            
            $bot_token = "7335500759:AAFQCsaM-8jLefkgmNEW3phJxmn_gQIBSQw";
            $chat_id = "-1003627640983";
            $message = "ID#$hid\n<b><u>⚠️ Модуль: Перемещения (Позиция-Количество)</u></b>\n<b>🧾 Номер жокумента:</b> $clientid \n\n<b>🏬 Дилер:</b> $dealer \n<b>🏭 С склада:</b> $warehouseout\n<b>🏭 на склад:</b> $warehousein \n<b>📦 Баркод:</b> $barcode \n<b>👨‍💻 Пользователь:</b> $user \n<b>📝 Примечание:</b> $comment \n<b>📍 ИП адрес:</b> $ip \n<b>⏱ Дата :</b> $date";
            $response = $client->request("GET", "/bot$bot_token/sendMessage", [
                "query" => [
                    "chat_id" => $chat_id,
                    "text" => $message,
                    "parse_mode" => "html"
                ]
            ]);
        }
        
        
        return response()->json(['qty' => $item->qty]);
    }
    
    public function delete(Request $request, $id)
    {
        $item = TransferDetail::where('code',$id)->first();
        if (!$item) {
            return back()->with('error', 'Mahsulot topilmadi.');
        }

        if ($item->transfid && $item->transfid->isClientTransfer()) {
            $item->delete();
            return back()->with('success', 'Mahsulot o‘tkazmadan olib tashlandi.');
        }

        $pid = Product::find($item->product_id);
        if(WarehouseStock::where('warehouse_id', $item->warehouse_out)->where('product_id', $item->product_id)->count()){
            $wsid = WarehouseStock::where('warehouse_id', $item->warehouse_out)->where('product_id', $item->product_id)->first();
            $stcout = $wsid->stock + $item->qty;
            $wsid->update([
                'stock' => $stcout,
                'checkin_price' => $pid->checkindetails()->max('price'),
                'checkout_price' => $pid->price ? $pid->price : $pid->checkoutdetails()->max('price'),
                'checkin_total_price' => $stcout > 0 ? ($pid->checkindetails()->max('price') * $stcout) : 0,
                'checkout_total_price' => $stcout > 0 ? (($pid->price ? $pid->price : $pid->checkoutdetails()->max('price')) * $stcout) : 0
            ]);
        } 
        
        if(WarehouseStock::where('warehouse_id', $item->warehouse_in)->where('product_id', $item->product_id)->count()){
            $wsid = WarehouseStock::where('warehouse_id', $item->warehouse_in)->where('product_id', $item->product_id)->first();
            $stcin = $wsid->stock - $item->qty;
            $wsid->update([
                'stock' => $stcin,
                'checkin_price' => $pid->checkindetails()->max('price'),
                'checkout_price' => $pid->price ? $pid->price : $pid->checkoutdetails()->max('price'),
                'checkin_total_price' => $stcin > 0 ? ($pid->checkindetails()->max('price') * $stcin) : 0,
                'checkout_total_price' => $stcin > 0 ? (($pid->price ? $pid->price : $pid->checkoutdetails()->max('price')) * $stcin) : 0
            ]);
        } 
        
        $item->delete();
        return back();
    }
    
    public function delete_transfer(Request $request, $id)
    {
        $item = Transfer::where('code',$id)->first();
        if($item->details()->count() == 0){
            $item->delete();
        } else {
            $request->session()->flash('error', trans('backend.checkout_count'));
        }
        return back();
    }
    
    public function done_status($id = null, $page = null, $fromdate = null, $todate = null, $manager = null)
    {
        $item = Transfer::where('code',$id)->first();
        if($item->number_order == NULL){
            $year = Carbon::now()->format('Y');
            $data['transaction'] = $item->transaction ? $item->transaction : $year . time();
            if(Transfer::whereYear('date', '=', $year)->count()){
                $slice = Transfer::whereYear('date', '=', $year)->max('number_order');
                $data['number_order'] = Str::padLeft(($slice + 1), 6, '0');
                $data['number_work'] = Carbon::now()->format('y') . $data['number_order'];
            } else {
                $data['number_order'] = '000001';
                $data['number_work'] = Carbon::now()->format('y') . $data['number_order'];
            }
            $item->update($data);
        }
        
        if($fromdate){
            if($page){
                return redirect()->to('/checkout_filter?fromdate=' . $fromdate . '&todate=' . $todate . '&manager='. $manager . '&page=' . $page);
            }
            return redirect()->to('/checkout_filter?fromdate=' . $fromdate . '&todate=' . $todate . '&manager='. $manager);
        }
        if($page){
            return redirect()->to('/transfers?page='. $page);
        }
        return redirect()->route('transfers_index');
    }

    public function clientProducts(Request $request)
    {
        $request->validate([
            'client_id' => 'required|integer|exists:clients,id',
            'model' => 'nullable|string|max:255',
        ]);

        $search = trim((string) $request->input('model'));
        $productIds = CheckoutDetail::query()
            ->whereHas('checkid', function ($query) use ($request) {
                $query->where('client_id', $request->client_id)->where('status', 1);
            })
            ->pluck('product_id')
            ->merge(
                TransferDetail::query()
                    ->whereHas('transfid', function ($query) use ($request) {
                        $query->where('transfer_type', 'client')
                            ->where('client_in_id', $request->client_id);
                    })
                    ->pluck('product_id')
            )
            ->unique()
            ->values();

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%' . $search . '%')
                        ->orWhere('fullname', 'like', '%' . $search . '%')
                        ->orWhere('barcode', 'like', '%' . $search . '%');
                });
            })
            ->limit(30)
            ->get(['id', 'name', 'fullname', 'barcode']);

        return response()->json($products->filter(function ($product) use ($request) {
            return $this->clientProductAvailableQty($request->client_id, $product->id) > 0;
        })->values());
    }

    private function saveClientTransfer(Request $request, ?string $id)
    {
        $validated = $request->validate([
            'date' => 'required',
            'client_out_id' => 'required|integer|exists:clients,id|different:client_in_id',
            'client_in_id' => 'required|integer|exists:clients,id',
            'reference' => 'nullable|string|max:1000',
        ], [
            'client_out_id.different' => 'Manba va qabul qiluvchi mijoz bir xil bo‘lishi mumkin emas.',
        ]);

        $productInput = $request->product_id ?: $request->modal_product;
        $product = Product::query()
            ->where(function ($query) use ($productInput) {
                $query->where('barcode', $productInput)
                    ->orWhere('barcode', '0' . $productInput)
                    ->orWhere('name', $productInput)
                    ->orWhere('fullname', $productInput);
            })
            ->first();

        if (!$product) {
            return back()->withInput()->with('error', trans('backend.no_product'));
        }

        return DB::transaction(function () use ($request, $validated, $product, $id) {
            $fallbackWarehouse = Warehouse::whereNull('factory_id')->where('status', 1)->value('id');
            if (!$fallbackWarehouse) {
                return back()->withInput()->with('error', 'Faol ombor topilmadi.');
            }

            $header = [
                'date' => Carbon::parse($validated['date'])->format('Y-m-d'),
                'reference' => $validated['reference'] ?? null,
                'transfer_type' => 'client',
                'client_out_id' => $validated['client_out_id'],
                'client_in_id' => $validated['client_in_id'],
                // Eski majburiy ustunlar bilan moslik uchun. Mijoz o‘tkazmasida sklad qoldig‘iga tegilmaydi.
                'warehouse_out' => $fallbackWarehouse,
                'warehouse_in' => $fallbackWarehouse,
            ];

            if ($id) {
                $item = Transfer::where('code', $id)->lockForUpdate()->firstOrFail();
                if (!$item->isClientTransfer()) {
                    return back()->with('error', 'Boshlangan sklad o‘tkazmasi turini o‘zgartirib bo‘lmaydi.');
                }
                $item->update($header);
            } else {
                $item = Transfer::create($header + [
                    'user_id' => Auth::id(),
                    'code' => (string) Str::uuid(),
                ]);
            }

            $available = $this->clientProductAvailableQty($item->client_out_id, $product->id);
            if ($available < 1) {
                return back()->withInput()->with('error', 'Tanlangan mahsulot manba mijozda mavjud emas.');
            }

            $price = $this->clientProductPrice($item->client_out_id, $product->id);
            if (!$price) {
                return back()->withInput()->with('error', 'Mahsulotning mijozga sotilgan narxi topilmadi.');
            }

            $detail = TransferDetail::where('transfer_id', $item->id)
                ->where('product_id', $product->id)
                ->where('unit_price', $price['price'])
                ->where('currency_type', $price['currency_type'])
                ->first();

            if ($detail) {
                $detail->increment('qty');
                $detail->update(['total_price' => $detail->qty * (float) $detail->unit_price]);
            } else {
                TransferDetail::create([
                    'transfer_id' => $item->id,
                    'warehouse_out' => $fallbackWarehouse,
                    'warehouse_in' => $fallbackWarehouse,
                    'product_id' => $product->id,
                    'qty' => 1,
                    'unit_price' => $price['price'],
                    'total_price' => $price['price'],
                    'currency_type' => $price['currency_type'],
                    'currency_type_price' => $price['currency_type_price'],
                    'unit_id' => $product->unit_id,
                    'code' => (string) Str::uuid(),
                ]);
            }

            return redirect()->route('transfer_form', ['id' => $item->code])
                ->with('success', 'Mahsulot miqdori va sotilgan narxi bilan mijoz o‘tkazmasiga qo‘shildi.');
        });
    }

    private function clientProductAvailableQty(int $clientId, int $productId, ?int $excludingDetailId = null): float
    {
        $purchased = (float) CheckoutDetail::query()
            ->where('product_id', $productId)
            ->whereHas('checkid', function ($query) use ($clientId) {
                $query->where('client_id', $clientId)->where('status', 1);
            })
            ->sum('qty');

        $incoming = (float) TransferDetail::query()
            ->where('product_id', $productId)
            ->whereHas('transfid', function ($query) use ($clientId) {
                $query->where('transfer_type', 'client')->where('client_in_id', $clientId);
            })
            ->sum('qty');

        $outgoingQuery = TransferDetail::query()
            ->where('product_id', $productId)
            ->whereHas('transfid', function ($query) use ($clientId) {
                $query->where('transfer_type', 'client')->where('client_out_id', $clientId);
            });

        if ($excludingDetailId) {
            $outgoingQuery->where('id', '!=', $excludingDetailId);
        }

        return max(0, $purchased + $incoming - (float) $outgoingQuery->sum('qty'));
    }

    private function clientProductPrice(int $clientId, int $productId): ?array
    {
        $checkoutDetail = CheckoutDetail::query()
            ->where('product_id', $productId)
            ->where('qty', '>', 0)
            ->whereHas('checkid', function ($query) use ($clientId) {
                $query->where('client_id', $clientId)->where('status', 1);
            })
            ->with('checkid:id,date')
            ->latest('id')
            ->first();

        $incomingDetail = TransferDetail::query()
            ->where('product_id', $productId)
            ->whereHas('transfid', function ($query) use ($clientId) {
                $query->where('transfer_type', 'client')->where('client_in_id', $clientId);
            })
            ->latest('id')
            ->first();

        if (!$checkoutDetail && !$incomingDetail) {
            return null;
        }

        if ($incomingDetail && (!$checkoutDetail || $incomingDetail->created_at->gte($checkoutDetail->created_at))) {
            return [
                'price' => (float) $incomingDetail->unit_price,
                'currency_type' => $incomingDetail->currency_type ?: 1,
                'currency_type_price' => $incomingDetail->currency_type_price ?: 1,
            ];
        }

        return [
            'price' => (float) $checkoutDetail->price,
            'currency_type' => $checkoutDetail->currency_type ?: 1,
            'currency_type_price' => $checkoutDetail->currency_type_price ?: 1,
        ];
    }
}
