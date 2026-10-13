<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ProductCategory;
use App\Models\CashReceiptType;
use App\Models\CheckoutDetail;
use App\Models\CheckinDetail;
use App\Models\CurrencyType;
use App\Models\CashReceipt;
use App\Models\Warehouse;
use App\Models\Checkout;
use App\Models\Currency;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Client;
use App\Models\User;
use App\Exports\CheckinFilterExport;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use Carbon\Carbon;
use Auth;
use Str;

class FilterController extends Controller
{
    public function index()
    { 
        return view('backend.filter.index', $this->formOptions());
    }

    private function formOptions(): array
    {
        if(Auth::user()->hasAnyRole('admin')){
            $clients = Client::orderBy('id', 'desc')->where('status', 1)->get();
        } else {
            $clients = Client::where('user_id', Auth::id())->where('status', 1)->get(); 
        } 

        return [
            'fromdate' => Carbon::parse('21.02.2024')->format('d.m.Y'),
            'todate' => Carbon::now()->format('d.m.Y'),
            'clients' => $clients,
            'managers' => User::role('sale')->get(),
            'warehouses' => Warehouse::all(),
        ];
    }
    
    
    public function filter(Request $request)
    {
        $query = $this->movementQuery($request);

        $data = DB::query()->fromSub($query, 'movements')
            ->orderBy('date', 'desc')
            ->orderBy('type')
            ->orderBy('detail_id', 'desc')
            ->paginate(20)
            ->appends($request->all());

        $totals = $this->movementTotals($query);

        return view('backend.filter.filter', array_merge(
            $this->formOptions(),
            compact('data', 'totals') + ['keyword' => null]
        ));
    }

    public function excel(Request $request)
    {
        $query = $this->movementQuery($request);

        $data = DB::query()->fromSub($query, 'movements')
            ->orderBy('date', 'desc')
            ->orderBy('type')
            ->orderBy('detail_id', 'desc')
            ->get();
        $totals = $this->movementTotals($query);
        $fromdate = $request->fromdate;
        $todate = $request->todate;
        $fileName = 'kirim-chiqim-' . Carbon::createFromFormat('d.m.Y', $fromdate)->format('Y-m-d')
            . '-' . Carbon::createFromFormat('d.m.Y', $todate)->format('Y-m-d') . '.xlsx';

        return Excel::download(new CheckinFilterExport($data, $totals, $fromdate, $todate), $fileName);
    }

    private function movementTotals($query): array
    {
        $rows = DB::query()->fromSub($query, 'movements')
            ->selectRaw('type, COUNT(*) as rows_count, SUM(qty) as qty, SUM(bonus) as bonus')
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        $in = (float) optional($rows->get('kirim'))->qty;
        $out = (float) optional($rows->get('chiqim'))->qty + (float) optional($rows->get('chiqim'))->bonus;

        return [
            'in_qty' => $in,
            'in_rows' => (int) optional($rows->get('kirim'))->rows_count,
            'out_qty' => (float) optional($rows->get('chiqim'))->qty,
            'out_bonus' => (float) optional($rows->get('chiqim'))->bonus,
            'out_rows' => (int) optional($rows->get('chiqim'))->rows_count,
            'diff' => $in - $out,
        ];
    }

