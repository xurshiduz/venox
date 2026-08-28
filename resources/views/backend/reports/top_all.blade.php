@extends('layouts.backend')

@section('css')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Pacifico&display=swap');
    </style>
@endsection

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="components-preview mx-auto">
                    <div class="nk-block nk-block-lg">
                        <div class="card">
                            <p style="font-size: 26px; text-align: center; padding-top: 10px">{!! $title !!}</p>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                  <thead>
                                    <tr class="text-center">
                                      <th scope="col">Штрих-код</th>
                                      <th scope="col">Наименование</th>
                                      <th scope="col">Категория</th>
                                      <th scope="col">Количество продаж</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @foreach($data as $item)
                                    <tr class="text-center">
                                      <td>{{ $item->barcode }}</td>
                                      <td style="text-align: left;">{{ $item->name }}</td>
                                      <td>{{ $item->category_id ? $item->catid->name : NULL}}</td>
                                      <td>{{ number_format($item->balance, 0, '.', ' ') }}</td>
                                    </tr>
                                    @endforeach
                                  </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection