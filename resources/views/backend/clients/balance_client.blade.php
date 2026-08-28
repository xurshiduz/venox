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
                                   <a href="{{ route('clients_balance_hisall_index') }}" class="btn btn-primary btn-block">{{ trans('backend.table.add_from') }}</a> 
                                </div>
                            </div>

                        <div class="card">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                  <thead>
                                    <tr class="text-center">
                                      <th>{{ trans('backend.table.fio_firma') }}</th>
                                      <th>{{ trans('backend.menu.client_balance') }}</th>
                                      <th>{{ trans('backend.table.view_all') }}</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @foreach($data as $item)
                                    <tr class="text-center">
                                       <td>{{ $item->name }}</td>
                                       <td>{{ number_format($item->balance, 2, '.', ' ') }} </td>
                                       <td><a href="{{ route('clients_balance_history_index', ['id' => $item->code]) }}">{{ trans('backend.table.view_all') }}</a></td>
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