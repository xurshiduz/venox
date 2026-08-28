@extends('layouts.backend')

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="components-preview mx-auto">
                    <div class="nk-block nk-block-lg">
                        <div class="card card-bordered card-preview">
                            <div class="card-inner">
                                <div class="preview-block">
                                   {!! Form::open(['id' => 'appointment_form']) !!}
                                    <div class="row gy-3">
                                        <div class="col-lg-2 col-sm-2">
                                            <div class="form-group">
                                                <label class="form-label">{{ trans('backend.input.select_date') }}</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" name="date" value="{{ $item && $item->date ? $item->date : Carbon\Carbon::now()->format('d.m.Y') }}" class="form-control date-picker" placeholder="ﾐｴﾐｰﾑひｰ">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-sm-4">
                                            <div class="form-group">
                                                <label class="form-label">{{ trans('backend.input.select_client') }}</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2" placeholder="Select Multiple options" name="client_id" required data-search="on">
                                                        @foreach($clients as $client)
                                                            <option @if($item && $item->client_id == $client->id) selected @endif value="{{ $client->id }}">{{ $client->name }} {{ $client->phone ? '| ' . $client->phone : null }} {{ $client->inn ? '| ' . $client->inn : ($client->pinfl ? '| ' . $client->pinfl : null) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-sm-3">
                                            <div class="form-group">
                                                <label class="form-label" for="formattedNumberField">{{ trans('backend.input.summs') }}</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" class="form-control" name="price" id="formattedNumberField" required placeholder="{{ trans('backend.input.summs') }}" data-type="currency" value="{{ $item ? $item->price : NULL }}">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-3 col-sm-3">
                                            <div class="form-group">
                                                <label class="form-label">{{ trans('backend.input.type_pay') }}</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2" required name="cash_receipt_type" data-search="on">
                                                        @foreach($types as $type)
                                                        <option @if($item && $item->cash_receipt_type == $type->id) selected @endif value="{{ $type->id }}">{{ $type->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <a href="{{ route('cash_receipts_index') }}" class="btn btn-danger btn-block">{{ trans('backend.input.button_cancel') }}</a>
                                        </div>
                                        <div class="col-md-6">
                                            <button id="register" type="submit" class="btn btn-primary btn-block">{{ trans('backend.input.button_done') }}</button>
                                        </div>
                                    </div>
                                    {!! Form::close() !!}
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
    $('#appointment_form').on('submit', function () {
       $('#register').attr('disabled', 'true'); 
    });
    
    $("#formattedNumberField").on('keyup', function(){
        var n = parseInt($(this).val().replace(/\D/g,''),10);
        $(this).val(n.toLocaleString());
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