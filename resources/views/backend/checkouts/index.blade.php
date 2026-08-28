@extends('layouts.backend')

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        @if(Route::currentRouteName() == 'checkouts_index')
        <div class="row mb-2">
            @foreach($ctypes as $octype)
                <div class="col-md-2">
                    <a href="{{ route('checkouts_index', ['ctypeAlias' => $octype->alias]) }}" class="btn btn-dim btn-secondary btn-block">{{ $octype->name }}</a>
                </div>
            @endforeach
        </div>
        @endif
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="components-preview mx-auto">
                    <div class="nk-block nk-block-lg">
                        <div class="card">
                            <div class="card-inner position-relative card-tools-toggle" style="padding: 0.75rem 0.75rem; border-top: 1px solid #dbdfea; border-left: 1px solid #dbdfea; border-right: 1px solid #dbdfea;">
                                <div class="card-title-group">
                                    <div class="card-tools">
                                    @if(Route::currentRouteName() == 'checkouts_index')
                                        <div class="form-inline flex-nowrap gx-3">
                                            <div class="btn-wrap">
                                                <span class="d-none d-md-block"><a href="{{ route('checkout_form') }}" class="btn btn-sm btn-primary">{{ trans('backend.table.add_from') }}</a></span>
                                                <span class="d-md-none"><a href="{{ route('checkout_form') }}" class="btn btn-dim btn-outline-primary btn-icon"><em class="icon ni ni-arrow-right"></em></a></span>
                                            </div>
                                            <div class="btn-wrap">
                                                <span class="d-none d-md-block"><a href="{{ route('checkout_today_send') }}" class="btn btn-sm btn-warning">Cегодняшняя</a></span>
                                                <span class="d-md-none"><a href="{{ route('checkout_today_send') }}" class="btn btn-dim btn-outline-warning btn-icon"><em class="icon ni ni-arrow-right"></em></a></span>
                                            </div>
                                            <!--<div class="btn-wrap">
                                                <span class="d-none d-md-block"><a href="{{ route('checkout_yesterday_send') }}" class="btn btn-sm btn-warning">Вчерашняя</a></span>
                                                <span class="d-md-none"><a href="{{ route('checkout_yesterday_send') }}" class="btn btn-dim btn-outline-warning btn-icon"><em class="icon ni ni-arrow-right"></em></a></span>
                                            </div>-->
                                            <div class="btn-wrap">
                                                <span class="d-none d-md-block"><p><b>Касса: {{ number_format(App\Models\CashReceipt::where('status', 1)->whereDate('date', Carbon\Carbon::today())->sum('price'), 2, '.', ' ') }} </b> 
                                                (@foreach(App\Models\CashReceiptType::where('status', 1)->get() as $cashtype)<i>{{ $cashtype->name_ru }}</i>: {{ number_format(App\Models\CashReceipt::where('cash_receipt_type', $cashtype->id)->where('status', 1)->whereDate('date', Carbon\Carbon::today())->sum('price'), 2, '.', ' ') }}@endforeach)
                                                <b>Долг</b>: {{ number_format(App\Models\Checkout::where('checkout_tip_id', 1)->whereDate('date', Carbon\Carbon::today())->sum('total_price_debt'), 2, '.', ' ') }}</p></span>
                                            </div>
                                        </div><!-- .form-inline -->
                                    @else
                                        <div class="form-inline flex-nowrap gx-3">
                                            <div class="btn-wrap">
                                                <span class="d-none d-md-block"><a href="{{ route('checkout_downloadDebtReport') }}" class="btn btn-sm btn-warning">EXCEL</a></span>
                                                <span class="d-md-none"><a href="{{ route('checkout_downloadDebtReport') }}" class="btn btn-dim btn-outline-warning btn-icon">EXCEL</a></span>
                                            </div>
                                        </div><!-- .form-inline -->
                                    @endif
                                    </div><!-- .card-tools -->
                                    <div class="card-tools me-n1">
                                        <ul class="btn-toolbar gx-1">
                                            <li>
                                                <a href="#" class="btn btn-icon search-toggle toggle-search" data-target="search"><em class="icon ni ni-search"></em></a>
                                            </li>
                                            <li>
                                                <div class="toggle-wrap">
                                                    <a href="#" class="btn btn-icon btn-trigger toggle" data-target="cardTools"><em class="icon ni ni-menu-right"></em></a>
                                                    <div class="toggle-content" data-content="cardTools">
                                                        <ul class="btn-toolbar gx-1">
                                                            <li class="toggle-close">
                                                                <a href="#" class="btn btn-icon btn-trigger toggle" data-target="cardTools"><em class="icon ni ni-arrow-left"></em></a>
                                                            </li><!-- li -->
                                                            <li>
                                                                <div class="dropdown">
                                                                    <a href="#" class="btn btn-trigger btn-icon dropdown-toggle" data-bs-toggle="dropdown">
                                                                        <em class="icon ni ni-money"></em>
                                                                    </a>
                                                                    <div class="filter-wg dropdown-menu dropdown-menu-xl dropdown-menu-end">
                                                                        <div class="dropdown-head">
                                                                            <span class="sub-title dropdown-title">{{ trans('backend.menu.client_balance') }}</span>
                                                                        </div>
                                                                        <div class="dropdown-body dropdown-body-rg">
                                                                            <form method="POST" action="{{ route('balance_save') }}" class="d-none">
                                                                                @csrf
                                                                                <div class="row gx-6 gy-2">
                                                                                    <div class="col-12 mt-0">
                                                                                        <div class="form-group">
                                                                                            <label class="overline-title overline-title-alt">{{ trans('backend.table.client') }}</label>
                                                                                            <input list="itemsnew" class="form-control" type="text" name="clientselect" placeholder="{{ trans('backend.table.client') }}" value="{{ $clientselect }}" autocomplete="off" id="modelNameNew" />
                                                                                            <datalist id="itemsnew" class="modelListNew"></datalist>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-lg-12">
                                                                                        <div class="form-group">
                                                                                            <label class="overline-title overline-title-alt">{{ trans('backend.input.summs') }}</label>
                                                                                            <div class="form-control-wrap">
                                                                                                <input type="text" class="form-control" name="price" id="formattedNumberField" required placeholder="{{ trans('backend.input.summs') }}" data-type="currency">
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    
                                                                                    <div class="col-lg-12">
                                                                                        <div class="form-group">
                                                                                            <label class="overline-title overline-title-alt">{{ trans('backend.input.type_pay') }}</label>
                                                                                            <div class="form-control-wrap">
                                                                                                <select class="form-select js-select2" required name="cash_receipt_type" data-search="on">
                                                                                                    @foreach($types as $type)
                                                                                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                                                                    @endforeach
                                                                                                </select>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-lg-12">
                                                                                        <button id="register" type="submit" class="btn btn-primary btn-block">{{ trans('backend.input.button_done') }}</button>
                                                                                    </div>
                                                                                </div>
                                                                            </form>
                                                                            <p>Vaqtinchalik mavjud emas</p>
                                                                        </div>
                                                                    </div><!-- .filter-wg -->
                                                                </div><!-- .dropdown -->
                                                            </li><!-- li -->
                                                        </ul><!-- .btn-toolbar -->
                                                    </div><!-- .toggle-content -->
                                                </div><!-- .toggle-wrap -->
                                            </li>
                                            <li>
                                                <div class="toggle-wrap">
                                                    <a href="#" class="btn btn-icon btn-trigger toggle" data-target="cardTools"><em class="icon ni ni-menu-right"></em></a>
                                                    <div class="toggle-content" data-content="cardTools">
                                                        <ul class="btn-toolbar gx-1">
                                                            <li class="toggle-close">
                                                                <a href="#" class="btn btn-icon btn-trigger toggle" data-target="cardTools"><em class="icon ni ni-arrow-left"></em></a>
                                                            </li><!-- li -->
                                                            <li>
                                                                <div class="dropdown">
                                                                    <a href="#" class="btn btn-trigger btn-icon dropdown-toggle" data-bs-toggle="dropdown">
                                                                        <div class="dot dot-primary"></div>
                                                                        <em class="icon ni ni-filter-alt"></em>
                                                                    </a>
                                                                    <div class="filter-wg dropdown-menu dropdown-menu-xl dropdown-menu-end">
                                                                        <div class="dropdown-head">
                                                                            <span class="sub-title dropdown-title">{{ trans('backend.table.filter') }}</span>
                                                                        </div>
                                                                        <div class="dropdown-body dropdown-body-rg">
                                                                            <form method="GET" action="{{ route('checkout_filter') }}">
                                                                                <div class="row gx-6 gy-3">
                                                                                    <div class="col-6">
                                                                                        <div class="form-group">
                                                                                            <label class="overline-title overline-title-alt">{{ trans('backend.table.from_date') }}</label>
                                                                                            <div class="form-control-wrap">
                                                                                                <input type="text" value="{{ $fromdate }}" name="fromdate" class="form-control date-picker">
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-6">
                                                                                        <div class="form-group">
                                                                                            <label class="overline-title overline-title-alt">{{ trans('backend.table.to_date') }}</label>
                                                                                            <div class="form-control-wrap">
                                                                                                <input type="text" value="{{ $todate }}" name="todate" class="form-control date-picker">
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-12">
                                                                                        <div class="custom-control custom-control-sm custom-checkbox">
                                                                                            <input type="checkbox" class="custom-control-input" {{ $sdata ? 'checked' : NULL }} name="sdata" id="sdata">
                                                                                            <label class="custom-control-label" for="sdata"> Поиск по датам?</label>
                                                                                        </div>
                                                                                    </div>
                                                                                    
                                                                                    <div class="col-12">
                                                                                        <div class="form-group">
                                                                                            <label class="overline-title overline-title-alt">{{ trans('backend.table.manager') }}</label>
                                                                                            <select class="form-select js-select2" name="manager">
                                                                                                <option value="all">Все</option>
                                                                                                @foreach($managers as $manager)
                                                                                                <option {{ $selmanager  == $manager->id ? 'selected' : NULL }} value="{{ $manager->id }}">{{ $manager->name }}</option>
                                                                                                @endforeach
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                    
                                                                                    
                                                                                    <div class="col-12">
                                                                                        <div class="form-group">
                                                                                            <label class="overline-title overline-title-alt">{{ trans('backend.table.client') }}</label>
                                                                                            <input list="items" class="form-control" type="text" name="clientselect" placeholder="{{ trans('backend.table.client') }}" value="{{ $clientselect }}" autocomplete="off" id="modelName" />
                                                                                            <datalist id="items" class="modelList"></datalist>
                                                                                        </div>
                                                                                    </div>
                                                                                    
                                                                                    <div class="col-6">
                                                                                        <div class="custom-control custom-control-sm custom-checkbox">
                                                                                            <input type="checkbox" class="custom-control-input" {{ $shipment ? 'checked' : NULL }} name="shipment" id="shipment">
                                                                                            <label class="custom-control-label" for="shipment"> Даставлено </label>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-6">
                                                                                        <div class="custom-control custom-control-sm custom-checkbox">
                                                                                            <input type="checkbox" class="custom-control-input" {{ $finish ? 'checked' : NULL }} name="finish" id="finish">
                                                                                            <label class="custom-control-label" for="finish"> {{ trans('backend.index.check_finish') }} </label>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-6">
                                                                                        <div class="custom-control custom-control-sm custom-checkbox">
                                                                                            <input type="checkbox" class="custom-control-input" {{ $draft ? 'checked' : NULL }} name="draft" id="draft">
                                                                                            <label class="custom-control-label" for="draft"> Черновик</label>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-12">
                                                                                        <div class="form-group">
                                                                                            <button type="submit" class="btn btn-secondary btn-block">{{ trans('backend.input.button_done') }}</button>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </form>
                                                                        </div>
                                                                        <div class="dropdown-foot between">
                                                                            <a class="clickable" href="{{ route('checkouts_index') }}">Reset Filter</a>
                                                                        </div>
                                                                    </div><!-- .filter-wg -->
                                                                </div><!-- .dropdown -->
                                                            </li><!-- li -->
                                                        </ul><!-- .btn-toolbar -->
                                                    </div><!-- .toggle-content -->
                                                </div><!-- .toggle-wrap -->
                                            </li><!-- li -->
                                        </ul><!-- .btn-toolbar -->
                                    </div><!-- .card-tools -->
                                </div><!-- .card-title-group -->
                                <div class="card-search search-wrap" data-search="search">
                                    <div class="card-body">
                                        <form method="POST" action="{{ route('checkouts_search') }}">
                                        @csrf
                                            <div class="search-content">
                                                <a href="#" class="search-back btn btn-icon toggle-search" data-target="search"><em class="icon ni ni-arrow-left"></em></a>
                                                <input type="text" class="form-control border-transparent form-focus-none" value="{{ $keyword ? $keyword : NULL }}" name="search" required placeholder="Поиск по № заявки и № спец-ии завода">
                                                <button class="search-submit btn btn-icon"><em class="icon ni ni-search"></em></button>
                                            </div>
                                        </form>
                                    </div>
                                </div><!-- .card-search -->
                            </div>
                            @include('layouts.message.success')
                            @include('layouts.message.error')
                            <div class="table-responsive">
                                <table class="table table-bordered text-nowrap">
                                  <thead>
                                    <tr class="text-center">
                                      <th style="padding: 0px 20px; vertical-align: middle;">{{ trans('backend.table.doc_number') }}</th>
                                      
                                      @hasanyrole('admin|cashier|report|select_manager|sale')
                                      <th style="padding: 0px;">{{ trans('backend.table.nakladnoy') }}</th>
                                      @endhasanyrole
                                      <th>{{ trans('backend.table.client') }}</th>
                                      <th width="160px">{{ trans('backend.table.manager') }}</th>
                                      <th width="80px" style="padding: 0px;">{{ trans('backend.table.vid_tovar') }}</th>
                                      <th width="50px">{{ trans('backend.table.post_edit_short') }}</th>
                                      <th>Курс</th>
                                      @hasanyrole('admin|cashier|report|sale')
                                      <th>{{ trans('backend.table.summa_dog') }}</th>
                                      <th>{{ trans('backend.table.cash_pay') }}</th>
                                      <th>{{ trans('backend.table.pay_title') }}</th>
                                      @endhasanyrole
                                      <th>{{ trans('backend.input.comment') }}</th>
                                      <th>{{ trans('backend.table.step') }}</th>
                                      <th width="150px">{{ trans('backend.table.data_add') }}</th>
                                      
                                      <th width="50px">{{ trans('backend.table.delete') }}</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @foreach($data as $item)
                                    <tr class="text-center">
                                      <td style="padding: 0px; vertical-align: middle;">
                                       <a target="_blank" href="{{ route('checkout_check', ['id' => $item->code])}}">@if($item->number_work) {{ $item->number_work }} @else Чер. #{{ $item->id }} @endif <em class="icon ni ni-download"></em></a>
                                      </td>
                                      @hasanyrole('admin|cashier|report|select_manager|sale')
                                      <td style="padding: 0px; vertical-align: middle;"><a href="{{ route('checkout_print', ['id' => $item->code, 'view' => 'full']) }}"><img width="22px" src="/upload/view-files.png"></a> <a href="{{ route('checkout_excel', ['id' => $item->code]) }}"><img width="22px" src="/upload/excel.png"></a> </td>
                                      <!--<td style="min-width: 60px; padding: 0px; vertical-align: middle;"><a href="{{ route('checkout_print', ['id' => $item->code, 'view' => 'short']) }}"><img width="22px" src="/upload/view-files.png"></a> <a href="{{ route('checkout_excel_null', ['id' => $item->code]) }}"><img width="22px" src="/upload/excel.png"></a> </td>
                                      -->@endhasanyrole
                                       <td style="padding: 2px; {{ $item->total_price_debt != 0 &&  $item->checkout_tip_id == 1 ? 'background-color: #fff29c' : NULL }}"><span data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $item->client_id ? $item->supid->name : NULL }}">{{ $item->client_id ? Str::limit($item->supid->name, 30, '') : NULL }} {{ $item->checkout_tip_id == 3 ? '*' : NULL }}</span></td>
                                       <td>{{ $item->managerid ? $item->managerid->name : NULL }}</td>
                                       <td>{{ $item->details()->count() }} </td>
                                       <td>
                                           @if($item->step == 1)
                                           <a href="{{ route('checkout_form', ['id' => $item->code, 'page' => $data->currentPage()])}}" style="text-decoration:underline;">{{ trans('backend.table.post_edit_short') }}</a>
                                           @else
                                           @hasanyrole('admin|cashier|sale|dealer_admin')
                                           <a href="{{ route('checkout_form', ['id' => $item->code, 'page' => $data->currentPage()])}}" style="text-decoration:underline;">{{ trans('backend.table.post_edit_short') }}</a>
                                           @endhasanyrole
                                           @endif
                                       </td>
                                       
                                       @hasanyrole('admin|cashier|report|sale')
                                       <td>{{ number_format($item->currency_type_price, 2, '.', ' ') }}</td>
                                       <td>{{ number_format($item->total_price, 2, '.', ' ') }} {{ $item->currencytypeid?->name }}</td>
                                       <td>
                                            @foreach($item->payments()->where('status', 1)->get() as $pays)
                                               <a href="{{ route('cash_receipt_form', ['id' => $pays->code, 'checkout' => 1, 'page' => $data->currentPage()]) }}">{{ $pays->tname ? Str::limit($pays->tname->name, 4, ''): 'tulov turi yuq' }}: {{ number_format($pays->price, 2, '.', ' ') }} <!--сум--></a><br>
                                            @endforeach
                                       </td>
                                       <td style="padding: 2px; font-size: 12px; vertical-align: middle;">
                                        @if($item->number_work && $item->checkout_tip_id == 1)
                                            @if($item->total_price == $item->payments()->where('status', 1)->sum('price')) 
                                                @if($item->total_price != 0)
                                                    оплачено
                                                @endif
                                            @else
                                                @if($item->total_price > 0)
                                                    <a style="padding: 0px;" href="{{ route('checkout_done_pay', ['id' => $item->code]) }}"data-bs-toggle="modal" data-bs-target="#modalDefault{{ $item->id }}" class="btn btn-warning btn-block btn-sm">Оплата</a> 
                                                @endif
                                            @endif
                                        @endif
                                       </td>
                                       <td>{{ $item->reference }}</td>
                                       @endhasanyrole
                                       <td style="padding: 2px; font-size: 12px; vertical-align: middle; text-transform: lowercase;" id="tsendsuccess{{ $item->id }}">
                                        @if($item->number_work)
                                           @if($item->shipment_status == 0 && $item->total_price > 0) 
                                            <a href="#" style="padding: 0px;" id="sendsuccess" data-id="{{ $item->id }}"  class="btn btn-primary btn-block btn-sm sendsuccess">Даставлено</a>
                                           @elseif($item->shipment_status == 1) 
                                            Даставлено
                                           @endif
                                        @else
                                        
                                        @endif
                                       </td>
                                      <td style="padding: 2px; font-size: 12px; vertical-align: middle;">{{ Carbon\Carbon::parse($item->date)->format('Y-m-d') . ' ' .  $item->created_at->format('H:i') }} </td>
                                       
                                       <td><a href="{{ route('delete_checkout', ['id' => $item->code])}}" style="text-decoration:underline;">{{ trans('backend.table.delete') }}</a></td>
                                    </tr>
                                    
                                    @if($item->number_work)
                                    <div class="modal fade" tabindex="-1" id="modalDefault{{ $item->id }}">
                                        <div class="modal-dialog modal-lg" role="document">
                                            <div class="modal-content">
                                                <div class="modal-body">
                                                    <form action="{{ route('checkout_pay', ['id' => $item->code]) }}" method="POST" id="appointment_form">
                                                    @csrf
                                                    <div class="row gy-1">
                                                        <div class="col-lg-6 col-sm-6">
                                                            <div class="form-group">
                                                                <label class="form-label">{{ trans('backend.table.date') }}</label>
                                                                <div class="form-control-wrap">
                                                                    <div class="form-icon form-icon-right">
                                                                        <em class="icon ni ni-calendar-alt"></em>
                                                                    </div>
                                                                    <input type="text" name="date" value="{{ Carbon\Carbon::now()->format('d.m.Y') }}" class="form-control date-picker" placeholder="дата">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="col-lg-6 col-sm-6">
                                                            <div class="form-group">
                                                                <label class="form-label">{{ trans('backend.table.type_pay') }}</label>
                                                                <div class="form-control-wrap">
                                                                    <select class="form-select js-select2" required name="cash_receipt_type" data-search="on">
                                                                        @foreach($types as $type)
                                                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @if($item->client_id && $item->supid->balance)
                                                        <div class="col-lg-12 col-sm-12 d-none">
                                                            <div class="form-group">
                                                                <label class="form-label" for="comment">{{ trans('backend.menu.client_balance') }}</label>
                                                                <div class="form-control-wrap">
                                                                    <input type="text" class="form-control" readonly id="comment" value="{{ $item->supid->balance }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endif
                                                        <div class="col-lg-3 col-sm-3">
                                                            <div class="form-group">
                                                                <label class="form-label" for="summa">{{ trans('backend.table.summs') }}</label>
                                                                <div class="form-control-wrap">
                                                                    <input type="text" class="form-control" required min="1" max="{{ $item->total_price - $item->payments()->where('status', 1)->sum('price') }}" value="{{ number_format($item->total_price - $item->payments()->where('status', 1)->sum('price'), 2, '.', ' ') }}" name="price" id="formattedNumberField" placeholder="Сумма">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="col-lg-3 col-sm-3">
                                                            <div class="form-group">
                                                                <label class="form-label" for="currency">{{ trans('backend.input.currency') }}</label>
                                                                <div class="form-control-wrap">
                                                                    <select class="form-select js-select2" required name="currency_type" data-search="on">
                                                                        @foreach(App\Models\CurrencyType::where('status', 1)->orderBy('id', 'asc')->get() as $currency)
                                                                        <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                                                        @endforeach
                                                                    </select>    
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="col-lg-3 col-sm-3">
                                                            <div class="form-group">
                                                                <label class="form-label" for="currency_type_price">Курс валют</label>
                                                                <div class="form-control-wrap">
                                                                    <input type="text" class="form-control" required name="currency_type_price" value="{{ number_format(App\Models\Currency::where('type_id', 1)->orderBy('id', 'desc')->first()->price, 2, '.', ' ') }}" data-type="currency" id="currency_type_price" placeholder="Курс валют">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="col-lg-3 col-sm-3">
                                                            <div class="form-group">
                                                                <label class="form-label" for="comment">{{ trans('backend.input.comment') }}</label>
                                                                <div class="form-control-wrap">
                                                                    <input type="text" class="form-control" name="comment" id="comment" placeholder="{{ trans('backend.input.comment') }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <a href="#" data-bs-dismiss="modal" aria-label="Close" class="btn btn-danger btn-sm btn-block text-uppercase">{{ trans('backend.input.priv') }}</a>
                                                        </div>
                                                        
                                                        <div class="col-md-6">
                                                            <button type="submit" id="register" class="btn btn-primary btn-sm btn-block text-uppercase" >{{ trans('backend.table.button_done') }}</button>
                                                        </div>
                                                    </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    @endforeach
                                  </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @include('backend.nav')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $("#formattedNumberField").on('keyup', function(){
        var n = parseInt($(this).val().replace(/\D/g,''),10);
        $(this).val(n.toLocaleString());
    });
    $('#appointment_form').on('submit', function () {
       $('#register').attr('disabled', 'true'); 
    });
