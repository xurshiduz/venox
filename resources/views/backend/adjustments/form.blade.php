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
                        <div class="card card-bordered">
                            @include('layouts.message.success')
                            @include('layouts.message.error')
                            <div class="card-inner">
                                <div class="preview-block">
                                    {!! Form::open(['files' => true]) !!}
                                    <div class="row gy-3">
                                        <div class="col-lg-11 col-sm-11">
                                            <div class="form-group">
                                                <div class="form-control-wrap">
                                                    <input type="text" autocomplete="off" autofocus class="form-control" name="part_number"  placeholder="{{ trans('backend.input.barcode') }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-1 col-md-1 col-sm-1">
                                            <button class="btn btn-dark btn-block" type="submit"><em class="icon ni ni-scan"></em></button>
                                        </div>
                                        <!-- <div class="col-lg-12 col-sm-12">
                                            <button class="btn btn-primary btn-block" type="submit">Сохранить</button>
                                        </div> -->
                                    </div>
                                    {!! Form::close() !!}
                                </div>
                            </div>
                        </div>

                        @if($item)
                        <div class="card card-bordered mt-2">
                            <div class="row">
                                <div class="col-md-9">
                                    <ul class="data-list is-compact">
                                        @php($curs = App\Models\CurrencyType::all())
                                        @foreach($curs as $cur)
                                        <li class="data-item">
                                            <div class="data-col">
                                                <div class="data-label">{{ trans('backend.table.price') }} ({{ $cur->belgi }})</div>
                                                <div class="data-value">
        
                                                    @if($item->price && $item->currency_type)
                                                        @if($item->currency_type == $cur->id)
                                                            {{ number_format($item->price, 2, '.', ' ') }} {{ $cur->belgi }}
                                                        @else
                                                            @if($item->currency_type == 1)
                                                                {{ number_format(($item->price / $cur->currencyid->first()->price), 2, '.', ' ') }} {{ $cur->belgi }}
                                                            @else
                                                                {{ number_format(($item->price * $item->currencyid->currencyid->first()->price), 2, '.', ' ') }} {{ $cur->belgi }}
                                                            @endif
                                                        @endif
                                                    @endif
        
                                                </div>
                                            </div>
                                        </li>
                                        @endforeach
                                        <li class="data-item">
                                            <div class="data-col">
                                                <div class="data-label">{{ trans('backend.table.name') }}</div>
                                                <div class="data-value">{{ $item->name }}</div>
                                            </div>
                                        </li>
                                        <li class="data-item">
                                            <div class="data-col">
                                                <div class="data-label">{{ trans('backend.table.barcode') }}</div>
                                                <div class="data-value">{{ $item->barcode }}</div>
                                            </div>
                                        </li>
                                        <li class="data-item">
                                            <div class="data-col">
                                                <div class="data-label">{{ trans('backend.table.country') }}</div>
                                                <div class="data-value">{{ $item->contryid->name }}</div>
                                            </div>
                                        </li>
            
                                        
                                        @php($wares = App\Models\Warehouse::all())
                                        @php($dealers = App\Models\Dealer::all())
                                        @foreach($dealers as $dealer)
                                            <li class="data-item" style="background-color: #ecfff1;">
                                                <div class="data-col">
                                                    <div class="data-label"><b>{{ trans('backend.menu.dealer') }}: {{ $dealer->name }} {{ $dealer->phone }}</b></div>
                                                    <div class="data-value">
                                                        {{ (($item->checkindetails->where('dealer_id', $dealer->id)->where('status', 1)->sum('qty') + $item->dealertransferdetails->where('dealer_id', $dealer->id)->where('status', 1)->sum('qty')) - $item->checkoutdetails->where('dealer_id', $dealer->id)->where('status', 1)->sum('qty')) }} {{ $item->unitid ? $item->unitid->name : null}}
                                                    </div>
                                                </div>
                                            </li>
                                            @foreach($wares->where('dealer_id', $dealer->id) as $ware)
                                            <li class="data-item">
                                                <div class="data-col">
                                                    <div class="data-label">* {{ trans('backend.table.stock') }} {{ $ware->name }} 
                                                    
                                                    </div>
                                                    <div class="data-value" style="width: 10%">
                                                        {{ (($item->checkindetails()->where('warehouse_id', $ware->id)->where('status', 1)->sum('qty') + $item->dealertransferdetails()->where('warehouse_id', $ware->id)->where('status', 1)->sum('qty') ) - $item->checkoutdetails()->where('warehouse_id', $ware->id)->where('status', 1)->sum('qty')) }} {{ $item->unitid ? $item->unitid->name : null}}
            
                                                    </div>
                                                    <div class="data-value">
                                                        @if(App\Models\WarehouseBlockProduct::where('product_id', $item->id)->where('warehouse_id', $ware->id)->count()) <b>Блок:</b> @endif
                                                        @foreach(App\Models\WarehouseBlockProduct::where('product_id', $item->id)->where('warehouse_id', $ware->id)->get() as $bid)
                                                            {{ $bid->wareblockid ? $bid->wareblockid->row : null }}-блок {{ $bid->wareblockcellid ? $bid->wareblockcellid->cell . '-' . $bid->wareblockcellid->cell_number  : null}},
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </li>
                                            @endforeach
                                        @endforeach
                                        <li class="data-item">
                                            <div class="data-col">
                                                <div class="data-label"><b>{{ trans('backend.table.total_stock') }}</b></div>
                                                <div class="data-value">
                                                    {{ (($item->checkindetails->where('status', 1)->sum('qty') + $item->dealertransferdetails->where('status', 1)->sum('qty') )- $item->checkoutdetails->where('status', 1)->sum('qty')) }} {{ $item->unitid ? $item->unitid->name : null}}
        
                                                </div>
                                            </div>
                                        </li>
                                        
                                        <li class="data-item">
                                            <div class="data-col">
                                                <div class="data-label">{{ trans('backend.table.in_stock') }}</div>
                                                <div class="data-value">
                                                    @if((($item->checkindetails->where('status', 1)->sum('qty') + $item->dealertransferdetails->where('status', 1)->sum('qty')) - $item->checkoutdetails->where('status', 1)->sum('qty')) > 0)
                                                    <span class="badge badge-dim badge-sm bg-outline-success">{{ trans('backend.table.yes') }}</span>
                                                    @else
                                                    <span class="badge badge-dim badge-sm bg-outline-danger">{{ trans('backend.table.not') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                    
                                </div>
                                <div class="col-md-3" style="text-align: center;">
                                    
                                    @if($item->image) 
                                    <div class="gallery">
                                        <a href="/upload/product_image/{{ $item->image }}">
                                          <img src="/upload/product_image/{{ $item->image }}" alt="" />
                                        </a>
                                        @if($item->image_2) <a href="/upload/product_image/{{ $item->image_2 }}"></a> @endif
                                        @if($item->image_3) <a href="/upload/product_image/{{ $item->image_3 }}"></a> @endif
                                    </div>
                                    @else
                                        <div class="gallery">
                                            <a href="/upload/no_photo.jpg">
                                              <img src="/upload/no_photo.jpg" alt="" />
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js"></script>
<script>
(function($){
  
  $('.gallery').each(function() { 
      $(this).magnificPopup({
          delegate: 'a', 
          type: 'image',
          gallery: {
            enabled:true
          }
      });
  });
  
})(jQuery);
</script>
@endsection