    /**
     * Kirim (checkin) va chiqim (checkout) qatorlari bitta ro'yxatda.
     */
    private function movementQuery(Request $request)
    {
        $request->validate([
            'fromdate' => ['required', 'date_format:d.m.Y'],
            'todate' => ['required', 'date_format:d.m.Y', 'after_or_equal:fromdate'],
            'type' => ['nullable', 'in:all,kirim,chiqim'],
            'manager' => ['nullable'],
            'warehouse' => ['nullable'],
            'client_id' => ['nullable'],
            'barcode' => ['nullable', 'string', 'max:255'],
        ]);

        $from       = Carbon::createFromFormat('d.m.Y', $request->fromdate)->format('Y-m-d');
        $to         = Carbon::createFromFormat('d.m.Y', $request->todate)->format('Y-m-d');
        $type       = $request->input('type', 'all');
        $shipment   = $request->shipment;
        $finish     = $request->finish;
        $selmanager = $request->input('manager', 'all');
        $warehouse  = $request->input('warehouse', 'all');
        $barcode    = trim((string) $request->barcode);
        $client_id  = $request->input('client_id', 'all');

        $checkins = DB::table('checkin_details as detail')
            ->join('checkins as document', 'document.id', '=', 'detail.checkin_id')
            ->leftJoin('products as product', 'product.id', '=', 'detail.product_id')
            ->leftJoin('warehouses as warehouse', 'warehouse.id', '=', 'detail.warehouse_id')
            ->leftJoin('clients as client', 'client.id', '=', 'document.client_id')
            ->leftJoin('users as manager', 'manager.id', '=', 'document.user_id')
            ->leftJoin('currency_types as currency', 'currency.id', '=', DB::raw('COALESCE(detail.currency_type, document.currency_type)'))
            ->whereBetween('document.date', [$from, $to])
            ->selectRaw("'kirim' as type, detail.id as detail_id, document.date, product.name as product_name,
                product.barcode as product_barcode, detail.barcode as unique_barcode, detail.qty, 0 as bonus,
                detail.price, detail.total_price, currency.belgi as currency, warehouse.name as warehouse_name,
                client.name as client_name, manager.name as manager_name, document.number_work, document.reference,
                document.code, document.id as document_id, document.status");

        $checkouts = DB::table('checkout_details as detail')
            ->join('checkouts as document', 'document.id', '=', 'detail.checkout_id')
            ->leftJoin('products as product', 'product.id', '=', 'detail.product_id')
            ->leftJoin('warehouses as warehouse', 'warehouse.id', '=', DB::raw('COALESCE(detail.warehouse_id, document.warehouse_id)'))
            ->leftJoin('clients as client', 'client.id', '=', 'document.client_id')
            ->leftJoin('users as manager', 'manager.id', '=', 'document.manager_id')
            ->leftJoin('currency_types as currency', 'currency.id', '=', DB::raw('COALESCE(detail.currency_type, document.currency_type)'))
            ->whereNull('document.deleted_at')
            ->whereBetween('document.date', [$from, $to])
            ->selectRaw("'chiqim' as type, detail.id as detail_id, document.date, product.name as product_name,
                product.barcode as product_barcode, NULL as unique_barcode, detail.qty, COALESCE(detail.bonus, 0) as bonus,
                detail.price, detail.total_price, currency.belgi as currency, warehouse.name as warehouse_name,
                client.name as client_name, manager.name as manager_name, document.number_work, document.reference,
                document.code, document.id as document_id, document.status");

        if ($shipment) {
            // Kirimda jo'natish holati yo'q: avvalgidek faqat yopilgan kirimlar.
            $checkins->where('document.status', 1);
            $checkouts->where('document.shipment_status', 1);
        }

        if ($finish) {
            $checkins->where('document.status', 1);
            $checkouts->where('document.status', 1);
        }

        if ($selmanager != 'all') {
            $checkins->where('document.user_id', $selmanager);
            $checkouts->where('document.manager_id', $selmanager);
        }

        if ($client_id != 'all') {
            $checkins->where('document.client_id', $client_id);
            $checkouts->where('document.client_id', $client_id);
        }

        if ($warehouse != 'all') {
            $checkins->where('detail.warehouse_id', $warehouse);
            $checkouts->whereRaw('COALESCE(detail.warehouse_id, document.warehouse_id) = ?', [$warehouse]);
        }

        if ($barcode) {
            $hasProductBarcode = Schema::hasColumn('checkin_details', 'product_barcode');

            $checkins->where(function ($query) use ($barcode, $hasProductBarcode) {
                $query->where('product.barcode', $barcode)->orWhere('detail.barcode', $barcode);
                if ($hasProductBarcode) {
                    $query->orWhere('detail.product_barcode', $barcode);
                }
            });
            $checkouts->where('product.barcode', $barcode);
        }

        if ($type === 'kirim') {
            return $checkins;
        }

        if ($type === 'chiqim') {
            return $checkouts;
        }

        return $checkins->unionAll($checkouts);
    }
}
