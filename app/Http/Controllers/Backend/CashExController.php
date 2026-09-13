<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller; 
use App\Models\CashExpenditure;
use App\Models\CashExpenditureType;
use Illuminate\Http\Request; 
use App\Models\Checkout;
use App\Models\Client;
use App\Models\CashReceipt;
use App\Models\Setting;
use App\Models\CashReceiptType;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Warehouse;
use Auth;
use Str;
use Carbon\Carbon;
use App\Exports\Export;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Excel;

class CashExController extends Controller
{
    public function index()
    { 
        if(Auth::user()->hasAnyRole('admin|report')){
            $data = CashExpenditure::orderBy('id', 'desc')->paginate(20); 
        } else{
            $data = CashExpenditure::where('user_id', Auth::id())->orderBy('id', 'desc')->paginate(20); 
        }
        $contracts = CashExpenditureType::where('type', 1)->get();
        $stores = Warehouse::all();
        
        return view('backend.cash_expenditures.index', compact('data', 'contracts', 'stores'));
    }
    
    public function category_select($id)
    { 
        $selectid = CashExpenditureType::where('code', $id)->first();
        if(Auth::user()->hasAnyRole('admin|report')){
            $data = CashExpenditure::where('cash_expenditure_types', $selectid->id)->orderBy('id', 'desc')->paginate(40); 
        } else{
            $data = CashExpenditure::where('cash_expenditure_types', $selectid->id)->where('user_id', Auth::id())->orderBy('id', 'desc')->paginate(40); 
        }
        
        return view('backend.cash_expenditures.category_select', compact('data', 'selectid'));
    }
    
    public function search(Request $request)
    { 
        $contracts = CashExpenditureType::where('type', 1)->get();
        $stores = Warehouse::all();
        $type = NULL;
        $ot = Carbon::parse($request->input('date_ot'));
        $do = Carbon::parse($request->input('date_do'));
        $magazin = $request->input('store');
            
        if($request->input('type')){
            $type = $request->input('type');
            
            if(Auth::user()->hasAnyRole('admin|report')){
                if($magazin){
                    $data = CashExpenditure::orderBy('id', 'desc')->where('store_id', $magazin)->whereBetween('date', [$ot, $do])->where('cash_expenditure_types', $type)->get();
                } else {
                   $data = CashExpenditure::orderBy('id', 'desc')->whereBetween('date', [$ot, $do])->where('cash_expenditure_types', $type)->get(); 
                }
                
            } else{
                $data = CashExpenditure::where('user_id', Auth::id())->orderBy('id', 'desc')->whereBetween('date', [$ot, $do])->where('cash_expenditure_types', $type)->get();
            }
            
            $tname = CashExpenditureType::find($type);
            return view('backend.cash_expenditures.search_type', compact('data', 'type', 'contracts', 'tname', 'stores', 'magazin', 'ot', 'do'));
        } else {
            if(Auth::user()->hasAnyRole('admin|report')){
                if($magazin){
                    $data = CashExpenditure::orderBy('id', 'desc')->where('store_id', $magazin)->whereBetween('date', [$ot, $do])->get();
                } else {
                   $data = CashExpenditure::orderBy('id', 'desc')->whereBetween('date', [$ot, $do])->get(); 
                }
            } else{
                $data = CashExpenditure::where('user_id', Auth::id())->orderBy('id', 'desc')->whereBetween('date', [$ot, $do])->get();
            }
            
            return view('backend.cash_expenditures.search', compact('data', 'type', 'contracts', 'stores', 'magazin', 'ot', 'do'));
        }
        

        
    }

