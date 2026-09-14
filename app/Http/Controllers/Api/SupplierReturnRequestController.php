<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupplierReturnRequest;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierReturnRequestController extends Controller
{
    public function show(string $code)
    {
        $item = SupplierReturnRequest::with('details.product')->where('code', $code)->firstOrFail();
        return response()->json([
            'source_code' => $item->code, 'source_id' => $item->id, 'date' => $item->date->format('Y-m-d'),
            'details' => $item->details->map(fn ($detail) => [
                'barcode' => (string)$detail->product->barcode, 'name' => $detail->product->name,
                'qty' => (float)$detail->qty, 'price' => (float)$detail->price,
            ])->values(),
        ]);
    }

    public function accepted(Request $request, string $code)
    {
        $request->validate(['accepted' => ['required', 'boolean']]);
        $status = DB::transaction(function () use ($code, $request) {
            $item = SupplierReturnRequest::with('details.product')->where('code', $code)->lockForUpdate()->firstOrFail();
            if ((int)$item->status !== 0) return (int)$item->status;
            if (!$request->boolean('accepted')) { $item->update(['status' => 2]); return 2; }
            foreach ($item->details as $detail) {
                $stock = WarehouseStock::where('warehouse_id', $item->warehouse_id)
                    ->where('product_id', $detail->product_id)->lockForUpdate()->firstOrFail();
                abort_if((float)$stock->stock < (float)$detail->qty, 422, $detail->product->name . ' qoldig‘i yetarli emas.');
                $newStock = (float)$stock->stock - (float)$detail->qty;
                $stock->update(['stock' => $newStock,
                    'checkin_total_price' => $stock->checkin_price * $newStock,
                    'checkout_total_price' => $stock->checkout_price * $newStock]);
            }
            $item->update(['status' => 1, 'accepted_at' => now()]);
            return 1;
        });
        return response()->json(['success' => true, 'status' => $status]);
    }
}
