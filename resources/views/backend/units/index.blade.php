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
                                    <form method="POST" action="{{ route('units_search') }}">
                                        @csrf
                                        <input type="text" class="form-control" value="{{ $keyword ? $keyword : NULL }}" name="search" required placeholder="Поиск по штрих-код и наименование">
                                    </form>
                                </div>
                                <div class="col-md-3 mb-3">
                                   <a href="{{ route('unit_form') }}" class="btn btn-primary btn-block">Добавить</a> 
                                </div>
                            </div>

                        <div class="card">
                            
                            
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                  <thead>
                                    <tr class="text-center">
                                      <th>Название</th>
                                      <th>Краткое название</th>
                                      <th>Дата создания</th>
                                      <th>Редактирование</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @foreach($data as $item)
                                    <tr class="text-center">
                                       <td>{{ $item->comment }}</td>
                                       <td>{{ $item->name }}</td>
                                       <td>{{ $item->created_at->format('Y-m-d H:i') }} </td>
                                       <td><a href="{{ route('unit_form', ['id' => $item->code])}}" style="text-decoration:underline;">Редактировать</a></td>
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