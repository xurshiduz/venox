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
                                    <div class="row gy-1">

                                        <div class="col-lg-2 col-sm-2 d-md-block">
                                            <div class="form-group">
                                                <label class="form-label" for="default-01">{{ trans('backend.input.select_date') }}</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" value="{{ $item && $item->date ? $item->date : NULL }}" class="form-control" readonly>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-2 col-sm-2 d-md-block">
                                            <div class="form-group">
                                                <label class="form-label" for="default-01">{{ trans('backend.index.clients') }}</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" value="{{ $item && $item->supid ? $item->supid->name : NULL }}" class="form-control" readonly>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-2 col-sm-2 d-md-block">
                                            <div class="form-group">
                                                <label class="form-label" for="default-01">{{ trans('backend.input.seller') }}</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" value="{{ $item && $item->managerid ? $item->managerid->name : NULL }}" class="form-control" readonly>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-2 col-sm-2 d-md-block">
                                            <div class="form-group">
                                                <label class="form-label" for="default-01">{{ trans('backend.input.warehouse') }}</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" value="{{ $item && $item->warid ? $item->warid->name : NULL }}" class="form-control" readonly>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-4 col-sm-12 d-none d-md-block">
                                            <div class="form-group">
                                                <label class="form-label" for="default-01">{{ trans('backend.input.comment') }}</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" value="{{ $item && $item->reference ? $item->reference : NULL }}" class="form-control" readonly placeholder="{{ trans('backend.input.comment') }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @if($item)
                                    
                                    <hr style="margin: 10px 0;">
                                    @php($itogsum = 0)
                                    @foreach($item->details()->get() as $detail)
                                        @if($detail->price && $detail->currencytypeid)
                                        @php($itogsum += ($detail->currencytypeid->currencyid->first()->price * ($detail->price * $detail->qty)))
                                        @endif
                                    @endforeach
                                    
                                    @php($itogsumusd = $itogsum / App\Models\Currency::where('type_id', 2)->orderBy('id', 'asc')->first()->price)
                                    
                                    
                                    <span>Итого:</span>
                                    @foreach(App\Models\CurrencyType::where('status', 1)->get() as $cur)
                                    <span><b>{{ $cur->belgi }}:</b> {{ number_format($itogsum / App\Models\Currency::where('type_id', $cur->id)->orderBy('id', 'asc')->first()->price, 0, '.', ' ') }}</span>
                                    @endforeach
                                    
                                    <div class="table-responsive pt-2">
                                        <table class="table table-bordered d-none d-md-inline-table">
                                            <thead>
                                                <tr>
                                                    <th>Наименование</th>
                                                    <th>Штрихкод</th>
                                                    <th>Склад</th>
                                                    <th width="150px">Кол.во</th>
                                                    <th style="border: 0px">Остаток</th>
                                                    <th width="150px">Цена продаж</th>
                                                    <th width="150px">Себестоимость</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                
                                                @foreach($item->details()->orderBy('id', 'desc')->get() as $detail)
                                                <input type="hidden" id="model" class="model" value="{{ $detail->id }}">
                                                @php($ost = ($detail->prodid->checkindetails()->where('warehouse_id', $detail->warehouse_id)->where('status', 1)->sum('qty') - $detail->prodid->checkoutdetails()->where('warehouse_id', $detail->warehouse_id)->where('status', 1)->sum('qty')))
                                                <tr>
                                                    <td>{{$loop->iteration}}) {{ $detail->prodid->name }}</td>
                                                    <td>{{ $detail->prodid->barcode }}</b></td>
                                                    <td>{{ $detail->warehouseid ? $detail->warehouseid->name : NULL }}</td>
                                                    <td>
                                                        {{ $detail->qty }}
                                                    </td>
                                                    
                                                    <td width="100px" style="border: 0px">{{ $ost }} {{ $detail->prodid->unitid ? $detail->prodid->unitid->name : null}}</td>
                                                    <td>
                                                        {{ number_format($detail->price, 2, '.', ' ') }}
                                                    </td>
                                                    <td style="padding: 0px;">
                                                        <input style="min-width: 110px; border: 0px;" type="text" data-id="{{ $detail->id }}" class="form-control tan_price" data-type="currency" value="{{ number_format($detail->tan_price, 2, '.', ' ') }}">
                                                    </td>
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
                                                    <th width="150px">Цена</th>
                                                    <th><em class="icon ni ni-trash"></em></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($item->details()->orderBy('id', 'desc')->get() as $detail)
                                                <input type="hidden" id="model" class="model" value="{{ $detail->id }}">
                                                @php($ost = ($detail->prodid->checkindetails()->where('warehouse_id', $detail->warehouse_id)->where('status', 1)->sum('qty') - $detail->prodid->checkoutdetails()->where('warehouse_id', $detail->warehouse_id)->where('status', 1)->sum('qty')))
                                                <tr>
                                                    <td colspan="3" class="text-center">{{$loop->iteration}}) {{ $detail->prodid->name }} <b>{{ $detail->prodid->barcode }}</b></td>
                                                    <td colspan="2" class="text-center">{{ $detail->warehouseid ? $detail->warehouseid->name : NULL }}</td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        {{ $detail->qty }}
                                                    </td>
                                                    
                                                    <td width="100px" class="d-none d-md-block" style="border: 0px">{{ $ost }} {{ $detail->prodid->unitid ? $detail->prodid->unitid->name : null}}</td>
                                                    
                                                    <td>
                                                        {{ number_format($detail->price, 2, '.', ' ') }}
                                                    </td>
                                                    <td style="padding: 0px;">
                                                        <input style="min-width: 110px; border: 0px;" type="text" data-id="{{ $detail->id }}" class="form-control tan_price" data-type="currency" value="{{ number_format($detail->tan_price, 2, '.', ' ') }}">
                                                    </td>
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
                                            <a href="{{ route('checkouts_index_report', ['page' => $page]) }}" class="btn btn-warning btn-block">{{ trans('backend.table.status_done') }}</a>
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
    
    $('.tan_price').change(function() {
        var _ = $(this),
            pcid = _.data('id'),
            pbrid = _.val();

        $.ajax({
            type: "POST",
            url: '{{ route("tan_price_checkout") }}',
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
</script>


@endsection