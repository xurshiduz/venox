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
                        <div class="card card-bordered mt-2">
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
                                        <th width="100px">{{ trans('backend.input.select') }}</th>
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
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js"></script>
<script>
$('.gallery').each(function() {
    $(this).magnificPopup({
        delegate: 'a',
        type: 'image',
        gallery: {
          enabled:true
        }
    });
});
</script>
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
                url: '{{ route("display_send") }}',
                dataType: 'JSON',
                data: { datacid: datacid },
                success: function(data) {
                    //$('#tsendsuccess'+datacid).empty();
                    $('#tsendsuccess'+datacid).append("Готова");
                },
                error: function(ajaxContext) {
                    alert(ajaxContext.responseText)
                }
            });
        });
    
    $('#searchmodal').change(function() {
        var model = $(this).val();
        let result;
        if(this.value.length > 3) {
            $.ajax({
                type: 'POST',
                url: '{{ route("products_api") }}', 
                data: {'model': model},
                success:function (data) {
                    $(".modeltable").empty();
                    
                    $.each(data, function (index, item) {
                        if (item.image) {
                          result = '/upload/product_image/'+item.image;
                        } else {
                          result = '/upload/no_photo.jpg';
                        }
  
						$('.modeltable').append("<tr>\
									<td><div class='gallery'><a href='"+result+"'><img width='64px' height='64px' src='"+result+"' /></a></div></td>\
									<td>"+item.name+"</td>\
									<td>"+item.barcode+"</td>\
									<td id='tsendsuccess"+item.barcode+"'><a href='#' id='sendsuccess' data-id='"+item.barcode+"' class='btn btn-block btn-sm btn-primary sendsuccess'>Показать</a></td>\
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