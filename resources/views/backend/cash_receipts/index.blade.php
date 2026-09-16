@extends('layouts.backend')

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="components-preview mx-auto">
                    <div class="nk-block nk-block-lg">
                        <div class="card mb-3">
                            <div class="card-inner py-3">
                                <form method="GET" action="{{ route($filterRoute) }}">
                                    <div class="row gy-2 align-items-end">
                                        <div class="col-lg-4 col-md-6">
                                            <label class="form-label mb-1">Qidirish</label>
                                            <input type="text" class="form-control" name="search" value="{{ $keyword }}" placeholder="Mijoz, shartnoma, summa yoki to‘lov turi">
                                        </div>
                                        <div class="col-lg-2 col-md-6">
                                            <label class="form-label mb-1">Boshlanish sanasi</label>
                                            <input type="date" class="form-control" name="date_from" value="{{ $dateFrom }}">
                                        </div>
                                        <div class="col-lg-2 col-md-6">
                                            <label class="form-label mb-1">Tugash sanasi</label>
                                            <input type="date" class="form-control" name="date_to" value="{{ $dateTo }}">
                                        </div>
                                        <div class="col-lg-4 col-md-6">
                                            <div class="d-flex" style="gap: 8px;">
                                                <button type="submit" class="btn btn-primary flex-grow-1">Qidirish</button>
                                                <a href="{{ route($filterRoute) }}" class="btn btn-outline-light">Tozalash</a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <!--<form method="POST" action="{{ route('products_search') }}">
                                    @csrf
                                    <input type="text" class="form-control" value="{{ $keyword ? $keyword : NULL }}" name="search" required placeholder="Поиск по штрих-код и наименование">
                                </form>-->
                            </div>
                            <div class="col-md-2 mb-2">
                               <a href="{{ route('cash_receipts_index_his') }}" class="btn btn-warning btn-block">Отмененные платежи</a> 
                            </div>

                            <div class="col-md-2 mb-2">
                               <a href="{{ route('cash_receipts_excel') }}" class="btn btn-success btn-block"><em class="icon ni ni-download"></em> Excel</a>
                            </div>
                            
                            <div class="col-md-2 mb-2">
                               <a href="{{ route('cash_receipt_form') }}" class="btn btn-primary btn-block">{{ trans('backend.table.add_from') }}</a> 
                            </div>
                        </div>

                        <div class="card">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                  <thead>
                                    <tr class="text-center">
                                      <th scope="col">Номер договора</th>
                                      <th scope="col">{{ trans('backend.table.client') }}</th>
                                      <th scope="col">{{ trans('backend.table.summs') }}</th>
                                      <th scope="col">{{ trans('backend.table.type_pay') }}</th>
                                      <!--<th scope="col">Комментарии</th>-->
                                      <th scope="col">{{ trans('backend.table.date') }}</th>
                                      <th scope="col">{{ trans('backend.table.add_user') }}</th>
                                      <th scope="col">{{ trans('backend.table.status') }}</th>
                                      <th scope="col">{{ trans('backend.table.annulirovat') }}</th>
                                      <th scope="col">Действия</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @foreach($data as $item)
                                    <tr class="text-center">
                                      <td width="180px">{{ $item->checkout_id ? $item->contracktname?->number_work : 'за долг' }}</td>
                                      <td>{{ $item->client_id ? $item->clientname?->name : NULL }}</td>
                                      <td>{{ number_format($item->price, 2, '.', ' ') }}</td>
                                      <td>{{ $item->tname ? $item->tname->name : NULL }}</td>
                                      <!--<td>{{ $item->comment }}</td>-->
                                      <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
                                      <td>{{ $item->user_id ? $item->uname->name : NULL }}</td>
                                      <td>{{ $item->status ? trans('backend.table.pay_success') : trans('backend.table.pay_cancel') }}</td>
                                      <td>@if($item->status) <a href="{{ route('cash_receipt_status', ['id' => $item->code])}}" style="text-decoration:underline;">{{ trans('backend.table.annulirovat') }}</a> @else <a href="{{ route('cash_receipt_status', ['id' => $item->code])}}" style="text-decoration:underline;">{{ trans('backend.table.return_pay') }}</a> @endif</td>
                                      <td width="120px"><a href="{{ route('cash_receipt_form', ['id' => $item->code])}}" style="text-decoration:underline;">Редактировать</a></td>
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