</script>

<script>
    $("input[data-type='currency']").on({
        keyup: function() {
          formatCurrency($(this));
        },
        blur: function() { 
          formatCurrency($(this), "blur");
        }
    });
    function formatNumber(n) {
      return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, " ")
    }
    function formatCurrency(input, blur) {
      var input_val = input.val();
      if (input_val === "") { return; }
      var original_len = input_val.length;
      var caret_pos = input.prop("selectionStart");
      if (input_val.indexOf(".") >= 0) {
        var decimal_pos = input_val.indexOf(".");
        var left_side = input_val.substring(0, decimal_pos);
        var right_side = input_val.substring(decimal_pos);
        left_side = formatNumber(left_side);
        right_side = formatNumber(right_side);
        if (blur === "blur") {
          right_side += "00";
        }
        right_side = right_side.substring(0, 2);
        input_val = left_side + "." + right_side;
      } else {
        input_val = formatNumber(input_val);
        if (blur === "blur") {
          input_val += ".00";
        }
      }
      input.val(input_val);
      var updated_len = input_val.length;
      caret_pos = updated_len - original_len + caret_pos;
      input[0].setSelectionRange(caret_pos, caret_pos);
    }    
</script>
<script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $(document).on('click', '.sendsuccess', function (){
            var _ = $(this),
            datacid = _.data('id');

            $.ajax({
                type: "POST",
                url: '{{ route("checkout_send_success") }}',
                dataType: 'JSON',
                data: { datacid: datacid },
                success: function(data) {
                    
                    $('#tsendsuccess'+datacid).empty();
                    $('#tsendsuccess'+datacid).append("{{ trans('backend.table.shipment_ok') }} ");
                },
                error: function(ajaxContext) {
                    alert(ajaxContext.responseText)
                }
            });
        });
        
        $(document).ready(function (){
            var delay = 500;
            $('#modelName').keyup(function() {
                var model = $(this).val();
                if(this.value.length > 2) {
                    setTimeout(function (){
                        $.ajax({
                            type: 'POST',
                            url: '{{ route("api_clients") }}', 
                            data: {'model': model},
                            success:function (data) {
                                $(".modelList").empty();
                                $.each(data, function (index, item){
                                    $(".modelList").append($('<option>',{ value: item.name }));
                                });
                            }
                        });
                    }, delay);
                }
            });
        });
        
        $(document).ready(function (){
            var delay = 500;
            $('#modelNameNew').keyup(function() {
                var model = $(this).val();
                if(this.value.length > 2) {
                    setTimeout(function (){
                        $.ajax({
                            type: 'POST',
                            url: '{{ route("api_clients") }}', 
                            data: {'model': model},
                            success:function (data) {
                                $(".modelListNew").empty();
                                $.each(data, function (index, item){
                                    $(".modelListNew").append($('<option>',{ value: item.name }));
                                });
                            }
                        });
                    }, delay);
                }
            });
        });
    </script>
    @endsection