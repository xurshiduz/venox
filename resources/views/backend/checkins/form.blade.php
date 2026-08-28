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
                                    {!! Form::open(['class' => 'invoice-repeater', 'id' => 'appointment_form']) !!}
                                    <div class="row gy-3">
                                        <div class="col-lg-2 col-sm-2">
                                            <div class="form-group">
                                                <label class="form-label">Тип прихода</label>
                                                <div class="form-control-wrap">
                                                    <!-- typechange klassi doim yoziladi -->
                                                    <select class="form-select typechange" required name="type_id" data-id="{{ $item ? $item->id : null}}">
                                                        @foreach($types as $type)
                                                        <option @if($item && $item->type_id == $type->id) selected @endif value="{{ $type->id }}">{{ $type->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
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
                                        <div class="col-lg-2 col-md-2 col-sm-3">
                                            <div class="form-group">
                                                <label class="form-label">{{ trans('backend.table.supplier') }}</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2 {{ $item ? 'suppchange' : null}}" data-id="{{ $item ? $item->id : null}}" name="client_id" required data-search="on">
                                                        @foreach($clients as $client)
                                                        <option @if($item && $item->client_id == $client->id) selected @endif value="{{ $client->id }}">{{ $client->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        @hasanyrole('admin')
                                        <div class="col-lg-2 col-sm-2">
                                            <div class="form-group">
                                                <label class="form-label">{{ trans('backend.input.warehouse') }}</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2 {{ $item ? 'warechange' : null}}" required name="warehouse_id" data-id="{{ $item ? $item->id : null}}" data-search="on">
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
                                                    <select class="form-select js-select2 {{ $item ? 'checkin_currency_type_change' : null }}" data-id="{{ $item ? $item->id : null }}" name="currency_type" required data-search="on">
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
                                                    <input type="number" step="any" id="checkout_currency_price" name="currency_type_price" value="{{ $item->currency_type_price ?? 1 }}" data-id="{{ $item ? $item->id : null}}" class="form-control {{ $item ? 'checkin_currency_price_change' : null}}" placeholder="Курс">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-2 col-sm-2">
                                            <div class="form-group">
                                                <label class="form-label" for="default-01">{{ trans('backend.input.comment') }}</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" value="{{ $item && $item->reference ? $item->reference : NULL }}" class="form-control {{ $item ? 'commentchange' : null}}" data-id="{{ $item ? $item->id : null}}" name="reference" id="default-01" placeholder="{{ trans('backend.input.comment') }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-2 col-sm-2 d-none">
                                            <div class="form-group">
                                                <label class="form-label" for="default-06">{{ trans('backend.table.select_file') }}</label>
                                                <div class="form-control-wrap">
                                                    <div class="form-file">
                                                        <input type="file" name="file" multiple class="form-file-input" id="customFile">
                                                        <label class="form-file-label" for="customFile">Прикрепить</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    <div class="row gy-2">
                                        <div class="col-lg-11">
                                            <div class="form-group">
                                                <label class="form-label" for="addLinks">{!! trans('backend.input.scan_text') !!}</label>
                                                <input list="items" class="form-control" required type="text" name="product_id" placeholder="{!! trans('backend.input.scan_text_input') !!}" autocomplete="off" autofocus id="modelName" />
                                                    <datalist id="items" class="modelList"></datalist>
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
                                                    <th>{{ trans('backend.input.name') }}</th>
                                                    <th width="16px" style="padding:0">Фото</th>
                                                    <th>{{ trans('backend.input.barcode_short') }}</th>
                                                    <th>{{ trans('backend.table.stock') }}</th>
                                                    <th>Блок</th>
                                                    <th>Цена за ед.</th>
                                                    <th>{{ trans('backend.table.qty_full') }}</th>
                                                    <th><em class="icon ni ni-trash"></em></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($details as $detail)
                                                <input type="hidden" id="model" class="model" value="{{ $detail->id }}">
                                                <tr>
                                                    <td width="50px">{{$loop->iteration}}</td>
                                                    <td>{{ $detail->prodid->name }}</td>
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
                                                    <td>{{ $detail->prodid->barcode }}</td>
                                                    <td width="100px">{{ $detail->prodid->stockid->where('warehouse_id', $item->warehouse_id)->sum('stock') }} {{ $detail->prodid->unitid ? $detail->prodid->unitid->name : NULL}}</td>
                                                    <td width="100px" style="padding: 0px;">
                                                        {{ $detail->blockid ? $detail->blockid->row . '-блок ' . ($detail->blockcellid ? $detail->blockcellid->cell . '-' . $detail->blockcellid->cell_number : null) : null }}
                                                        <input style="border: 0px;" type="hidden" data-id="{{ $detail->id }}" class="form-control warehouse_block_id" value="{{ $detail->warehouse_block_id }}">
                                                    </td>
                                                    <td width="160px" style="padding: 0px;">
                                                        <input style="border: 0px;" type="number" data-id="{{ $detail->id }}" class="form-control price" value="{{ $detail->price }}">
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
                                    <a href="{{ route('checkin_done_status', ['id' => $item->code]) }}" class="btn btn-primary btn-block">{{ trans('backend.table.status_done') }}</a>
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
    
    $('.datechange').change(function() {
        var _ = $(this),
            cid = _.data('id'),
            typeid = _.val();

        $.ajax({
            type: "POST",
            url: '{{ route("date_checkin_change") }}',
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
    
    $('.suppchange').change(function() {
        var _ = $(this),
            cid = _.data('id'),
            typeid = _.val();

        $.ajax({
            type: "POST",
            url: '{{ route("supp_checkout_change") }}',
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
    
    
    $('.checkin_currency_type_change').change(function() {
        var _ = $(this),
            cid = _.data('id'),
            currency_type = _.val();
    
        if (!cid) return;
    
        $.ajax({
            type: "POST",
            url: '{{ route("checkin_currency_type_change") }}',
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
    $('.checkin_currency_price_change').on('input', function() {
        var _ = $(this),
            cid = _.data('id'),
            price = _.val();
    
        if (!cid || price === '') return;
    
        clearTimeout(courseTimeout);
        // Yozib bo'lgandan so'ng 500ms dan keyin bazaga so'rov yuboradi
        courseTimeout = setTimeout(function() {
            $.ajax({
                type: "POST",
                url: '{{ route("checkin_currency_price_change") }}',
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
    
    $('.warechange').change(function() {
        var _ = $(this),
            cid = _.data('id'),
            typeid = _.val();

        $.ajax({
            type: "POST",
            url: '{{ route("ware_checkout_change") }}',
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
    
    $('.warehouse_block_id').change(function() {
        var _ = $(this),
            pcid = _.data('id'),
            pbrid = _.val();

        $.ajax({
            type: "POST",
            url: '{{ route('warehouse_block_checkin') }}',
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
    
    // Izoh (Comment) o'zgarganda ishlaydigan script
    $('.commentchange').change(function() {
        var _ = $(this),
            cid = _.data('id'),
            val = _.val();

        $.ajax({
            type: "POST",
            url: '{{ route("comment_checkin_change") }}',
            dataType: 'JSON',
            data: {
                cid: cid,
                val: val
            },
            success: function(data) {
                if (data.status) {
                    console.log("Izoh muvaffaqiyatli saqlandi!");
                }
            },
            error: function(ajaxContext) {
                alert(ajaxContext.responseText)
            }
        });
    });

    // "Тип прихода" (Type) o'zgarganda ishlaydigan script
   $('.typechange').change(function() {
    var _ = $(this),
        cid = _.data('id'),
        val = _.val();

    $.ajax({
        type: "POST",
        url: '{{ route("type_checkin_change") }}',
        dataType: 'JSON',
        data: {
            cid: cid, // yangi item qo'shilayotganda bu bo'sh null bo'ladi
            val: val
        },
        success: function(data) {
            if (data.status) {
                var clientSelect = $('select[name="client_id"]');
                clientSelect.empty();
                
                $.each(data.clients, function(index, client) {
                    clientSelect.append('<option value="' + client.id + '">' + client.name + '</option>');
                });
                
                // js-select2 ni yangilash (ui ni re-render qilish uchun)
                clientSelect.trigger('change.select2'); 
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

@endsection