@extends('layouts.backend')
@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.css">
@endsection
@section('content')

<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="components-preview mx-auto">
                    <div class="nk-block nk-block-lg">
                        <!--<div class="row">
                            <div class="col-md-10 mb-3">
                                <form method="POST" action="{{ route('products_search') }}">
                                    @csrf
                                    <input type="text" class="form-control" value="{{ $keyword ? $keyword : NULL }}" name="search" required placeholder="Поиск по штрих-код и наименование">
                                </form>
                            </div>
                            <div class="col-md-2 mb-2">
                               <a href="{{ route('product_form') }}" class="btn btn-primary btn-block">Добавить</a> 
                            </div>
                        </div>-->
                            @include('layouts.message.success')
                            @include('layouts.message.error')
                        @php($curs = App\Models\CurrencyType::all())
                        <div class="card">
                            <div class="card-inner position-relative card-tools-toggle" style="padding: 0.75rem 0.75rem; border-top: 1px solid #dbdfea; border-left: 1px solid #dbdfea; border-right: 1px solid #dbdfea;">
                                <div class="card-title-group">
                                    <div class="card-tools">
                                        <div class="form-inline flex-nowrap gx-3">
                                            <div class="btn-wrap">
                                                @hasanyrole('admin|sale|cashier')
                                                <span class="d-none d-md-block"><a href="{{ route('product_form') }}" class="btn btn-sm btn-primary">{{ trans('backend.table.add_from') }}</a></span>
                                                <span class="d-md-none"><a href="{{ route('product_form') }}" class="btn btn-dim btn-outline-primary btn-icon"><em class="icon ni ni-arrow-right"></em></a></span>
                                                @endhasanyrole
                                            </div>
                                            <div class="btn-wrap">
                                                @hasanyrole('admin|sale|cashier')
                                                <span class="d-none d-md-block"><a href="{{ route('products_index', ['archive' => 'archive'])}}" class="btn btn-sm btn-warning">Архив</a></span>
                                                <span class="d-md-none"><a href="{{ route('products_index', ['archive' => 'archive'])}}" class="btn btn-dim btn-outline-warning btn-icon"><em class="icon ni ni-trash"></em></a></span>
                                                @endhasanyrole
                                            </div>
                                            
                                            <div class="btn-wrap">
                                                <span class="d-none d-md-block"><a href="{{ route('products_new_search') }}" class="btn btn-sm btn-success">Поиск</a></span>
                                                <span class="d-md-none"><a href="{{ route('products_new_search') }}" class="btn btn-dim btn-outline-success btn-icon"><em class="icon ni ni-arrow-right"></em></a></span>
                                            </div>
                                        </div><!-- .form-inline -->
                                    </div><!-- .card-tools -->
                                    <div class="card-tools me-n1">
                                        <ul class="btn-toolbar gx-1">
                                            <li>
                                                <a href="#" class="btn btn-icon search-toggle toggle-search" data-target="search"><em class="icon ni ni-search"></em></a>
                                            </li><!-- li -->
                                            <li class="btn-toolbar-sep"></li><!-- li -->
                                            <li class="d-none d-md-none">
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
                                                                            <span class="sub-title dropdown-title">Фильтр</span>
                                                                        </div>
                                                                        <div class="dropdown-body dropdown-body-rg">
                                                                            <div class="row gx-6 gy-3">
                                                                                <div class="col-12">
                                                                                    <div class="custom-control custom-control-sm custom-checkbox">
                                                                                        <input type="checkbox" class="custom-control-input" id="hasBalance">
                                                                                        <label class="custom-control-label" for="hasBalance"> В наличии</label>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-6">
                                                                                    <div class="form-group">
                                                                                        <label class="overline-title overline-title-alt">Склад</label>
                                                                                        <select class="form-select js-select2">
                                                                                            <option value="all">Все</option>
                                                                                            
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-6">
                                                                                    <div class="form-group">
                                                                                        <label class="overline-title overline-title-alt">Категория</label>
                                                                                        <select class="form-select js-select2">
                                                                                            <option value="all">Все</option>
                                                                                            
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-12">
                                                                                    <div class="form-group">
                                                                                        <button type="button" class="btn btn-secondary btn-block">Принимать</button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="dropdown-foot between">
                                                                            <a class="clickable" href="#">Reset Filter</a>
                                                                        </div>
                                                                    </div><!-- .filter-wg -->
                                                                </div><!-- .dropdown -->
                                                            </li><!-- li -->
                                                            <li>
                                                                <div class="dropdown">
                                                                    <a href="#" class="btn btn-trigger btn-icon dropdown-toggle" data-bs-toggle="dropdown">
                                                                        <em class="icon ni ni-setting"></em>
                                                                    </a>
                                                                    <div class="dropdown-menu dropdown-menu-xs dropdown-menu-end">
                                                                        <ul class="link-check">
                                                                            <li><span>Show</span></li>
                                                                            <li class="active"><a href="#">10</a></li>
                                                                            <li><a href="#">20</a></li>
                                                                            <li><a href="#">50</a></li>
                                                                        </ul>
                                                                        <ul class="link-check">
                                                                            <li><span>Order</span></li>
                                                                            <li class="active"><a href="#">DESC</a></li>
                                                                            <li><a href="#">ASC</a></li>
                                                                        </ul>
                                                                    </div>
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
                                        <form method="GET" action="{{ route('products_search') }}">
                                            <div class="search-content">
                                                <a href="#" class="search-back btn btn-icon toggle-search" data-target="search"><em class="icon ni ni-arrow-left"></em></a>
                                                <input type="text" class="form-control border-transparent form-focus-none" value="{{ $keyword ? $keyword : NULL }}" name="search" required placeholder="Поиск по штрих-код и наименование">
                                                <button class="search-submit btn btn-icon"><em class="icon ni ni-search"></em></button>
                                            </div>
                                        </form>
                                    </div>
                                </div><!-- .card-search -->
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                  <thead class=" text-nowrap">
                                    <tr class="text-center">
                                      <th scope="col" width="64px">{{ trans('backend.input.foto') }}</th>
                                      <th scope="col">{{ trans('backend.table.name') }}</th>
                                      <th scope="col">{{ trans('backend.table.stock_name_short') }}</th>
                                      <th width="115px">{{ trans('backend.input.barcode_short') }}</th>
                                      @hasanyrole('admin|sale|cashier')
                                      <th width="100px">{{ trans('backend.table.action') }}</th>
                                      <!--<th scope="col">Категория</th>-->
                                      <th width="110px" style="padding: 1px;">Цена</th>
                                      <th width="110px" style="padding: 1px;">Оптовая цена</th>
                                      <th style="padding: 1px;">Подробнее</th>
                                      <!--<th width="110px" style="padding: 1px;">Бренды</th>-->
                                      @endhasanyrole
                                      <th width="80px" style="padding: 1px;">{{ trans('backend.table.etiketka') }}</th>
                                      <th width="110px">{{ trans('backend.table.add_user') }}</th>
                                      <th width="80px">{{ trans('backend.table.date') }}</th>
                                      @hasanyrole('admin|sale|cashier')
                                      <th><em class="icon ni ni-trash"></em></th>
                                      @endhasanyrole
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @foreach($data as $item)
                                    <tr class="text-center" style="vertical-align: middle;">
                                      <td style="padding: 3px;">
                                        @if($item->image) 
                                        <div class="gallery">
                                            <a href="/upload/product_image/{{ $item->image }}">
                                              <img width="64px" height="64px" src="/upload/product_image/{{ $item->image }}" alt="" />
                                            </a>
                                            @if($item->image_2) <a href="/upload/product_image/{{ $item->image_2 }}"></a> @endif
                                            @if($item->image_3) <a href="/upload/product_image/{{ $item->image_3 }}"></a> @endif
                                        </div>
                                        @else
                                            <div class="gallery">
                                                <a href="/upload/no_photo.jpg">
                                                  <img width="64px" src="/upload/no_photo.jpg" alt="" />
                                                </a>
                                            </div>
                                        @endif
                                      </td>
                                      <td style="text-align: left;padding: 5px;">{{ $item->name }}</td>
                                      <td>
                                          @hasanyrole('admin|sale|report')
                                            {{ $item->stockid->sum('stock') }}
                                          @else
                                            {{ ($item->dealertransferdetails->where('dealer_id', Auth::user()->dealer_id)->where('status', 1)->sum('qty') - $item->checkoutdetails->where('dealer_id', Auth::user()->dealer_id)->where('status', 1)->sum('qty')) }}  
                                          @endif
                                          {{ $item->unitid ? $item->unitid->name : null}}</td>
                                      <td style="padding: 5px;">{{ $item->barcode }}</td>
                                      @hasanyrole('admin|sale|cashier')
                                      <td>
                                          @hasanyrole('admin')
                                          <a href="{{ route('product_form', ['id' => $item->code, 'page' => $data->currentPage()])}}" style="text-decoration:underline;" title="{{ trans('backend.table.post_edit') }}">{{ trans('backend.table.post_edit_short') }}</a>
                                          @else
                                              @if(Auth::user()->dealer_id == $item->dealer_id)
                                              <a href="{{ route('product_form', ['id' => $item->code, 'page' => $data->currentPage()])}}" style="text-decoration:underline;" title="{{ trans('backend.table.post_edit') }}">{{ trans('backend.table.post_edit_short') }}</a>
                                              @endif
                                          @endif
                                      </td>
                                      <!--<td>{{ $item->catid ? $item->catid->name : NULL}}</td>-->
                                      
                                      <td>{{ number_format($item->price, 2, '.', ' ') }}</td>
                                      <td>{{ number_format($item->wholesale_price, 2, '.', ' ') }}</td>
                                      <td>{!! nl2br(e($item->description)) !!}</td>
                                      <!--<td>@foreach($item->brands as $br) {{ $br->name }}, @endforeach</td>-->
                                      @endhasanyrole
                                      <td style="padding: 1px;"><a href="{{ route('product_print', ['id' => $item->code, 'vmm' => 30, 'hmm' => 20]) }}">30*20</a> <br> <a href="{{ route('product_print', ['id' => $item->code, 'vmm' => 58, 'hmm' => 40]) }}">58*40</a></td>
                                      <td>{{ $item->userid ? $item->userid->name : null  }}</td>
                                      <td data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $item->created_at->format('Y-m-d H:i') }}" style="padding: 1px;">{{ $item->created_at->format('Y-m-d') }}</td>
                                      @hasanyrole('admin|sale|cashier')
                                      <td>
                                          <a href="{{ route('product_status', ['id' => $item->code, 'page' => $data->currentPage()])}}" title="В Архив"><em class="icon ni ni-scissor"></em></a>
                                          @if($item->checkindetails->count() == 0 && $item->checkoutdetails->count() == 0) 
                                              @hasanyrole('admin')
                                                <a href="{{ route('product_delete', ['id' => $item->code, 'page' => $data->currentPage()])}}"><em class="icon ni ni-trash"></em></a>
                                              @else
                                                  @if(Auth::user()->dealer_id == $item->dealer_id)
                                                  <a href="{{ route('product_delete', ['id' => $item->code, 'page' => $data->currentPage()])}}"><em class="icon ni ni-trash"></em></a>
                                                  @endif
                                              @endif
                                          @endif
                                       </td>
                                       @endhasanyrole
                                    </tr>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js"></script>
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