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
                                    {!! Form::open(['files' => true, 'id' => 'appointment_form']) !!}
                                    <div class="row gy-3">
                                        <div class="col-lg-4 col-sm-4">
                                            <div class="form-group">
                                                <label class="form-label" for="name">{{ trans('backend.input.name') }}</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" autofocus value="{{ $item ? $item->name : NULL}}" class="form-control" name="name" id="name" placeholder="Наименование">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-2 col-sm-2">
                                            <div class="form-group">
                                                <label class="form-label">{{ trans('backend.table.unit_title') }}</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2" name="unit_id" required data-search="on">
                                                        @foreach($units as $unit)
                                                        <option @if($item && $item->unit_id == $unit->id) selected @endif value="{{ $unit->id }}">{{ $unit->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-2 col-sm-2">
                                            <div class="form-group">
                                                <label class="form-label" for="unit">Категория</label>
                                                <select class="form-select js-select2" name="category_id" required data-search="on">
                                                    @foreach($categories as $category)
                                                    <option @if($item && $item->category_id == $category->id) selected @endif value="{{ $category->id }}">{{ $category->name }}</option>
                                                    @endforeach
                                                </select>
                                                    
                                            </div>
                                        </div>

                                        <div class="col-lg-2 col-sm-2">
                                            <div class="form-group">
                                                <label class="form-label">Страна</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2" name="country_id" required data-search="on">
                                                        @foreach(App\Models\Country::orderBy('id', 'asc')->get() as $country)
                                                        <option @if($item && $item->country_id == $country->id) selected @endif value="{{ $country->id }}">{{ $country->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        @php
  
                                          $number = Carbon\Carbon::now()->format('y') . mt_rand(100,999) . mt_rand(10,99);
                                          $code = '3' . str_pad($number, 9, '0');
                                          $weightflag = true;
                                          $sum = 0;
                                          
                                          for ($i = strlen($code) - 1; $i >= 0; $i--)
                                          {
                                            $sum += (int)$code[$i] * ($weightflag?3:1);
                                            $weightflag = !$weightflag;
                                          }
                                          $code .= (10 - ($sum % 10)) % 10;
                                        
                                        @endphp
                                        <div class="col-lg-2 col-sm-2">
                                            <div class="form-group">
                                                <label class="form-label" for="barcode">{{ trans('backend.input.barcode_short') }}</label>
                                                <div class="form-control-wrap">
                                                    <!-- Inputga 'is-invalid' klassi JS orqali qo'shiladi -->
                                                    <input type="text" 
                                                           value="{{ $item ? $item->barcode : $code }}" 
                                                           class="form-control" 
                                                           name="barcode" 
                                                           id="barcode" 
                                                           placeholder="Shtrix kod"
                                                           autocomplete="off">
                                                    
                                                    <!-- Xatolik xabari chiqadigan joy -->
                                                    <div id="barcode-error" class="invalid-feedback" style="display: none; color: red;">
                                                        Ushbu shtrix kod tizimda mavjud!
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-2 col-sm-2">
                                            <div class="form-group">
                                                <label class="form-label" for="price">Цена товара</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" name="price" class="form-control" id="formattedNumberField" value="{{ $item && $item->price ? number_format($item->price, 2, '.', ' ') : NULL }}" data-type="currency" placeholder="Цена товара">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-2 col-sm-2">
                                            <div class="form-group">
                                                <label class="form-label" for="wholesale_price">Оптовая цена</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" name="wholesale_price" class="form-control" id="formattedNumberField2" value="{{ $item && $item->wholesale_price ? number_format($item->wholesale_price, 2, '.', ' ') : NULL }}" data-type="currency" placeholder="Оптовая цена">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label class="form-label" for="default-07">Бренд</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2" name="brands[]" multiple="multiple" data-placeholder="Select Multiple options">
                                                        <option value="">Танланг</option>
                                                        @foreach($brands as $brand)
                                                        <option value="{{ $brand->id }}" @if($item && $item->brands->contains($brand->id)) selected @endif>{{ $brand->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-2 col-sm-2">
                                            <div class="form-group">
                                                <label class="form-label">Валюта</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2" name="currency_type" required data-search="on">
                                                        @foreach(App\Models\CurrencyType::orderBy('id', 'asc')->get() as $currency)
                                                        <option @if($item && $item->currency_type == $currency->id) selected @endif value="{{ $currency->id }}">{{ $currency->belgi }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        
                                        <div class="col-lg-2 col-sm-2">
                                            <div class="form-group">
                                                <label class="form-label" for="notification_qty">{{ trans('backend.input.min_qty') }}</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" value="{{ $item ? $item->notification_qty : NULL }}" class="form-control" name="notification_qty" id="notification_qty" placeholder="{{ trans('backend.input.min_qty_full') }}">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        
                                        <div class="col-lg-2 col-sm-2">
                                            <div class="form-group">
                                                <label class="form-label" for="description">Подробнее</label>
                                                <div class="form-control-wrap">
                                                    <textarea class="form-control" name="description">{!! $item ? $item->description : NULL !!}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-3 col-sm-3">
                                            <div class="form-group">
                                                <label class="form-label" for="default-06">Изображения 1</label>
                                                <div class="form-control-wrap">
                                                    <div class="form-file">
                                                        <input type="file" name="image" class="form-file-input" id="customFile">
                                                        <label class="form-file-label" style="color: #c6c6e5;" for="customFile">Изображения 1</label>
                                                    </div>
                                                </div>
                                            </div>
                                            @if($item && $item->image)
                                            <img width="128px" src="/upload/product_image/{{ $item->image }}"> 
                                            @endif
                                        </div>
                                        
                                        <div class="col-lg-3 col-sm-3">
                                            <div class="form-group">
                                                <label class="form-label" for="default-06">Изображения 2</label>
                                                <div class="form-control-wrap">
                                                    <div class="form-file">
                                                        <input type="file" name="image_2" class="form-file-input" id="image_2">
                                                        <label class="form-file-label" style="color: #c6c6e5;" for="image_2">Изображения 2</label>
                                                    </div>
                                                </div>
                                            </div>
                                            @if($item && $item->image_2)
                                            <img width="128px" src="/upload/product_image/{{ $item->image_2 }}"> 
                                            @endif
                                        </div>
                                        
                                        <div class="col-lg-3 col-sm-3">
                                            <div class="form-group">
                                                <label class="form-label" for="default-06">Изображения 3</label>
                                                <div class="form-control-wrap">
                                                    <div class="form-file">
                                                        <input type="file" name="image_3" class="form-file-input" id="image_3">
                                                        <label class="form-file-label" style="color: #c6c6e5;" for="image_3">Изображения 3</label>
                                                    </div>
                                                </div>
                                            </div>
                                            @if($item && $item->image_3)
                                            <img width="128px" src="/upload/product_image/{{ $item->image_3 }}"> 
                                            @endif
                                        </div>

                                        <div class="col-lg-12 col-sm-12">
                                            <button id="register" class="btn btn-primary btn-block" type="submit">{{ trans('backend.input.button_done') }}</button>
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
       $('#register').attr('disabled', true); 
    });

    function formatDecimalInput(el) {

        let value = $(el).val();

        // vergulni nuqtaga aylantirish
        value = value.replace(/,/g, '.');

        // faqat raqam va bitta nuqta qoldirish
        value = value.replace(/[^\d.]/g, '');

        // bir nechta nuqta bo‘lsa bittasini qoldirish
        let parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }

        parts = value.split('.');

        // chap tomonni formatlash
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ' ');

        // qayta yig‘ish
        value = parts.length > 1
            ? parts[0] + '.' + parts[1]
            : parts[0];

        $(el).val(value);
    }

    $("#formattedNumberField").on('input', function () {
        formatDecimalInput(this);
    });

    $("#formattedNumberField2").on('input', function () {
        formatDecimalInput(this);
    });
</script>

<script>
    $(document).ready(function() {
        // Tahrirlanayotgan mahsulot IDsi (agar bo'lsa)
        var currentItemId = "{{ $item ? $item->id : '' }}";
        var submitButton = $('#register');
        var barcodeInput = $('#barcode');
        var errorMsg = $('#barcode-error');
        var timer = null; // Debounce uchun

        // Funksiya: Barcodeni tekshirish
        function checkBarcodeAvailability() {
            var barcode = barcodeInput.val();

            // Agar bo'sh bo'lsa tekshirmaymiz
            if (barcode.trim() === '') {
                barcodeInput.removeClass('is-invalid');
                errorMsg.hide();
                submitButton.removeAttr('disabled');
                return;
            }

            $.ajax({
                url: "{{ route('check.barcode') }}",
                type: "GET",
                data: {
                    barcode: barcode,
                    id: currentItemId
                },
                success: function(response) {
                    if (response.exists) {
                        // Mavjud bo'lsa: Qizil qilish va tugmani o'chirish
                        barcodeInput.addClass('is-invalid'); // Bootstrap klassi (qizil chegaralar)
                        barcodeInput.css('border-color', 'red');
                        errorMsg.show();
                        submitButton.attr('disabled', 'disabled');
                    } else {
                        // Mavjud bo'lmasa: Tozalash va tugmani yoqish
                        barcodeInput.removeClass('is-invalid');
                        barcodeInput.css('border-color', ''); // Stili tozalash
                        errorMsg.hide();
                        submitButton.removeAttr('disabled');
                    }
                },
                error: function() {
                    console.log('Serverda xatolik yuz berdi');
                }
            });
        }

        // Sahifa yuklanganda darhol tekshirish (Generatsiya qilingan kod uchun)
        checkBarcodeAvailability();

        // Input o'zgarganda tekshirish (KeyUp)
        barcodeInput.on('keyup paste input', function() {
            // Har bir harf bosganda so'rov yubormaslik uchun 500ms kutamiz
            clearTimeout(timer);
            timer = setTimeout(checkBarcodeAvailability, 500);
        });
    });
</script>
<script>
    $("input[data-type='currency']").on({
        input: function () {
            formatCurrency($(this));
        },
        blur: function () {
            formatCurrency($(this), "blur");
        }
    });

    function formatNumber(n) {
        // faqat raqam qoldiradi
        return n.replace(/\D/g, "");
    }

    function formatCurrency(input, blur) {

        let input_val = input.val();

        if (input_val === "") return;

        // vergulni nuqtaga almashtirish
        input_val = input_val.replace(",", ".");

        let original_len = input_val.length;
        let caret_pos = input.prop("selectionStart");

        // decimal borligini tekshirish
        if (input_val.indexOf(".") >= 0) {

            let decimal_pos = input_val.indexOf(".");

            let left_side = input_val.substring(0, decimal_pos);
            let right_side = input_val.substring(decimal_pos + 1);

            // chap tomonni formatlash
            left_side = formatNumber(left_side);

            left_side = left_side.replace(/\B(?=(\d{3})+(?!\d))/g, " ");

            // o‘ng tomonda faqat 2 xona
            right_side = formatNumber(right_side);

            if (blur === "blur") {
                right_side = (right_side + "00").substring(0, 2);
            }

            if (right_side.length > 0) {
                input_val = left_side + "." + right_side.substring(0, 2);
            } else {
                input_val = left_side + ".";
            }

        } else {

            input_val = formatNumber(input_val);

            input_val = input_val.replace(/\B(?=(\d{3})+(?!\d))/g, " ");

            if (blur === "blur") {
                input_val += ".00";
            }
        }

        input.val(input_val);

        let updated_len = input_val.length;
        caret_pos = updated_len - original_len + caret_pos;

        input[0].setSelectionRange(caret_pos, caret_pos);
    }
</script>
@endsection