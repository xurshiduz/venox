<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client as GClient;
use Illuminate\Http\Request;

use App\Exports\ClientDebtReportExport;
use App\Exports\ClientDebtExport;
use App\Exports\CheckoutOneNull;
use App\Exports\CheckoutDebt;
use App\Exports\CheckoutOne;
use App\Exports\DayExcel;
use App\Exports\CheckoutMonthExport;

use App\Models\CashExpenditure;
use App\Models\InventoryDetail;
use App\Models\ProductCategory;
use App\Models\CashReceiptType;
use App\Models\CheckoutDetail;
use App\Models\WarehouseStock;
use App\Models\CheckinDetail;
use App\Models\CheckType;
use App\Models\CurrencyType;
use App\Models\CashReceipt;
use App\Models\Warehouse;
use App\Models\Currency;
use App\Models\Checkout;
use App\Models\Checkin;
use App\Models\Setting;
use App\Models\History;
use App\Models\Product;
use App\Models\Client;
use App\Models\User;
use App\Models\Unit;

use Carbon\Carbon;
use Excel;
use Auth;
use Str;
use DB;

class CheckoutController extends Controller
{
    
    public function index($ctypeAlias = null)
    {
        $user = Auth::user();
    
        $query = Checkout::where('type_id', 1);
    
        $query->when($user->hasAnyRole('admin|cashier|select_manager'), function ($q) use ($ctypeAlias) {
            if ($ctypeAlias) {
                $ctype = CheckType::where('alias', $ctypeAlias)->firstOrFail();
                $q->where('checkout_tip_id', $ctype->id);
            }
        })->when($user->hasRole('dealer_admin'), function ($q) use ($user) {
            $q->where('dealer_id', $user->dealer_id);
        })->when($user->hasRole('sale') && !$user->hasAnyRole('admin|cashier|select_manager|dealer_admin'), function ($q) use ($user) {
            $q->where('manager_id', $user->id);
        });
    
        // Ma'lumotlarni saralash va sahifalash
        $data = $query->orderByDesc('date')
                       ->orderByDesc('created_at')
                       ->paginate(35);
    
        // View uchun kerakli o'zgaruvchilarni tayyorlash
        $ctypes = CheckType::where('status', 1)->get();
        $managers = User::where('status', 1)->role('sale')->get();
        $types = CashReceiptType::where('status', 1)->get();
    
        $viewData = [
            'data' => $data,
            'ctypes' => $ctypes,
            'managers' => $managers,
            'types' => $types,
            'ctype' => $ctype ?? null, // Agar $ctypeAlias mavjud bo'lsa, $ctype ni o'tkazamiz
            'keyword' => null,
            'shipment' => null,
            'finish' => null,
            'selmanager' => null,
            'clientselect' => null,
            'draft' => null,
            'sdata' => null,
            'fromdate' => Carbon::now()->subDay()->format('d.m.Y'),
            'todate' => Carbon::now()->format('d.m.Y'),
        ];
    
        return view('backend.checkouts.index', $viewData);
    }
    
    public function exportDebts()
    {
        return Excel::download(new ClientDebtReportExport, 'qarzdorlar_hisoboti.xlsx');
    }
    
    public function downloadDebtReport()
    {
        return Excel::download(new ClientDebtExport, 'mijozlar_qarzdorligi_' . date('Y-m-d') . '.xlsx');
    }
    
    public function debts_report()
    {
        $report_data_usd = $this->buildDebtReportUsd();
        $report_data_sum = $this->buildDebtReportSum();
    
        return view('backend.checkouts.clients_debt', compact('report_data_usd', 'report_data_sum'));
    }
    
    /**
     * FAQAT USD (currency_type = 1) qarzdorliklar. Konvertatsiya yo'q.
     */
    /**
     * FAQAT USD (currency_type = 1) qarzdorliklar. Konvertatsiya yo'q.
     */
    private function buildDebtReportUsd()
    {
        $clients = $this->getClientsForReport();
        $report_data = [];
    
        foreach ($clients as $client) {
            $usd_checkouts = $client->checkouts->where('currency_type', 1);
            $all_checkout_ids = $client->checkouts->pluck('id')->toArray();
    
            $current_debt = 0;
            $initial_debt = 0;

            // 0. Boshlang'ich qarz (faqat USD bo'lsa)
            if ((int)$client->currency_type === 1) {
                $initial_debt = (float)$client->balance;
                $current_debt += $initial_debt;
            }
    
            // 1. Savdolar
            foreach ($usd_checkouts as $checkout) {
                $sale_amount = $checkout->alldetails ? $checkout->alldetails->sum('total_price') : 0;
    
                $linked_payments = $client->cashReceipts
                    ->where('checkout_id', $checkout->id)
                    ->where('currency_type', 1)
                    ->sum('price');
    
                $current_debt += ($sale_amount - $linked_payments);
            }
    
            // 2. Egasiz / umumiy to'lovlar (USD)
            $general_payments = $client->cashReceipts
                ->where('currency_type', 1)
                ->filter(function ($receipt) use ($all_checkout_ids) {
                    return is_null($receipt->checkout_id) || !in_array($receipt->checkout_id, $all_checkout_ids);
                })
                ->sum('price');
    
            $current_debt -= $general_payments;
    
            // 3. Vozvratlar (Checkin type_id == 4 va currency_type == 1)
            $returns = 0;
            if ($client->checkins) {
                $return_checkins = $client->checkins
                    ->where('type_id', 4)
                    ->where('currency_type', 1);

                foreach ($return_checkins as $checkin) {
                    $returns += $checkin->details ? $checkin->details->sum('total_price') : 0;
                }
            }
            
            // Qarzdan vozvratni ayiramiz
            $current_debt -= $returns;
    
            if ($current_debt > 0.01) {
                // $initial_debt o'zgaruvchisini ham yuboramiz
                $report_data[] = $this->buildAgingRow($client, $usd_checkouts, $current_debt, $returns, $initial_debt, 1);
            }
        }
    
        return $report_data;
    }
    
    /**
     * HAMMASI SUM ekvivalentida. USD (currency_type=1) yozuvlar
     * o'z currency_type_price kursiga ko'paytirilib qo'shiladi.
     */
    private function buildDebtReportSum()
    {
        $clients = $this->getClientsForReport();
        $report_data = [];
    
        foreach ($clients as $client) {
            $all_checkout_ids = $client->checkouts->pluck('id')->toArray();
            $current_debt = 0;
            
            // 0. Boshlang'ich qarz
            $initial_debt = (float)$client->balance;
            if ((int)$client->currency_type === 1) {
                $initial_debt = $initial_debt * (float)$client->currency_type_price;
            }
            $current_debt += $initial_debt;
    
            // 1. Savdolar
            foreach ($client->checkouts as $checkout) {
                $sale_amount = $checkout->alldetails ? $checkout->alldetails->sum('total_price') : 0;
    
                $linked_payments = $client->cashReceipts
                    ->where('checkout_id', $checkout->id)
                    ->where('currency_type', $checkout->currency_type)
                    ->sum('price');
    
                $balance = $sale_amount - $linked_payments;
    
                if ((int)$checkout->currency_type === 1) {
                    $balance = $balance * (float)$checkout->currency_type_price;
                }
    
                $current_debt += $balance;
            }
    
            // 2. Egasiz / umumiy to'lovlar
            $general_receipts = $client->cashReceipts->filter(function ($receipt) use ($all_checkout_ids) {
                return is_null($receipt->checkout_id) || !in_array($receipt->checkout_id, $all_checkout_ids);
            });
    
            foreach ($general_receipts as $receipt) {
                $amount = $receipt->price;
                if ((int)$receipt->currency_type === 1) {
                    $amount = $amount * (float)$receipt->currency_type_price;
                }
                $current_debt -= $amount;
            }
    
            // 3. Vozvratlar (Checkin type_id == 4)
            $returns = 0;
            if ($client->checkins) {
                $return_checkins = $client->checkins->where('type_id', 4);

                foreach ($return_checkins as $checkin) {
                    $sum_amount = $checkin->details ? $checkin->details->sum('total_price') : 0;
    
                    if ((int)$checkin->currency_type === 1) {
                        $sum_amount = $sum_amount * (float)$checkin->currency_type_price;
                    }
    
                    $returns += $sum_amount;
                }
            }

            // Qarzdan vozvratni ayiramiz
            $current_debt -= $returns;
    
            if ($current_debt > 0.01) {
                // $initial_debt o'zgaruvchisini ham yuboramiz
                $report_data[] = $this->buildAgingRow($client, $client->checkouts, $current_debt, $returns, $initial_debt, null);
            }
        }
    
        return $report_data;
    }
    
    /**
     * Muddatli qarz va oxirgi to'lovni hisoblovchi umumiy funksiya.
     * $initial_debt parametri qo'shildi.
     */
    private function buildAgingRow($client, $checkouts_for_aging, $current_debt, $total_return, $initial_debt, $currency_filter = null)
    {
        $debt_30 = 0;
        $debt_60 = 0;
        $debt_90 = 0;
        $remaining_debt = $current_debt;
    
        $sorted_checkouts = $checkouts_for_aging->sortByDesc('date');
    
        // Oldin savdolardagi qarzlarni hisoblaymiz (savdo sanasi bo'yicha)
        foreach ($sorted_checkouts as $checkout) {
            if ($remaining_debt <= 0) break;
    
            $checkout_sum = $checkout->alldetails ? $checkout->alldetails->sum('total_price') : 0;
    
            if (is_null($currency_filter) && (int)$checkout->currency_type === 1) {
                $checkout_sum = $checkout_sum * (float)$checkout->currency_type_price;
            }
    
            if ($checkout_sum > 0) {
                $amount_in_debt = min($checkout_sum, $remaining_debt);
                $days_diff = \Carbon\Carbon::parse($checkout->date)->diffInDays(now());
    
                if ($days_diff > 90) {
                    $debt_90 += $amount_in_debt;
                } elseif ($days_diff > 60) {
                    $debt_60 += $amount_in_debt;
                } elseif ($days_diff > 30) {
                    $debt_30 += $amount_in_debt;
                }
    
                $remaining_debt -= $amount_in_debt;
            }
        }

        // Qolgan qarz (asosan boshlang'ich qarz yoki boshqa qoldiq) hisobini `created_at` sanasidan ayiramiz
        if ($remaining_debt > 0.01) {
            $days_diff = \Carbon\Carbon::parse($client->created_at)->diffInDays(now());
    
            if ($days_diff > 90) {
                $debt_90 += $remaining_debt;
            } elseif ($days_diff > 60) {
                $debt_60 += $remaining_debt;
            } elseif ($days_diff > 30) {
                $debt_30 += $remaining_debt;
            }
        }
    
        $payments_query = $client->cashReceipts->where('price', '>', 0);
        if ($currency_filter) {
            $payments_query = $payments_query->where('currency_type', $currency_filter);
        }
        $last_payment = $payments_query->sortByDesc('date')->first();
    
        $last_payment_amount = 0;
        if ($last_payment) {
            $last_payment_amount = $last_payment->price;
            if (is_null($currency_filter) && (int)$last_payment->currency_type === 1) {
                $last_payment_amount = $last_payment_amount * (float)$last_payment->currency_type_price;
            }
        }
    
        return (object) [
            'name' => $client->name,
            'phone' => $client->phone,
            'initial_debt' => $initial_debt, // <--- Boshlang'ich qarz ustuni
            'total_debt' => $current_debt,
            'total_return' => $total_return,
            'debt_30' => $debt_30,
            'debt_60' => $debt_60,
            'debt_90' => $debt_90,
            'last_payment_amount' => $last_payment_amount,
            'last_payment_date' => $last_payment ? \Carbon\Carbon::parse($last_payment->date)->format('Y-m-d') : '-',
        ];
    }
    
    /**
     * Ikkala report uchun umumiy client so'rovi.
     */
    private function getClientsForReport()
    {
        return Client::with([
            'checkouts.alldetails',
            'checkins.details',
            'cashReceipts' => function ($query) {
                $query->where('status', 1);
            }
        ])
        ->when(!Auth::user()->hasAnyRole(['admin', 'report']), function ($query) {
            $query->where('user_id', Auth::id());
        })
        ->get();
    }
    
    public function debts_report_oooll()
{
    // 1. Ma'lumotlarni yuklaymiz
    $clients = Client::with([
        'checkouts.alldetails',
        'checkins.details',
        'cashReceipts' => function ($query) {
            $query->where('status', 1);
        }
    ])
    ->when(!Auth::user()->hasAnyRole(['admin', 'report']), function ($query) {
        $query->where('user_id', Auth::id());
    })
    ->get();

    $report_data = [];

    foreach ($clients as $client) {
        
        // --- YANGI QO'SHILGAN QISM: Boshlang'ich qarz ---
        $initial_debt = $client->balance ? (float)$client->balance : 0;
        $current_debt = $initial_debt; // Qarzni 0 dan emas, boshlang'ich qarzlardan boshlaymiz
        
        // Mijozning mavjud barcha checkout ID larini yig'ib olamiz
        $existing_checkout_ids = $client->checkouts->pluck('id')->toArray();

        // --- 1. HAR BIR SAVDONI (CHECKOUT) TAHLIL QILAMIZ ---
        if ($client->checkouts) {
            foreach ($client->checkouts as $checkout) {
                // 1.1. Savdo summasi
                $sale_amount = $checkout->alldetails ? $checkout->alldetails->sum('total_price') : 0;

                // 1.2. Aynan SHU savdoga tegishli to'lovlar
                $linked_payments = $client->cashReceipts
                    ->where('checkout_id', $checkout->id)
                    ->sum('price');

                // 1.3. Balans (Savdo - To'lov)
                $balance = $sale_amount - $linked_payments;

                $current_debt += $balance; 
            }
        }

        // --- 2. UMUMIY VA "EGASIZ" TO'LOVLAR ---
        $general_payments = 0;
        if ($client->cashReceipts) {
            $general_payments = $client->cashReceipts->filter(function ($receipt) use ($existing_checkout_ids) {
                if (is_null($receipt->checkout_id)) {
                    return true;
                }
                if (!in_array($receipt->checkout_id, $existing_checkout_ids)) {
                    return true;
                }
                return false;
            })->sum('price');
        }
        
        // Umumiy to'lovlar qarzni kamaytiradi
        $current_debt -= $general_payments;


        // --- 3. VOZVRATLAR (CHECKINS) ---
        $returns = 0;
        if ($client->checkins) {
            foreach ($client->checkins as $checkin) {
                if ($checkin->details) {
                    $returns += $checkin->details->sum('total_price');
                }
            }
        }
        $current_debt -= $returns;


        // --- HISOBOTGA QO'SHISH ---
        if ($current_debt > 0.01) {
            
            // --- 4. MUDDATLI QARZNI HISOBLASH ---
            $debt_30 = 0;
            $debt_60 = 0;
            $debt_90 = 0;
            $remaining_debt = $current_debt;

            // Muddatni faqat NASIYA (Tip != 2) savdolar bo'yicha hisoblaymiz
            $nasiya_checkouts = $client->checkouts->sortByDesc('date');

            foreach ($nasiya_checkouts as $checkout) {
                if ($remaining_debt <= 0) break;

                $checkout_sum = $checkout->alldetails ? $checkout->alldetails->sum('total_price') : 0;

                if ($checkout_sum > 0) {
                    $amount_in_debt = min($checkout_sum, $remaining_debt);
                    $days_diff = \Carbon\Carbon::parse($checkout->date)->diffInDays(now());

                    if ($days_diff > 90) {
                        $debt_90 += $amount_in_debt;
                    } elseif ($days_diff > 60) {
                        $debt_60 += $amount_in_debt;
                    } elseif ($days_diff > 30) {
                        $debt_30 += $amount_in_debt;
                    }

                    $remaining_debt -= $amount_in_debt;
                }
            }

            // --- YANGI QO'SHILGAN QISM: Boshlang'ich qarzning muddati ---
            // Agar oxirgi savdolardan keyin ham qarz qolib ketgan bo'lsa, demak bu eng eskilari, 
            // ya'ni Boshlang'ich qarzdorlikka tegishli qoldiq!
            if ($remaining_debt > 0) {
                // created_at orqali necha kun bo'lganini topamiz
                $days_diff = \Carbon\Carbon::parse($client->created_at)->diffInDays(now());

                if ($days_diff > 90) {
                    $debt_90 += $remaining_debt;
                } elseif ($days_diff > 60) {
                    $debt_60 += $remaining_debt;
                } elseif ($days_diff > 30) {
                    $debt_30 += $remaining_debt;
                }
            }

            // Oxirgi to'lov sanasi
            $last_payment = null;
            if ($client->cashReceipts) {
                $last_payment = $client->cashReceipts
                    ->where('price', '>', 0)
                    ->sortByDesc('date')
                    ->first();
            }

            $report_data[] = (object) [
                'name' => $client->name,
                'phone' => $client->phone,
                'initial_debt' => $initial_debt, // <--- Boshlang'ich qarz Blade uchun
                'total_debt' => $current_debt,
                'debt_30' => $debt_30,
                'debt_60' => $debt_60,
                'debt_90' => $debt_90,
                'last_payment_amount' => $last_payment ? $last_payment->price : 0,
                'last_payment_date' => $last_payment ? \Carbon\Carbon::parse($last_payment->date)->format('Y-m-d') : '-',
            ];
        }
    }

    return view('backend.checkouts.clients_debt', compact('report_data'));
}
    
    public function debts_report_old()
    {
        // 1. Ma'lumotlarni Eager Loading bilan olamiz
        $clients = Client::with([
            'checkouts.alldetails', // Sotuv detallari (Modelda items() deb nomlaganmiz)
            'checkins.details',  // Vozvrat detallari
            'cashReceipts' => function($query) {
                $query->where('status', 1);
            }
        ])
        ->where('id', '!=', 1) // ID 1 ni chiqarib tashlaymiz
        ->get();

        $report_data = [];

        foreach ($clients as $client) {
            // A. Jami savdo
            $sales = 0;
            if ($client->checkouts) {
                foreach ($client->checkouts as $checkout) {
                    if ($checkout->alldetails) {
                        $sales += $checkout->alldetails->sum('total_price');
                    }
                }
            }

            // B. To'lovlar va Vozvratlar
            $cash = $client->cashReceipts ? $client->cashReceipts->sum('price') : 0;
            
            $returns = 0;
            if ($client->checkins) {
                foreach ($client->checkins as $checkin) {
                    if ($checkin->details) {
                        $returns += $checkin->details->sum('total_price');
                    }
                }
            }

            $total_payments = $cash + $returns;

            // C. Haqiqiy qarz
            $total_debt = $sales - $total_payments;

            // Agar qarz 0 dan katta bo'lsa, hisoblashni davom ettiramiz
            if ($total_debt > 0.01) {
                
                // D. Muddatli qarzdorlikni hisoblash (30, 60, 90)
                $debt_30 = 0;
                $debt_60 = 0;
                $debt_90 = 0;
                $remaining_debt = $total_debt;

                // Eng yangi savdolarni olamiz
                $sorted_checkouts = $client->checkouts->sortByDesc('date');

                foreach ($sorted_checkouts as $checkout) {
                    if ($remaining_debt <= 0) break;

                    $checkout_sum = $checkout->alldetails ? $checkout->alldetails->sum('total_price') : 0;

                    if ($checkout_sum > 0) {
                        $amount_in_debt = min($checkout_sum, $remaining_debt);
                        $days_diff = Carbon::parse($checkout->date)->diffInDays(now());

                        if ($days_diff > 90) {
                            $debt_90 += $amount_in_debt;
                        } elseif ($days_diff > 60) {
                            $debt_60 += $amount_in_debt;
                        } elseif ($days_diff > 30) {
                            $debt_30 += $amount_in_debt;
                        }

                        $remaining_debt -= $amount_in_debt;
                    }
                }

                // E. Oxirgi to'lov (faqat pul)
                $last_payment = null;
                if ($client->cashReceipts) {
                    $last_payment = $client->cashReceipts
                        ->where('price', '>', 0)
                        ->sortByDesc('date')
                        ->first();
                }

                // Ma'lumotni arrayga yig'amiz
                $report_data[] = (object) [
                    'name' => $client->name,
                    'phone' => $client->phone,
                    'total_debt' => $total_debt,
                    'debt_30' => $debt_30,
                    'debt_60' => $debt_60,
                    'debt_90' => $debt_90,
                    'last_payment_amount' => $last_payment ? $last_payment->price : 0,
                    'last_payment_date' => $last_payment ? Carbon::parse($last_payment->date)->format('Y-m-d') : '-',
                ];
            }
        }

        // Viewga jo'natamiz
        return view('backend.checkouts.clients_debt', compact('report_data'));
    }

    public function refresh_total()
    { 
        foreach(Checkout::all() as $item){
            $sum = CashReceipt::where('status', 1)->where('checkout_id',$item->id)->sum('price');
            $item->update(['total_price' => $item->details()->sum('total_price'), 'total_price_payme' => $sum, 'total_price_debt' => $item->details()->sum('total_price') - $sum]);
        }
        
        dd('success');
    }
    
    public function debtors($ctype = null)
    { 
        $ctypes = CheckType::where('status', 1)->get();
        $managers = User::role('sale')->get();
        if(Auth::user()->hasAnyRole('admin|cashier|select_manager')){
            $data = Checkout::where('checkout_tip_id', 1)->where('total_price_debt', '!=', 0)->where('type_id', 1)->orderBy('id', 'desc')->paginate(35);
        } elseif(Auth::user()->hasAnyRole('dealer_admin')) {
            $data = Checkout::where('checkout_tip_id', 1)->where('total_price_debt', '!=', 0)->where('dealer_id', Auth::user()->dealer_id)->where('type_id', 1)->orderBy('id', 'desc')->paginate(35);
        } else {
            $data = Checkout::where('checkout_tip_id', 1)->here('total_price_debt', '!=', 0)->where('manager_id', Auth::id())->where('type_id', 1)->orderBy('id', 'desc')->paginate(35);
        }
         
        $types          = CashReceiptType::where('status', 1)->get();
        $keyword        = NULL; 
        $shipment       = NULL;
        $finish         = NULL;
        $selmanager     = NULL;
        $clientselect   = NULL;
        $draft          = NULL;
        $sdata          = NULL;
        $fromdate       = Carbon::now()->subDay()->format('d.m.Y');
        $todate         = Carbon::now()->format('d.m.Y');

        return view('backend.checkouts.index', compact('data', 'keyword', 'types', 'managers', 'shipment', 'finish', 'selmanager', 'fromdate', 'todate', 'clientselect', 'draft', 'sdata', 'ctypes'));
    }

    public function debtors_excel()
    {
        return Excel::download(new CheckoutDebt(), 'Задолженность клиентов по состоянию на ' . Carbon::now()->format('Y-m-d') . ' г.' . '.xlsx');
    }
    
    public function in_price()
    { 
        $from       = Carbon::parse('01.12.2024')->format('d.m.Y');
        $to         = Carbon::now()->format('d.m.Y');
        
        $data = CheckoutDetail::whereBetween('created_at', [Carbon::parse($from)->startOfDay()->format('Y-m-d H:i:s'), Carbon::parse($to)->endOfDay()->format('Y-m-d H:i:s')])->get()->groupBy('product_id');

        return view('backend.checkouts.inprice_form', compact('data'));
    }
    
    public function index_stiock_del()
    { 
        foreach(InventoryDetail::where('checkin_status', 0)->where('warehouse_id', 4)->get() as $dd){
            if($dd->prodid){
                if(CheckinDetail::where('checkin_id', 181)->where('product_id', $dd->prodid->id)->where('warehouse_cell_id', $dd->warehouse_cell_id)->count()){
                    $ddd = CheckinDetail::where('checkin_id', 181)->where('product_id', $dd->prodid->id)->where('warehouse_cell_id', $dd->warehouse_cell_id)->first();
                    $ddd->update(['qty' => $ddd->qty + $dd->qty]);
                } else {
                    $detdata['checkin_id'] = 181;
                    $detdata['product_id'] = $dd->prodid->id;
                    $detdata['warehouse_id'] = 4;
                    $detdata['warehouse_cell_id'] = $dd->warehouse_cell_id;
                    $detdata['warehouse_block_id'] = $dd->warehouse_block_id;
                    $detdata['category_id'] = 1;
                    $detdata['qty'] = $dd->qty;
                    $detdata['currency_type'] = 1;
                    $detdata['currency_type_price'] = 0;
                    $detdata['price'] = 0;
                    $detdata['status'] = 1;
                    $detdata['code'] = Str::uuid();
                    $detdata['barcode'] = mt_rand(1000,9999) . time() . mt_rand(1000,9999);
                    $detitem = CheckinDetail::create($detdata);
                }
                $dd->update(['checkin_status' => 1]);
            }
        }
        dd('s');
        //Inventarizatisya
        
        foreach(InventoryDetail::where('warehouse_id', 2)->get() as $dd){
            if($dd->prodid){
                if(CheckinDetail::where('checkin_id', 169)->where('product_id', $dd->prodid->id)->where('warehouse_cell_id', $dd->warehouse_cell_id)->count()){
                    $ddd = CheckinDetail::where('checkin_id', 169)->where('product_id', $dd->prodid->id)->where('warehouse_cell_id', $dd->warehouse_cell_id)->first();
                    $ddd->update(['qty' => $ddd->qty + $dd->qty]);
                } else {
                    $detdata['checkin_id'] = 169;
                    $detdata['product_id'] = $dd->prodid->id;
                    $detdata['warehouse_id'] = 2;
                    $detdata['warehouse_cell_id'] = $dd->warehouse_cell_id;
                    $detdata['warehouse_block_id'] = $dd->warehouse_block_id;
                    $detdata['category_id'] = 1;
                    $detdata['qty'] = $dd->qty;
                    $detdata['currency_type'] = 1;
                    $detdata['currency_type_price'] = 0;
                    $detdata['price'] = 0;
                    $detdata['status'] = 1;
                    $detdata['code'] = Str::uuid();
                    $detdata['barcode'] = mt_rand(1000,9999) . time() . mt_rand(1000,9999);
                    $detitem = CheckinDetail::create($detdata);
                }
                $dd->update(['checkin_status' => 1]);
            }
            
            
        }
        dd('s');
        
        //Prodajani bilan 0 qilish
        foreach(CheckoutDetail::where('warehouse_id', 2)->get() as $dd){
            if(CheckinDetail::where('checkin_id', 2)->where('product_id', $dd->product_id)->count()){
                $ddd = CheckinDetail::where('checkin_id', 2)->where('product_id', $dd->product_id)->first();
                $ddd->update(['qty' => $ddd->qty + $dd->qty]);
            } else {
                $detdata['checkin_id'] = 2;
                $detdata['product_id'] = $dd->product_id;
                $detdata['warehouse_id'] = 2;
                $detdata['category_id'] = 1;
                $detdata['qty'] = $dd->qty;
                $detdata['currency_type'] = 1;
                $detdata['currency_type_price'] = 0;
                $detdata['price'] = 0;
                $detdata['status'] = 1;
                $detdata['code'] = Str::uuid();
                $detdata['barcode'] = mt_rand(1000,9999) . time() . mt_rand(1000,9999);
                $detitem = CheckinDetail::create($detdata);
            }
            
        }
        dd('s');
        
        //
        $checkout = CheckinDetail::whereIn('checkin_id', [1,2])->get();
        
        foreach($checkout as $s){
            $s->delete();
        }
        dd('s');
        
        
        //data buyicha
        $from = date('2024-02-24');
        $to = date('2024-09-30');
        
        $checkout = Checkout::whereBetween('created_at', [$from, $to])->get();
        
        foreach($checkout as $s){
            foreach($s->details() as $det){
                $det->delete();
            }
            $s->delete();
        }
        dd('s');
        
        dd($checkout->count());

        //Prodajani bilan 0 qilish
        foreach(CheckoutDetail::where('warehouse_id', 2)->get() as $dd){
            if(CheckinDetail::where('checkin_id', 2)->where('product_id', $dd->product_id)->count()){
                $ddd = CheckinDetail::where('checkin_id', 2)->where('product_id', $dd->product_id)->first();
                $ddd->update(['qty' => $ddd->qty + $dd->qty]);
            } else {
                $detdata['checkin_id'] = 2;
                $detdata['product_id'] = $dd->product_id;
                $detdata['warehouse_id'] = 2;
                $detdata['category_id'] = 1;
                $detdata['qty'] = $dd->qty;
                $detdata['currency_type'] = 1;
                $detdata['currency_type_price'] = 0;
                $detdata['price'] = 0;
                $detdata['status'] = 1;
                $detdata['code'] = Str::uuid();
                $detdata['barcode'] = mt_rand(1000,9999) . time() . mt_rand(1000,9999);
                $detitem = CheckinDetail::create($detdata);
            }
            
        }
        dd('s');
        
        //
        foreach(Checkin::where('warehouse_id', 2)->get() as $s){
            foreach($s->details as $det){
                $det->delete();
            }
            $s->delete();
        }
        dd('s');
        
        
        //s
        $data = Checkout::all();
        
        foreach($data as $s){
            if($s->details()->count()){
                
            } else {
                $s->delete();
            }
        }
        dd('s');
        
        
        //tan narxlarni kiritish
        $checks = CheckoutDetail::where('tan_price', '>=', 100)->get()->groupBy('product_id');
        
        foreach($checks as $ch){
            foreach(CheckoutDetail::where('product_id', $ch->first()->product_id)->get() as $det){
                $det->update(['tan_price' => $ch->first()->tan_price, 'total_tan_price' => ($ch->first()->qty * $ch->first()->tan_price)]);
            }
        }
        dd('success');
    }
    
    public function index_report()
    { 
        
        $managers = User::role('sale')->get();
        $data = Checkout::where('type_id', 1)->orderBy('id', 'desc')->paginate(40);
         
        $types = CashReceiptType::where('status', 1)->get();
        $keyword = NULL; 
        $shipment       = NULL;
        $finish         = NULL;
        $selmanager     = NULL;
        $fromdate       = Carbon::parse('21.02.2024')->format('d.m.Y');
        $todate         = Carbon::now()->format('d.m.Y');

        return view('backend.checkouts.report_index', compact('data', 'keyword', 'types', 'managers', 'shipment', 'finish', 'selmanager', 'fromdate', 'todate'));
    }
    
    public function all_done_status()
    {
        $adata = Checkout::where('shipment_status', 0)->paginate(200);
        
        foreach($adata as $item){
            $data['shipment_status'] = 1;
            $item->update($data);
        }
        
        dd('success');
        
        $adata = Checkout::whereNull('number_order')->paginate(50);
        
        foreach($adata as $item){
            $year = Carbon::now()->format('Y');
            
            $data['transaction'] = $year . time();
            $data['status'] = 1;
            $data['step'] = 2;
            
            
            if(Checkout::whereYear('date', '=', $year)->count()){
                $slice = Checkout::whereYear('date', '=', $year)->max('number_order');
                $data['number_order'] = Str::padLeft(($slice + 1), 6, '0');
                $data['number_work'] = Carbon::now()->format('y') . $data['number_order'];
            } else {
                $data['number_order'] = '000001';
                $data['number_work'] = Carbon::now()->format('y') . $data['number_order'];
            }
            
            $item->update($data);
            
            foreach($item->details()->get() as $det){
                $det->update(['status' => 1]);
            }
            
        }
        
        
        dd('success');
    }
    
    public function checkout_filter(Request $request)
    { 
        $managers = User::role('sale')->get();
        $types = CashReceiptType::where('status', 1)->get();
        $keyword = $request->input('search');
        
        $shipment       = $request->shipment;
        $finish         = $request->finish;
        $selmanager     = $request->manager;
        $fromdate       = $request->fromdate;
        $todate         = $request->todate;
        $clientselect         = $request->clientselect;
        $draft         = $request->draft;
        $sdata         = $request->sdata;
        
        $result = Checkout::query()->orderBy('id', 'desc');
        
        
        if($sdata){
            $result = $result->whereBetween('created_at', [Carbon::parse($fromdate)->startOfDay()->format('Y-m-d H:i:s'), Carbon::parse($todate)->endOfDay()->format('Y-m-d H:i:s')]);
        }
        
        if($shipment){
            $result = $result->where('shipment_status', 1);
        }
        
        if($finish){
            $result = $result->where('status', 1);
        }
        
        if($selmanager != 'all'){
            $result = $result->where('manager_id', $request->manager);
        }
        
        if($clientselect != null){
            if(Client::where('name', $clientselect)->count()){
                $idcl = Client::where('name', $clientselect)->first();
            
                $result = $result->where('client_id', $idcl->id);
            }
        }
        
        if($draft){
            $result = $result->whereNull('number_order');
        }
        
        $data = $result->paginate(20)->appends($request->all());
        return view('backend.checkouts.index', compact('data', 'keyword', 'types', 'managers', 'shipment', 'finish', 'selmanager', 'fromdate', 'todate', 'clientselect', 'draft', 'sdata'));
    }
    
    public function payment_check(Request $request,$id)
    { 
        $item = Checkout::where('code', $id)->first();
        $sum = Str::replace(',', '.', Str::replace(' ', '', $request->price));
        $currency = Str::replace(',', '.', Str::replace(' ', '', $request->currency_type_price));
        
        $tsum = $item->total_price_payme + $sum;
        if($tsum > $item->total_price){
            $request->session()->flash('error', 'Оплата больше суммы контракта невозможна.');
        } else {
            $data = $request->all();
            
            $data['price'] = $sum;
            $data['checkout_id'] = $item->id;
            $data['client_id'] = $item->client_id;
            $data['user_id'] = Auth::id();
            $data['date'] = Carbon::now()->format('Y-m-d');
            $data['currency_type'] = $request->currency_type;
            $data['currency_type_price'] = $currency;
            $data['code'] = Str::uuid();
            
            CashReceipt::create($data);
            
            $item->update(['total_price_payme' => $tsum, 'total_price_debt' => $item->total_price_debt - $sum]);
            $request->session()->flash('success', 'Успешно');
        }
        
        return back();
    }

    public function form($id = null, $page = null)
    {
        $item = null;
        
        if(Auth::user()->hasAnyRole('admin|cashier|sale')){
            $warehouses = Warehouse::whereNull('factory_id')->where('status', 1)->get();
        } else {
            $warehouses = Warehouse::whereNull('factory_id')->where('dealer_id', Auth::user()->dealer_id)->where('status', 1)->get();
        } 
       
        $managers = User::role('sale')->where('status', 1)->get();
        $types = CheckType::where('status', 1)->get();
        
        if(Auth::user()->hasAnyRole('admin|select_manager')){
            $clients = Client::orderBy('id', 'desc')->where('status', 1)->get();
        } else {
            $clients = Client::where('user_id', Auth::id())->where('status', 1)->get(); 
        } 
        
        if($id) {
            $item = Checkout::where('code', $id)->first();
        }
        
        if(Auth::user()->hasAnyRole('tan_report')){
            return view('backend.checkouts.report_form', compact('item', 'warehouses', 'clients', 'managers', 'page', 'types'));
        } else {
            return view('backend.checkouts.form', compact('item', 'warehouses', 'clients', 'managers', 'page', 'types'));
        }
    }
    
