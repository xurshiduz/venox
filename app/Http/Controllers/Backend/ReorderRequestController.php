<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReorderRequestController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'warehouse_id' => ['nullable', 'integer', 'exists:warehouses,id'],
            'remaining_percent' => ['nullable', 'numeric', 'min:1', 'max:50'],
            'max_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'min_received_qty' => ['nullable', 'numeric', 'min:1', 'max:1000000000'],
        ]);

        $remainingPercent = (float) ($filters['remaining_percent'] ?? 10);
        $remainingRatio = $remainingPercent / 100;
        $maxDays = (int) ($filters['max_days'] ?? 30);
        $minReceivedQty = (float) ($filters['min_received_qty'] ?? 10);
        $keyword = trim((string) ($filters['search'] ?? ''));
        $warehouseId = isset($filters['warehouse_id']) ? (int) $filters['warehouse_id'] : null;

        // Har bir mahsulot va ombor uchun tasdiqlangan eng so'nggi kirim sanasi.
        // Faqat ID bo'yicha olish mumkin emas: eski sana bilan keyinroq kiritilgan
        // hujjat oxirgi real kirim sifatida noto'g'ri tanlanib qolishi mumkin.
        $latestIncomingDates = DB::table('checkin_details as dated_incoming')
            ->join('checkins as dated_checkin', 'dated_checkin.id', '=', 'dated_incoming.checkin_id')
            ->where('dated_incoming.status', 1)
            ->where('dated_checkin.status', 1)
            ->where('dated_checkin.type_id', 1)
            ->groupBy('dated_incoming.product_id', 'dated_incoming.warehouse_id')
            ->select([
                'dated_incoming.product_id',
                'dated_incoming.warehouse_id',
            ])
            ->selectRaw('MAX(dated_checkin.date) as latest_date');

        // Bir sanada bir nechta kirim bo'lsa, shu sanadagi eng so'nggi pozitsiya olinadi.
        $latestIncomingIds = DB::table('checkin_details as latest_incoming')
            ->join('checkins as latest_checkin', 'latest_checkin.id', '=', 'latest_incoming.checkin_id')
            ->joinSub($latestIncomingDates, 'latest_dates', function ($join) {
                $join->on('latest_dates.product_id', '=', 'latest_incoming.product_id')
                    ->on('latest_dates.warehouse_id', '=', 'latest_incoming.warehouse_id')
                    ->on('latest_dates.latest_date', '=', 'latest_checkin.date');
            })
            ->where('latest_incoming.status', 1)
            ->where('latest_checkin.status', 1)
            ->where('latest_checkin.type_id', 1)
            ->groupBy('latest_incoming.product_id', 'latest_incoming.warehouse_id')
            ->selectRaw('MAX(latest_incoming.id) as detail_id');

        $candidates = DB::table('checkin_details as incoming')
            ->joinSub($latestIncomingIds, 'latest', function ($join) {
                $join->on('latest.detail_id', '=', 'incoming.id');
            })
            ->join('checkins as checkin', 'checkin.id', '=', 'incoming.checkin_id')
            ->join('products as product', 'product.id', '=', 'incoming.product_id')
            ->join('warehouses as warehouse', 'warehouse.id', '=', 'incoming.warehouse_id')
            ->join('warehouse_stocks as stock', function ($join) {
                $join->on('stock.product_id', '=', 'incoming.product_id')
                    ->on('stock.warehouse_id', '=', 'incoming.warehouse_id');
            })
            ->leftJoin('units as unit', 'unit.id', '=', 'product.unit_id')
            ->select([
                'incoming.id as incoming_detail_id',
                'incoming.product_id',
                'incoming.warehouse_id',
                'incoming.qty as received_qty',
                'checkin.date as received_at',
                'product.name as product_name',
                'product.barcode',
                'warehouse.name as warehouse_name',
                'unit.name as unit_name',
                'stock.stock as remaining_qty',
            ])
            ->selectSub(function ($sold) {
                $sold->from('checkout_details as sold_detail')
                    ->join('checkouts as sale', 'sale.id', '=', 'sold_detail.checkout_id')
                    ->whereColumn('sold_detail.product_id', 'incoming.product_id')
                    ->whereColumn('sold_detail.warehouse_id', 'incoming.warehouse_id')
                    ->whereColumn('sale.date', '>=', 'checkin.date')
                    ->where('sold_detail.status', 1)
                    ->where('sale.status', 1)
                    ->selectRaw('COALESCE(SUM(sold_detail.qty), 0)');
            }, 'sold_qty')
            ->where('incoming.status', 1)
            ->where('checkin.status', 1)
            ->where('checkin.type_id', 1)
            ->where('incoming.qty', '>=', $minReceivedQty)
            ->where('checkin.date', '>=', Carbon::today()->subDays($maxDays)->toDateString())
            ->where('stock.stock', '>=', 0)
            ->whereRaw('stock.stock <= incoming.qty * ?', [$remainingRatio]);

        if ($keyword !== '') {
            $candidates->where(function ($query) use ($keyword) {
                $query->where('product.name', 'like', '%' . $keyword . '%')
                    ->orWhere('product.barcode', 'like', '%' . $keyword . '%')
                    ->orWhere('warehouse.name', 'like', '%' . $keyword . '%');
            });
        }

        if ($warehouseId) {
            $candidates->where('incoming.warehouse_id', $warehouseId);
        }

        if (Auth::user()->hasAnyRole('dealer_admin|diler_admin')) {
            $candidates->where('warehouse.dealer_id', Auth::user()->dealer_id);
        }

        // Ombordagi real qoldiq asosiy mezon hisoblanadi. Sotuvlar izoh va
        // tezlikni ko'rsatish uchun olinadi, lekin noto'liq eski sotuv yozuvi
        // zayavkani yashirib yubormasligi kerak.
        $query = DB::query()
            ->fromSub($candidates, 'reorder_candidates');

        $requests = $query
            ->orderByRaw('(remaining_qty / NULLIF(received_qty, 0)) ASC')
            ->orderBy('received_at')
            ->paginate(50)
            ->withQueryString();

        $requests->getCollection()->transform(function ($item) {
            $receivedAt = Carbon::parse($item->received_at)->startOfDay();
            $item->days_elapsed = max(0, $receivedAt->diffInDays(Carbon::today(), false));
            $item->remaining_percent = (float) $item->received_qty > 0
                ? ((float) $item->remaining_qty / (float) $item->received_qty) * 100
                : 0;
            $item->recommended_qty = (float) $item->received_qty;
            $item->comment = trans('backend.ui.reorder_comment', [
                'received' => $this->formatQty($item->received_qty),
                'days' => $item->days_elapsed,
                'remaining' => $this->formatQty($item->remaining_qty),
                'sold' => $this->formatQty($item->sold_qty),
                'recommended' => $this->formatQty($item->recommended_qty),
                'unit' => $item->unit_name ?: trans('backend.ui.piece'),
            ]);

            return $item;
        });

        $warehouses = Warehouse::query()
            ->where('status', 1)
            ->when(Auth::user()->hasAnyRole('dealer_admin|diler_admin'), function ($query) {
                $query->where('dealer_id', Auth::user()->dealer_id);
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('backend.reorder_requests.index', compact(
            'requests',
            'warehouses',
            'keyword',
            'warehouseId',
            'remainingPercent',
            'maxDays',
            'minReceivedQty'
        ));
    }

    private function formatQty($value): string
    {
        return number_format((float) $value, 2, '.', ' ');
    }
}
