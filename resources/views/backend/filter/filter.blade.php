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

                        <div class="card mb-3">
                            <div class="card-inner">
                                @include('backend.filter._form')
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <div class="card card-bordered"><div class="card-inner py-2">
                                    <div class="text-soft">Приход ({{ $totals['in_rows'] }} строк)</div>
                                    <h5 class="text-success mb-0">+{{ number_format($totals['in_qty'], 2, '.', ' ') }}</h5>
                                </div></div>
                            </div>
                            <div class="col-md-4">
                                <div class="card card-bordered"><div class="card-inner py-2">
                                    <div class="text-soft">Расход ({{ $totals['out_rows'] }} строк)</div>
                                    <h5 class="text-danger mb-0">−{{ number_format($totals['out_qty'], 2, '.', ' ') }}@if($totals['out_bonus'] > 0) <small>+ {{ number_format($totals['out_bonus'], 2, '.', ' ') }} бонус</small>@endif</h5>
                                </div></div>
                            </div>
                            <div class="col-md-4">
                                <div class="card card-bordered"><div class="card-inner py-2">
                                    <div class="text-soft">Разница (приход − расход)</div>
                                    <h5 class="mb-0">{{ number_format($totals['diff'], 2, '.', ' ') }}</h5>
                                </div></div>
                            </div>
                        </div>
                        <div class="card">
                            @include('layouts.message.success')
                            @include('layouts.message.error')
                            <div class="table-responsive">
                                <table class="table table-bordered text-nowrap">
                                  <thead>
                                    <tr class="text-center">
                                      <th width="90px">Тип</th>
                                      <th width="110px">Дата</th>
                                      <th>Товар</th>
                                      <th width="140px">Штрих-код товара</th>
                                      <th width="100px">Кол-во</th>
                                      <th width="120px">Цена</th>
                                      <th width="120px">Сумма</th>
                                      <th width="150px">Склад</th>
                                      <th>Поставщик / Клиент</th>
                                      <th>Менеджер</th>
                                      <th>Документ</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @forelse($data as $item)
                                    <tr class="text-center">
                                      <td>
                                        @if($item->type === 'kirim')
                                          <span class="badge bg-success">Приход</span>
                                        @else
                                          <span class="badge bg-danger">Расход</span>
                                        @endif
                                      </td>
                                      <td>{{ $item->date ? \Carbon\Carbon::parse($item->date)->format('d.m.Y') : '—' }}</td>
                                      <td class="text-start">{{ $item->product_name ?: '—' }}</td>
                                      <td>{{ $item->product_barcode ?: ($item->unique_barcode ?: '—') }}</td>
                                      <td class="{{ $item->type === 'kirim' ? 'text-success' : 'text-danger' }}">
                                        {{ $item->type === 'kirim' ? '+' : '−' }}{{ number_format((float) $item->qty, 2, '.', ' ') }}
                                        @if((float) $item->bonus > 0)<br><small>+{{ (float) $item->bonus }} бонус</small>@endif
                                      </td>
                                      <td>{{ number_format((float) $item->price, 2, '.', ' ') }} {{ $item->currency }}</td>
                                      <td>{{ number_format((float) $item->total_price, 2, '.', ' ') }} {{ $item->currency }}</td>
                                      <td>{{ $item->warehouse_name ?: '—' }}</td>
                                      <td>{{ $item->client_name ?: '—' }}</td>
                                      <td>{{ $item->manager_name ?: '—' }}</td>
                                      <td>
                                        @php($docName = $item->number_work ?: ($item->reference ?: 'Черновик #' . $item->document_id))
                                        @if($item->type === 'kirim')
                                          <a target="_blank" href="{{ route('checkin_print', ['id' => $item->code]) }}">{{ $docName }}</a>
                                        @else
                                          <a target="_blank" href="{{ route('checkout_print', ['id' => $item->code, 'view' => 'full']) }}">{{ $docName }}</a>
                                        @endif
                                      </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="11" class="text-center text-soft">Ничего не найдено</td></tr>
                                    @endforelse
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