    public function calculateCost(Request $request)
    {
        $yearMonth = $request->input('year_month', date('Y-m'));

        if (!$yearMonth || !preg_match('/^\d{4}-\d{2}$/', $yearMonth)) {
            $yearMonth = date('Y-m');
        }
        
        [$year, $month] = explode('-', $yearMonth);
        
        // Carbon bilan xavfsizroq
        $fromDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $toDate   = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        // 2. Shu davrdagi Checkout ID larini topamiz
        $checkoutIds = Checkout::whereYear('date', $year)
                               ->whereMonth('date', $month)
                               ->pluck('id');

        if ($checkoutIds->isEmpty()) {
            return back()->with('error', 'Bu davrda hech qanday savdo topilmadi.');
        }

        // 3. Tannarxi yo'q yoki 0 bo'lgan detallarni olamiz
        $detailsToUpdate = CheckoutDetail::whereIn('checkout_id', $checkoutIds)
            ->where(function($query) {
                $query->whereNull('tan_price')
                      ->orWhere('tan_price', 1)
                      ->orWhere('tan_price', 0)
                      ->orWhere('tan_price', NULL);
            })
            ->get();

        $count = 0;

        foreach ($detailsToUpdate as $detail) {
            // 4. Shu tovarning (product_id) eng oxirgi kirim narxini (CheckinDetail) topamiz
            // latest('id') - ID bo'yicha eng oxirgisini oladi (yoki created_at qilish ham mumkin)
            $lastCheckin = CheckinDetail::where('product_id', $detail->product_id)
                                        ->latest('id') 
                                        ->first();

            if ($lastCheckin) {
                // 5. Narxlarni yangilash
                $detail->tan_price = $lastCheckin->price; // Donasini tannarxi
                $detail->total_tan_price = $lastCheckin->price * $detail->qty; // Jami tannarx (Narx * Soni)
                $detail->save();
                
                $count++;
            }
        }

        return back()->with('success', "Muvaffaqiyatli yakunlandi! {$count} ta mahsulotning tannarxi hisoblandi.");
    }
    
    //API Qty
    public function qty()
    {
        $brid = request()->brid; //qty Keladi
        $cid = request()->cid; //ID keladi
        $item = CheckoutDetail::findOrFail($cid);
        $oldqty = $item->qty;
        $item->update(['qty' => $brid, 'total_price' => ($brid * $item->price), 'total_tan_price' => $item->tan_price >=0 ? ($brid * $item->tan_price) : null]);
        
        if($oldqty != $item->qty){
            if(WarehouseStock::where('warehouse_id', $item->warehouse_id)->where('product_id', $item->product_id)->count()){
                $wsid = WarehouseStock::where('warehouse_id', $item->warehouse_id)->where('product_id', $item->product_id)->first();
                if($oldqty < $item->qty){
                    $stc = ($wsid->stock - ($item->qty - $oldqty));
                } else {
                    $stc = ($wsid->stock + ($oldqty - $item->qty));
                }
                $wsid->update([
                    'stock' => $stc,
                    'checkin_price' => $item->prodid->checkindetails()->max('price'),
                    'checkout_price' => $item->prodid->price ? $item->prodid->price : $item->prodid->checkoutdetails()->max('price'),
                    'checkin_total_price' => $item->prodid->checkindetails()->max('price') * $stc,
                    'checkout_total_price' => ($item->prodid->price ? $item->prodid->price : $item->prodid->checkoutdetails()->max('price')) * $stc
                ]);
            } else {
                WarehouseStock::create(['warehouse_id' => $item->warehouse_id, 'product_id' => $item->product_id, 'stock' => (0 - $item->qty)]);
            }
        }
        
        
        $citem = Checkout::findOrFail($item->checkout_id);
        
        if($citem->status == 1){
            $hdata['dealer_id'] = $citem->dealer_id;
            $hdata['user_id'] = Auth::id();
            $hdata['code'] = Str::uuid();
            $hdata['name'] = 8;
            $hdata['database'] = 'checkout_details';
            $hdata['ip_address'] = request()->ip();
            $hdata['agent'] = request()->server('HTTP_USER_AGENT');
            $hdata['comment'] = 'В контракте "' . ($citem->number_work ? '№ ' . $citem->number_work : 'Черновик #' . $citem->id) . '" от ' . $citem->date . ' изменилось количество запчастей «<u>' . $item->prodid->name . '</u>» с ' . $oldqty . ' на ' . $brid;
            
            $history = History::create($hdata);
            
            $client = new GClient([
                "base_uri" => "https://api.telegram.org",
            ]);
                    
            $clientid       = $citem->number_work ? $citem->number_work : 'Черновик ID#' . $citem->id;
            $ip             = request()->ip();
            $dealer         = $citem->managerid->name;
            $warehouse      = $item->warehouseid?->name;
            $barcode        = $item->prodid->barcode;
            $hid            = $history->id;
            $user           = Auth::user()->name . ' Тел: ' . Auth::user()->phone;
            $comment        = $hdata['comment'];
            $date           = Carbon::now()->format('Y-m-d H:i:s');
            
            $bot_token = "7335500759:AAFQCsaM-8jLefkgmNEW3phJxmn_gQIBSQw";
            $chat_id = "-1003699847586";
            $message = "ID#$hid\n<b><u>⚠️ Модуль: Продажа (Позиция-Количество)</u></b>\n<b>🧾 Договор:</b> $clientid \n\n<b>🏬 Клиент:</b> $dealer \n<b>🏭 Склад:</b> $warehouse \n<b>📦 Баркод:</b> $barcode \n<b>👨‍💻 Пользователь:</b> $user \n<b>📝 Примечание:</b> $comment \n<b>📍 ИП адрес:</b> $ip \n<b>⏱ Дата :</b> $date";
            $response = $client->request("GET", "/bot$bot_token/sendMessage", [
                "query" => [
                    "chat_id" => $chat_id,
                    "text" => $message,
                    "parse_mode" => "html"
                ]
            ]);
        }
        
        
        $citem->update(['total_price' => $citem->details()->sum('total_price'), 'total_price_debt' => $citem->total_price_payme ? ($citem->details()->sum('total_price') - $citem->total_price_payme) : $citem->details()->sum('total_price')]);
        return response()->json(['qty' => $item->qty]);
    }
    
    public function bonus()
    {
        $brid = request()->brid; //qty Keladi
        $cid = request()->cid; //ID keladi
        $item = CheckoutDetail::findOrFail($cid);
        $oldqty = $item->bonus;
        $item->update(['bonus' => $brid]);
        
        if($oldqty != $item->bonus){
            if(WarehouseStock::where('warehouse_id', $item->warehouse_id)->where('product_id', $item->product_id)->count()){
                $wsid = WarehouseStock::where('warehouse_id', $item->warehouse_id)->where('product_id', $item->product_id)->first();
                if($oldqty < $item->bonus){
                    $stc = ($wsid->stock - ($item->bonus - $oldqty));
                } else {
                    $stc = ($wsid->stock + ($oldqty - $item->bonus));
                }
                $wsid->update([
                    'stock' => $stc,
                    'checkin_price' => $item->prodid->checkindetails()->max('price'),
                    'checkout_price' => $item->prodid->price ? $item->prodid->price : $item->prodid->checkoutdetails()->max('price'),
                    'checkin_total_price' => $item->prodid->checkindetails()->max('price') * $stc,
                    'checkout_total_price' => ($item->prodid->price ? $item->prodid->price : $item->prodid->checkoutdetails()->max('price')) * $stc
                ]);
            } else {
                WarehouseStock::create(['warehouse_id' => $item->warehouse_id, 'product_id' => $item->product_id, 'stock' => (0 - $item->qty)]);
            }
        }
        
        
        $citem = Checkout::findOrFail($item->checkout_id);
        
        if($citem->status == 1){
            $hdata['dealer_id'] = $citem->dealer_id;
            $hdata['user_id'] = Auth::id();
            $hdata['code'] = Str::uuid();
            $hdata['name'] = 8;
            $hdata['database'] = 'checkout_details';
            $hdata['ip_address'] = request()->ip();
            $hdata['agent'] = request()->server('HTTP_USER_AGENT');
            $hdata['comment'] = 'В контракте "' . ($citem->number_work ? '№ ' . $citem->number_work : 'Черновик #' . $citem->id) . '" от ' . $citem->date . ' изменилось количество запчастей «<u>' . $item->prodid->name . '</u>» с ' . $oldqty . ' на ' . $brid;
            
            $history = History::create($hdata);
            
            $client = new GClient([
                "base_uri" => "https://api.telegram.org",
            ]);
                    
            $clientid       = $citem->number_work ? $citem->number_work : 'Черновик ID#' . $citem->id;
            $ip             = request()->ip();
            $dealer         = $citem->managerid->name;
            $warehouse      = $item->warehouseid?->name;
            $barcode        = $item->prodid->barcode;
            $hid            = $history->id;
            $user           = Auth::user()->name . ' Тел: ' . Auth::user()->phone;
            $comment        = $hdata['comment'];
            $date           = Carbon::now()->format('Y-m-d H:i:s');
            
            $bot_token = "7335500759:AAFQCsaM-8jLefkgmNEW3phJxmn_gQIBSQw";
            $chat_id = "-1003699847586";
            $message = "ID#$hid\n<b><u>⚠️ Модуль: Продажа (Позиция-Количество)</u></b>\n<b>🧾 Договор:</b> $clientid \n\n<b>🏬 Клиент:</b> $dealer \n<b>🏭 Склад:</b> $warehouse \n<b>📦 Баркод:</b> $barcode \n<b>👨‍💻 Пользователь:</b> $user \n<b>📝 Примечание:</b> $comment \n<b>📍 ИП адрес:</b> $ip \n<b>⏱ Дата :</b> $date";
            $response = $client->request("GET", "/bot$bot_token/sendMessage", [
                "query" => [
                    "chat_id" => $chat_id,
                    "text" => $message,
                    "parse_mode" => "html"
                ]
            ]);
        }
        
        
        return response()->json(['bonus' => $item->bonus]);
    }
    
    
    public function today_send()
    {
        
        $client = new GClient([
            "base_uri" => "https://api.telegram.org",
        ]);
        
        $result_pay = "";
        
        $bugungiSotilganMahsulotlarQarzsiz = Checkout::where('checkout_tip_id', 1)->whereDate('date', today())
            ->where('total_price_debt', 0)
            ->join('checkout_details', 'checkouts.id', '=', 'checkout_details.checkout_id')
            ->select('checkout_details.product_id', DB::raw('SUM(checkout_details.qty) as total_qty'))
            ->groupBy('checkout_details.product_id')
            ->get();
             
        
        $bugungiSotilganMahsulotlarQarzBilan = Checkout::where('checkout_tip_id', 1)->whereDate('date', today())
            ->where('total_price_debt', '>', 0)
            ->join('checkout_details', 'checkouts.id', '=', 'checkout_details.checkout_id')
            ->select('checkout_details.product_id', DB::raw('SUM(checkout_details.qty) as total_qty'))
            ->groupBy('checkout_details.product_id')
            ->get();
        
        
        foreach(CashReceipt::where('status', 1)->whereDate('date', Carbon::today())->get()->groupBy('cash_receipt_type') as $pay){ 
            $result_pay .= $pay->first()->tname->name . ': ' . number_format($pay->sum('price'), 0, '.', ' ') . "\n";
        }
        
        $pay_list = $result_pay = rtrim($result_pay, "\n");
        
        $ip             = request()->ip();
        $user           = Auth::check() ? Auth::user()->name . ' Тел: ' . Auth::user()->phone : 'Auto send';
        $summa          = number_format(Checkout::where('checkout_tip_id', 1)->where('status', 1)->whereDate('date', Carbon::today())->sum('total_price'), 0, '.', ' ') ;
        $zatrat         = number_format(CashExpenditure::whereDate('date', Carbon::today())->sum('price'), 0, '.', ' ') ;
        $summa_dolg     = number_format(Checkout::where('checkout_tip_id', 1)->where('status', 1)->whereDate('date', Carbon::today())->sum('total_price_debt'), 0, '.', ' ') ;
        $qtyparts       = number_format(CheckoutDetail::join('checkouts', 'checkout_details.checkout_id', '=', 'checkouts.id')->whereDate('checkouts.date', Carbon::today())->where('checkouts.checkout_tip_id', 1)->sum(DB::raw('checkout_details.qty')), 0, '.', ' ') ;
        $vidparts       = number_format(CheckoutDetail::join('checkouts', 'checkout_details.checkout_id', '=', 'checkouts.id')->whereDate('checkouts.date', Carbon::today())->where('checkouts.checkout_tip_id', 1)->count(DB::raw('checkout_details.product_id')), 0, '.', ' ') ;
        $stock_wtm      = number_format(WarehouseStock::where('warehouse_id', 1)->where('stock', '>', 0)->sum('stock'), 0, '.', ' ');
        $stock_mtm      = number_format(WarehouseStock::where('warehouse_id', 2)->where('stock', '>', 0)->sum('stock'), 0, '.', ' ');
        $date           = Carbon::now()->format('Y-m-d H:i:s');
        $ddate           = Carbon::now()->format('d.m.Y');
        
        $bot_token = "7335500759:AAFQCsaM-8jLefkgmNEW3phJxmn_gQIBSQw";
            $chat_id = "-1003699847586";
        $message = "<b><u>🛒 $ddate кунги сотув</u></b>\n"
                    . "<b>💵 Умумий сотув суммаси:</b> $summa\n"
                    . "<b>💵 Қарзга берилган:</b> $summa_dolg\n"
                    . "<b>💵 Харажатлар:</b> $zatrat\n\n"
                    . "<b>📑 Пул тушимлари:</b>\n$pay_list\n"
                    . "<b>🛠 Эхтиёт қисмлар сони:</b> $qtyparts\n"
                    . "<b>📦 Эхтиёт қисмлар сони номи бўйича:</b> $vidparts\n\n"
                    . "<b>👨‍💻 Фойдаланувчи:</b> $user\n"
                    . "<b>📍 ИП адрес:</b> $ip\n"
                    . "<b>⏱ Сана:</b> $date\n\n";
            
        $response = $client->request("GET", "/bot$bot_token/sendMessage", [
            "query" => [
                "chat_id" => $chat_id,
                "text" => $message,
                "parse_mode" => "html"
            ]
        ]);
        
        return back();
        $message = "<b><u>🛒 Cегодняшняя продажа</u></b>\n<b>💵 Касса:</b> $summa\n<b>💵 Продажа (долг):</b> $summa_dolg \n<b>🛠 Кол-во зап.частей:</b> $qtyparts \n<b>📦 Вид зап.частей:</b> $vidparts \n\n<b>📦 Остаток на `Склад TM` :</b> $stock_wtm \n<b>📦 Остаток на `Магазин TM` :</b> $stock_mtm \n<b>👨‍💻 Пользователь:</b> $user \n<b>📍 ИП адрес:</b> $ip \n<b>⏱ Дата :</b> $date \n\n\n<b>📑 Список зачастей:</b> \n$products";
    }
    
