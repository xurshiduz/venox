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
                                <!--<form method="POST" action="{{ route('products_search') }}">
                                    @csrf
                                    <input type="text" class="form-control" value="{{ $keyword ? $keyword : NULL }}" name="search" required placeholder="Поиск по имя и логин">
                                </form>-->
                            </div>
                            <div class="col-md-2 mb-2">
                               <a href="{{ route('users_noactive') }}" class="btn btn-danger btn-block">Не активный</a> 
                            </div>
                            <div class="col-md-2 mb-2">
                               <a href="{{ route('user_form') }}" class="btn btn-primary btn-block">Добавить</a> 
                            </div>
                        </div>

                        <div class="card">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                  <thead>
                                    <tr class="text-center">
                                      <th scope="col">Юзернаем</th>
                                      <th scope="col">Тел</th>
                                      <th>{{ trans('backend.menu.dealers') }}</th>
                                      <th scope="col">{{ trans('backend.table.client_buy') }}</th>
                                      <th scope="col">Наименование</th>
                                      <!--<th scope="col">QR Code</th>-->
                                      <th scope="col">Роль</th>
                                      <th scope="col">Дата</th>
                                      <th width="150px">Действия</th>
                                      <th width="150px">Статус</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @foreach($data as $item)
                                    <tr class="text-center">
                                      <td>{{ $item->username }}<hr style="margin: 3px;">{{ $item->text_password }}</td>
                                      <td>{{ $item->phone }}</td>
                                      <td>{{ $item->dealerid ? $item->dealerid->name : null}} </td>
                                      <td><a href="{{ route('user_checkouts', ['id' => $item->code]) }}">{{ $item->checkouts()->count() }} {{ trans('backend.table.qty_short_t') }}</a></td>
                                      <td>{{ $item->name }}</td>
                                      <!--<td>{{ $item->code }}</td>-->
                                      <td>@foreach($item->uroles as $role) {{ $role->rolenameid->name_full }} @endforeach</td>
                                      <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
                                      <td>@if($item->username != 'admin')<a href="{{ route('user_form', ['id' => $item->code])}}" style="text-decoration:underline;">Редактировать</a>@endif</td>
                                      <td>
                                        @if($item->uroles()->whereIn('role_id', [1])->count() == 0)
                                          @if($item->status == 1) 
                                              <a href="{!! route('lock_user', ['id'=> $item->code]) !!}"  class="btn btn-danger btn-sm btn-block"> Заблокироват</a> 
                                          @else 
                                              <a href="{!! route('unlock_user', ['id'=> $item->code]) !!}"  class="btn btn-primary btn-sm btn-block">Активировать</a> 
                                          @endif 
                                        @endif 
                                      </td>
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