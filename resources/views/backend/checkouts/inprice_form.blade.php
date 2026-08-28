@extends('layouts.backend')

@section('content')

<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="components-preview mx-auto">
                    <div class="nk-block nk-block-lg">
                        <div class="card card-bordered card-preview">
                            <div class="card-inner" style="padding: 0.75rem;">
                                <div class="preview-block">
                                    <div class="table-responsive pt-2">
                                        <table class="table table-bordered d-none d-md-inline-table">
                                            <thead>
                                                <tr>
                                                    <th>Наименование</th>
                                                    <th>Штрихкод</th>
                                                    <th width="150px">Цена продаж</th>
                                                    <th width="150px">Себестоимость</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($data as $detail)
                                                <input type="hidden" id="model" class="model" value="{{ $detail->first()->id }}">
                                                <tr>
                                                    <td>{{ $detail->first()->prodid->name }}</td>
                                                    <td>{{ $detail->first()->prodid->barcode }}</b></td>
                                                    <td>
                                                        {{ number_format($detail->avg('price'), 2, '.', ' ') }}
                                                    </td>
                                                    <td style="padding: 0px;">
                                                        <input style="min-width: 110px; border: 0px;" type="text" data-id="{{ $detail->first()->id }}" class="form-control tan_price" data-type="currency" value="{{ number_format($detail->first()->tan_price, 2, '.', ' ') }}">
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
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