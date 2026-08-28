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
                                <form method="POST" action="{{ route('products_search') }}" class="d-none">
                                    @csrf
                                    <input type="text" class="form-control" value="{{ $keyword ? $keyword : NULL }}" name="search" required placeholder="Поиск по штрих-код и наименование">
                                </form>
                            </div>
                            <div class="col-md-2 mb-2">
                               <a href="{{ route('transfer_form') }}" class="btn btn-primary btn-block">Добавить</a> 
                            </div>
                        </div>

                        <div class="card">
                            @include('layouts.message.success')
                            @include('layouts.message.error')
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                  <thead>
                                    <tr class="text-center">
                                      <th>{{ trans('backend.table.doc_number') }}</th>
                                      <th>{{ trans('backend.table.manager') }}</th>
                                      <th>{{ trans('backend.table.vid_tovar') }}</th>
                                      @hasanyrole('admin')
                                      <th>{{ trans('backend.table.post_edit_short') }}</th>
                                      @endhasanyrole
                                      <th>{{ trans('backend.table.step') }}</th>
                                      <th>{{ trans('backend.table.data_add') }}</th>
                                      <th>{{ trans('backend.table.delete') }}</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @foreach($data as $item)
                                    <tr class="text-center">
                                      <td>
                                        <a target="_blank" href="{{ route('transfers_check', ['id' => $item->code])}}">
                                           @if($item->number_work)
                                            {{ $item->number_work }}
                                           @else
                                            Чер. #{{ $item->id }}
                                           @endif
                                           <em class="icon ni ni-download"></em>
                                        </a>
                                      </td>
                                      <td>{{ $item->managerid ? $item->managerid->name : ($item->userid ? $item->userid->name : null)  }}</td>
                                      <td>{{ $item->details()->count() }}</td>
                                      @hasanyrole('admin')
                                      <td><a href="{{ route('transfer_form', ['id' => $item->code])}}" style="text-decoration:underline;">{{ trans('backend.table.post_edit_short') }}</a></td>
                                      @endhasanyrole
                                      <td></td>
                                      <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
                                      <td><a href="{{ route('delete_transfer', ['id' => $item->code])}}" style="text-decoration:underline;">{{ trans('backend.table.delete') }}</a></td>
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