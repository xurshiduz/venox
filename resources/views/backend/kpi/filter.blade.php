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
                                      <th width="160px">{{ trans('backend.table.doc_number') }}</th>
                                      
                                      @hasanyrole('admin|cashier|report')
                                      <th colspan="2" width="160px">{{ trans('backend.table.nakladnoy') }}</th>
                                      @endhasanyrole
                                      <th>{{ trans('backend.table.client') }}</th>
                                      <th width="160px">{{ trans('backend.table.manager') }}</th>
                                      <th width="120px">{{ trans('backend.table.vid_tovar') }}</th>
                                      @hasanyrole('admin|cashier|report')
                                      <th>{{ trans('backend.table.summa_dog') }}</th>
                                      <!--<th>{{ trans('backend.table.cash_pay') }}</th>
                                      <th>{{ trans('backend.table.pay_title') }}</th>-->
                                      <th>{{ trans('backend.table.stock') }}</th>
                                      @endhasanyrole
                                      <th>{{ trans('backend.table.step') }}</th>
                                      <th width="150px">{{ trans('backend.table.data_add') }}</th>
                                      <th width="50px">{{ trans('backend.table.edit') }}</th>
                                      <th width="50px">{{ trans('backend.table.delete') }}</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @foreach($data as $item)
                                    <tr class="text-center">
                                      <td>
                                       @if($item->number_work)
                                       <a target="_blank" href="{{ route('checkout_check', ['id' => $item->code])}}">{{ $item->number_work }} <em class="icon ni ni-download"></em></a>
                                       @else
                                       {{ trans('backend.table.draft') }}
                                       @endif
                                      </td>
                                      
                                      @hasanyrole('admin|cashier|report')
                                      <td><a href="{{ route('checkout_print', ['id' => $item->code, 'view' => 'full']) }}">{{ trans('backend.table.nak_view_one') }}</a> </td>
                                      <td><a href="{{ route('checkout_print', ['id' => $item->code, 'view' => 'short']) }}">{{ trans('backend.table.nak_view_two') }}</a> </td>
                                      @endhasanyrole
                                       <td>{{ $item->client_id ? $item->supid->name : NULL }}</td>
                                       <td>{{ $item->manager_id ? $item->managerid->name : NULL }}</td>
                                       <td>{{ $item->details()->count() }} </td>
                                       @hasanyrole('admin|cashier|report')
                                       <td>{{ number_format($item->sumtotal(), 2, '.', ' ') }} {{ $item->currencytypeid->belgi }}</td>
                                       <!--<td>{{ number_format($item->payments()->where('status', 1)->sum('price'), 2, '.', ' ') }} сум</td>
                                       <td style="padding: 2px 10px;">
                                        @if($item->number_work)
                                           @if($item->sumtotal() == $item->payments()->where('status', 1)->sum('price')) 
                                            оплачено 
                                           @else 
                                            <a href="{{ route('checkout_done_pay', ['id' => $item->code]) }}"data-bs-toggle="modal" data-bs-target="#modalDefault{{ $item->id }}" class="btn btn-warning btn-block btn-sm">Оплата</a> 
                                           @endif
                                        @else
                                        
                                        @endif
                                       </td>-->
                                       @endhasanyrole
                                       <td></td>
                                       <td style="padding: 2px 10px;" id="tsendsuccess{{ $item->id }}">
                                        @if($item->number_work)
                                           @if($item->shipment_status == 0) 
                                            <a href="#" id="sendsuccess" data-id="{{ $item->id }}"  class="btn btn-primary btn-block btn-sm">{{ trans('backend.table.shipment') }}</a>
                                            <!--<a href="{{ route('checkout_done_send', ['id' => $item->code]) }}" class="btn btn-primary btn-block btn-sm">{{ trans('backend.table.shipment') }}</a>--> 
                                           @elseif($item->shipment_status == 1) 
                                            {{ trans('backend.table.shipment_ok') }} 
                                           @endif
                                        @else
                                        
                                        @endif
                                       </td>
                                       <td>{{ $item->created_at->format('Y-m-d H:i') }} </td>
                                       <td>
                                           @if($item->step == 1)
                                           <a href="{{ route('checkout_form', ['id' => $item->code, 'page' => $data->currentPage()])}}" style="text-decoration:underline;">{{ trans('backend.table.post_edit') }}</a>
                                           @else
                                           @hasanyrole('admin|cashier')
                                           <a href="{{ route('checkout_form', ['id' => $item->code, 'page' => $data->currentPage()])}}" style="text-decoration:underline;">{{ trans('backend.table.post_edit') }}</a>
                                           @endhasanyrole
                                           @endif
                                       </td>
                                       <td><a href="{{ route('delete_checkout', ['id' => $item->code])}}" style="text-decoration:underline;">{{ trans('backend.table.delete') }}</a></td>
                                    </tr>
                                    
                                    @if($item->number_work)
                                    <div class="modal fade" tabindex="-1" id="modalDefault{{ $item->id }}">
                                        <div class="modal-dialog modal-lg" role="document">
                                            <div class="modal-content">
                                                <div class="modal-body">
                                                    <form action="{{ route('checkout_pay', ['id' => $item->code]) }}" method="POST">
                                                    @csrf
                                                    <div class="row gy-1">
                                                        <div class="col-lg-6 col-sm-6">
                                                            <div class="form-group">
                                                                <label class="form-label">{{ trans('backend.table.date') }}</label>
                                                                <div class="form-control-wrap">
                                                                    <div class="form-icon form-icon-right">
                                                                        <em class="icon ni ni-calendar-alt"></em>
                                                                    </div>
                                                                    <input type="text" name="date" value="{{ Carbon\Carbon::now()->format('m/d/Y') }}" class="form-control date-picker" placeholder="дата">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="col-lg-6 col-sm-6">
                                                            <div class="form-group">
                                                                <label class="form-label">{{ trans('backend.table.type_pay') }}</label>
                                                                <div class="form-control-wrap">
                                                                    <select class="form-select js-select2" required name="cash_receipt_type" data-search="on">
                                                                        @foreach($types as $type)
                                                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="col-lg-3 col-sm-3">
                                                            <div class="form-group">
                                                                <label class="form-label" for="summa">{{ trans('backend.table.summs') }}</label>
                                                                <div class="form-control-wrap">
                                                                    <input type="number" class="form-control" min="1" max="{{ $item->sumtotal() - $item->payments()->where('status', 1)->sum('price') }}" value="{{ $item->sumtotal() - $item->payments()->where('status', 1)->sum('price') }}" name="price" id="summa" placeholder="Сумма">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="col-lg-3 col-sm-3">
                                                            <div class="form-group">
                                                                <label class="form-label" for="currency">{{ trans('backend.input.currency') }}</label>
                                                                <div class="form-control-wrap">
                                                                    <select class="form-select js-select2" required name="currency_type" data-search="on">
                                                                        @foreach(App\Models\CurrencyType::where('status', 1)->orderBy('id', 'asc')->get() as $currency)
                                                                        <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                                                        @endforeach
                                                                    </select>    
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="col-lg-6 col-sm-6">
                                                            <div class="form-group">
                                                                <label class="form-label" for="comment">{{ trans('backend.input.comment') }}</label>
                                                                <div class="form-control-wrap">
                                                                    <input type="text" class="form-control" name="comment" id="comment" placeholder="{{ trans('backend.input.comment') }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <a href="#" data-bs-dismiss="modal" aria-label="Close" class="btn btn-danger btn-sm btn-block text-uppercase">{{ trans('backend.input.priv') }}</a>
                                                        </div>
                                                        
                                                        <div class="col-md-6">
                                                            <button type="submit" class="btn btn-primary btn-sm btn-block text-uppercase" >{{ trans('backend.table.button_done') }}</button>
                                                        </div>
                                                    </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
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