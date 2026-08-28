@extends('layouts.backend')
@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="nk-block">
                    <div class="row g-gs">
                        <div class="col-sm-12 col-lg-12 col-xxl-12">
                            <div class="card card-bordered">
                                <div class="card-inner">
                                    <form method="GET" action="{{ route('dashboard_month', ['type' => $type]) }}">
                                        @csrf
                                        <div class="row">
                                            <div class="col-lg-3 col-sm-3">
                                                @php
                                                $p = range(Carbon\Carbon::now()->year, App\Models\CheckoutDetail::first()->created_at->format('Y'));
                                                @endphp
                                                <select class="form-select js-select2" title="Магазин" placeholder="Select Multiple options" name="year" required data-search="on">
                                                    @foreach ($p as $syear) 
                                                        <option value="{{ $syear }}">{{ $syear }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            
                                            <div class="col-lg-3 col-sm-3">
                                                <select class="form-select js-select2" title="Магазин" placeholder="Select Multiple options" name="month" required data-search="on">
                                                    @for($m=1; $m <= 12; $m++)
                                                        <option value="{{ date('m', mktime(0,0,0,$m, 1, date('Y'))) }}">{{ date('F', mktime(0,0,0,$m, 1, date('Y'))) }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                            
                                            <div class="col-lg-3 col-sm-3">
                                                <select class="form-select js-select2" title="Магазин" placeholder="Select Multiple options" name="store" required data-search="on">
                                                    <option value="0">Все</option>
                                                    @foreach($stores as $store)
                                                        <option value="{{ $store->id }}">{{ $store->name }}</option>
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
                        @php
                            $startDate = Carbon\Carbon::parse(Carbon\Carbon::now()->startOfMonth()->format('Y-m-d'));
                            $endDate = Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y-m-d'));
                            $diffInDays = $startDate->diffInDays($endDate) + 1;
                            $n = 0;
                        @endphp
                        
                        @while ($n < $diffInDays)
                            @php
                            $r = (1 + $n++);
                            $dd = Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y-m-'.$r))->format('Y-m-d');
                            @endphp
                        <div class="col-sm-2">
                            <div class="card card-bordered">
                                <div class="card-inner" style="padding: 10px">
                                    <div class="team">
                                        <div class="text-center">
                                            <h5>{{ $dd }}</h5>
                                        </div>
                                        <ul class="team-info" style="padding: 5px 5px 0px;">
                                            @foreach($cashtypes as $cashtype)
                                            <li><span>{{ $cashtype->short_text }}</span><span><b>{{ number_format(App\Models\CashReceipt::where('cash_receipt_type', $cashtype->id)->where('status', 1)->whereDate('date', Carbon\Carbon::parse($dd))->sum('price'), 0, '.', ' ') }} </b> сум</span></li>
                                            @endforeach
                                            <li><span>Долг:</span><span><b>{{ number_format(App\Models\Checkout::whereDate('date', Carbon\Carbon::parse($dd))->sum('total_price_debt'), 0, '.', ' ') }}</b></span></li>
                                        </ul>
                                    </div><!-- .team -->
                                </div><!-- .card-inner -->
                            </div><!-- .card -->
                        </div>
                        @endwhile
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection