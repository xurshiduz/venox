@extends('layouts.backend')

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="components-preview mx-auto">
                    <div class="nk-block nk-block-lg">
                        <div class="card dashboard-filter mb-3">
                            <div class="card-inner py-3">
                                <form method="GET" action="{{ route($filterRoute) }}" data-auto-filter="true">
                                    <div class="row gy-2 align-items-end">
                                        <div class="col-lg-4 col-md-6">
                                            <label class="form-label mb-1">{{ trans('backend.ui.search') }}</label>
                                            <input type="text" class="form-control" name="search" value="{{ $keyword }}" placeholder="{{ trans('backend.ui.cash_search_hint') }}" autocomplete="off">
                                        </div>
                                        <div class="col-lg-2 col-md-6">
                                            <label class="form-label mb-1">{{ trans('backend.ui.date_from') }}</label>
                                            <input type="date" class="form-control js-open-picker" name="date_from" value="{{ $dateFrom }}">
                                        </div>
                                        <div class="col-lg-2 col-md-6">
                                            <label class="form-label mb-1">{{ trans('backend.ui.date_to') }}</label>
                                            <input type="date" class="form-control js-open-picker" name="date_to" value="{{ $dateTo }}">
                                        </div>
                                        <div class="col-lg-2 col-md-6">
                                            <div class="d-flex" style="gap: 8px;">
                                                <button type="submit" class="btn btn-primary flex-grow-1"><em class="icon ni ni-search"></em>{{ trans('backend.ui.search_action') }}</button>
                                                <a href="{{ route($filterRoute) }}" class="btn btn-outline-light"><em class="icon ni ni-reload"></em>{{ trans('backend.ui.clear') }}</a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="row dashboard-actions">
                            <div class="col-md-6 mb-3 mr-auto">
                                <!--<form method="POST" action="{{ route('products_search') }}">
                                    @csrf
                                    <input type="text" class="form-control" value="{{ $keyword ? $keyword : NULL }}" name="search" required placeholder="Поиск по штрих-код и наименование">
                                </form>-->
                            </div>
                            <div class="col-md-2 mb-2">
                               <a href="{{ route('cash_receipts_index_his') }}" class="btn btn-warning btn-block">{{ trans('backend.ui.cancelled_payments') }}</a>
                            </div>

                            <div class="col-md-2 mb-2">
                               <a href="{{ route('cash_receipts_excel') }}" class="btn btn-success btn-block"><em class="icon ni ni-download"></em>{{ trans('backend.ui.excel') }}</a>
                            </div>
                            
                            <div class="col-md-2 mb-2">
                               <a href="{{ route('cash_receipt_form') }}" class="btn btn-primary btn-block">{{ trans('backend.table.add_from') }}</a> 
                            </div>
                        </div>

                        <div class="card dashboard-table-card">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                  <thead>
                                    <tr class="text-center">
                                      <th scope="col">{{ trans('backend.ui.contract_number') }}</th>
                                      <th scope="col">{{ trans('backend.table.client') }}</th>
                                      <th scope="col">{{ trans('backend.table.summs') }}</th>
                                      <th scope="col">{{ trans('backend.table.type_pay') }}</th>
                                      <!--<th scope="col">Комментарии</th>-->
                                      <th scope="col">{{ trans('backend.table.date') }}</th>
                                      <th scope="col">{{ trans('backend.table.add_user') }}</th>
                                      <th scope="col">{{ trans('backend.table.status') }}</th>
                                      <th scope="col">{{ trans('backend.table.annulirovat') }}</th>
                                      <th scope="col">{{ trans('backend.ui.actions') }}</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @foreach($data as $item)
                                    <tr class="text-center">
                                      <td width="180px">{{ $item->checkout_id ? $item->contracktname?->number_work : trans('backend.ui.for_debt') }}</td>
                                      <td>{{ $item->clientname?->name ?: $item->contracktname?->supid?->name }}</td>
                                      <td>{{ number_format($item->price, 2, '.', ' ') }}</td>
                                      <td>{{ $item->tname ? $item->tname->name : NULL }}</td>
                                      <!--<td>{{ $item->comment }}</td>-->
                                      <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
                                      <td>{{ $item->user_id ? $item->uname->name : NULL }}</td>
                                      <td>{{ $item->status ? trans('backend.table.pay_success') : trans('backend.table.pay_cancel') }}</td>
                                      <td class="table-actions">
                                          @if($item->status)
                                              <a href="{{ route('cash_receipt_status', ['id' => $item->code])}}" class="btn btn-icon btn-sm btn-outline-danger" title="{{ trans('backend.table.annulirovat') }}" aria-label="{{ trans('backend.table.annulirovat') }}" data-confirm="{{ trans('backend.ui.confirm_cancel_payment') }}"><em class="icon ni ni-cross-circle"></em></a>
                                          @else
                                              <a href="{{ route('cash_receipt_status', ['id' => $item->code])}}" class="btn btn-icon btn-sm btn-outline-success" title="{{ trans('backend.table.return_pay') }}" aria-label="{{ trans('backend.table.return_pay') }}" data-confirm="{{ trans('backend.ui.confirm_restore_payment') }}"><em class="icon ni ni-undo"></em></a>
                                          @endif
                                      </td>
                                      <td width="80px" class="table-actions"><a href="{{ route('cash_receipt_form', ['id' => $item->code])}}" class="btn btn-icon btn-sm btn-outline-primary" title="{{ trans('backend.ui.edit') }}" aria-label="{{ trans('backend.ui.edit') }}"><em class="icon ni ni-edit"></em></a></td>
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