    public function yesterday_send()
    {
        
        $client = new GClient([
            "base_uri" => "https://api.telegram.org",
        ]);
        
        $result = "";
        
        foreach(CheckoutDetail::whereDate('created_at', Carbon::yesterday())->get()->groupBy('product_id') as $saa){ 
            $result .= $saa->first()->prodid->name . ' - ' . $saa->sum('qty') . ' ' . $saa->first()->prodid->unitid->name. "\n";
        }
        
        $products = $result = rtrim($result, "\n");
        
        $ip             = request()->ip();
        $user           = Auth::user()->name . ' Тел: ' . Auth::user()->phone;
        $summa          = number_format(CashReceipt::where('status', 1)->whereDate('date', Carbon::yesterday())->sum('price'), 0, '.', ' ') ;
        $summa_dolg     = number_format(Checkout::where('checkout_tip_id', 1)->where('status', 1)->whereDate('date', Carbon::yesterday())->sum('total_price_debt'), 0, '.', ' ') ;
        $qtyparts       = number_format(CheckoutDetail::join('checkouts', 'checkout_details.checkout_id', '=', 'checkouts.id')->whereDate('checkouts.date', Carbon::yesterday())->where('checkouts.checkout_tip_id', 1)->sum(DB::raw('checkout_details.qty')), 0, '.', ' ') ;
        $vidparts       = number_format(CheckoutDetail::join('checkouts', 'checkout_details.checkout_id', '=', 'checkouts.id')->whereDate('checkouts.date', Carbon::yesterday())->where('checkouts.checkout_tip_id', 1)->count(DB::raw('checkout_details.product_id')), 0, '.', ' ') ;
        $stock_wtm      = number_format(WarehouseStock::where('warehouse_id', 1)->where('stock', '>', 0)->sum('stock'), 0, '.', ' ');
        $stock_mtm      = number_format(WarehouseStock::where('warehouse_id', 2)->where('stock', '>', 0)->sum('stock'), 0, '.', ' ');
        $date           = Carbon::yesterday();
        $ddate          = Carbon::yesterday()->format('d.m.Y');
        
        $bot_token = "7335500759:AAFQCsaM-8jLefkgmNEW3phJxmn_gQIBSQw";
        $chat_id = "-1003699847586";
        $message = "<b><u>🛒 Продажа на $ddate</u></b>\n<b>💵 Касса:</b> $summa\n<b>💵 Продажа (долг):</b> $summa_dolg \n<b>🛠 Кол-во зап.частей:</b> $qtyparts \n<b>📦 Вид зап.частей:</b> $vidparts \n\n<b>👨‍💻 Пользователь:</b> $user \n<b>📍 ИП адрес:</b> $ip \n<b>⏱ Дата :</b> $date \n\n\n<b>📑 Список зачастей:</b> \n$products";
        $response = $client->request("GET", "/bot$bot_token/sendMessage", [
            "query" => [
                "chat_id" => $chat_id,
                "text" => $message,
                "parse_mode" => "html"
            ]
        ]);
        
        return back();
        $message = "<b><u>🛒 Вчерашняя продажа</u></b>\n<b>💵 Продажа:</b> $summa\n<b>💵 Продажа (долг):</b> $summa_dolg \n<b>🛠 Кол-во зап.частей:</b> $qtyparts \n<b>📦 Вид зап.частей:</b> $vidparts \n\n<b>📦 Остаток на `Склад TM` :</b> $stock_wtm \n<b>📦 Остаток на `Магазин TM` :</b> $stock_mtm \n<b>👨‍💻 Пользователь:</b> $user \n<b>📍 ИП адрес:</b> $ip \n<b>⏱ Дата :</b> $date \n\n\n<b>📑 Список зачастей:</b> \n$products";
    }
    
    public function send_success()
    {
        $datacid = request()->datacid; //ID Keladi
        
        $item = Checkout::findOrFail($datacid);
        $data['shipment_status'] = 1;
        $item->update($data);
        
        return response()->json(['status' => 1]);
    }
    
    public function select_warehouse()
    {
        $brid = request()->brid; //qty Keladi
        $cid = request()->cid; //ID keladi
        $item = Checkout::findOrFail($cid);
        $item->update(['warehouse_id' => $brid]);
        return response()->json(['status' => 'success']);
    }
    
    public function select_checkout_type()
    {
        $typeid = request()->typeid; //qty Keladi
        $cid = request()->cid; //ID keladi
        $item = Checkout::findOrFail($cid);
        $item->update(['checkout_tip_id' => $typeid]);
        return response()->json(['status' => 'success']);
    }
    
    public function select_checkout_date()
    {
        $typeid = request()->typeid; //qty Keladi
        $dat = Carbon::parse($typeid)->format('Y-m-d');
        $cid = request()->cid; //ID keladi
        $item = Checkout::findOrFail($cid);
        $item->update(['date' => $dat]);
        return response()->json(['status' => 'success']);
    }
    
    public function client_change()
    {
        $clientid = request()->clientid; //qty Keladi
        $cid = request()->cid; //ID keladi
        $item = Checkout::findOrFail($cid);
        $item->update(['client_id' => $clientid]);
        
        if(CashReceipt::where('checkout_id', $cid)->count()){
            foreach(CashReceipt::where('checkout_id', $cid)->get() as $crep){
                    $crep->update(['client_id' => $clientid]);
            }
        }
        return response()->json(['status' => 'success']);
    }
    
    public function full_price()
    {
        $gid = request()->gid; //ID keladi
        $itemid = request()->itemid; //ID keladi
        $item = Checkout::findOrFail($itemid);
        $pr = Currency::where('type_id', $gid)->orderBy('id', 'desc')->first()->price;
        
        return response()->json(['full_price' => $item->details()->sum('total_price'), 'curr' => $pr]);
    }

    //API Price
    public function price()
    {
        $pbrid = request()->pbrid; //Price keladi
        $pbrid = str_replace(' ', '', $pbrid);
        $pcid = request()->pcid; //ID keladi
        $item = CheckoutDetail::findOrFail($pcid);
        $oldprice = $item->price;
        $item->update(['price' => $pbrid, 'total_price' => ($item->qty * $pbrid)]);
        
        $citem = Checkout::findOrFail($item->checkout_id);
        
        if($citem->status == 1){
            $hdata['dealer_id'] = $citem->dealer_id;
            $hdata['user_id'] = Auth::id();
            $hdata['code'] = Str::uuid();
            $hdata['name'] = 9;
            $hdata['database'] = 'checkout_details';
            $hdata['ip_address'] = request()->ip();
            $hdata['agent'] = request()->server('HTTP_USER_AGENT');
            $hdata['comment'] = 'В контракте "' . ($citem->number_work ? '№ ' . $citem->number_work : 'Черновик #' . $citem->id) . '" от ' . $citem->date . ' изменилось цена запчастей «<u>' . $item->prodid->name . '</u>» с ' . $oldprice . ' на ' . $pbrid;
            
            $history = History::create($hdata);
            
            $client = new GClient([
                "base_uri" => "https://api.telegram.org",
            ]);
                    
            $clientid       = $citem->number_work ? $citem->number_work : 'Черновик ID#' . $citem->id;
            $ip             = request()->ip();
            $dealer         = $citem->managerid->name;
            $warehouse      = $item->warehouseid?->name;
            $barcode        = $item->prodid->barcode;
            $hid            = $history->id;
            $user           = Auth::user()->name . ' Тел: ' . Auth::user()->phone;
            $comment        = $hdata['comment'];
            $date           = Carbon::now()->format('Y-m-d H:i:s');
            
            $bot_token = "7335500759:AAFQCsaM-8jLefkgmNEW3phJxmn_gQIBSQw";
            $chat_id = "-1003699847586";
            $message = "ID#$hid\n<b><u>⚠️ Модуль: Продажа (Позиция-Цена)</u></b>\n<b>🧾 Договор:</b> $clientid \n\n<b>🏬 Клиент:</b> $dealer \n<b>🏭 Склад:</b> $warehouse \n<b>📦 Баркод:</b> $barcode \n<b>👨‍💻 Пользователь:</b> $user \n<b>📝 Примечание:</b> $comment \n<b>📍 ИП адрес:</b> $ip \n<b>⏱ Дата :</b> $date";
            $response = $client->request("GET", "/bot$bot_token/sendMessage", [
                "query" => [
                    "chat_id" => $chat_id,
                    "text" => $message,
                    "parse_mode" => "html"
                ]
            ]);
        }
        
        $citem->update(['total_price' => $citem->details()->sum('total_price'), 'total_price_debt' => $citem->total_price_payme ? ($citem->details()->sum('total_price') - $citem->total_price_payme) : $citem->details()->sum('total_price')]);
        $onetotalp = number_format($item->total_price, 2, '.', ' ');
        $ctotalp = number_format($citem->details()->sum('total_price'), 2, '.', ' ');
        return response()->json(['one_total_price' => $onetotalp, 'total_price' => $ctotalp, 'price' => $item->price]);
    }
    
