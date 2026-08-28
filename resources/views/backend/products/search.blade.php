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
                                                <th>{{ trans('backend.table.post_edit_short') }}</th>
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

    $(document).ready(function (){
        var timer; // Timerni tashqarida e'lon qilamiz
        var delay = 500;

        $('#searchmodal').keyup(function() {
            // Har safar tugma bosilganda eski kutilayotgan so'rovni bekor qilamiz
            clearTimeout(timer);

            var model = $(this).val();

            if(model.length > 2) {
                // Yangi vaqt belgilaymiz
                timer = setTimeout(function (){
                    $.ajax({
                        type: 'POST',
                        url: '{{ route("products_api") }}', 
                        data: {'model': model},
                        success: function (data) {
                            $(".modeltable").empty();
                        
                            $.each(data, function (index, item) {
                                // 1. Rasm nomini aniqlaymiz (item.image dan foydalanamiz)
                                var imageSrc = (item.image && item.image != '0') ? item.image : 'no_photo.jpg';

                                // 2. Papka yo'lini aniqlaymiz
                                var folderPath = (imageSrc === 'no_photo.jpg') ? '/upload/' : '/upload/product_image/';

                                // 3. HTML ni yig'amiz
                                $('.modeltable').append("<tr>\
                                            <td><a href='" + folderPath + imageSrc + "' target='_blank'><img src='" + folderPath + imageSrc + "' alt='" + item.name + "' width='50' height='50'></a></td>\
                                            <td>" + item.name + "</td>\
                                            <td>" + (item.barcode ? item.barcode : '') + "</td>\
                                            <td><a href='/product/form/" + item.code + "' target='_blank'>{{ trans('backend.table.post_edit_short') }}</a></td>\
                                            </tr>");
                            });
                        },
                        error: function(err) {
                            console.log("Xatolik:", err);
                        }
                    });
                }, delay);
            } else {
                $(".modeltable").empty();
                $('.modeltable').append("<tr>\
                                        <td colspan='4' class='text-center'>Сканируйте штрих-код или ищите товары</td>\
                                        </tr>");
            }
        });
    });
</script>
@endsection