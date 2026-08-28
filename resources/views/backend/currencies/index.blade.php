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
                                    <form method="POST" action="#">
                                        @csrf
                                        <input type="text" class="form-control" value="{{ $keyword ? $keyword : NULL }}" name="search" required placeholder="Поиск по штрих-код и наименование">
                                    </form>
                                </div>
                                <div class="col-md-3 mb-3">
                                   <a href="{{ route('currency_type_form') }}" class="btn btn-primary btn-block">Добавить</a> 
                                </div>
                            </div>-->

                        <div class="card">
                            
                            
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                  <thead>
                                    <tr class="text-center"> 
                                      <th scope="col">{{ trans('backend.input.currency') }}</th>
                                      <th scope="col">{{ trans('backend.table.currency') }}</th>
                                      <th scope="col">{{ trans('backend.table.date_update') }}</th>
                                      <th scope="col">{{ trans('backend.table.action') }}</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @foreach($data as $item)
                                    <tr class="text-center">
                                      <td>1 {{ $item->belgi }} </td>
                                      <td>{{ $item->currencyid->count() ? $item->currencyid->first()->price : NULL}} сум</td>
                                      <td>{{ $item->currencyid->count() ? $item->currencyid->first()->created_at->format('Y-m-d H:i') : NULL }}</td>
                                      <td style="padding: 0px;"><a class="btn btn-sm btn-info btn-block" href="{{ route('currency_form', ['id' => $item->belgi])}}">{{ trans('backend.table.curr_update') }}</a></td>
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