    public function checkout_discount(Request $request)
    {
        $checkout = Checkout::find($request->pcid);
        if (!$checkout) {
            return response()->json(['status' => 'error']);
        }
    
        $discount = (float)$request->pbrid;
        if ($discount > 100) $discount = 100;
        if ($discount < 0) $discount = 0;
    
        $checkout->discount = $discount;
        $baseTotalSum = 0;
        $updatedDetails = [];
    
        foreach ($checkout->details()->get() as $detail) {
            // 1. Agar avval umuman skidka berilmagan bo'lsa (org_price null), joriy price'ni org_price'ga yozib qo'yamiz
            if (is_null($detail->org_price)) {
                $detail->org_price = $detail->price;
            }
    
            // 2. Asl narx (org_price) orqali yangi narxni hisoblaymiz
            if ($discount > 0) {
                $detail->price = $detail->org_price - ($detail->org_price * ($discount / 100));
            } else {
                // Agar skidka 0 yozilsa, asl narxiga qaytaramiz
                $detail->price = $detail->org_price;
            }
    
            // 3. Yangi narxni miqdorga ko'paytirib total_price'ni chiqaramiz
            $detail->total_price = $detail->price * $detail->qty;
            $detail->save();
    
            // UI predprosmotr uchun asosiy (skidkasiz) summani hisoblab ketamiz
            $baseTotalSum += $detail->org_price * $detail->qty;
    
            // JS dagi jadvalni qayta yuklamasdan o'zgartirish uchun kerakli ma'lumotlarni yiqqamiz
            $updatedDetails[] = [
                'id' => $detail->id,
                'price' => number_format($detail->price, 2, '.', ''),
                'total_price' => number_format($detail->total_price, 2, '.', ' ')
            ];
        }
    
        // 4. Checkoutni umumiy summasini saqlash
        $newTotal = $checkout->details()->sum('total_price');
        $checkout->total_price = $newTotal;
        
        // Debt (Qarz) ustuni hisobini to'g'rilash
        if ($checkout->total_price_payme) {
            $checkout->total_price_debt = $newTotal - $checkout->total_price_payme;
        } else {
            $checkout->total_price_debt = $newTotal;
        }
        
        $checkout->save();
    
        return response()->json([
            'status'      => 'success',
            'discount'    => $discount,
            'total_price' => number_format($newTotal, 2, '.', ' '),
            'base_total'  => $baseTotalSum,
            'details'     => $updatedDetails
        ]);
    }

    public function checkout_commission_scheme(Request $request)
    {
        $schemes = [
            'special' => ['label' => 'Spes', 'kpi' => 0, 'agent' => 8, 'venox' => 0],
            'contract' => ['label' => 'Shartnoma', 'kpi' => 5, 'agent' => 8, 'venox' => 25],
            'venox_10' => ['label' => 'Venox bonus 10%', 'kpi' => 5, 'agent' => 8, 'venox' => 10],
            'venox_15' => ['label' => 'Venox bonus 15%', 'kpi' => 5, 'agent' => 8, 'venox' => 15],
            'venox_20' => ['label' => 'Venox bonus 20%', 'kpi' => 5, 'agent' => 8, 'venox' => 20],
            'venox_25' => ['label' => 'Venox bonus 25%', 'kpi' => 5, 'agent' => 8, 'venox' => 25],
        ];

        $validated = $request->validate([
            'checkout_id' => ['required', 'integer', 'exists:checkouts,id'],
            'scheme' => ['required', 'string', 'in:' . implode(',', array_keys($schemes))],
        ]);

        $checkout = Checkout::findOrFail($validated['checkout_id']);
        $scheme = $schemes[$validated['scheme']];
        $factoryPercent = 100 - $scheme['kpi'] - $scheme['agent'] - $scheme['venox'];

        $checkout->update([
            'commission_scheme' => $validated['scheme'],
            'kpi_percent' => $scheme['kpi'],
            'agent_percent' => $scheme['agent'],
            'venox_bonus_percent' => $scheme['venox'],
        ]);

        return response()->json([
            'status' => 'success',
            'label' => $scheme['label'],
            'kpi_percent' => $scheme['kpi'],
            'agent_percent' => $scheme['agent'],
            'venox_bonus_percent' => $scheme['venox'],
            'factory_percent' => $factoryPercent,
        ]);
    }
    
    public function checkout_reference_change()
    {
        $reference = request()->reference; //Price keladi
        $cid = request()->cid; //ID keladi
        $item = Checkout::findOrFail($cid);
        $item->update(['reference' => $reference]);
        return response()->json(['status' => 'success']);
    }
    
    public function price_total()
    {
        $pbrid = request()->pbrid; //Price keladi
        $pbrid = str_replace(' ', '', $pbrid);
        $pcid = request()->pcid; //ID keladi
        $item = Checkout::findOrFail($pcid);
        $item->update(['total_price' => $pbrid]);
        return response()->json(['status' => 'success']);
    }
    
    public function price_total_detail()
    {
        $pbrid = request()->pbrid; //Price keladi
        $pbrid = str_replace(' ', '', $pbrid);
        $pcid = request()->pcid; //ID keladi
        $item = CheckoutDetail::findOrFail($pcid);
        $item->update(['total_price' => $pbrid, 'price' => ($pbrid / $item->qty)]);
        $citem = Checkout::findOrFail($item->checkout_id);
        $citem->update(['total_price' => $citem->details()->sum('total_price'), 'total_price_debt' => $citem->total_price_payme ? ($citem->details()->sum('total_price') - $citem->total_price_payme) : $citem->details()->sum('total_price')]);
        $ctotalp = number_format($citem->details()->sum('total_price'), 2, '.', ' ');
        $price = number_format($item->price, 2, '.', ' ');
        return response()->json(['total_price' => $ctotalp, 'price' => $price]);
    }
    
    public function tan_price()
    {
        $pbrid = request()->pbrid; //Price keladi
        $pbrid = str_replace(' ', '', $pbrid);
        $pcid = request()->pcid; //ID keladi
        $item = CheckoutDetail::findOrFail($pcid);
        
        $item->update(['tan_price' => $pbrid, 'total_tan_price' => $item->qty * $pbrid]);
        
        $data = CheckoutDetail::where('product_id', $item->product_id)->get();
        foreach($data as $ditem){
            $ditem->update(['tan_price' => $pbrid]);
        }
        
        $pitem = Product::findOrFail($item->product_id);
        
        $pitem->update(['tan_price' => $pbrid]);
        
        
        return response()->json(['status' => 'success']);
    }
    
    public function checkout_currency_type_change(Request $request)
    {
        $checkout = Checkout::find($request->cid);
        if (!$checkout) {
            return response()->json(['status' => 'error']);
        }
    
        $currencyType = $request->currency_type;
    
        // Eng oxirgi (eng yangi) valyuta kursini olish (id bo'yicha eng kattasi)
        $currency = Currency::where('type_id', $currencyType)->orderBy('id', 'desc')->first();
        
        // Agar topilmasa (masalan asosiy pul sum bo'lsa), kursni 1 deb olamiz
        $price = $currency ? $currency->price : 1;
    
        // Asosiy chekda saqlash
        $checkout->currency_type = $currencyType;
        $checkout->currency_type_price = $price;
        $checkout->save();
    
        // Chekka tegishli BARCHA tovarlar (details) da ham valyuta turi va kursni o'zgartirish
        $checkout->details()->update([
            'currency_type'       => $currencyType,
            'currency_type_price' => $price
        ]);
    
        return response()->json([
            'status' => 'success',
            'price'  => $price
        ]);
    }
    
    public function checkout_currency_price_change(Request $request)
    {
        $checkout = Checkout::find($request->cid);
        if (!$checkout) {
            return response()->json(['status' => 'error']);
        }
    
        $price = (float) $request->price;
    
        // Asosiy chekda narxni saqlash
        $checkout->currency_type_price = $price;
        $checkout->save();
    
        // Chekka tegishli BARCHA tovarlar (details) da ham valyuta kursni o'zgartirish
        $checkout->details()->update([
            'currency_type_price' => $price
        ]);
    
        return response()->json([
            'status' => 'success',
            'price'  => $price
        ]);
    }

    public function currency()
    {
        $currency = request()->currency;
        $curcid = request()->curcid;
        $item = CheckoutDetail::findOrFail($curcid);
        $item->update(['currency_type' => $currency, 'currency_type_price' => CurrencyType::find($currency)->currencyid->first()->price]);
        return response()->json(['price' => $item->price]);
    }
    
    public function currencies()
    {
        $currency = request()->currency;
        $curcid = request()->curcid;
        $item = Checkout::findOrFail($curcid);
        $item->update(['currency_type' => $currency, 'currency_type_price' => CurrencyType::find($currency)->currencyid->first()->price]);
        return response()->json(['status' => 'success']);
    }
    
