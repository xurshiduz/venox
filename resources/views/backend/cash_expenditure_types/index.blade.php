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
                                <form method="POST" action="{{ route('cash_expenditure_types_search') }}">
                                    @csrf
                                    <input type="text" class="form-control" value="{{ $keyword ? $keyword : NULL }}" name="search" required placeholder="Поиск по штрих-код и наименование">
                                </form>
                            </div>
                            <!-- <div class="col-md-2 mb-2">
                               <a href="{{ route('product_import') }}" class="btn btn-warning btn-block">Импорт</a> 
                            </div> -->
                            <div class="col-md-2 mb-2">
                               <a href="{{ route('cash_expenditure_type_form') }}" class="btn btn-primary btn-block">Добавить</a> 
                            </div>
                        </div>

                        <div class="card">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                  <thead>
                                    <tr class="text-center">
                                      <th scope="col">Наимнование</th>
                                      <th scope="col">Сумма</th>
                                      <th scope="col">Дата</th>
                                      <th scope="col">Кто завел</th>
                                      <th scope="col">Действия</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @foreach($data as $item)
                                    <tr class="text-center">
                                      <td width="180px">{{ $item->name }}</td>
                                      <td width="180px;">{{ number_format($item->price, 2, '.', ' ') }}</td>
                                      <td width="150px">{{ $item->created_at->format('Y-m-d H:i') }}</td>
                                      <td width="120px">{{ $item->user_id ? $item->uname->name : NULL }}</td>
                                      <td width="120px"><a href="{{ route('cash_expenditure_type_form', ['id' => $item->code])}}" style="text-decoration:underline;">Редактировать</a></td>
                                    </tr>
                                    @endforeach
                                  </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    @include('layouts.nav')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection