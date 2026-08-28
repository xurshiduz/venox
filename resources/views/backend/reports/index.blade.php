@extends('layouts.backend')

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="components-preview mx-auto">
                    <div class="nk-block nk-block-lg">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <form method="POST" action="{{ route('products_search') }}">
                                    @csrf
                                    <input type="text" class="form-control" value="{{ $keyword ? $keyword : NULL }}" name="search" required placeholder="Поиск по штрих-код и наименование">
                                </form>
                            </div>
                            <div class="col-md-2 mb-2">
                               <a href="{{ route('product_import') }}" class="btn btn-warning btn-block">Импорт</a> 
                            </div>
                            <div class="col-md-2 mb-2">
                               <a href="{{ route('product_form') }}" class="btn btn-primary btn-block">Добавить</a> 
                            </div>
                        </div>

                        <div class="card">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                  <thead>
                                    <tr class="text-center">
                                      <th scope="col">Парт номер</th>
                                      <th scope="col">Наименование</th>
                                      <th scope="col">Категория</th>
                                      <th width="150px">Остатка</th>
                                      <th scope="col">Средняя продажа в месяц</th>
                                      <th width="150px">Действия</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @foreach($data as $item)
                                    <tr class="text-center">
                                      <td>{{ $item->id }}</td>
                                      <td>{{ $item->name }}</td>
                                      <td>{{ $item->category_id ? $item->catid->name : NULL}}</td>
                                      <td>{{ $item->tolshina }}</td>
                                      <td>{{ $item->tolshina }}</td>
                                      <td><a href="{{ route('product_form', ['id' => $item->code])}}" style="text-decoration:underline;">Редактировать</a></td>
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