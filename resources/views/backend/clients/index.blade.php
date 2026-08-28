@extends('layouts.backend')

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="components-preview mx-auto">
                    <div class="nk-block nk-block-lg">
                        <div class="row">
                                <div class="col-md-9 mb-3">
                                    <form method="GET" action="{{ route('clients_search') }}">
                                        <input type="text" class="form-control" value="{{ $keyword ? $keyword : NULL }}" name="search" required placeholder="Поиск по наименование">
                                    </form>
                                </div>
                                <div class="col-md-3 mb-3">
                                   <a href="{{ route('client_form') }}" class="btn btn-primary btn-block">{{ trans('backend.table.add_from') }}</a> 
                                </div>
                            </div>

                        <div class="card">
                            
                            
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                  <thead>
                                    <tr class="text-center">
                                      <th>{{ trans('backend.table.fio_firma') }}</th>
                                      <th>{{ trans('backend.table.type_client') }}</th>
                                      @hasanyrole('admin|sale|cashier')
                                      <th>Расход</th>
                                      <th>Приход</th>
                                      @endhasanyrole
                                      <th>{{ trans('backend.table.address') }}</th>
                                      <th>{{ trans('backend.table.contact') }}</th>
                                      <th>{{ trans('backend.table.ras_sch') }}</th>
                                      <th>{{ trans('backend.table.date_add') }}</th>
                                      <!--<th>Отчёть</th>-->
                                      <th>{{ trans('backend.table.edit') }}</th>
                                      <th><em class="icon ni ni-trash"></em></th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @foreach($data as $item)
                                    <tr class="text-center">
                                       <td>{{ $item->name }}</td>
                                       <td>{{ $item->is_customer && $item->is_supplier ? 'Кл. и Пос.' : ($item->is_customer ? 'Клиент' : ($item->is_supplier ? 'Поставщик' : null) ) }}</td>
                                       @hasanyrole('admin|sale|cashier')
                                       <td><a href="{{ route('client_checkouts', ['id' => $item->code]) }}">{{ $item->checkouts()->count() }} {{ trans('backend.table.qty_short_t') }}</a></td>
                                       <td><a href="{{ route('client_checkins', ['id' => $item->code]) }}">{{ $item->checkins()->count() }} {{ trans('backend.table.qty_short_t') }}</a></td>
                                       @endhasanyrole
                                       <td>{{ $item->address }} </td>
                                       <td>{{ $item->phone }} </td>
                                       <td>{{ $item->schet }} </td>
                                       <td>{{ $item->created_at->format('Y-m-d H:i') }} </td>
                                       <!--<td><a href="#" style="text-decoration:underline;">Посмотреть</a></td>-->
                                       <td><a href="{{ route('client_form', ['id' => $item->code])}}" style="text-decoration:underline;">{{ trans('backend.table.post_edit') }}</a></td>
                                       <td>
                                           @if($item->checkouts->count() == 0 && $item->checkins->count() == 0)
                                                <a href="{{ route('client_delete', ['id' => $item->code])}}" style="text-decoration:underline;"><em class="icon ni ni-trash"></em></a>
                                           @endif
                                        </td>
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