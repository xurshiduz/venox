@extends('layouts.backend')

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="components-preview mx-auto">
                    <div class="nk-block nk-block-lg">
                        <div class="row">
                            <div class="col-md-10 mb-3">
                                <!-- <form method="POST" action="{{ route('categories_search') }}">
                                    @csrf
                                    <input type="text" class="form-control" value="{{ $keyword ? $keyword : NULL }}" name="search" required placeholder="Поиск по штрих-код и наименование">
                                </form> -->
                            </div>
                            <div class="col-md-2 mb-2">
                               <a href="{{ route('category_form') }}" class="btn btn-primary btn-block">{{ trans('backend.table.add_from') }}</a> 
                            </div>
                        </div>

                        <div class="card">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                  <thead>
                                    <tr class="text-center">
                                      <th scope="col">{{ trans('backend.table.name') }}</th>
                                      <th scope="col">{{ trans('backend.table.products') }}</th>
                                      <th scope="col">{{ trans('backend.table.stock_name') }}</th>
                                      <th scope="col">{{ trans('backend.table.action') }}</th>
                                      <!-- <th scope="col">Отчёт</th> -->
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @foreach($data as $item)
                                    <tr class="text-center">
                                      <td>{{ $item->name }}</td>
                                      <td>{{ $item->productsid()->count() }}</td>
                                      <td>{{ ($item->checkindetails()->where('status', 1)->sum('qty') - $item->checkoutdetails()->where('status', 1)->sum('qty')) }}</td>
                                      <td><a href="{{ route('category_form', ['id' => $item->code])}}" style="text-decoration:underline;">{{ trans('backend.table.post_edit') }}</a></td>
                                      <!-- <td><a href="{{ route('category_report', ['id' => $item->code])}}" style="text-decoration:underline;">Посмотреть</a></td> -->
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