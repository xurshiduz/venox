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
                            <div class="card-inner">
                                <div class="preview-block">
                                    {!! Form::open(['files' => true, 'class' => 'invoice-repeater', 'id' => 'appointment_form']) !!}
                                    <div class="row gy-3">
                                        <div class="col-lg-2 col-sm-2">
                                            <div class="form-group">
                                                <label class="form-label">{{ trans('backend.table.date') }}</label>
                                                <div class="form-control-wrap">
                                                    <div class="form-icon form-icon-right">
                                                        <em class="icon ni ni-calendar-alt"></em>
                                                    </div>
                                                    <input type="text" name="date" value="{{ $item && $item->date ? $item->date : Carbon\Carbon::now()->format('m/d/Y') }}" class="form-control date-picker" placeholder="дата">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-2 col-sm-2">
                                            <div class="form-group">
                                                <label class="form-label">{{ trans('backend.table.supplier') }}</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2" required name="client_id" data-search="on">
                                                        @foreach($clients as $client)
                                                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-2 col-sm-2">
                                            <div class="form-group">
                                                <label class="form-label">{{ trans('backend.input.warehouse') }}</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2" required name="warehouse_id" data-search="on">
                                                        @foreach($warehouses as $warehouse)
                                                        <option @if($item && $item->warehouse_id == $warehouse->id) selected @endif value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-3 col-sm-3">
                                            <div class="form-group">
                                                <label class="form-label" for="default-01">{{ trans('backend.input.comment') }}</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" value="{{ $item && $item->reference ? $item->reference : NULL }}" class="form-control" name="reference" id="default-01" placeholder="{{ trans('backend.input.comment') }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-2 col-sm-2">
                                            <div class="form-group">
                                                <label class="form-label" for="default-06">EXCEL <a href="/upload/template_prixod_block.xlsx">Скачать шаблон <img width="22px" src="/upload/excel.png"></a></label>
                                                <div class="form-control-wrap">
                                                    <div class="form-file">
                                                        <input type="file" name="file_excel" required class="form-file-input" id="customFile">
                                                        <label class="form-file-label" for="customFile"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-1">
                                            <label class="form-label" style="color: white;">.</label>
                                            <button id="register" type="submit" class="btn btn-primary btn-block">OK</button>
                                        </div>
                                    </div>
                                    {!! Form::close() !!}
                                    <hr style="margin-top: 10px;">
                                    @if($item)
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Наименование</th>
                                                    <th>Штрих код</th>
                                                    <th>Остаток</th>
                                                    <th>Цена за ед.</th>
                                                    <th>Валюта</th>
                                                    <th>Количество</th>
                                                    <th><em class="icon ni ni-trash"></em></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($details as $detail)
                                                <input type="hidden" id="model" class="model" value="{{ $detail->id }}">
                                                <tr>
                                                    <td width="50px">{{$loop->iteration}}</td>
                                                    <td>{{ $detail->prodid->name }}</td>
                                                    <td>{{ $detail->prodid->barcode }}</td>
                                                    <td width="100px">{{ ($detail->prodid->checkindetails->where('status', 1)->sum('qty') - $detail->prodid->checkoutdetails->where('status', 1)->sum('qty')) }} {{ $detail->prodid->unitid ? $detail->prodid->unitid->name : NULL}}</td>
                                                    <td width="160px" style="padding: 0px;">
                                                        <input style="border: 0px;" type="number" data-id="{{ $detail->id }}" class="form-control price" value="{{ $detail->price }}">
                                                    </td>
                                                    <td width="100px" style="padding: 0px;">
                                                        <select style="border: 0px" class="form-select currency" id="currency{{ $detail->id }}" required name="currency_type">
                                                            @foreach(App\Models\CurrencyType::orderBy('id', 'asc')->get() as $currency)
                                                            <option @if($detail->currency_type == $currency->id) selected @endif value="{{ $currency->id }}">{{ $currency->belgi }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td width="160px" style="padding: 0px;">
                                                        <input style="border: 0px;" type="number" data-id="{{ $detail->id }}" class="form-control discount" value="{{ $detail->qty }}">
                                                    </td>
                                                    <td width="50px"><a href="{{ route('checkin_delete', ['id' => $detail->code]) }}"><em class="icon ni ni-trash"></em></a></td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @endif
                                    @if($item && $item->details()->count())
                                    <br>
                                    <a href="{{ route('checkin_done_status', ['id' => $item->code]) }}" class="btn btn-primary btn-block">Завершить</a>
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
@endsection

@section('script')
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
            url: '{{ route('qty_checkin') }}',
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
    
    $('.price').change(function() {
        var _ = $(this),
            pcid = _.data('id'),
            pbrid = _.val();

        $.ajax({
            type: "POST",
            url: '{{ route('price_checkin') }}',
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

    $(".currency").on("change", function(e) {
        $('.model').each(function(index, value){
            var curcid = $( this ).val();
            var currency = $('#currency'+curcid).val();
            $.ajax({
                type: 'POST',
                url: '{{ route("currency_checkin") }}', 
                data: {'curcid': curcid, 'currency': currency},
            }); 
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
                                $(".modelList").append($('<option>', { value: item.fullname }));
                            });
                        }
                    });
                }, delay);
            }
        });
    });

    $('#appointment_form').on('submit', function () {
       $('#register').attr('disabled', 'true'); 
    });
</script>
@endsection