    public function form($id = null)
    {
        $item = null;
        $contracts = CashExpenditureType::where('type', 1)->get();
        $types = CashReceiptType::all();
        $suppliers = Client::whereNotNull('is_supplier')->get();
        $clients = Client::orderBy('name')->get(['id', 'name', 'phone']);
        $mainExpenditureTypeIds = $contracts
            ->filter(fn (CashExpenditureType $type) => $type->supportsBonusSource())
            ->pluck('id')
            ->map(fn ($typeId) => (int) $typeId)
            ->all();
        
        if(Auth::user()->hasAnyRole('admin|report')){
            $employees = User::orderBy('id', 'desc')->get(); 
        } else{
            $employees = User::where('store_id', Auth::user()->store_id)->orderBy('id', 'desc')->get(); 
        }
        
        if($id) {
            $item = CashExpenditure::where('code', $id)->first();
        }

        $selectedBonusClientId = old('bonus_client_id', optional($item)->bonus_client_id);
        $sourcePayments = collect();
        $selectedExpenditureTypeId = (int) old('cash_expenditure_types', optional($item)->cash_expenditure_types);
        if ($selectedBonusClientId && in_array($selectedExpenditureTypeId, $mainExpenditureTypeIds, true)) {
            $selectedBonusClient = Client::find($selectedBonusClientId);
            if ($selectedBonusClient) {
                $sourcePayments = $this->bonusPaymentOptions($selectedBonusClient, optional($item)->id);
            }
        }
        
        return view('backend.cash_expenditures.form', compact(
            'item',
            'contracts',
            'types',
            'suppliers',
            'employees',
            'clients',
            'sourcePayments',
            'mainExpenditureTypeIds'
        ));
    }

