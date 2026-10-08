<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WarehouseStockAsOfService
{
    public function get(Warehouse $warehouse, Carbon $date): Collection
    {
        $dateValue = $date->toDateString();
        $endOfDay = $date->copy()->endOfDay();

        $checkins = DB::table('checkin_details as detail')
            ->join('checkins as document', 'document.id', '=', 'detail.checkin_id')
            ->where('detail.warehouse_id', $warehouse->id)
            ->where('detail.status', 1)
            ->where('document.status', 1)
            ->whereDate('document.date', '<=', $dateValue)
            ->selectRaw('detail.product_id, SUM(detail.qty) as quantity')
            ->groupBy('detail.product_id')
            ->pluck('quantity', 'detail.product_id');

        $checkouts = DB::table('checkout_details as detail')
            ->join('checkouts as document', 'document.id', '=', 'detail.checkout_id')
            ->where('detail.warehouse_id', $warehouse->id)
            ->where('detail.status', 1)
            ->where('document.status', 1)
            ->whereDate('document.date', '<=', $dateValue)
            ->selectRaw('detail.product_id, SUM(detail.qty + COALESCE(detail.bonus, 0)) as quantity')
            ->groupBy('detail.product_id')
            ->pluck('quantity', 'detail.product_id');

        // Checkout detail miqdori qaytarish paytida kamaytiriladi. Tanlangan
        // sanadan keyingi qaytarishlarni qayta qo'shib, o'sha kundagi sotuv
        // miqdorini tiklaymiz.
        $futureReturns = DB::table('returns as returned')
            ->join('checkouts as document', 'document.id', '=', 'returned.checkout_id')
            ->where('returned.warehouse_id', $warehouse->id)
            ->where('document.status', 1)
            ->whereDate('document.date', '<=', $dateValue)
            ->where('returned.created_at', '>', $endOfDay)
            ->selectRaw('returned.product_id, SUM(returned.qty) as quantity')
            ->groupBy('returned.product_id')
            ->pluck('quantity', 'returned.product_id');

        $transferIn = DB::table('transfer_details as detail')
            ->join('transfers as document', 'document.id', '=', 'detail.transfer_id')
            ->where('detail.warehouse_in', $warehouse->id)
            ->where(function ($query) {
                $query->whereNull('document.transfer_type')
                    ->orWhere('document.transfer_type', 'warehouse');
            })
            ->whereDate('document.date', '<=', $dateValue)
            ->selectRaw('detail.product_id, SUM(detail.qty) as quantity')
            ->groupBy('detail.product_id')
            ->pluck('quantity', 'detail.product_id');

        $transferOut = DB::table('transfer_details as detail')
            ->join('transfers as document', 'document.id', '=', 'detail.transfer_id')
            ->where('detail.warehouse_out', $warehouse->id)
            ->where(function ($query) {
                $query->whereNull('document.transfer_type')
                    ->orWhere('document.transfer_type', 'warehouse');
            })
            ->whereDate('document.date', '<=', $dateValue)
            ->selectRaw('detail.product_id, SUM(detail.qty) as quantity')
            ->groupBy('detail.product_id')
            ->pluck('quantity', 'detail.product_id');

        $productIds = collect()
            ->merge($checkins->keys())
            ->merge($checkouts->keys())
            ->merge($futureReturns->keys())
            ->merge($transferIn->keys())
            ->merge($transferOut->keys())
            ->unique()
            ->values();

        if ($productIds->isEmpty()) {
            return collect();
        }

        return Product::with('unitid')
            ->where('status', 1)
            ->whereIn('id', $productIds)
            ->orderBy('name')
            ->get()
            ->map(function (Product $product) use ($checkins, $checkouts, $futureReturns, $transferIn, $transferOut) {
                $stock = (float) ($checkins[$product->id] ?? 0)
                    + (float) ($transferIn[$product->id] ?? 0)
                    - (float) ($checkouts[$product->id] ?? 0)
                    - (float) ($futureReturns[$product->id] ?? 0)
                    - (float) ($transferOut[$product->id] ?? 0);

                return (object) [
                    'productid' => $product,
                    'stock' => $stock,
                    'checkin_price' => 0,
                ];
            })
            ->filter(fn ($row) => $row->stock > 0)
            ->values();
    }
}
