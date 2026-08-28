<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\CheckoutDetail;
use App\Models\CashReceipt;
use App\Models\CashExpenditureType;
use App\Models\CashExpenditure;
use App\Models\CashReceiptType;
use App\Models\Warehouse;
use App\Models\Checkout;
use App\Models\Product;
use App\Models\Client;
use App\Models\User;

use Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        return view('backend.dashboard');
    }
    
    public function dashboard_new()
    {
        $managers = User::where('status', 1)->role('sale');
        $products = Product::where('status', 1)->paginate(5);
        if(Auth::id() == 1){
            return view('backend.reports.dashboard', compact('managers', 'products'));
        }
        
        return view('backend.reports.dashboard_noadmin', compact('managers', 'products'));
    }
    
    public function printTable(Request $request)
    {
        $date = [
            'year' => $request->input('year', date('Y')),
            'month' => $request->input('month', date('F')),
        ];

        // 1. KIRIMLAR (TUSHUM)
        $revenue = 100000000; // Sotuv (Vyruchka)
        $cogs = 75000000;     // Tannarx (Sebestoimost)

        // 2. YALPI FOYDA (Valovaya pribyl)
        $gross_profit = $revenue - $cogs;
        
        // Marja foizi (Margin %)
        $margin_percent = $revenue > 0 ? ($gross_profit / $revenue) * 100 : 0;
        
        // Tannarx ulushi (Sebestoimost %)
        $cost_percent = $revenue > 0 ? ($cogs / $revenue) * 100 : 0;

        // 3. XARAJATLAR (Operatsion)
        // Bularni bazadan alohida kategoriyalar bo'yicha olishingiz mumkin
        $expenses = [
            'salary' => 10000000, // Ish haqi
            'rent' => 2000000,    // Arenda
            'communal' => 500000, // Kommunal
            'transport' => 1500000, // Transport
            'other' => 1000000,   // Boshqa
        ];
        
        $total_expenses = array_sum($expenses);

        // 4. OPERATSION FOYDA
        $operating_profit = $gross_profit - $total_expenses;
        $operating_profit_percent = $revenue > 0 ? ($operating_profit / $revenue) * 100 : 0;

        // 5. SOLIQLAR VA BOSHQA CHIQIMLAR
        $taxes = 0; // Soliq
        $loans = 0; // Kredit foizlari

        // 6. SOF FOYDA (Chistaya Pribyl)
        $net_profit = $operating_profit - $taxes - $loans;

        // Viewga uzatamiz
        return view('backend.reports.table_print', compact(
            'date', 'revenue', 'cogs', 'gross_profit', 
            'margin_percent', 'cost_percent', 'expenses', 
            'total_expenses', 'operating_profit', 
            'operating_profit_percent', 'taxes', 'net_profit'
        ));
    }
    
    public function print(Request $request)
    {
        $yearMonth = $request->input('year_month', date('Y-m'));

        if (!$yearMonth || !preg_match('/^\d{4}-\d{2}$/', $yearMonth)) {
            $yearMonth = date('Y-m');
        }
        
        [$year, $month] = explode('-', $yearMonth);
        
        $fromDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $toDate   = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        
        $monthName = Carbon::createFromDate($year, $month, 1)->translatedFormat('F');
        
        $paymentTypes = CashReceiptType::all();
        $expenseTypes = CashExpenditureType::where('id', '!=', 10)->get();
        
        // ----------------------------------------------------------------
        // 2. SAVDO (CHECKOUT)
        // ----------------------------------------------------------------
        $checkouts = Checkout::whereBetween('date', [$fromDate, $toDate]);
        
        $total_invoice_sum = $checkouts->sum('total_price'); 
        $debt_sum = $checkouts->sum('total_price_debt');     
        $checkout_ids = $checkouts->pluck('id');             

        // ----------------------------------------------------------------
        // 3. TANNARXNI HISOBLASH (PHP Collection usuli - Aniqroq)
        // ----------------------------------------------------------------
        
        // Shu oydagi barcha sotilgan tovarlar detallarini olamiz
        $all_details = CheckoutDetail::where('qty', '>', 0)->whereIn('checkout_id', $checkout_ids)->get();

        // A) Ikkiga ajratamiz: Tannarxi borlar va Tannarxi yo'qlar
        $known_items = $all_details->filter(function ($item) {
            return $item->total_tan_price > 1000;
        });

        $unknown_items = $all_details->filter(function ($item) {
            return $item->total_tan_price <= 1000 || is_null($item->total_tan_price);
        });
        
        $unknown_count = $unknown_items->count(); 
        
        $unknown_ids = $unknown_items->map(function($item) {
            return "#" . $item->checkout_id . " chekdagi (detal ID: " . $item->id . ")";
        })->toArray();

        // B) Hisoblash
        $sum_sales_known = $known_items->sum('total_price');       // Tannarxi borlarning sotuv narxi
        $sum_cost_known  = $known_items->sum('total_tan_price');   // Tannarxi borlarning tannarxi
        $unknown_sales   = $unknown_items->sum('total_price');     // Tannarxi yo'qlarning sotuv narxi

        // C) O'rtacha koeffitsiyentni topish (Cost Ratio)
        // Misol: Agar 100 so'mga sotib 80 so'm tannarx bo'lsa, ratio = 0.8
        $cost_ratio = 0;
        if ($sum_sales_known > 0) {
            $cost_ratio = $sum_cost_known / $sum_sales_known;
        }

        // D) Taxminiy tannarxni chiqarish
        // Agar hech qaysi tovarda tannarx bo'lmasa, ratio 0 bo'ladi va bu qism ishlamaydi
        $estimated_cost = $unknown_sales * $cost_ratio;

        // E) Yakuniy JAMI TANNARX
        $total_cost = $sum_cost_known + $estimated_cost;

        // Agar $unknown_sales > 0 bo'lsa, demak taxminiy hisob ishlatildi
        $is_approximate = ($unknown_sales > 0);

        // F) Marja foizi
        $margin_percent = 0;
        if ($total_invoice_sum > 0) {
            $margin_percent = (($total_invoice_sum - $total_cost) / $total_invoice_sum) * 100;
        }

        // ----------------------------------------------------------------
        // 4. MOLIYAVIY MA'LUMOTLAR (KIRIM-CHIQIM)
        // ----------------------------------------------------------------
        
        // Kassa kirimlari
        $all_receipts = CashReceipt::whereYear('date', $year)
                                   ->whereMonth('date', $month)
                                   ->get();
        
        $total_income = ['total' => $all_receipts->sum('price'), 'by_type' => []];
        foreach ($paymentTypes as $type) {
            $total_income['by_type'][$type->id] = $all_receipts->where('cash_receipt_type', $type->id)->sum('price');
        }

        // Nakladnoy bo'yicha tushum
        $invoice_receipts = CashReceipt::whereIn('checkout_id', $checkout_ids)->whereYear('date', $year)
                                       ->whereMonth('date', $month)
                                       ->get();
        $invoice_income = ['total' => $invoice_receipts->sum('price'), 'by_type' => []];
        foreach ($paymentTypes as $type) {
            $invoice_income['by_type'][$type->id] = $invoice_receipts->where('cash_receipt_type', $type->id)->sum('price');
        }

        // Xarajatlar (Egasi olganidan tashqari)
        $expenditures = CashExpenditure::whereYear('date', $year)
                                       ->whereMonth('date', $month)
                                       ->where('cash_expenditure_types', '!=', 10)
                                       ->get();

        $expenses = ['total' => $expenditures->sum('price'), 'by_type' => []];
        foreach ($paymentTypes as $type) {
            $expenses['by_type'][$type->id] = $expenditures->where('cash_receipt_type_id', $type->id)->sum('price');
        }

        // Kassa Qoldiq
        $balance = ['total' => $total_income['total'] - $expenses['total'], 'by_type' => []];
        foreach ($paymentTypes as $type) {
            $balance['by_type'][$type->id] = ($total_income['by_type'][$type->id] ?? 0) - ($expenses['by_type'][$type->id] ?? 0);
        }

        // SOF FOYDA (YANGILANGAN TOTAL COST BILAN)
        $net_profit = $total_invoice_sum - $expenses['total'] - $total_cost;

        // Egasi olgan pullar
        $owner_withdrawals_list = CashExpenditure::whereYear('date', $year)
                                           ->whereMonth('date', $month)
                                           ->where('cash_expenditure_types', 10)
                                           ->orderBy('date', 'asc')
                                           ->get();
        $owner_withdrawal_sum = $owner_withdrawals_list->sum('price');

        // Viewga
        $net_profit_potential = $total_invoice_sum - $total_cost - $expenses['total'];

        // 2. SOF FOYDA (Kassaga tushgan jami mablag' bo'yicha - Cash method)
        // Bu yerda jami kassa kirimi (eski qarzlar bilan birga) inobatga olinadi
        $net_profit_actual_cash = (($total_income['total'] - $expenses['total']) * $margin_percent) /100;
        
        // 3. SOF FOYDA (Faqat shu oygi nakladnoylar uchun tushgan pul bo'yicha)
        $net_profit_invoice_cash = (($invoice_income['total'] - $expenses['total']) * $margin_percent) /100;
        
        // Viewga o'zgaruvchilarni uzatamiz
        return view('backend.reports.pl_print', compact(
            'unknown_count', 'unknown_ids', 'year', 'monthName', 'paymentTypes', 'expenseTypes',
            'total_invoice_sum', 'total_income', 'invoice_income',
            'total_cost', 'margin_percent', 'is_approximate',
            'debt_sum', 'expenses', 'expenditures', 'balance', 
            'net_profit_potential', 'net_profit_actual_cash', 'net_profit_invoice_cash', // Yangi o'zgaruvchilar
            'owner_withdrawals_list', 'owner_withdrawal_sum', 'month'
        ));
    }
    
    public function print_old(Request $request)
    {
        // 1. Sana filtrlari
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('m')); // 1 dan 12 gacha raqam
        
        // Oyni nomini chiqarish uchun (View uchun)
        $monthName = Carbon::createFromDate($year, $month, 1)->translatedFormat('F');

        // To'lov turlarini olamiz (Naqd, Click, Terminal va h.k)
        $paymentTypes = CashReceiptType::all();
        
        $expenseTypes = CashExpenditureType::where('id', '!=', 10)->get();

        // ----------------------------------------------------------------
        // 1. Умумий накладнойлар суммаси
        // Checkout modelidan date ustunida olish, total_price sum qilish
        // ----------------------------------------------------------------
        $checkouts = Checkout::whereYear('date', $year)
                             ->whereMonth('date', $month);
        
        $total_invoice_sum = $checkouts->sum('total_price');
        $checkout_ids = $checkouts->pluck('id'); // Shu oyga tegishli ID lar

        // ----------------------------------------------------------------
        // 2. Кассага тушган жами маблағ
        // CashReceipt dan date ustuni bo'yicha
        // ----------------------------------------------------------------
        $all_receipts = CashReceipt::whereYear('date', $year)
                                   ->whereMonth('date', $month)
                                   ->get();
        
        $total_income = [
            'total' => $all_receipts->sum('price'),
            'by_type' => []
        ];

        // Har bir to'lov turi bo'yicha yig'ish
        foreach ($paymentTypes as $type) {
            $total_income['by_type'][$type->id] = $all_receipts->where('cash_receipt_type', $type->id)->sum('price');
        }

        // ----------------------------------------------------------------
        // 3. Накладнойлар бўйича тушум
        // Checkout date -> ID -> CashReceipt checkout_id
        // ----------------------------------------------------------------
        // Yuqorida olingan $checkout_ids dan foydalanamiz
        $invoice_receipts = CashReceipt::whereIn('checkout_id', $checkout_ids)->get();

        $invoice_income = [
            'total' => $invoice_receipts->sum('price'),
            'by_type' => []
        ];

        foreach ($paymentTypes as $type) {
            $invoice_income['by_type'][$type->id] = $invoice_receipts->where('cash_receipt_type', $type->id)->sum('price');
        }

        // ----------------------------------------------------------------
        // 4. Таннарх ва Маржа
        // CheckoutDetail dan total_tan_price (shu oydagi checkout_id lar bo'yicha)
        // ----------------------------------------------------------------
        $total_cost = CheckoutDetail::whereIn('checkout_id', $checkout_ids)->sum('total_tan_price');

        // Маржа (Фойда) % = ((Умумий накладной - Таннарх) / Умумий накладной) * 100
        $margin_percent = 0;
        if ($total_invoice_sum > 0) {
            $margin_percent = (($total_invoice_sum - $total_cost) / $total_invoice_sum) * 100;
        }

        // ----------------------------------------------------------------
        // 5. Қарздорликлар суммаси
        // Checkout -> total_price_debt
        // ----------------------------------------------------------------
        $debt_sum = $checkouts->sum('total_price_debt');

        // ----------------------------------------------------------------
        // 6. Харажатлар
        // CashExpenditure -> date, cash_expenditure_types != 10
        // ----------------------------------------------------------------
        $expenditures = CashExpenditure::whereYear('date', $year)
                                       ->whereMonth('date', $month)
                                       ->where('cash_expenditure_types', '!=', 10) // Egasi olgani emas
                                       ->get();

        $expenses = [
            'total' => $expenditures->sum('price'),
            'by_type' => []
        ];

        foreach ($paymentTypes as $type) {
            // cash_receipt_type_id orqali qaysi kassadan chiqqani aniqlanadi deb hisoblandi
            $expenses['by_type'][$type->id] = $expenditures->where('cash_receipt_type_id', $type->id)->sum('price');
        }

        // ----------------------------------------------------------------
        // 7. Касса қолдиқ
        // Кассага тушган - Харажатлар
        // ----------------------------------------------------------------
        $balance = [
            'total' => $total_income['total'] - $expenses['total'],
            'by_type' => []
        ];

        foreach ($paymentTypes as $type) {
            $in = $total_income['by_type'][$type->id] ?? 0;
            $out = $expenses['by_type'][$type->id] ?? 0;
            $balance['by_type'][$type->id] = $in - $out;
        }

        // ----------------------------------------------------------------
        // 8. Соф Фойда
        // Умумий накладнойлар суммаси - Харажатлар - Товарлар таннархи
        // ----------------------------------------------------------------
        $net_profit = $total_invoice_sum - $expenses['total'] - $total_cost;

        // ----------------------------------------------------------------
        // 9. Эгаси олган пул
        // CashExpenditure -> cash_expenditure_types == 10
        // ----------------------------------------------------------------
        $owner_withdrawal = CashExpenditure::whereYear('date', $year)
                                           ->whereMonth('date', $month)
                                           ->where('cash_expenditure_types', 10)
                                           ->sum('price');
                                           
        $owner_withdrawals_list = CashExpenditure::whereYear('date', $year)
                                       ->whereMonth('date', $month)
                                       ->where('cash_expenditure_types', 10)
                                       ->orderBy('date', 'asc') // Sana bo'yicha
                                       ->get();
                                       
        $owner_withdrawal_sum = $owner_withdrawals_list->sum('price'); // Jami summasi                               
                                       
        // Viewga barcha ma'lumotlarni jo'natish
        return view('backend.reports.pl_print', compact(
                                'year', 'monthName', 'paymentTypes', 'expenseTypes', // expenseTypes yangi
                                'total_invoice_sum', 'total_income', 'invoice_income',
                                'total_cost', 'margin_percent', 'debt_sum',
                                'expenses', 'expenditures', // expenditures collection ham kerak bo'ladi
                                'balance', 'net_profit',
                                'owner_withdrawals_list', 'owner_withdrawal_sum' // Yangi
                            ));
    }
    
    public function dashboard_month(Request $request, $type)
    {
        // 1. Filtrlarni qabul qilish (agar tanlanmagan bo'lsa, hozirgi vaqtni oladi)
        $year = $request->year ?? Carbon::now()->year;
        $month = $request->month ?? Carbon::now()->month;
        $store_id = $request->store ?? 0;
    
        $cashtypes = CashReceiptType::where('status', 1)->get();
        $stores = Warehouse::where('status', 1)->get();
        
        // Tanlangan oyda necha kun borligini aniqlaymiz
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
    
        return view('backend.reports.dashboard_month', compact(
            'stores', 'type', 'cashtypes', 
            'year', 'month', 'store_id', 'daysInMonth'
        ));
    }
    
    public function dashboard_month_old($type)
    {
        $cashtypes = CashReceiptType::where('status', 1)->get();
        $stores = Warehouse::where('status', 1)->get();
        return view('backend.reports.dashboard_month', compact('stores', 'type', 'cashtypes'));
    }
    
    public function inventory_report()
    {
        return view('backend.inventories.report');
    }
    
    public function topclienttwo_api()
    {
        $data = Client::addSelect(['balance_client' => Checkout::where('warehouse_id', 2)->where('status', 1)->selectRaw('count(*) as total')
                                                        ->whereColumn('client_id', 'clients.id')->groupBy('client_id')])->orderBy('balance_client', 'DESC')->paginate(10);
        return response()->json($data);
    }
    
    
}
