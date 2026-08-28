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
                            @php($page = 1)
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
                                                <label class="form-label">С {{ trans('backend.input.warehouse') }}</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2" name="warehouse_out" required data-search="on">
                                                        <option>{{ trans('backend.table.in_select_ware') }}</option>
                                                        @foreach($warehouses as $warehouse)
                                                        <option @if($item && $item->warehouse_out == $warehouse->id) selected @endif value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-2 col-md-3 col-sm-4">
                                            <div class="form-group">
                                                <label class="form-label">На {{ trans('backend.input.warehouse') }}</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2" name="warehouse_in" required data-search="on">
                                                        <option>{{ trans('backend.table.in_select_ware') }}</option>
                                                        @foreach($warehouses as $warehouse)
                                                        <option @if($item && $item->warehouse_in == $warehouse->id) selected @endif value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-2 col-sm-12 d-none d-md-block">
                                            <div class="form-group">
                                                <label class="form-label" for="default-01">{{ trans('backend.input.comment') }}</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" value="{{ $item && $item->reference ? $item->reference : NULL }}" class="form-control" name="reference" id="default-01" placeholder="{{ trans('backend.input.comment') }}">
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
                                    
                                    
                                    <div class="table-responsive pt-2">
                                        <table class="table table-bordered d-none d-md-inline-table">
                                            <thead>
                                                <tr>
                                                    <th>Наименование</th>
                                                    <th>Штрихкод</th>
                                                    <th>Со склада</th>
                                                    <th style="border: 0px">Остаток</th>
                                                    <th>На склад</th>
                                                    <th style="border: 0px">Остаток</th>
                                                    <th width="150px">Кол.во</th>
                                                    <th><em class="icon ni ni-trash"></em></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                
                                                @foreach($item->details()->orderBy('id', 'desc')->get() as $detail)
                                                <input type="hidden" id="model" class="model" value="{{ $detail->id }}">
                                                <tr>
                                                    <td>{{$loop->iteration}}) {{ $detail->prodid->name }}</td>
                                                    <td>{{ $detail->prodid->barcode }}</b></td>
                                                    <td><span data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $detail->warehouseoutid ? $detail->warehouseoutid->name : NULL }}">{{ $detail->warehouseoutid ? $detail->warehouseoutid->num_code : NULL }}</span></td>
                                                    <td width="100px" style="border: 0px">{{ $detail->prodid->stockid->where('warehouse_id', $item->warehouse_out)->sum('stock') }} {{ $detail->prodid->unitid ? $detail->prodid->unitid->name : null}}</td>
                                                    <td><span data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $detail->warehouseinid ? $detail->warehouseinid->name : NULL }}">{{ $detail->warehouseinid ? $detail->warehouseinid->num_code : NULL }}</span></td>
                                                    <td width="100px" style="border: 0px">{{ $detail->prodid->stockid->where('warehouse_id', $item->warehouse_in)->sum('stock') }} {{ $detail->prodid->unitid ? $detail->prodid->unitid->name : null}}</td>
                                                    <td style="padding: 0px;">
                                                            <input style="width: 100%; border: 0px; text-align: center; height: 36px;" type="number" class="discount" placeholder="number" data-id="{{ $detail->id }}" value="{{ $detail->qty }}" min="1">
                                                    </td>
                                                    <td width="50px"><a href="{{ route('transfers_delete', ['id' => $detail->code]) }}"><em class="icon ni ni-trash"></em></a></td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        
                                        <table class="table table-bordered d-md-none">
                                            <thead>
                                                <tr>
                                                    <th colspan="3" class="text-center">{{ trans('backend.input.name') }}</th>
                                                    <th colspan="2">{{ trans('backend.input.warehouse') }}</th>
                                                </tr>
                                                <tr>
                                                    <th width="150px">Кол.во</th>
                                                    <th class="d-none d-md-block" style="border: 0px">Остаток</th>
                                                    <th><em class="icon ni ni-trash"></em></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($item->details()->orderBy('id', 'desc')->get() as $detail)
                                                <input type="hidden" id="model" class="model" value="{{ $detail->id }}">
                                                <tr>
                                                    <td colspan="3" class="text-center">{{$loop->iteration}}) {{ $detail->prodid->name }} <b>{{ $detail->prodid->barcode }}</b></td>
                                                    <td colspan="2" class="text-center">{{ $detail->warehouseoutid ? $detail->warehouseoutid->num_code : NULL }} {{ $detail->warehouseinid ? $detail->warehouseinid->num_code : NULL }}</td>
                                                </tr>
                                                <tr>
                                                    <td style="padding: 0px;">
                                                            <input style="width: 100%; border: 0px; text-align: center; height: 36px;" type="number" class="discount" placeholder="number" data-id="{{ $detail->id }}" value="{{ $detail->qty }}" min="1">
                                                    </td>
                                                    <td width="100px" class="d-none d-md-block" style="border: 0px">{{ $detail->prodid->stockid->where('warehouse_id', $item->warehouse_out)->sum('stock') }} {{ $detail->prodid->unitid ? $detail->prodid->unitid->name : null}}</td>
                                                    <td width="50px"><a href="{{ route('transfers_delete', ['id' => $detail->code]) }}"><em class="icon ni ni-trash"></em></a></td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    @endif
                                    @if($item && $item->details()->count())
                                    <hr style="margin-top: 0px;">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <a href="{{ route('transfers_done_status', ['id' => $item->code, 'page' => $page]) }}" class="btn btn-warning btn-block">{{ trans('backend.table.status_done') }}</a>
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
                <form action="#" method="POST">
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
    
    
    $('.discount').change(function() {
        var _ = $(this),
            cid = _.data('id'),
            brid = _.val();

        $.ajax({
            type: "POST",
            url: '{{ route("qty_transfer") }}',
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
    
    
    $('.clientchange').change(function() {
        var _ = $(this),
            cid = _.data('id'),
            clientid = _.val();

        $.ajax({
            type: "POST",
            url: '{{ route("client_transfer_change") }}',
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
    
    $('.datechange').change(function() {
        var _ = $(this),
            cid = _.data('id'),
            typeid = _.val();

        $.ajax({
            type: "POST",
            url: '{{ route("date_transfer_change") }}',
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


@endsection