@extends('layouts.backend')

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="nk-block">
                    <div class="row g-gs">
                        
                        {{-- 1. FILTER QISMI --}}
                        <div class="col-sm-12 col-lg-12 col-xxl-12">
                            <div class="card card-bordered">
                                <div class="card-inner">
                                    <form method="GET" action="{{ route('dashboard_month', ['type' => $type]) }}">
                                        {{-- @csrf GET so'rovda shart emas, lekin tursa ham ziyoni yo'q --}}
                                        <div class="row">
                                            {{-- YIL TANLASH --}}
                                            <div class="col-lg-3 col-sm-3">
                                                @php
                                                    // Baza bo'sh bo'lsa xato bermasligi uchun
                                                    $startYear = App\Models\CheckoutDetail::exists() 
                                                        ? App\Models\CheckoutDetail::oldest()->first()->created_at->format('Y') 
                                                        : Carbon\Carbon::now()->year;
                                                    $p = range(Carbon\Carbon::now()->year, $startYear);
                                                @endphp
                                                <select class="form-select js-select2" title="Йил" name="year" required data-search="on">
                                                    @foreach ($p as $syear) 
                                                        <option value="{{ $syear }}" {{ $year == $syear ? 'selected' : '' }}>{{ $syear }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            
                                            {{-- OY TANLASH --}}
                                            <div class="col-lg-3 col-sm-3">
                                                <select class="form-select js-select2" title="Ой" name="month" required data-search="on">
                                                    @for($m=1; $m <= 12; $m++)
                                                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                                            {{ date('F', mktime(0,0,0,$m, 1, date('Y'))) }}
                                                        </option>
                                                    @endfor
                                                </select>
                                            </div>
                                            
                                            {{-- DO'KON TANLASH --}}
                                            <div class="col-lg-3 col-sm-3">
                                                <select class="form-select js-select2" title="Дўкон" name="store" required data-search="on">
                                                    <option value="0" {{ $store_id == 0 ? 'selected' : '' }}>Все (Барчаси)</option>
                                                    @foreach($stores as $store)
                                                        <option value="{{ $store->id }}" {{ $store_id == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                                    
                                            <div class="col-md-3 mb-2">
                                               <button type="submit" class="btn btn-warning btn-block">{{ trans('savdo.home.search_button') }}</button> 
                                            </div>
                                        </div>
                                    </form>
                                </div><!-- .card-inner -->
                            </div><!-- .card -->
                        </div><!-- .col -->

                        {{-- 2. JAMI OY UCHUN UMUMIY BLOK --}}
                        <div class="col-sm-2">
                            <div class="card card-bordered" style="border: 2px solid #09c2de;">
                                <div class="card-inner" style="padding: 10px; background-color: #f1fcff;">
                                    <div class="team">
                                        <div class="text-center">
                                            <h5>{{ sprintf("%02d", $month) }}.{{ $year }}</h5>
                                        </div>
                                        <ul class="team-info" style="padding: 5px 5px 0px;">
                                            @foreach($cashtypes as $cashtype)
                                            <li>
                                                <span>{{ $cashtype->short_text }}</span>
                                                <span>
                                                    <b>
                                                    {{ number_format(
                                                        App\Models\CashReceipt::where('cash_receipt_type', $cashtype->id)
                                                        ->where('status', 1)
                                                        ->whereYear('date', $year)   // Yil bo'yicha
                                                        ->whereMonth('date', $month) // Oy bo'yicha
                                                        // Agar CashReceipt da warehouse_id bo'lsa, pastdagi qatorni yoqing:
                                                        // ->when($store_id > 0, function($q) use ($store_id) { $q->where('warehouse_id', $store_id); })
                                                        ->sum('price'), 0, '.', ' ') 
                                                    }} 
                                                    </b>
                                                </span>
                                            </li>
                                            @endforeach
                                            <li>
                                                <span class="text-danger">Долг:</span>
                                                <span>
                                                    <b class="text-danger">
                                                    {{ number_format(
                                                        App\Models\Checkout::whereYear('date', $year)
                                                        ->whereMonth('date', $month)
                                                        ->when($store_id > 0, function($query) use ($store_id) {
                                                            return $query->where('warehouse_id', $store_id);
                                                        })
                                                        ->sum('total_price_debt'), 0, '.', ' ') 
                                                    }}
                                                    </b>
                                                </span>
                                            </li>
                                        </ul>
                                    </div><!-- .team -->
                                </div><!-- .card-inner -->
                            </div><!-- .card -->
                        </div>

                        {{-- 3. KUNLIK SIKL (1-kunidan oy oxirigacha) --}}
                        @for ($d = 1; $d <= $daysInMonth; $d++)
                            @php
                                $dateObj = Carbon\Carbon::createFromDate($year, $month, $d);
                                $currentDate = $dateObj->format('Y-m-d');
                                
                                // Kelajakdagi kunlarni ko'rsatmaslik (ixtiyoriy)
                                if($dateObj->isFuture()) break;
                            @endphp
                        <div class="col-sm-2">
                            <div class="card card-bordered">
                                <div class="card-inner" style="padding: 10px">
                                    <div class="team">
                                        <div class="text-center">
                                            <h5>{{ $dateObj->format('d.m.Y') }}</h5>
                                        </div>
                                        <ul class="team-info" style="padding: 5px 5px 0px;">
                                            @foreach($cashtypes as $cashtype)
                                            <li>
                                                <span>{{ $cashtype->short_text }}</span>
                                                <span>
                                                    <b>
                                                    {{ number_format(
                                                        App\Models\CashReceipt::where('cash_receipt_type', $cashtype->id)
                                                        ->where('status', 1)
                                                        ->whereDate('date', $currentDate) // Aniq kun
                                                        // ->when($store_id > 0, function($q) use ($store_id) { $q->where('warehouse_id', $store_id); })
                                                        ->sum('price'), 0, '.', ' ') 
                                                    }} 
                                                    </b>
                                                </span>
                                            </li>
                                            @endforeach
                                            <li>
                                                <span>Долг:</span>
                                                <span>
                                                    <b>
                                                    {{ number_format(
                                                        App\Models\Checkout::whereDate('date', $currentDate)
                                                        ->when($store_id > 0, function($query) use ($store_id) {
                                                            return $query->where('warehouse_id', $store_id);
                                                        })
                                                        ->sum('total_price_debt'), 0, '.', ' ') 
                                                    }}
                                                    </b>
                                                </span>
                                            </li>
                                        </ul>
                                    </div><!-- .team -->
                                </div><!-- .card-inner -->
                            </div><!-- .card -->
                        </div>
                        @endfor
                        {{-- SIKL TUGASHI --}}

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection