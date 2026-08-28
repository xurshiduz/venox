@extends('layouts.backend')

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="components-preview mx-auto">
                    <div class="nk-block nk-block-lg">
                        <div class="card card-bordered">
                            @include('layouts.message.success')
                            @include('layouts.message.error')
                            <div class="card-inner">
                                <div class="preview-block">
                                    <form action="{{ route('return_save') }}" method="GET">
                                    <div class="row gy-3">
                                        <div class="col-lg-12 col-sm-12">
                                            <div class="form-group">
                                                <div class="form-control-wrap">
                                                    <input type="text" autocomplete="off" autofocus class="form-control" name="part_number" value="{{ $keyword }}" placeholder="Штрих-код чек или номер заказа">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 col-sm-12">
                                            <button class="btn btn-primary btn-block" type="submit">{{ trans('backend.table.check_search') }}</button>
                                        </div>
                                    </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        @if($item)
                        <div class="card card-bordered mt-2">
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
                                      @endhasanyrole
                                      <th width="150px">{{ trans('backend.table.data_add') }}</th>
                                    </tr>
                                  </thead>
                                  <tbody>
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
                                       @endhasanyrole
                                       <td>{{ $item->created_at->format('Y-m-d H:i') }} </td>
                                    </tr>
                                  </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="card card-bordered mt-2">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{ trans('backend.table.name') }}</th>
                                            <th>{{ trans('backend.input.warehouse') }}</th>
                                            <th>{{ trans('backend.input.barcode_short') }}</th>
                                            <th>{{ trans('backend.table.stock') }}</th>
                                            <th width="150px">{{ trans('backend.table.price') }}</th>
                                            <th width="50px">{{ trans('backend.table.qty_short') }}</th>
                                            <th><em class="icon ni ni-undo"></em></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($item->details()->get() as $detail)
                                        <input type="hidden" id="model" class="model" value="{{ $detail->id }}">
                                        <tr>
                                            <td width="50px">{{$loop->iteration}}</td>
                                            <td>{{ $detail->prodid->name }}</td>
                                            <td>{{ $detail->warehouseid->name }}</td>
                                            <td>{{ $detail->prodid->barcode }}</td>
                                            <td width="100px">{{$detail->prodid->checkindetails()->where('warehouse_id', $detail->warehouse_id)->where('status', 1)->sum('qty')) - ($detail->prodid->checkoutdetails()->where('warehouse_id', $detail->warehouse_id)->where('status', 1)->sum('qty') }} {{ $detail->prodid->unitid->name}}</td>
                                            <td> {{ $detail->price }} </td>
                                            <td> {{ $detail->qty }} </td>
                                            <td width="50px">@if($detail->qty > 0)<a href="#" data-bs-toggle="modal" data-bs-target="#modalDefault{{ $detail->id }}"><em class="icon ni ni-undo"></em></a>@endif</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <a href="{{ route('returns_index') }}" class="mt-3 btn btn-block btn-warning">{{ trans('backend.input.close') }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    @if($item)
        @foreach($item->details()->get() as $detail)
            @if($detail->qty > 0)
                <div class="modal fade" tabindex="-1" id="modalDefault{{ $detail->id }}">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <a href="#" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <em class="icon ni ni-cross"></em>
                            </a>
                            <div class="modal-header">
                                <h5 class="modal-title">{{ $detail->prodid->name }}</h5>
                            </div>
                            <form action="{{ route('return_post', ['code' => $detail->code]) }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <div class="form-group">
                                        <div class="form-control-wrap">
                                            <input type="number" class="form-control form-control-outlined" name="qty" id="outlined-default" value="1" min="1" max="{{ $detail->qty }}">
                                            <label class="form-label-outlined" for="outlined-default">{{ trans('backend.table.qty_full') }}</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer" style="padding: 10px 0; display: block">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <button class="btn btn-warning btn-block" data-bs-dismiss="modal" aria-label="Close">{{ trans('backend.input.close') }}</button>
                                        </div>
                                        <div class="col-md-6">
                                            <button class="btn btn-primary btn-block">{{ trans('backend.input.button_done') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    @endif

@endsection