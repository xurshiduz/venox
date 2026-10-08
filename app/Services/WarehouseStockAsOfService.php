<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WarehouseStockAsOfService
{
    /**
     * Tanlangan sanadagi ombor qoldig'i.
     *
     * Qoldiq hujjatlardan noldan qayta hisoblanmaydi: dastur qoldiqni
     * qoralama sotuvlarda, korrektirovkada, ko'chirishda va h.k. darhol
     * o'zgartiradi, shuning uchun hujjat yig'indisi haqiqiy qoldiqqa mos
     * kelmaydi. Buning o'rniga joriy warehouse_stocks qoldig'idan tanlangan
     * sanadan keyingi harakatlar orqaga qaytariladi.
     */
    public function get(Warehouse $warehouse, Carbon $date): Collection
    {
        $current = WarehouseStock::where('warehouse_id', $warehouse->id)
            ->get(['product_id', 'stock', 'checkin_price'])
            ->keyBy('product_id');

        $changes = $date->copy()->startOfDay()->lt(now()->startOfDay())
            ? $this->changesAfter($warehouse, $date)
            : collect();

        $productIds = $current->keys()->merge($changes->keys())->unique()->values();

        if ($productIds->isEmpty()) {
            return collect();
        }

        return Product::with('unitid')
            ->where('status', 1)
            ->whereIn('id', $productIds)
            ->orderBy('name')
            ->get()
            ->map(function (Product $product) use ($current, $changes) {
                $row = $current->get($product->id);

                return (object) [
                    'productid' => $product,
                    'stock' => (float) optional($row)->stock - (float) ($changes[$product->id] ?? 0),
                    'checkin_price' => (float) optional($row)->checkin_price,
                ];
            })
            ->filter(fn ($row) => $row->stock > 0)
            ->values();
    }

    /**
     * Sanadan keyin qoldiqqa qo'shilgan sof miqdor (product_id => qty).
     */
    private function changesAfter(Warehouse $warehouse, Carbon $date): Collection
    {
        $dateValue = $date->toDateString();
        $endOfDay = $date->copy()->endOfDay();
        $changes = collect();

        $add = function (Collection $rows, int $sign) use (&$changes) {
            foreach ($rows as $productId => $qty) {
                $changes[$productId] = ($changes[$productId] ?? 0) + $sign * (float) $qty;
            }
        };

        // Kirim: miqdor kiritilganda qoldiq oshadi. LIDAZ kirimlari esa
        // faqat tasdiqlanganda qo'shiladi.
        $add(DB::table('checkin_details as detail')
            ->join('checkins as document', 'document.id', '=', 'detail.checkin_id')
            ->where('detail.warehouse_id', $warehouse->id)
            ->whereDate('document.date', '>', $dateValue)
            ->where(function ($query) {
                $query->whereNull('document.source_system')
                    ->orWhere('document.source_system', '!=', 'lidaz')
                    ->orWhere('detail.status', 1);
            })
            ->selectRaw('detail.product_id, SUM(detail.qty) as quantity')
            ->groupBy('detail.product_id')
            ->pluck('quantity', 'detail.product_id'), 1);

        // Sotuv: qoralama (tasdiqlanmagan) hujjatda ham qoldiq kamayadi.
        $add(DB::table('checkout_details as detail')
            ->join('checkouts as document', 'document.id', '=', 'detail.checkout_id')
            ->where('detail.warehouse_id', $warehouse->id)
            ->whereDate('document.date', '>', $dateValue)
            ->selectRaw('detail.product_id, SUM(detail.qty + COALESCE(detail.bonus, 0)) as quantity')
            ->groupBy('detail.product_id')
            ->pluck('quantity', 'detail.product_id'), -1);

        // Qaytarish qoldiqni oshiradi va sotuv qatoridagi miqdorni kamaytiradi.
        // Sanadan keyingi sotuvlar uchun bu sof miqdorda allaqachon hisobda.
        $add(DB::table('returns as returned')
            ->join('checkouts as document', 'document.id', '=', 'returned.checkout_id')
            ->where('returned.warehouse_id', $warehouse->id)
            ->whereDate('document.date', '<=', $dateValue)
            ->where('returned.created_at', '>', $endOfDay)
            ->selectRaw('returned.product_id, SUM(returned.qty) as quantity')
            ->groupBy('returned.product_id')
            ->pluck('quantity', 'returned.product_id'), 1);

        foreach (['warehouse_in' => 1, 'warehouse_out' => -1] as $column => $sign) {
            $add(DB::table('transfer_details as detail')
                ->join('transfers as document', 'document.id', '=', 'detail.transfer_id')
                ->where("detail.$column", $warehouse->id)
                ->where(function ($query) {
                    $query->whereNull('document.transfer_type')
                        ->orWhere('document.transfer_type', 'warehouse');
                })
                ->whereDate('document.date', '>', $dateValue)
                ->selectRaw('detail.product_id, SUM(detail.qty) as quantity')
                ->groupBy('detail.product_id')
                ->pluck('quantity', 'detail.product_id'), $sign);
        }

        if (Schema::hasTable('adjustments')) {
            $add(DB::table('adjustments')
                ->where('warehouse_id', $warehouse->id)
                ->where('created_at', '>', $endOfDay)
                ->selectRaw('product_id, SUM(COALESCE(qty_new, 0) - COALESCE(qty_old, 0)) as quantity')
                ->groupBy('product_id')
                ->pluck('quantity', 'product_id'), 1);
        }

        if (Schema::hasTable('supplier_return_requests') && Schema::hasColumn('supplier_return_requests', 'accepted_at')) {
            $add(DB::table('supplier_return_request_details as detail')
                ->join('supplier_return_requests as document', 'document.id', '=', 'detail.supplier_return_request_id')
                ->where('document.warehouse_id', $warehouse->id)
                ->where('document.status', 1)
                ->where('document.accepted_at', '>', $endOfDay)
                ->selectRaw('detail.product_id, SUM(detail.qty) as quantity')
                ->groupBy('detail.product_id')
                ->pluck('quantity', 'detail.product_id'), -1);
        }

        return $changes;
    }
}