    public function save(Request $request, $id = null)
    {
        // 1. Qidirilayotgan qiymatni aniqlash
        $chprid = $request->product_id ?: $request->modal_product;
        
        if(Auth::user()->hasAnyRole('admin')){
            $warehouseId = $request->warehouse_id;
        } else {
            $warehouseId = Auth::user()->warehouse_id;
        }
        
        // 2. Mahsulotni qidirish
        $product = Product::where('status', 1)
            ->where(function ($query) use ($chprid) {
                $query->where('barcode', $chprid)
                      ->orWhere('barcode', '0' . $chprid)
                      ->orWhere('name', $chprid)
                      ->orWhere('fullname', $chprid);
            })->first();
    
        if (!$product) {
            return back()->with('error', trans('backend.no_product'));
        }
    
        // 3. Skladda borligini tekshirish
        $stock = WarehouseStock::where('warehouse_id', $warehouseId)
            ->where('product_id', $product->id)
            ->first();
    
        if (!$stock || $stock->stock <= 0) {
            return back()->with('error', "Skladda ushbu mahsulot qolmagan!");
        }
    
        return DB::transaction(function () use ($request, $id, $product, $stock, $warehouseId) {
            $userId = Auth::id();
            $dealerId = Auth::user()->hasAnyRole('dealer_admin') ? Auth::user()->dealer_id : 1;
    
            // 4. Checkout (Header) ma'lumotlarini tayyorlash
            $data = [
                'date' => Carbon::parse($request->date)->format('Y-m-d'),
                'reference' => $request->reference,
                'type_id' => 1,
                'checkout_tip_id' => $request->checkout_tip_id ?: 1,
                'warehouse_id' => $warehouseId,
                'client_id' => $request->client_id,
                'dealer_id' => $dealerId,
            ];
    
            // Valyuta va kurs uchun o'zgaruvchilar
            $currencyType = 1;
            $currencyPrice = 1;
    
            if ($id) {
                // AGAR CHEK AVVAL YARATILGAN BO'LSA
                $item = Checkout::where('code', $id)->firstOrFail();
                
                // Eng muhim joyi: Eski chekdagi valyuta va kursni olamiz (bazadagi eng yangisini EMAS!)
                $currencyType = $item->currency_type;
                $currencyPrice = $item->currency_type_price;
    
                $item->update($data); // Asosiy datalarni yangilaymiz (valyuta kursiga tegmaymiz)
                session()->flash('success', trans('backend.post_update'));
            } else {
                // AGAR YANGI CHEK YARATILAYOTGAN BO'LSA
                // Bazadan aynan shu tipdagi eng oxirgi kursni qidiramiz
                $currencyType = $request->currency_type ?? 1;
                $latestCurrency = \App\Models\Currency::where('type_id', $currencyType)->orderBy('id', 'desc')->first();
                $currencyPrice = $latestCurrency ? $latestCurrency->price : 1;
    
                $data += [
                    'transaction' => date('Y') . time(),
                    'manager_id' => $request->manager_id ?: $userId,
                    'user_id' => $userId,
                    'code' => (string) Str::uuid(),
                    // Yangi topilgan kursni va tipni yozamiz
                    'currency_type' => $currencyType,
                    'currency_type_price' => $currencyPrice,
                ];
                $item = Checkout::create($data);
                
                // Queue hisoblash
                $queue = Checkout::whereDate('date', Carbon::today())->max('queue') + 1;
                $item->update(['queue' => $queue]);
                session()->flash('success', trans('backend.post_create'));
            }
    
            // 5. CheckoutDetail (Mahsulot qo'shish) qismi
            $exists = CheckoutDetail::where('checkout_id', $item->id)
                ->where('product_id', $product->id)
                ->exists();
    
            if ($exists) {
                session()->flash('error', trans('backend.old_added_product'));
            } else {
                // Narxni hisoblash (Skidkani inobatga olgan holda)
                $originalPrice = $product->price;
                $finalPrice = $originalPrice;
                $orgPrice = null;
    
                if ($item->discount > 0) {
                    $orgPrice = $originalPrice;
                    $finalPrice = $originalPrice - ($originalPrice * ($item->discount / 100));
                }
    
                CheckoutDetail::create([
                    'checkout_id'   => $item->id,
                    'warehouse_id'  => $item->warehouse_id,
                    'product_id'    => $product->id,
                    'category_id'   => $product->category_id,
                    'qty'           => 1,
                    'price'         => $finalPrice,
                    'org_price'     => $orgPrice,
                    'total_price'   => $finalPrice,
                    'tan_price'     => $product->tan_price,
                    'total_tan_price' => $product->tan_price,
                    'unit_id'       => $product->unit_id,
                    'user_id'       => $userId,
                    'dealer_id'     => $dealerId,
                    
                    // BU YERDA YUQORIDA ANIQLANGAN KURS VA TURI YOZILADI
                    // (Yangi chek bo'lsa yangi kurs, eski chek bo'lsa eski kurs avtomat tushadi)
                    'currency_type' => $currencyType,
                    'currency_type_price' => $currencyPrice,
                    
                    'code'          => (string) Str::uuid(),
                ]);
    
                // 6. Sklad miqdorini kamaytirish 
                $stock->decrement('stock', 1); 
            }
    
            // 7. Checkout total narxlarini yangilash
            $totalSum = $item->details()->sum('total_price');
            $item->update([
                'total_price' => $totalSum,
                'total_price_debt' => $item->total_price_payme ? ($totalSum - $item->total_price_payme) : $totalSum
            ]);
    
            return redirect()->route('checkout_form', ['id' => $item->code]);
        });
    }
    public function save_old(Request $request, $id = null)
    {
        // 1. Qidirilayotgan qiymatni aniqlash
        $chprid = $request->product_id ?: $request->modal_product;
        
        if(Auth::user()->hasAnyRole('admin')){
            $warehouseId = $request->warehouse_id;
        } else {
            $warehouseId = Auth::user()->warehouse_id;
        }
        
        // 2. Mahsulotni qidirish (Guruhlangan qidiruv)
        $product = Product::where('status', 1)
            ->where(function ($query) use ($chprid) {
                $query->where('barcode', $chprid)
                      ->orWhere('barcode', '0' . $chprid)
                      ->orWhere('name', $chprid)
                      ->orWhere('fullname', $chprid);
            })->first();
    
        if (!$product) {
            return back()->with('error', trans('backend.no_product'));
        }
    
        // 3. Skladda borligini tekshirish (WarehouseStock check)
        $stock = WarehouseStock::where('warehouse_id', $warehouseId)
            ->where('product_id', $product->id)
            ->first();
    
        if (!$stock || $stock->stock <= 0) {
            return back()->with('error', "Skladda ushbu mahsulot qolmagan!");
        }
    
        return DB::transaction(function () use ($request, $id, $product, $stock, $warehouseId) {
            $userId = Auth::id();
            $dealerId = Auth::user()->hasAnyRole('dealer_admin') ? Auth::user()->dealer_id : 1;
    
            // 4. Checkout (Header) ma'lumotlarini tayyorlash
            $data = [
                'date' => Carbon::parse($request->date)->format('Y-m-d'),
                'reference' => $request->reference,
                'type_id' => 1,
                'checkout_tip_id' => $request->checkout_tip_id ?: 1,
                'warehouse_id' => $warehouseId,
                'client_id' => $request->client_id,
                'dealer_id' => $dealerId,
            ];
    
            if ($id) {
                $item = Checkout::where('code', $id)->firstOrFail();
                $item->update($data);
                session()->flash('success', trans('backend.post_update'));
            } else {
                $data += [
                    'transaction' => date('Y') . time(),
                    'manager_id' => $request->manager_id ?: $userId,
                    'user_id' => $userId,
                    'code' => (string) Str::uuid(),
                    'currency_type' => 1,
                    'currency_type_price' => 1,
                ];
                $item = Checkout::create($data);
                
                // Queue hisoblash (Osonroq yo'li)
                $queue = Checkout::whereDate('date', Carbon::today())->max('queue') + 1;
                $item->update(['queue' => $queue]);
                session()->flash('success', trans('backend.post_create'));
            }
    
            // 5. CheckoutDetail (Mahsulot qo'shish) qismi
            $exists = CheckoutDetail::where('checkout_id', $item->id)
                ->where('product_id', $product->id)
                ->exists();
            
            if ($exists) {
                session()->flash('error', trans('backend.old_added_product'));
            } else {
                // Narxni hisoblash (SKIDKA BORLIGINI TEKSHIRAMIZ)
                $originalPrice = $product->price;
                $finalPrice = $originalPrice;
                $orgPrice = null;
            
                // Agar joriy chekda skidka mavjud bo'lsa, org_price'ni saqlab yangi narxni hisoblaymiz
                if ($item->discount > 0) {
                    $orgPrice = $originalPrice;
                    $finalPrice = $originalPrice - ($originalPrice * ($item->discount / 100));
                }
            
                CheckoutDetail::create([
                    'checkout_id'   => $item->id,
                    'warehouse_id'  => $item->warehouse_id,
                    'product_id'    => $product->id,
                    'category_id'   => $product->category_id,
                    'qty'           => 1,
                    'price'         => $finalPrice,
                    'org_price'     => $orgPrice, // <- Yangi qo'shilgan qator!
                    'total_price'   => $finalPrice,
                    'tan_price'     => $product->tan_price,
                    'total_tan_price' => $product->tan_price,
                    'unit_id'       => $product->unit_id,
                    'user_id'       => $userId,
                    'dealer_id'     => $dealerId,
                    'currency_type' => 1,
                    'currency_type_price' => 1,
                    'code'          => (string) Str::uuid(),
                ]);
            
                // 6. Sklad miqdorini kamaytirish
                $stock->decrement('stock', 1); 
            }
    
            // 7. Checkout total narxlarini yangilash
            $totalSum = $item->details()->sum('total_price');
            $item->update([
                'total_price' => $totalSum,
                'total_price_debt' => $item->total_price_payme ? ($totalSum - $item->total_price_payme) : $totalSum
            ]);
    
            return redirect()->route('checkout_form', ['id' => $item->code]);
        });
    }

    public function delete(Request $request, $id = null)
    {
        $item = CheckoutDetail::where('code',$id)->first();
        
        $hdata['dealer_id'] = $item->dealer_id;
        $hdata['user_id'] = Auth::id();
        $hdata['code'] = Str::uuid();
        $hdata['name'] = 6;
        $hdata['database'] = 'checkout_details';
        $hdata['ip_address'] = $request->ip();
        $hdata['agent'] = $request->server('HTTP_USER_AGENT');
        $hdata['comment'] = 'Запчасть "<u>' . $item->prodid->name . '</u>" исключена из договора "' . ($item->checkid->number_work ? '№ ' . $item->checkid->number_work : 'Черновик #' . $item->checkid->id) . '" от ' . $item->checkid->date;
        
        $citem = Checkout::find($item->checkout_id);
        
        if ($citem->status == 1 && !Auth::user()->hasRole('admin')) {
            $request->session()->flash('error', 'Ўчириш иимкони ёқ хужжат ёпилган');
            return back();
        }
        
        $history = History::create($hdata);
        
        $client = new GClient([
            "base_uri" => "https://api.telegram.org",
        ]);
                
        $clientid       = $item->checkid->number_work ? $item->checkid->number_work : 'Черновик ID#' . $item->checkid->id;
        $ip             = $request->ip();
        $dealer         = $item->checkid->supid->name;
        $warehouse      = $item->warehouseid?->name;
        $qty            = $item->qty;
        $barcode        = $item->prodid->barcode;
        $hid            = $history->id;
        $user           = Auth::user()->name . ' Тел: ' . Auth::user()->phone;
        $comment        = $hdata['comment'];
        $date           = Carbon::now()->format('Y-m-d H:i:s');
        
        $bot_token = "7335500759:AAFQCsaM-8jLefkgmNEW3phJxmn_gQIBSQw";
        $chat_id = "-1003699847586";
        $message =
"ID #$hid\n".
"<b><u>⚠️ Модуль: Продажа (Позиция)</u></b>\n".
"<b>🧾 Договор:</b> $clientid\n".
"<b>🏬 Клиент:</b> $dealer\n".
"<b>🏭 Склад:</b> $warehouse\n".
"<b>📦 Баркод:</b> $barcode\n".
"<b>🧮 Кол-во:</b> $qty\n".
"<b>👨‍💻 Пользователь:</b> $user\n".
"<b>📝 Примечание:</b> $comment\n".
"<b>📍 IP адрес:</b> $ip\n".
"<b>⏱ Дата:</b> $date";
                    
        $response = $client->request("GET", "/bot$bot_token/sendMessage", [
            "query" => [
                "chat_id" => $chat_id,
                "text" => $message,
                "parse_mode" => "html"
            ]
        ]);
        
        if(WarehouseStock::where('warehouse_id', $item->warehouse_id)->where('product_id', $item->product_id)->count()){
            $wsid = WarehouseStock::where('warehouse_id', $item->warehouse_id)->where('product_id', $item->product_id)->first();
            $st = ($wsid->stock + $item->qty);
            $wsid->update([
                'stock' => $st,
                'checkin_price' => $item->prodid->checkindetails()->max('price'),
                'checkout_price' => $item->prodid->price ? $item->prodid->price : $item->prodid->checkoutdetails()->max('price'),
                'checkin_total_price' => $st > 0 ? ($item->prodid->checkindetails()->max('price') * $st) : 0,
                'checkout_total_price' => $st > 0 ? (($item->prodid->price ? $item->prodid->price : $item->prodid->checkoutdetails()->max('price')) * $st) : 0
                
                ]);
        } else {
            WarehouseStock::create(['warehouse_id' => $item->warehouse_id, 'product_id' => $item->product_id, 'stock' => (0 - $item->qty)]);
        }
        $item->delete();
        $citem->update(['total_price' => $citem->details()->sum('total_price'), 'total_price_debt' => $citem->total_price_payme ? ($citem->details()->sum('total_price') - $citem->total_price_payme) : $citem->details()->sum('total_price')]);
        return back();
    }
    
