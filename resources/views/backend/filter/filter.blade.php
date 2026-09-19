@extends('layouts.backend')

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="components-preview mx-auto">
                    <div class="nk-block nk-block-lg">
                        <!--<div class="row">
                                <div class="col-md-9 mb-3">
                                    <form method="POST" action="{{ route('checkouts_search') }}">
                                        @csrf
                                        <input type="text" class="form-control" value="{{ $keyword ? $keyword : NULL }}" name="search" required placeholder="Поиск по № заявки и № спец-ии завода">
                                    </form>
                                </div>
                                <div class="col-md-3 mb-3">
                                   <a href="{{ route('checkout_form') }}" class="btn btn-primary btn-block">Добавить</a> 
                                </div>
                            </div>-->

                        <div class="card">
                            @include('layouts.message.success')
                            @include('layouts.message.error')
                            <div class="table-responsive">
                                <table class="table table-bordered text-nowrap">
                                  <thead>
                                    <tr class="text-center">
                                      <th width="150px">Дата прихода</th>
                                      <th>Товар</th>
                                      <th width="140px">Штрих-код</th>
                                      <th width="100px">Кол-во</th>
                                      <th width="120px">Цена</th>
                                      <th width="120px">Сумма</th>
                                      <th width="150px">Склад</th>
                                      <th>Поставщик</th>
                                      <th>Документ</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @foreach($data as $item)
                                    <tr class="text-center">
                                      <td>{{ $item->checkid ? \Carbon\Carbon::parse($item->checkid->date)->format('d.m.Y') : '—' }}</td>
                                      <td>{{ optional($item->prodid)->name ?: '—' }}</td>
                                      <td>{{ optional($item->prodid)->barcode ?: ($item->barcode ?: '—') }}</td>
                                      <td>{{ number_format((float) $item->qty, 2, '.', ' ') }}</td>
                                      <td>{{ number_format((float) $item->price, 2, '.', ' ') }}</td>
                                      <td>{{ number_format((float) $item->total_price, 2, '.', ' ') }}</td>
                                      <td>{{ optional($item->warehouseid)->name ?: '—' }}</td>
                                      <td>{{ optional($item->checkid->supid)->name ?: '—' }}</td>
                                      <td>{{ optional($item->checkid)->reference ?: optional($item->checkid)->code ?: '—' }}</td>
                                    </tr>
                                    @endforeach
                                  </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @include('backend.nav')
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
        
        $('#sendsuccess').click(function(){
            var _ = $(this),
            datacid = _.data('id');

            $.ajax({
                type: "POST",
                url: '{{ route("checkout_send_success") }}',
                dataType: 'JSON',
                data: { datacid: datacid },
                success: function(data) {
                    
                    $('#tsendsuccess'+datacid).empty();
                    $('#tsendsuccess'+datacid).append("{{ trans('backend.table.shipment_ok') }} ");
                },
                error: function(ajaxContext) {
                    alert(ajaxContext.responseText)
                }
            });
        });
    </script>
    @endsection
