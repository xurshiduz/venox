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
                                @if($item && $checkout)
                                <div class="row">
                                    <div class="col-lg-2 col-sm-2">
                                        <a href="{{ route('cash_receipt_status', ['id' => $item->code, 'checkout' => $checkout, 'page' => $page]) }}" class="btn btn-danger btn-block">{{ trans('backend.table.delete') }}</a>
                                    </div>
                                </div>
                                @endif
                                   {!! Form::open(['id' => 'appointment_form']) !!}
                                    <div class="row gy-3">
                                        <div class="col-lg-2 col-sm-2">
                                            <div class="form-group">
                                                <label class="form-label">{{ trans('backend.input.select_date') }}</label>
                                                <div class="form-control-wrap">
                                                    <!-- Kichik xatolik (\ belgisi) \ ga to'g'irlandi -->
                                                    <input type="text" name="date" value="{{ $item && $item->date ? $item->date : \Carbon\Carbon::now()->format('m/d/Y') }}" class="form-control date-picker" placeholder="„D„p„„„p">
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
                                                <label class="form-label" for="price-input">{{ trans('backend.input.summs') }}</label>
                                                <div class="form-control-wrap">
                                                    <!-- type="text" ga o'zgartirildi va id="price-input" berildi. PHP number_format qo'shildi -->
                                                    <input type="text" class="form-control" name="price" id="price-input" required placeholder="{{ trans('backend.input.summs') }}" value="{{ $item && $item->price ? number_format((float)$item->price, 2, '.', ' ') : '' }}">
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
                                        
                                        <div class="col-lg-12 col-sm-12">
                                            <div class="form-group">
                                                <label class="form-label" for="comment-input">Коммент</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" class="form-control" name="comment" id="comment-input" required placeholder="Коммент" value="{{ $item ? $item->comment : '' }}">
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
    $(document).ready(function() {
        // Kiritish vaqtida raqamlarni formatlash
        $('#price-input').on('input', function(e) {
            // Faqat raqamlar va bitta nuqtani qoldirish
            let val = $(this).val().replace(/[^\d.]/g, '');

            let parts = val.split('.');
            // Agar ikkitadan ortiq nuqta bo'lsa, ortiqchasini olib tashlash
            if (parts.length > 2) {
                parts.pop();
                val = parts.join('.');
            }

            // Butun qismiga minglik ajratuvchi probellarni qo'shish
            if (parts[0].length > 0) {
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
            }

            // O'nlik qismni (nuqtadan keyinni) maksimal 2 xona bilan cheklash
            if (parts.length > 1) {
                parts[1] = parts[1].substring(0, 2);
                val = parts.join('.');
            } else {
                val = parts[0];
            }

            $(this).val(val);
        });

        // Forma jo'natilayotganda
        $('#appointment_form').on('submit', function () {
            let priceInput = $('#price-input');
            
            // Backend xato bermasligi uchun barcha probellarni olib tashlab, asl (12000.50) holatiga qaytarish
            let cleanValue = priceInput.val().replace(/\s/g, '');
            priceInput.val(cleanValue);

            $('#register').attr('disabled', 'true'); 
        });
    });
</script>
@endsection