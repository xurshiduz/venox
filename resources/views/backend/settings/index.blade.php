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
                            </div>
                            <div class="col-md-2 mb-2">
                               <a href="{{ route('setting_form') }}" class="btn btn-primary btn-block">Добавить</a> 
                            </div>
                        </div>

                        <div class="card">
                            @include('layouts.message.success')
                            @include('layouts.message.error')
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                  <thead>
                                    <tr class="text-center">
                                      <th scope="col">Атрибут</th>
                                      <th scope="col">Значение</th>
                                      <th scope="col">Действия</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @foreach($data as $item)
                                    <tr class="text-center">
                                      <td>{{ $item->atribute }}</td>
                                      <td>{{ $item->value }}</td>
                                      <td><a href="{{ route('setting_form', ['id' => $item->atribute])}}" style="text-decoration:underline;">Редактировать</a></td>
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