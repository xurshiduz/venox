@extends('layouts.backend')

@section('content')

<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="components-preview mx-auto">
                    <div class="nk-block nk-block-lg">
                        <div class="card card-bordered card-preview">
                            @include('layouts.message.success')
                            @include('layouts.message.error')
                            <div class="card-inner" style="padding: 0.75rem;">
                                <div class="preview-block">
                                    {!! Form::open(['class' => 'invoice-repeater']) !!}
                                    <div class="row gy-1">
                                        <div class="col-lg-2 col-md-3 col-sm-4 d-none d-md-block">
                                            <div class="form-group">
                                                <label class="form-label">{{ trans('backend.input.select_date') }}</label>
                                                <div class="form-control-wrap">
                                                    <div class="form-icon form-icon-right">
                                                        <em class="icon ni ni-calendar-alt"></em>
                                                    </div>
                                                    <input type="text" name="date" value="{{ $item && $item->date ? Carbon\Carbon::parse($item->date)->format('d.m.Y') : Carbon\Carbon::now()->format('d.m.Y') }}" class="form-control date-picker {{ $item ? 'datechange' : null}}" data-id="{{ $item ? $item->id : null}}" placeholder="дата">
                                                </div>
                                            </div>
                                        </div>
                                         <div class="col-lg-2 col-md-3 col-sm-4">
                                            <div class="form-group">
                                                <label class="form-label">{{ trans('backend.index.type_checkout') }}</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2 {{ $item ? 'typechange' : null}}" data-id="{{ $item ? $item->id : null}}" name="checkout_tip_id" required data-search="on">
                                                        @foreach($types as $type)
                                                        <option @if($item && $item->checkout_tip_id == $type->id) selected @endif value="{{ $type->id }}">{{ $type->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-2 col-md-3 col-sm-4">
                                            <div class="form-group">
                                                <label class="form-label">{{ trans('backend.index.clients') }}</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2 {{ $item ? 'clientchange' : null}}" data-id="{{ $item ? $item->id : null}}" name="client_id" required data-search="on">
                                                        @foreach($clients as $client)
                                                        <option @if($item && $item->client_id == $client->id) selected @endif value="{{ $client->id }}">{{ $client->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        @hasanyrole('admin|select_manager')
                                        <div class="col-lg-2 col-md-3 col-sm-4">
                                            <div class="form-group">
                                                <label class="form-label">{{ trans('backend.input.seller') }}</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2" name="manager_id" required data-search="on">
                                                        <option>{{ trans('backend.table.in_select_men') }}</option>
                                                        @foreach($managers as $manager)
                                                        <option @if($item && $item->manager_id == $manager->id) selected @endif value="{{ $manager->id }}">{{ $manager->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        @endhasanyrole
                                        
                                        @hasanyrole('admin')
                                        <div class="col-lg-2 col-md-3 col-sm-4">
                                            <div class="form-group">
                                                <label class="form-label">{{ trans('backend.input.warehouse') }}</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2" name="warehouse_id" required data-search="on">
                                                        @foreach($warehouses as $warehouse)
                                                        <option @if($item && $item->warehouse_id == $warehouse->id) selected @endif value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        @endhasanyrole
                                        
                                        <div class="col-lg-2 col-md-3 col-sm-4">
                                            <div class="form-group">
                                                <label class="form-label">Валюта</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2 {{ $item ? 'checkout_currency_type_change' : null }}" data-id="{{ $item ? $item->id : null }}" name="currency_type" required data-search="on">
                                                        @foreach(App\Models\CurrencyType::where('status', 1)->get() as $curType)
                                                            <option @if($item && $item->currency_type == $curType->id) selected @endif value="{{ $curType->id }}">{{ $curType->name ?? $curType->belgi }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-2 col-md-3 col-sm-4">
                                            <div class="form-group">
                                                <label class="form-label">Курс</label>
                                                <div class="form-control-wrap">
                                                    <input type="number" step="any" id="checkout_currency_price" name="currency_type_price" value="{{ $item->currency_type_price ?? 1 }}" data-id="{{ $item ? $item->id : null}}" class="form-control {{ $item ? 'checkout_currency_price_change' : null}}" placeholder="Курс">
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-lg-2 col-sm-12 d-none d-md-block">
                                            <div class="form-group">
                                                <label class="form-label" for="default-01">{{ trans('backend.input.comment') }}</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" value="{{ $item && $item->reference ? $item->reference : NULL }}" data-id="{{ $item ? $item->id : null}}" class="form-control {{ $item ? 'referencechange' : null}}" name="reference" id="default-01" placeholder="{{ trans('backend.input.comment') }}">
                                                </div>
                                            </div>
                                        </div><!-- 
                                        <div class="col-lg-2 col-sm-2">
                                            <div class="form-group">
                                                <label class="form-label" for="default-06">Выберите файл</label>
                                                <div class="form-control-wrap">
                                                    <div class="form-file">
                                                        <input type="file" name="file" multiple class="form-file-input" id="customFile">
                                                        <label class="form-file-label" for="customFile">Прикрепить</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div> -->
                                    </div>
                                    
                                    <hr style="margin: 13px 0 6px 0;">
                                    <div class="row gy-1">
                                        <div class="col-lg-10 col-md-10 col-sm-9">
                                            <div class="form-group">
                                                <label class="form-label" for="modelName">{!! trans('backend.input.scan_text') !!}</label>
                                                <input list="items" class="form-control" required type="text" name="product_id" placeholder="{!! trans('backend.input.scan_text_input') !!}" autofocus autocomplete="off" id="modelName" />
                                                    <datalist id="items" class="modelList"></datalist>
                                            </div>
                                        </div>
                                        <div class="col-lg-1 col-md-1 col-sm-1">
                                            <label class="form-label" style="color: white;">.</label>
                                            <a class="btn btn-dark btn-block" data-bs-toggle="modal" data-bs-target="#productmodal"><em class="icon ni ni-scan"></em></a>
                                        </div>
                                        <div class="col-lg-1 col-md-1 col-sm-1">
                                            <label class="form-label" style="color: white;">.</label>
                                            <button type="submit" class="btn btn-primary btn-block">OK</button>
                                        </div>
                                    </div>
                                    
                                    <div class="modal fade" tabindex="-1" id="productmodal">
                                        <div class="modal-dialog modal-xl" role="document">
                                            <div class="modal-content">
                                                <a href="#" class="close" data-bs-dismiss="modal" aria-label="Close">
                                                    <em class="icon ni ni-cross"></em>
                                                </a>
                                                <div class="modal-header">
                                                    <h5 class="modal-title">{{ trans('backend.input.scan_text_search') }}</h5>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row gy-1 mb-2">
                                                        <div class="col-lg-10 col-md-10 col-sm-10">
                                                            <div class="form-group">
                                                                <input class="form-control" type="text" placeholder="{!! trans('backend.input.scan_text_input') !!}" autocomplete="off" id="searchmodal" />
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-2 col-md-2 col-sm-2">
                                                            <button type="submit" class="btn btn-primary btn-block">OK</button>
                                                        </div>
                                                    </div>
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>{{ trans('backend.input.foto') }}</th>
                                                                <th>{{ trans('backend.input.comment') }} </th>
                                                                <th>{{ trans('backend.input.barcode_short') }}</th>
                                                                <th>{{ trans('backend.input.select') }}</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="modeltable">
                                                            <tr>
                                                                <td colspan="4" class="text-center">{!! trans('backend.input.scan_text') !!}</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {!! Form::close() !!}
                                    @if($item)
                                    
                                    @hasanyrole('admin2|cashier2|report2')
                                        <hr style="margin: 10px 0;">
                                        @php
                                        $itogsum = 0;
                                        @endphp
                                        @foreach($item->details()->get() as $detail)
                                            @if($detail->price && $detail->currencytypeid)
                                            @php
                                                $itogsum += ($detail->currencytypeid->currencyid->first()->price * ($detail->price * $detail->qty));
                                            @endphp
                                            @endif
                                        @endforeach
                                        
                                        @php
                                            $itogsumusd = $itogsum / App\Models\Currency::where('type_id', 2)->orderBy('id', 'asc')->first()->price;
                                        @endphp
                                        
                                        
                                        <span>Итого:</span>
                                        @foreach(App\Models\CurrencyType::where('status', 1)->get() as $cur)
                                        <span><b>{{ $cur->belgi }}:</b> {{ number_format($itogsum / App\Models\Currency::where('type_id', $cur->id)->orderBy('id', 'asc')->first()->price, 2, '.', ' ') }}</span>
                                        @endforeach
                                    @endhasanyrole
                                    
                                    <div class="table-responsive pt-2">
                                        <table class="table table-bordered d-none d-md-inline-table">
                                            <thead>
                                                <tr>
                                                    <th>Наименование</th>
                                                    <th width="16px" style="padding:0">Фото</th>
                                                    <th>Штрихкод</th>
                                                    <th>Склад</th>
                                                    <th width="150px">Кол.во</th>
                                                    <th style="border: 0px">Остаток</th>
                                                    @hasanyrole('admin|cashier|report|sale')
                                                    <!--<th>Валюта</th>-->
                                                    <th width="150px">Цена</th>
                                                    <th width="150px">Итого цена</th>
                                                    @endhasanyrole
                                                    <th width="150px">Бонус</th>
                                                    <th><em class="icon ni ni-trash"></em></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                
                                                @foreach($item->details()->orderBy('id', 'desc')->get() as $detail)
                                                @php
                                                    // Ushbu tovarning shu mijozga oldingi savdo tarixini topamiz
                                                    $lastHistory = App\Models\CheckoutDetail::join('checkouts', 'checkout_details.checkout_id', '=', 'checkouts.id')
                                                        ->where('checkouts.client_id', $item->client_id) // Faqat shu mijoz
                                                        ->where('checkout_details.product_id', $detail->product_id) // Faqat shu tovar
                                                        ->where('checkouts.date', '<', $item->date) // Hozirgi chek sanasidan OLDINGIlari
                                                        ->orderBy('checkouts.date', 'desc') // Eng yangisi (oxirgisi) tepadaga chiqsin
                                                        ->select('checkout_details.price', 'checkouts.date') // Kerakli ustunlar (narx va sana)
                                                        ->first();
                                                @endphp
                                                <input type="hidden" id="model" class="model" value="{{ $detail->id }}">
                                                <tr>
                                                    <td>{{$loop->iteration}}) {{ $detail->prodid->name }}
                                                    @hasanyrole('admin|cashier|report|sale')
                                                    <p class="mt-1" style="font-size: 12px; line-height: 8px;">{{ $detail->prodid->price >= 1 ? 'Прайс: ' . number_format($detail->prodid->price, 2, '.', ' ') . ' ' . $detail->prodid->currencyid->name . ' | ' : null }} 
                                                    {{ $detail->prodid->wholesale_price >= 1 ? 'Оптовая цена: ' . number_format($detail->prodid->wholesale_price, 2, '.', ' ') . ' ' . $detail->prodid->currencyid->name . ' | ' : null }}
                                                    {{ $detail->prodid->checkoutdetails()->max('price') >= 1 ? 'Высокий: ' . number_format($detail->prodid->checkoutdetails()->max('price'), 2, '.', ' ') . ' $ | ' : null }}
                                                    @if($lastHistory)
                                                        Клиент: {{ number_format($lastHistory->price, 2, '.', ' ') }} $ {{ \Carbon\Carbon::parse($lastHistory->date)->format('d.m.Y') }}
                                                    @endif
                                                    </p>
                                                    @endhasanyrole
                                                    </td>
                                                    <td style="padding:0">
                                                        @if($detail->prodid->image) 
                                                        <div class="gallery">
                                                            <a href="/upload/product_image/{{ $detail->prodid->image }}">
                                                              <img width="32px" height="32px" src="/upload/product_image/{{ $detail->prodid->image }}" alt="" />
                                                            </a>
                                                            @if($detail->prodid->image_2) <a href="/upload/product_image/{{ $detail->prodid->image_2 }}"></a> @endif
                                                            @if($detail->prodid->image_3) <a href="/upload/product_image/{{ $detail->prodid->image_3 }}"></a> @endif
                                                        </div>
                                                        @else
                                                            <div class="gallery">
                                                                <a href="/upload/no_photo.jpg">
                                                                  <img width="32px" src="/upload/no_photo.jpg" alt="" />
                                                                </a>
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td>{{ $detail->prodid->barcode }}</b></td>
                                                    <td><span data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $detail->warehouseid ? $detail->warehouseid->name : NULL }}">{{ $detail->warehouseid ? $detail->warehouseid->num_code : NULL }}</span></td>
                                                    <td style="padding: 0px;">
                                                            <input style="width: 100%; border: 0px; text-align: center; height: 36px;" type="number" class="qty_change" placeholder="number" data-id="{{ $detail->id }}" value="{{ $detail->qty }}" min="1">
                                                    </td>
                                                    
                                                    <td width="100px" style="border: 0px">{{ $detail->prodid->stockid->where('warehouse_id', $item->warehouse_id)->sum('stock') }} {{ $detail->prodid->unitid ? $detail->prodid->unitid->name : null}}</td>
                                                    @hasanyrole('admin|cashier|report|sale')
                                                    <td width="100px" style="padding: 0px; display: none">
                                                        <select style="border: 0px" class="form-select currency" data-id="{{ $detail->id }}" required name="currency_type">
                                                            @foreach(App\Models\CurrencyType::where('status', 1)->orderBy('id', 'asc')->get() as $currency)
                                                            <option @if($detail->currency_type == $currency->id) selected @endif value="{{ $currency->id }}">{{ $currency->belgi }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td style="padding: 0px;">
                                                        <input style="min-width: 110px; border: 0px;" id="get_price_1_{{ $detail->id }}" type="text" data-id="{{ $detail->id }}" class="form-control price" data-type="currency" value="{{ number_format($detail->price, 2, '.', ' ') }}">
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="total_detail" data-id="{{ $detail->id }}" id="get_total_price_1_{{ $detail->id }}">{{ number_format($detail->total_price, 2, '.', ' ') }}</span>
                                                        <!--<input id="get_total_price_1_{{ $detail->id }}" style="min-width: 110px; border: 0px;" type="text" data-id="{{ $detail->id }}" class="form-control total_detail" data-type="currency" value="{{ number_format($detail->total_price, 2, '.', ' ') }}">-->
                                                    </td>
                                                    @endhasanyrole
                                                    <td style="padding: 0px;">
                                                            <input style="width: 100%; border: 0px; text-align: center; height: 36px;" type="number" class="bonus_change" data-id="{{ $detail->id }}" value="{{ $detail->bonus }}">
                                                    </td>
                                                    <td width="50px"><a href="{{ route('checkout_delete', ['id' => $detail->code]) }}"><em class="icon ni ni-trash"></em></a></td>
                                                </tr>
                                                @endforeach
                                                @php
                                                    $baseTotalSum = 0;
                                                    if($item) {
                                                        foreach($item->details()->get() as $d) {
                                                            $basePrice = $d->org_price ?? $d->price;
                                                            $baseTotalSum += $basePrice * $d->qty;
                                                        }
                                                    }
                                                @endphp
                                                <tr>
                                                    <td class="text-end" colspan="7"><b>Скидка в %</b></td>
                                                    <td>
                                                        <div class="d-flex align-items-center" style="gap: 5px;">
                                                            <input style="min-width: 70px; border: 1px solid #dbdfea; border-radius: 4px; text-align: center; height: 36px;" id="discount" type="number" data-id="{{ $item->id }}" class="form-control discount" value="{{ $item->discount ?? 0 }}" min="0" max="100">
                                                            <button type="button" class="btn btn-sm btn-success apply-discount-btn" data-id="{{ $item->id }}" title="Сақлаш"><em class="icon ni ni-check"></em></button>
                                                        </div>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-end" colspan="7"><b>Итого сумма</b></td>
                                                    <td class="text-center"><span id="get_total_price_3" data-base="{{ $baseTotalSum }}">{{ number_format($item->total_price, 2, '.', ' ') }}</span></td>
                                                    <td></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        
                                        <table class="table table-bordered d-md-none">
                                            <thead>
                                                <tr>
                                                    <th colspan="3" class="text-center">{{ trans('backend.input.name') }}</th>
                                                    <th colspan="2"></th>
                                                </tr>
                                                <tr>
                                                    <th width="150px">Кол.во</th>
                                                    <th class="d-none d-md-block" style="border: 0px">Остаток</th>
                                                    @hasanyrole('admin|cashier|report')
                                                    <th width="150px">Цена</th>
                                                    <th width="150px">Итого цена</th>
                                                    @endhasanyrole
                                                    <th width="150px">Бонус</th>
                                                    <th><em class="icon ni ni-trash"></em></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($item->details()->orderBy('id', 'desc')->get() as $detail)
                                                <input type="hidden" id="model" class="model" value="{{ $detail->id }}">
                                                <tr>
                                                    <td colspan="3">{{$loop->iteration}}) {{ $detail->prodid->name }} <b>{{ $detail->prodid->barcode }}</b></td>
                                                    <td colspan="2">{{ $detail->warehouseid ? $detail->warehouseid->num_code : NULL }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 0px;">
                                                            <input style="width: 100%; border: 0px; text-align: center; height: 36px;" type="number" class="qty_change" placeholder="number" data-id="{{ $detail->id }}" value="{{ $detail->qty }}" min="1">
                                                    </td>
                                                    
                                                    <td width="100px" class="d-none d-md-block" style="border: 0px">{{ $detail->prodid->stockid->where('warehouse_id', $item->warehouse_id)->sum('stock') }} {{ $detail->prodid->unitid ? $detail->prodid->unitid->name : null}}</td>
                                                    @hasanyrole('admin|cashier|report|sale')
                                                    <td width="100px" style="padding: 0px;  display: none">
                                                        <select style="border: 0px" class="form-select currency" data-id="{{ $detail->id }}" required name="currency_type">
                                                            @foreach(App\Models\CurrencyType::where('status', 1)->orderBy('id', 'asc')->get() as $currency)
                                                            <option @if($detail->currency_type == $currency->id) selected @endif value="{{ $currency->id }}">{{ $currency->belgi }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td style="padding: 0px;">
                                                        <input style="min-width: 110px; border: 0px;" id="get_price_2_{{ $detail->id }}" type="text" data-id="{{ $detail->id }}" class="form-control price" data-type="currency" value="{{ number_format($detail->price, 2, '.', ' ') }}">
                                                    </td>
                                                    
                                                    <td class="text-center">
                                                        <span class="total_detail" data-id="{{ $detail->id }}" id="get_total_price_1_{{ $detail->id }}">{{ number_format($detail->total_price, 2, '.', ' ') }}</span>
                                                        
                                                    </td>
                                                    @endhasanyrole
                                                    <td style="padding: 0px;">
                                                            <input style="width: 100%; border: 0px; text-align: center; height: 36px;" type="number" class="bonus_change" data-id="{{ $detail->id }}" value="{{ $detail->bonus }}">
                                                    </td>
                                                    <td width="50px"><a href="{{ route('checkout_delete', ['id' => $detail->code]) }}"><em class="icon ni ni-trash"></em></a></td>
                                                </tr>
                                                @endforeach
                                                <tr>
                                                    <td class="text-center" colspan="2"><b>Скидка в %</b></td>
                                                    <td>
                                                        <div class="d-flex align-items-center" style="gap: 5px;">
                                                            <input style="min-width: 70px; border: 1px solid #dbdfea; border-radius: 4px; text-align: center; height: 36px;" type="number" data-id="{{ $item->id }}" class="form-control discount" value="{{ $item->discount ?? 0 }}" min="0" max="100">
                                                            <button type="button" class="btn btn-sm btn-success apply-discount-btn" data-id="{{ $item->id }}"><em class="icon ni ni-check"></em></button>
                                                        </div>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-center" colspan="2"><b>Итого сумма</b></td>
                                                    <td><span id="get_total_price_4" data-base="{{ $baseTotalSum }}">{{ number_format($item->total_price, 2, '.', ' ') }}</span></td>
                                                    <td></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    @php
                                        $commissionOptions = [
                                            'special' => 'Spes — KPI 0% · Agent 8% · Venox 0%',
                                            'contract' => 'Shartnoma — KPI 0% · Agent 8% · Venox 5%',
                                            'venox_10' => 'Venox bonus 10% — KPI 5% · Agent 8%',
                                            'venox_15' => 'Venox bonus 15% — KPI 5% · Agent 8%',
                                            'venox_20' => 'Venox bonus 20% — KPI 5% · Agent 8%',
                                            'venox_25' => 'Venox bonus 25% — KPI 5% · Agent 8%',
                                        ];
                                        $commissionSelected = $item->commission_scheme;
                                        $factoryPercent = 100
                                            - (float) ($item->kpi_percent ?? 0)
                                            - (float) ($item->agent_percent ?? 0)
                                            - (float) ($item->venox_bonus_percent ?? 0);
                                    @endphp
                                    <div class="card border mt-3 commission-card">
                                        <div class="card-inner py-3">
                                            <div class="row gy-2 align-items-end">
                                                <div class="col-lg-5 col-md-6">
                                                    <label class="form-label mb-1">KPI va bonus hisoblash turi</label>
                                                    <select class="form-select commission-scheme" data-id="{{ $item->id }}">
                                                        <option value="">Tanlanmagan</option>
                                                        @foreach($commissionOptions as $value => $label)
                                                            <option value="{{ $value }}" @if($commissionSelected === $value) selected @endif>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-lg-5 col-md-6">
                                                    <div class="d-flex flex-wrap commission-summary" style="gap: 8px;">
                                                        <span class="badge bg-outline-primary">KPI: <b class="commission-kpi">{{ number_format((float) ($item->kpi_percent ?? 0), 0) }}</b>%</span>
                                                        <span class="badge bg-outline-info">Agent: <b class="commission-agent">{{ number_format((float) ($item->agent_percent ?? 0), 0) }}</b>%</span>
                                                        <span class="badge bg-outline-warning">Venox: <b class="commission-venox">{{ number_format((float) ($item->venox_bonus_percent ?? 0), 0) }}</b>%</span>
                                                        <span class="badge bg-outline-success">Zavod: <b class="commission-factory">{{ number_format($factoryPercent, 0) }}</b>%</span>
                                                    </div>
                                                </div>
                                                <div class="col-lg-2 col-md-12">
                                                    <button type="button" class="btn btn-success btn-block apply-commission-btn" data-id="{{ $item->id }}">
                                                        <em class="icon ni ni-check"></em><span>Saqlash</span>
                                                    </button>
                                                </div>
                                            </div>
                                            <small class="text-soft d-block mt-2">Bu foizlar chegirma emas. Ular ushbu nakladnoy uchun saqlanadi va kassa Excel hisobotida ishlatiladi.</small>
                                        </div>
                                    </div>
                                    
                                    @endif
                                    @if($item && $item->details()->count())
                                    <hr style="margin-top: 0px;">
                                    <div class="row">
                                        
                                        <div class="col-md-8">
                                            <a href="{{ route('checkout_done_status', ['id' => $item->code, 'page' => $page]) }}" class="btn btn-warning btn-block">{{ trans('backend.table.status_done') }}</a>
                                        </div>
                                        <div class="col-md-4">
                                            <a href="{{ route('checkout_check', ['id' => $item->code])}}" class="btn btn-dark btn-block">{{ trans('backend.index.button_print') }}</a>
                                        </div>
                                    </div>
                                    
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" tabindex="-1" id="productmodal222">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <a href="#" class="close" data-bs-dismiss="modal" aria-label="Close">
                <em class="icon ni ni-cross"></em>
            </a>
            <div class="modal-header">
                <h5 class="modal-title">{{ trans('backend.input.scan_text_search') }}</h5>
            </div>
            <div class="modal-body">
                <div class="row gy-1 mb-2">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="form-group">
                            <input class="form-control" type="text" name="product_id" placeholder="{!! trans('backend.input.scan_text_input') !!}" autocomplete="off" id="searchmodal" />
                        </div>
                    </div>
                </div>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ trans('backend.input.foto') }}</th>
                            <th>{{ trans('backend.input.comment') }} </th>
                            <th>{{ trans('backend.input.barcode_short') }}</th>
                            <th>{{ trans('backend.input.select') }}</th>
                        </tr>
                    </thead>
                    <tbody class="modeltable">
                        <tr>
                            <td colspan="4" class="text-center">{!! trans('backend.input.scan_text') !!}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@if($item)
@foreach($item->details()->get() as $detail)
<div class="modal fade" tabindex="-1" id="modalDefault{{ $detail->id }}">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a href="#" class="close" data-bs-dismiss="modal" aria-label="Close">
                <em class="icon ni ni-cross"></em>
            </a>
            <div class="modal-header">
                <h5 class="modal-title">{{$loop->iteration}}) {{ $detail->prodid->id }}</b></h5>
            </div>
            <div class="modal-body">
                <form action="{{ route('one_qty_checkout', ['id' => $detail->code]) }}" method="POST">
                    @csrf
                <div class="form-group">
                    <label class="form-label">Количество?</label>
                    <div class="form-control-wrap"> 
                        <div id="slider{{ $detail->id }}"></div>
                        <input id="sliderValueInput{{ $detail->id }}" name="qty_one" type="hidden" value=""> 
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Сохранить</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach
@endif
@endsection

@section('script')
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
    function myFunction() {
        var x =
        document.getElementById("myNumber").max;
        document.getElementById("demo").innerHTML = x;
    }
    
    document.getElementById("idOfButton").onclick = function() {
        this.disabled = true;
    }
</script>
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    $('.checkout_currency_type_change').change(function() {
        var _ = $(this),
            cid = _.data('id'),
            currency_type = _.val();
    
        if (!cid) return;
    
        $.ajax({
            type: "POST",
            url: '{{ route("checkout_currency_type_change") }}',
            dataType: 'JSON',
            data: {
                cid: cid,
                currency_type: currency_type
            },
            success: function(data) {
                if (data.status === 'success') {
                    // Yangi olingan kursni inputga yozib qo'yamiz
                    $('#checkout_currency_price').val(data.price);
                    // alert('Валюта ва курс муваффақиятли янгиланди!');
                }
            },
            error: function(xhr) {
                console.log(xhr.responseText);
            }
        });
    });
    
    // 2. Valyuta kursi qo'lda (ruchnoy) o'zgartirilganda
    let courseTimeout = null;
    $('.checkout_currency_price_change').on('input', function() {
        var _ = $(this),
            cid = _.data('id'),
            price = _.val();
    
        if (!cid || price === '') return;
    
        clearTimeout(courseTimeout);
        // Yozib bo'lgandan so'ng 500ms dan keyin bazaga so'rov yuboradi
        courseTimeout = setTimeout(function() {
            $.ajax({
                type: "POST",
                url: '{{ route("checkout_currency_price_change") }}',
                dataType: 'JSON',
                data: {
                    cid: cid,
                    price: price
                },
                success: function(data) {
                    if (data.status === 'success') {
                        console.log("Kurs qo'lda yangilandi: " + data.price);
                    }
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            });
        }, 500);
    });
    

    function customFormatMoney(amount) {
        return Number(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$& ');
    }
    
    // 1. Skidka yozilganda faqat preview qilib vizual ko'rsatamiz (ajax ketmaydi)
    $('.discount').on('input', function() {
        var _ = $(this);
        var val = parseFloat(_.val());
        
        if (isNaN(val) || val < 0) { val = 0; }
        if (val > 100) { val = 100; _.val(100); }
    
        // Ikkala inputni sinxronlash (Mobil va PC)
        $('.discount').not(_).val(val);
    
        var baseTotal1 = parseFloat($('#get_total_price_3').data('base')) || 0;
        var newTotal = baseTotal1 - (baseTotal1 * (val / 100));
    
        $('#get_total_price_3').text(customFormatMoney(newTotal) + ' (Кутилмоқда)');
        $('#get_total_price_4').text(customFormatMoney(newTotal) + ' (Кутилмоқда)');
    });
    
    // 2. "Saqlash" (Check) tugmasi bosilganda AJAX orqali barcha qatorlarni o'zgartirish
    $('.apply-discount-btn').click(function(e) {
        e.preventDefault();
        var btn = $(this);
        var pcid = btn.data('id');
        var pbrid = btn.siblings('.discount').val();
    
        if(pbrid === "") pbrid = 0;
    
        btn.prop('disabled', true); // Ikkimarta bosilishini oldini olish
    
        $.ajax({
            type: "POST",
            url: '{{ route("checkout_discount") }}',
            dataType: 'JSON',
            data: {
                pcid: pcid,
                pbrid: pbrid
            },
            success: function(data) {
                if (data.status === 'success') {
                    // Yangilangan asosiy summani yozish va data-base ni yangilash
                    $('#get_total_price_3').text(data.total_price).data('base', data.base_total);
                    $('#get_total_price_4').text(data.total_price).data('base', data.base_total);
                    
                    // DOM dagi har bir tovar (detail) narxi va jami narxlarini o'zgartiramiz
                    $.each(data.details, function(index, detail) {
                        $('#get_price_1_' + detail.id).val(detail.price);
                        $('#get_price_2_' + detail.id).val(detail.price);
                        $('#get_total_price_1_' + detail.id).text(detail.total_price);
                    });
    
                    $('.discount').val(data.discount);
                }
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                alert("Xatolik yuz berdi!");
            },
            complete: function() {
                btn.prop('disabled', false);
            }
        });
    });

    $('.apply-commission-btn').click(function(e) {
        e.preventDefault();
        var btn = $(this);
        var scheme = $('.commission-scheme').val();

        if (!scheme) {
            alert('KPI va bonus hisoblash turini tanlang.');
            return;
        }

        btn.prop('disabled', true);
        $.ajax({
            type: 'POST',
            url: '{{ route("checkout_commission_scheme") }}',
            dataType: 'JSON',
            data: {
                checkout_id: btn.data('id'),
                scheme: scheme
            },
            success: function(data) {
                if (data.status === 'success') {
                    $('.commission-kpi').text(data.kpi_percent);
                    $('.commission-agent').text(data.agent_percent);
                    $('.commission-venox').text(data.venox_bonus_percent);
                    $('.commission-factory').text(data.factory_percent);
                    alert('KPI va bonus hisoblash turi saqlandi.');
                }
            },
            error: function(xhr) {
                var message = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Hisoblash turini saqlashda xatolik yuz berdi.';
                alert(message);
            },
            complete: function() {
                btn.prop('disabled', false);
            }
        });
    });
    
    $('.bonus_change').change(function() {
        var _ = $(this),
            cid = _.data('id'),
            brid = _.val();

        $.ajax({
            type: "POST",
            url: '{{ route('bonus_checkout') }}',
            dataType: 'JSON',
            data: {
                cid: cid,
                brid: brid
            },
            success: function(data) {
                if (data.status) {
                    //$('#price_id'+cid).val(data.price);
                }
            },
            error: function(ajaxContext) {
                alert(ajaxContext.responseText)
            }
        });
    });
    
    $('.qty_change').change(function() {
        var _ = $(this),
            cid = _.data('id'),
            brid = _.val();

        $.ajax({
            type: "POST",
            url: '{{ route('qty_checkout') }}',
            dataType: 'JSON',
            data: {
                cid: cid,
                brid: brid
            },
            success: function(data) {
                if (data.status) {
                    //$('#price_id'+cid).val(data.price);
                }
            },
            error: function(ajaxContext) {
                alert(ajaxContext.responseText)
            }
        });
    });
    
    $('.total').change(function() {
        var _ = $(this),
            pcid = _.data('id'),
            pbrid = _.val();

        $.ajax({
            type: "POST",
            url: '{{ route("price_total_checkout") }}',
            dataType: 'JSON',
            data: {
                pcid: pcid,
                pbrid: pbrid
            },
            success: function(data) {
                if (data.status) {
                    //$('#price_id'+cid).val(data.price);
                }
            },
            error: function(ajaxContext) {
                alert(ajaxContext.responseText)
            }
        });
    });
    
    $('.total_detail').change(function() {
        var _ = $(this),
            pcid = _.data('id'),
            pbrid = _.val();

        $.ajax({
            type: "POST",
            url: '{{ route("price_total_detail_checkout") }}',
            dataType: 'JSON',
            data: {
                pcid: pcid,
                pbrid: pbrid
            },
            success: function(data) {
                $('#get_price_1_'+pcid).val(data.price);
                $('#get_price_2_'+pcid).val(data.price);
                document.getElementById("get_total_price_3").innerHTML = data.total_price;
                document.getElementById("get_total_price_4").innerHTML = data.total_price;
            },
            error: function(ajaxContext) {
                alert(ajaxContext.responseText)
            }
        });
    });
    
    $('.price').change(function() {
        var _ = $(this),
            pcid = _.data('id'),
            pbrid = _.val();

        $.ajax({
            type: "POST",
            url: '{{ route("price_checkout") }}',
            dataType: 'JSON',
            data: {
                pcid: pcid,
                pbrid: pbrid
            },
            success: function(data) {
                $('#get_total_price_1_'+pcid).val(data.one_total_price);
                $('#get_total_price_2_'+pcid).val(data.one_total_price);
                document.getElementById("get_total_price_3").innerHTML = data.total_price;
                document.getElementById("get_total_price_4").innerHTML = data.total_price;
            },
            error: function(ajaxContext) {
                alert(ajaxContext.responseText)
            }
        });
    });
    
    $('.currency').change(function() {
        var _ = $(this),
            curcid = _.data('id'),
            currency = _.val();

        $.ajax({
            type: "POST",
            url: '{{ route("currency_checkout") }}',
            dataType: 'JSON',
            data: {
                curcid: curcid,
                currency: currency
            },
            success: function(data) {
                if (data.status) {
                    //$('#price_id'+cid).val(data.price);
                }
            },
            error: function(ajaxContext) {
                alert(ajaxContext.responseText)
            }
        });
    });
    
    $('.clientchange').change(function() {
        var _ = $(this),
            cid = _.data('id'),
            clientid = _.val();

        $.ajax({
            type: "POST",
            url: '{{ route("client_checkout_change") }}',
            dataType: 'JSON',
            data: {
                cid: cid,
                clientid: clientid
            },
            success: function(data) {
                if (data.status) {
                    //$('#price_id'+cid).val(data.price);
                }
            },
            error: function(ajaxContext) {
                alert(ajaxContext.responseText)
            }
        });
    });
    
    $('.referencechange').change(function() {
        var _ = $(this),
            cid = _.data('id'),
            reference = _.val();

        $.ajax({
            type: "POST",
            url: '{{ route("checkout_reference_change") }}',
            dataType: 'JSON',
            data: {
                cid: cid,
                reference: reference
            },
            success: function(data) {
                if (data.status) {
                    //$('#price_id'+cid).val(data.price);
                }
            },
            error: function(ajaxContext) {
                alert(ajaxContext.responseText)
            }
        });
    });
    
    $('.datechange').change(function() {
        var _ = $(this),
            cid = _.data('id'),
            typeid = _.val();

        $.ajax({
            type: "POST",
            url: '{{ route("date_checkout_change") }}',
            dataType: 'JSON',
            data: {
                cid: cid,
                typeid: typeid
            },
            success: function(data) {
                if (data.status) {
                    //$('#price_id'+cid).val(data.price);
                }
            },
            error: function(ajaxContext) {
                alert(ajaxContext.responseText)
            }
        });
    });
    
    $('.typechange').change(function() {
        var _ = $(this),
            cid = _.data('id'),
            typeid = _.val();

        $.ajax({
            type: "POST",
            url: '{{ route("type_checkout_change") }}',
            dataType: 'JSON',
            data: {
                cid: cid,
                typeid: typeid
            },
            success: function(data) {
                if (data.status) {
                    //$('#price_id'+cid).val(data.price);
                }
            },
            error: function(ajaxContext) {
                alert(ajaxContext.responseText)
            }
        });
    });
    
    $('.totalcurrency').change(function() {
        var _ = $(this),
            curcid = _.data('id'),
            currency = _.val();

        $.ajax({
            type: "POST",
            url: '{{ route("currencies_checkout") }}',
            dataType: 'JSON',
            data: {
                curcid: curcid,
                currency: currency
            },
            success: function(data) {
                if (data.status) {
                    //$('#price_id'+cid).val(data.price);
                }
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
                        url: '{{ route("products_api") }}', 
                        data: {'model': model},
                        success:function (data) {
                            $(".modelList").empty();
                            $.each(data, function (index, item){
                                $(".modelList").append($('<option>',{ value: item.fullname }));
                            });
                        }
                    });
                }, delay);
            }
        });
    });
    
        $('#searchmodal').change(function() {
            var model = $(this).val();
            if(this.value.length > 5) {
                $.ajax({
                    type: 'POST',
                    url: '{{ route("products_api") }}', 
                    data: {'model': model},
                    success:function (data) {
                        $(".modeltable").empty();
                        
                        $.each(data, function (index, item) {
							$('.modeltable').append("<tr>\
										<td>"+item.image+"</td>\
										<td>"+item.name+"</td>\
										<td>"+item.barcode+"</td>\
										<td><input type='checkbox' name='modal_product' onChange='this.form.submit()' value='"+item.barcode+"'></td>\
										</tr>");
						})
                    }
                });
            } else {
                $(".modeltable").empty();
                $('.modeltable').append("<tr>\
										<td colspan='4' class='text-center'>Сканируйте штрих-код или ищите товары</td>\
										</tr>");
            }
        });
</script>

<script>
$('.gallery').each(function() { // the containers for all your galleries
    $(this).magnificPopup({
        delegate: 'a', // the selector for gallery item
        type: 'image',
        gallery: {
          enabled:true
        }
    });
});
</script>
@endsection
