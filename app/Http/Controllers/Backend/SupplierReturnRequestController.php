<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Checkin;
use App\Models\SupplierReturnRequest;
use App\Models\SupplierReturnRequestDetail;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SupplierReturnRequestController extends Controller
{
    public function form(string $code)
    {
        $checkin = Checkin::with('details.prodid')->where('code', $code)
            ->where('source_system', 'lidaz')->where('status', 1)->firstOrFail();
        return view('backend.checkins.supplier_return', compact('checkin'));
    }

    public function store(Request $request, string $code)
    {
        $checkin = Checkin::with('details.prodid')->where('code', $code)
            ->where('source_system', 'lidaz')->where('status', 1)->firstOrFail();
        $request->validate(['qty' => ['required', 'array']]);

        $selected = collect($request->input('qty'))->map(fn ($qty) => (float) $qty)->filter(fn ($qty) => $qty > 0);
        abort_if($selected->isEmpty(), 422, 'Kamida bitta mahsulot miqdorini kiriting.');

        $return = DB::transaction(function () use ($checkin, $selected) {
            $item = SupplierReturnRequest::create([
                'code' => (string) Str::uuid(), 'date' => now()->toDateString(),
                'checkin_id' => $checkin->id, 'warehouse_id' => $checkin->warehouse_id,
                'user_id' => auth()->id(), 'status' => 0,
            ]);
            foreach ($selected as $detailId => $qty) {
                $detail = $checkin->details->firstWhere('id', (int) $detailId);
                abort_unless($detail, 422, 'Mahsulot topilmadi.');
                $stock = WarehouseStock::where('warehouse_id', $checkin->warehouse_id)
                    ->where('product_id', $detail->product_id)->lockForUpdate()->first();
                abort_if(!$stock || (float)$stock->stock < $qty, 422, $detail->prodid->name . ' qoldig‘i yetarli emas.');
                SupplierReturnRequestDetail::create([
                    'supplier_return_request_id' => $item->id, 'product_id' => $detail->product_id,
                    'qty' => $qty, 'price' => $detail->price, 'total_price' => $qty * $detail->price,
                ]);
            }
            return $item;
        });

        $sent = Http::timeout(15)->retry(2, 500)->post(
            rtrim(config('services.lidaz_factory.url'), '/') . '/api/venox-return/request',
            ['source_code' => $return->code]
        )->successful();

        return redirect()->route('checkins_index')->with($sent ? 'success' : 'error',
            $sent ? 'Qaytarish so‘rovi LIDAZga yuborildi.' : 'So‘rov saqlandi, ammo LIDAZga yuborilmadi.');
    }
}