    public function save(Request $request, $id = null)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'cash_expenditure_types' => ['required', 'integer', 'exists:cash_expenditure_types,id'],
            'cash_receipt_type_id' => ['required', 'integer', 'exists:cash_receipt_types,id'],
            'price' => ['required', 'numeric', 'gt:0'],
            'comment' => ['nullable', 'string'],
            'supplier_id' => ['nullable', 'integer', 'exists:clients,id'],
            'employee_id' => ['nullable', 'integer', 'exists:users,id'],
            'bonus_client_id' => ['nullable', 'integer', 'exists:clients,id', 'required_with:source_cash_receipt_id'],
            'source_cash_receipt_id' => ['nullable', 'integer', 'required_with:bonus_client_id'],
        ]);

        $item = $id ? CashExpenditure::where('code', $id)->firstOrFail() : null;
        $expenditureType = CashExpenditureType::findOrFail($validated['cash_expenditure_types']);
        $isMainExpenditure = $expenditureType->supportsBonusSource();

        if ($isMainExpenditure && (empty($validated['bonus_client_id']) || empty($validated['source_cash_receipt_id']))) {
            throw ValidationException::withMessages([
                'bonus_client_id' => 'Основной xarajat uchun mijozni tanlang.',
                'source_cash_receipt_id' => 'Основной xarajat uchun qaysi to‘lovdan kamayishini tanlang.',
            ]);
        }

        $data = [
            'date' => Carbon::parse($validated['date'])->format('Y-m-d'),
            'cash_expenditure_types' => $validated['cash_expenditure_types'],
            'cash_receipt_type_id' => $validated['cash_receipt_type_id'],
            'price' => (float) $validated['price'],
            'comment' => $validated['comment'] ?? null,
            'supplier_id' => $validated['supplier_id'] ?? null,
            'employee_id' => $validated['employee_id'] ?? null,
            // Faqat "Основной" turi mijoz bonusiga ta'sir qiladi.
            'bonus_client_id' => $isMainExpenditure ? $validated['bonus_client_id'] : null,
            'source_cash_receipt_id' => $isMainExpenditure ? $validated['source_cash_receipt_id'] : null,
        ];

        DB::transaction(function () use (&$item, $data) {
            if ($data['source_cash_receipt_id']) {
                $receipt = CashReceipt::with('checkout')
                    ->whereKey($data['source_cash_receipt_id'])
                    ->where('status', 1)
                    ->lockForUpdate()
                    ->first();

                if (! $receipt) {
                    throw ValidationException::withMessages([
                        'source_cash_receipt_id' => 'Tanlangan to‘lov faol emas yoki topilmadi.',
                    ]);
                }

                $receiptClientId = $receipt->client_id ?: optional($receipt->checkout)->client_id;
                if ((int) $receiptClientId !== (int) $data['bonus_client_id']) {
                    throw ValidationException::withMessages([
                        'source_cash_receipt_id' => 'Tanlangan to‘lov ushbu mijozga tegishli emas.',
                    ]);
                }

                $alreadyAllocated = (float) CashExpenditure::where('source_cash_receipt_id', $receipt->id)
                    ->when($item, fn ($query) => $query->where('id', '!=', $item->id))
                    ->sum('price');

                if ($alreadyAllocated + (float) $data['price'] > (float) $receipt->price + 0.0001) {
                    throw ValidationException::withMessages([
                        'price' => 'Bonus summasi tanlangan to‘lovning qolgan summasidan oshmasligi kerak.',
                    ]);
                }
            }

            if ($item) {
                $item->update($data);
            } else {
                $data['code'] = Str::uuid();
                $data['user_id'] = Auth::id();
                $data['store_id'] = Auth::user()->store_id;
                $item = CashExpenditure::create($data);
            }
        });

        if ($id) {
            $request->session()->flash('update_cash', trans('backend.post_update'));
        } else {
            $request->session()->flash('success_cash', trans('backend.post_create'));
        }

        return redirect()->action('Backend\CashExController@index');
    }

    public function clientPayments(Request $request, Client $client)
    {
        $excludeExpenseId = (int) $request->input('exclude_expense_id') ?: null;

        return response()->json([
            'payments' => $this->bonusPaymentOptions($client, $excludeExpenseId)->values(),
        ]);
    }

    private function bonusPaymentOptions(Client $client, ?int $excludeExpenseId = null)
    {
        return CashReceipt::query()
            ->where('status', 1)
            ->where(function ($query) use ($client) {
                $query->where('client_id', $client->id)
                    ->orWhere(function ($checkoutQuery) use ($client) {
                        $checkoutQuery->whereNull('client_id')
                            ->whereHas('checkout', fn ($query) => $query->where('client_id', $client->id));
                    });
            })
            ->with(['checkout:id,client_id,number_work,currency_type', 'tname:id,name_uz,name_ru'])
            ->withSum(['linkedBonusExpenses as allocated_bonus' => function ($query) use ($excludeExpenseId) {
                $query->when($excludeExpenseId, fn ($expenseQuery) => $expenseQuery->where('id', '!=', $excludeExpenseId));
            }], 'price')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get()
            ->map(function (CashReceipt $receipt) {
                $currencyType = optional($receipt->checkout)->currency_type ?: $receipt->currency_type;
                $currency = (int) $currencyType === 1 ? 'USD' : 'UZS';
                $allocated = (float) ($receipt->allocated_bonus ?? 0);
                $available = max(0, (float) $receipt->price - $allocated);
                $document = optional($receipt->checkout)->number_work;
                $label = Carbon::parse($receipt->date ?: $receipt->created_at)->format('d.m.Y')
                    . ' | ' . number_format((float) $receipt->price, 2, '.', ' ') . ' ' . $currency
                    . ($receipt->tname ? ' | ' . $receipt->tname->name : '')
                    . ($document ? ' | №' . $document : '')
                    . ($allocated > 0 ? ' | bonus: ' . number_format($allocated, 2, '.', ' ') : '');

                return [
                    'id' => $receipt->id,
                    'label' => $label,
                    'available' => $available,
                    'currency' => $currency,
                ];
            });
    }

    public function delete(Request $request, $id = null)
    {

        if ($id) {
            $item = CashExpenditure::where('code', $id)->first();
            if($item) {
                $item->delete();
                $request->session()->flash('success', 'o`chirildi');
            }
        } 

        return back();
    }
    
    public function excel()
    {
        return Excel::download(new Export(), 'export- ' . Carbon::now()->format('Ymd') . '.xlsx');
    }
    
    ///
    public function types_index()
    { 
        $data = CashExpenditureType::where('type', 1)->orderBy('id', 'desc')->paginate(20); 
        $keyword = NULL; 
        return view('backend.cash_expenditure_types.index', compact('data', 'keyword'));
    }
    
    public function types_form($id = null)
    {
        $item = null;
        
        if($id) {
            $item = CashExpenditureType::where('code', $id)->first();
        }
        
        return view('backend.cash_expenditure_types.form', compact('item'));
    }

    public function types_save(Request $request, $id = null)
    {
        $data = $request->all();

        if ($id) {
            $item = CashExpenditureType::where('code', $id)->first();

            if($item) {
                $item->update($data);
                $request->session()->flash('update_cash', trans('backend.post_update'));
            }
        } else {
            $data['code'] = Str::uuid();
            $data['user_id'] = Auth::id();

            $item = CashExpenditureType::create($data);
            $request->session()->flash('success_cash', trans('backend.post_create'));
        }

        return redirect()->action('Backend\CashExController@types_index');
    }
}