    public function one_qty(Request $request, $id = null)
    {
        $item = CheckoutDetail::where('code',$id)->first();
        $pid = Product::find($item->product_id);
        $oldqty = $item->qty;
        $pr = $pid->currency_type != 1 ? ($pid->currencyid->currencyid->first()->price * $pid->price) : $pid->price;
        $item->update(['qty' => $request->qty_one, 'price' => $pr, 'total_price' => ($pr * $request->qty_one)]);
        
        if($oldqty != $item->qty){
            
            if(WarehouseStock::where('warehouse_id', $item->warehouse_id)->where('product_id', $pid->id)->count()){
                $wsid = WarehouseStock::where('warehouse_id', $item->warehouse_id)->where('product_id', $pid->id)->first();
                if($oldqty < $item->qty){
                    $stc = ($wsid->stock - ($item->qty - $oldqty));
                } else {
                    $stc = ($wsid->stock + ($oldqty - $item->qty));
                }
                $wsid->update([
                    'stock' => $stc,
                    'checkin_price' => $pid->checkindetails()->max('price'),
                    'checkout_price' => $pid->price ? $pid->price : $pid->checkoutdetails()->max('price'),
                    'checkin_total_price' => $stc > 0 ? ($pid->checkindetails()->max('price') * $stc) : 0,
                    'checkout_total_price' => $stc > 0 ? (($pid->price ? $pid->price : $pid->checkoutdetails()->max('price')) * $stc) : 0
                ]);
            } else {
                WarehouseStock::create(['warehouse_id' => $item->warehouse_id, 'product_id' => $pid->id, 'stock' => (0 - $item->qty)]);
            }
            
        }
        $citem = Checkout::find($item->checkout_id);
        $citem->update(['total_price' => $citem->details()->sum('total_price'), 'total_price_debt' => $citem->total_price_payme ? ($citem->details()->sum('total_price') - $citem->total_price_payme) : $citem->details()->sum('total_price')]);
        return back();
    }
    
    public function done_status($id = null, $page = null, $fromdate = null, $todate = null, $manager = null)
    {
        $item = Checkout::where('code', $id)->firstOrFail();
    
        if (is_null($item->number_order)) {
            $year = now()->year;
    
            $lastNumber = Checkout::whereYear('date', $year)->max('number_order');
            $nextNumber = $lastNumber
                ? Str::padLeft($lastNumber + 1, 6, '0')
                : '000001';
    
            $item->update([
                'transaction'  => $item->transaction ?? $year . time(),
                'status'       => 1,
                'step'         => 2,
                'number_order' => $nextNumber,
                'number_work'  => now()->format('y') . $nextNumber,
            ]);
        }
    
        // hamma detail'larni yopamiz
        $item->details()->update(['status' => 1]);
    
        // redirect
        if ($fromdate) {
            return redirect()->to(
                '/checkout_filter?' . http_build_query([
                    'fromdate' => $fromdate,
                    'todate'   => $todate,
                    'manager'  => $manager,
                    'page'     => $page,
                ])
            );
        }
    
        if ($page) {
            return redirect()->to('/checkouts?page=' . $page);
        }
    
        return redirect()->route('checkouts_index');
    }

    public function payment_status($id = null)
    {
        $item = Checkout::where('code',$id)->first();
        $data['status'] = 1;
        $data['step'] = 3;
        $item->update($data);
        
        foreach($item->details()->get() as $det){
            $det->update(['status' => 1]);
        }
        $cdata['code'] = Str::uuid();
        $cdata['user_id'] = Auth::id();
        $cdata['price'] = $item->details()->sum('total_price');
        if($request->date){
            $cdata['date'] = Carbon::parse($request->date)->format('Y-m-d');
        } else {
            $cdata['date'] = Carbon::now()->format('Y-m-d');
        }
        $cdata['checkout_id'] = $item->id;
        $cdata['client_id'] = $item->client_id;
        $cdata['currency_type'] = 1;
        $cdata['currency'] = 1;
        
        CashReceipt::create($cdata);
            
        return redirect()->action('Backend\CheckoutController@index');
    }

    public function send_status($id = null)
    {
        $item = Checkout::where('code',$id)->first();
        $data['shipment_status'] = 1;
        $item->update($data);
        return back();
    }
    
    public function cancel_status($id = null)
    {
        $item = Checkout::where('code',$id)->first();
        $item->update(['status' => 2]);
        foreach($item->details as $det){
            $det->update(['status' => 2]);
        }
        return back();
    }
    
   public function delete_checkout(Request $request, $id = null)
    {
        $item = Checkout::where('code', $id)->first();
    
        if (!$item) {
            return back();
        }
    
        if ($item->details()->exists()) {
            return back()->with('error', trans('backend.checkout_count'));
        }
    
        if ($item->payments()->where('status', 1)->exists()) {
            return back()->with('error', 'To\'langan summani bekor qiling');
        }
    
        if ($item->status == 1 && !auth()->user()->hasRole('admin')) {
            return back()->with('error', 'Ўчириш имкони йўқ, ҳужжат ёпилган');
        }
    
        $item->delete();
    
        return back();
    }

    public function search(Request $request)
    { 
        
        $keyword = $request->input('search');
        $managers = User::role('sale')->get();
        $data = Checkout::where(function ($query) use($keyword) {
                $query->where('transaction', $keyword);
              })
        ->paginate(100);
         
        $types = CashReceiptType::where('status', 1)->get();
        $keyword = NULL; 
        $shipment       = NULL;
        $finish         = NULL;
        $selmanager     = NULL;
        $fromdate       = Carbon::parse('21.02.2024')->format('d.m.Y');
        $todate         = Carbon::now()->format('d.m.Y');
        $clientselect = NULL;
        $draft = NULL;
        $sdata = NULL;

        return view('backend.checkouts.index', compact('data', 'keyword', 'types', 'managers', 'shipment', 'finish', 'selmanager', 'fromdate', 'todate', 'clientselect', 'draft','sdata'));
    }

    public function check($id = null)
    {
        $item = null;
        if($id) {
            $item = Checkout::where('code', $id)->first();
        }
        return view('backend.checkouts.check', compact('item'));
    }

    public function print_doc($id = null, $view = null)
    {
        $comp = Setting::all();
        $item = null;
        if($id) {
            $item = Checkout::where('code', $id)->first();
        }
        
        if(Setting::where('atribute', 'document_type')->first()->value == 1){
            return view('backend.checkouts.print_doc_old', compact('item', 'comp', 'view'));
        }
        return view('backend.checkouts.print_doc', compact('item', 'comp', 'view'));
    }

    public function report_filter($id = null)
    {
        $managers = User::where('status', 1)->role('sale')->get();
        return view('backend.checkouts.report_filter_all', compact('managers'));
    }

    public function report_print_filter(Request $request)
    {
        $selmanager     = $request->manager_id;
        $fromdate       = $request->fromdate;
        $todate         = $request->todate;
        $result = Checkout::query()->whereBetween('created_at', [Carbon::parse($fromdate)->startOfDay()->format('Y-m-d H:i:s'), Carbon::parse($todate)->endOfDay()->format('Y-m-d H:i:s')])->orderBy('date', 'asc');
        
        if($selmanager != 'all'){
            $result = $result->where('manager_id', $selmanager);
        }
        
        $data = $result->get();
        
        return view('backend.checkouts.report_print_all', compact('data' ,'fromdate', 'todate'));
        
        if($shipment){
            $result = $result->where('shipment_status', 1);
        }
        
        if($finish){
            $result = $result->where('status', 1);
        }
    }

    public function day_filter($id = null)
    {
        $managers = User::where('status', 1)->role('sale')->get();
         if(Auth::user()->hasAnyRole('admin')){
            $clients = Client::orderBy('id', 'desc')->where('status', 1)->get();
        } else {
            $clients = Client::where('user_id', Auth::id())->where('status', 1)->get(); 
        } 
        $types = CashReceiptType::where('status', 1)->get();
        $chtypes = CheckType::where('status', 1)->get();
        return view('backend.checkouts.day_filter_all', compact('managers', 'clients', 'types', 'chtypes'));
    }

    public function day_print_filter(Request $request)
    {
        $selmanager     = $request->manager_id;
        $selclient      = $request->client_id;
        $fromdate       = $request->fromdate;
        $todate         = $request->todate;
        $checkouttip    = $request->checkout_tip_id;
        $ch_tip_id      = $request->ch_tip_id;
        $result = Checkout::query()->whereBetween('date', [Carbon::parse($fromdate)->format('Y-m-d'), Carbon::parse($todate)->format('Y-m-d')])->orderBy('date', 'asc');
        
        if($selmanager != 'all'){
            $result = $result->where('manager_id', $selmanager);
        }
        
        if($selclient != 'all'){
            $result = $result->where('client_id', $selclient);
        }
        
        if($ch_tip_id != 'all'){
            $result = $result->where('checkout_tip_id', $ch_tip_id);
        }
        
        $data = $result->get();
        
        if($request->type == 'pdf'){
            return view('backend.checkouts.day_print_all', compact('data', 'fromdate', 'todate', 'checkouttip'));
        } 
        
        return Excel::download(new DayExcel($data, $fromdate, $todate, $checkouttip), 'Фильтр по продажам от ' . Carbon::parse($fromdate)->format('Y-m-d') . ' до ' . Carbon::parse($todate)->format('Y-m-d') . '.xlsx');
        
        if($finish){
            $result = $result->where('status', 1);
        }
        
        $result = Checkout::query()->whereBetween('created_at', [Carbon::parse($fromdate)->startOfDay()->format('Y-m-d H:i:s'), Carbon::parse($todate)->endOfDay()->format('Y-m-d H:i:s')])->orderBy('id', 'asc');
    }
    
    public function month_filter()
    {
        return view('backend.checkouts.month_filter');
    }

    public function month_filter_post(Request $request)
    {
        $request->validate([
            'month_year' => 'required'
        ]);

        $monthYear = $request->month_year; // Masalan: "2026-08"

        return Excel::download(
            new CheckoutMonthExport($monthYear), 
            'Mijozlar_hisoboti_' . $monthYear . '.xlsx'
        );
    }

    public function report_print($id = null, $view = null)
    {
        $comp = Setting::all();
        $item = null;
        if($id) {
            $item = Checkout::where('code', $id)->first();
        }
        
        return view('backend.checkouts.report_print', compact('item', 'comp', 'view'));
    }
    
    public function checkout_excel($id)
    {
        $item = Checkout::where('code', $id)->first();
        return Excel::download(new CheckoutOne($id), 'Накладной ' . ($item->number_work ? $item->number_work : 'Чер. ID' . $item->id) . '  Клиент ' . $item->supid->name . '.xlsx');
    }
    
    public function checkout_excel_null($id)
    {
        return Excel::download(new CheckoutOneNull($id), 'export-null- ' . $id . '.xlsx');
    }

    public function report_avg($id = null)
    {
        $managers = User::where('status', 1)->role('sale')->get();
        return view('backend.checkouts.report_avg_index', compact('managers'));
    }

    public function report_print_avg(Request $request)
    {
        $fromdate       = $request->fromdate;
        $todate         = $request->todate;
        $data = CheckoutDetail::whereBetween('created_at', [Carbon::parse($fromdate)->startOfDay()->format('Y-m-d H:i:s'), Carbon::parse($todate)->endOfDay()->format('Y-m-d H:i:s')])->get()->groupBy('product_id');
        
        return view('backend.checkouts.report_avg_print', compact('data' ,'fromdate', 'todate'));
        
        if($shipment){
            $result = $result->where('shipment_status', 1);
        }
        
        if($finish){
            $result = $result->where('status', 1);
        }
    }
    
    
}
