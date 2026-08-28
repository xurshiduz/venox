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
                                    {!! Form::open() !!}
                                    <div class="row gy-3">
                                        <div class="col-lg-3 col-sm-3">
                                            <div class="form-group">
                                                <label class="form-label" for="default-01">{{ trans('backend.table.fio_firma') }}</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" class="form-control" id="default-01" name="name" placeholder="{{ trans('backend.table.fio_firma') }}"  value="{{ $item ? $item->name : NULL }}">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-3 col-sm-3">
                                            <div class="form-group">
                                                <label class="form-label" for="formattedNumberField">{{ trans('backend.input.summs') }}</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" class="form-control" name="balance" id="formattedNumberField" required placeholder="{{ trans('backend.input.summs') }}" data-type="currency" value="{{ $item ? $item->balance : NULL }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-3 col-sm-3">
                                            <div class="form-group">
                                                <label class="form-label" for="director">Имя директора</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" class="form-control" id="director" name="director" placeholder="Имя директора"  value="{{ $item ? $item->director : NULL }}">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-3 col-sm-3">
                                            <div class="form-group">
                                                <label class="form-label" for="address">{{ trans('backend.table.address') }}</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" class="form-control" id="address" name="address" placeholder="{{ trans('backend.table.address') }}"  value="{{ $item ? $item->address : NULL }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-3 col-sm-3">
                                            <div class="form-group">
                                                <label class="form-label" for="phone">{{ trans('backend.table.contact') }}</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" class="form-control" id="phone" name="phone" placeholder="{{ trans('backend.table.contact') }}"  value="{{ $item ? $item->phone : NULL }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-3 col-sm-3">
                                            <div class="form-group">
                                                <label class="form-label" for="schet">{{ trans('backend.table.ras_sch') }}</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" class="form-control" id="schet" name="schet" placeholder="{{ trans('backend.table.ras_sch') }}"  value="{{ $item ? $item->schet : NULL }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-3 col-sm-3">
                                            <div class="form-group">
                                                <label class="form-label" for="region">Город</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" class="form-control" id="region" name="region" placeholder="Город"  value="{{ $item ? $item->region : NULL }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-3 col-sm-3">
                                            <div class="form-group">
                                                <label class="form-label" for="mfo">МФО</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" class="form-control" id="mfo" name="mfo" placeholder="МФО"  value="{{ $item ? $item->mfo : NULL }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-3 col-sm-3">
                                            <div class="form-group">
                                                <label class="form-label" for="inn">ИНН</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" class="form-control" id="inn" name="inn" placeholder="ИНН"  value="{{ $item ? $item->inn : NULL }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-3 col-sm-3">
                                            <div class="form-group">
                                                <label class="form-label" for="inn">ОКЭД</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" class="form-control" id="inn" name="inn" placeholder="ОКЭД"  value="{{ $item ? $item->inn : NULL }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-9 col-sm-9 mb-3">
                                            <div class="form-group">
                                                <label class="form-label" for="default-01">{{ trans('backend.input.comment') }}</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" class="form-control" name="comment" id="default-01" placeholder="{{ trans('backend.input.comment') }}"  value="{{ $item ? $item->comment : NULL }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <div class="custom-control custom-control-sm custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="is_customer" name="is_customer" {{ $item && $item->is_customer ? 'checked' : null }}>
                                                <label class="custom-control-label" for="is_customer">Мижоз (Клиент)</label>
                                            </div>
                                            <div class="custom-control custom-control-sm custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="is_supplier" name="is_supplier" {{ $item && $item->is_supplier ? 'checked' : NULL }}>
                                                <label class="custom-control-label" for="is_supplier">Етказиб берувчи (Поставшик)</label>
                                            </div>
                                        </div>


                                    </div>
                                    <div class="row gy-3">
                                        <div class="col-md-6">
                                            <a href="{{ route('clients_index') }}" class="btn btn-warning btn-block">{{ trans('backend.input.button_cancel') }}</a>
                                        </div>
                                        <div class="col-md-6">
                                            <button type="submit" class="btn btn-primary btn-block">{{ trans('backend.input.button_done') }}</button>
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

    $("input[data-type='currency']").on({
        input: function () {
            formatCurrency($(this));
        },
        blur: function () {
            formatCurrency($(this), true);
        }
    });

    function formatCurrency(input, blur = false) {
        let value = input.val();

        if (value === '') return;

        // faqat raqam va nuqtani qoldirish
        value = value.replace(/[^\d.]/g, '');

        // bir nechta nuqta bo‘lsa bittasini qoldirish
        let parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
            parts = value.split('.');
        }

        let integerPart = parts[0];
        let decimalPart = parts[1] || '';

        // minglik ajratish
        integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');

        // decimal 2 xonadan oshmasin
        if (decimalPart.length > 2) {
            decimalPart = decimalPart.substring(0, 2);
        }

        // blur bo‘lganda 00 qo‘shish
        if (blur) {
            decimalPart = (decimalPart + '00').substring(0, 2);
        }

        let formatted = integerPart;

        if (parts.length > 1 || blur) {
            formatted += '.' + decimalPart;
        }

        input.val(formatted);
    }
</script>
@endsection