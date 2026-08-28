@extends('layouts.backend')

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="components-preview mx-auto">
                    <div class="nk-block nk-block-lg">
                        <div class="card">
                            <h4 class="text-center mb-2 mt-2">{{ $selectid->name  }}</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                  <thead>
                                    <tr class="text-center">
                                      <th scope="col">Сумма</th>
                                      <th scope="col">Способ</th>
                                      <th scope="col">Комментарии</th>
                                      @if($selectid->id == 8)
                                      <th scope="col">Поставщик</th>
                                      @endif
                                      <th scope="col">Дата</th>
                                      <th scope="col">Кто завел</th>
                                      <th scope="col">Действия</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @foreach($data as $item)
                                    <tr class="text-center">
                                      <td>{{ number_format($item->price, 0, '.', ' ') }} сум</td>
                                      <td>{{ $item->typename ? $item->typename->name : NULL }}</td>
                                      <td>{{ $item->comment }}</td>
                                      @if($selectid->id == 8)
                                      <td>{{ $item->supplier ? $item->supplier->name : NULL }}</td>
                                      @endif
                                      <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
                                      <td>{{ $item->user_id ? $item->uname->name : NULL }}</td>
                                      <td><a href="{{ route('cash_expenditure_form', ['id' => $item->code])}}" style="text-decoration:underline;">Редактировать</a></td>
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