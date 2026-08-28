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
                                    <!-- <form method="POST" action="{{ route('checkins_search') }}">
                                        @csrf
                                        <input type="text" class="form-control" value="{{ $keyword ? $keyword : NULL }}" name="search" required placeholder="Поиск по № заявки и № спец-ии завода">
                                    </form> -->
                                </div>
                                <div class="col-md-3 mb-3">
                                   <a href="{{ route('return_form') }}" class="btn btn-primary btn-block">Добавить</a> 
                                </div>
                            </div>

                        <div class="card">
                            
                            
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                  <thead>
                                    <tr class="text-center">
                                      <th width="160px">{{ trans('backend.table.doc_number') }}</th>
                                      <th>{{ trans('backend.table.name') }}</th>
                                      <th>{{ trans('backend.input.warehouse') }}</th>
                                      <th>{{ trans('backend.input.barcode_short') }}</th>
                                      <th>{{ trans('backend.table.qty_short') }}</th>
                                      <th>{{ trans('backend.table.add_user') }}</th>
                                      <th>{{ trans('backend.input.seller') }}</th>
                                      <th>Дата создания</th>
                                    </tr>
                                  </thead>
                                  <tbody class="text-center">
                                     @foreach($data as $item)
                                    <tr>
                                        <td>{{ $item->checkdetid ? $item->checkdetid->checkid->number_work : 'Удалено'}}</td>
                                        <td>{{ $item->prodid ? $item->prodid->name : 'Удалено' }}</td>
                                        <td>{{ $item->checkdetid ? $item->checkdetid->warehouseid->name : 'Удалено' }}</td>
                                        <td>{{ $item->prodid ? $item->prodid->barcode : 'Удалено' }}</td>
                                        <td>{{ $item->qty }} из {{ $item->qty + ($item->checkdetid ? $item->checkdetid->qty : 0)}} {{ $item->checkdetid ? $item->checkdetid->prodid->unitid->name : null }} </td>
                                        <td>{{ $item->userid ? $item->userid->name : NULL }}</td>
                                        <td>{{ $item->sellerid ? $item->sellerid->name : NULL }}</td>
                                        <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
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