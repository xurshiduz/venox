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
                            </div>
                            <div class="col-md-3 mb-3">
                               
                                <div class="form-group">
                                    <div class="form-control-wrap">
                                        <select class="form-select js-select2" required name="type_id" data-search="on">
                                            <option>{{ trans('backend.table.modul') }}</option>
                                            @foreach($types as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                  <thead>
                                    <tr class="text-center">
                                      <th>{{ trans('backend.table.modul') }}</th>
                                      <th>{{ trans('backend.table.add_user') }}</th>
                                      <th>{{ trans('backend.table.ip_address') }}</th>
                                      <th>{{ trans('backend.table.contact') }}</th>
                                      <th>{{ trans('backend.table.date_add') }}</th>
                                      <th>{{ trans('backend.index.view_button') }}</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @foreach($data as $key => $item)
                                    <tr class="text-center">
                                       <td>{{ $item->modulid->name }}</td>
                                       <td>{{ $item->userid->name }}</td>
                                       <td>{{ $item->ip_address }} </td>
                                       <td>{{ $item->userid->phone }} </td>
                                       <td>{{ $item->created_at->format('Y-m-d H:i') }} </td>
                                       <td><a data-bs-toggle="modal" data-bs-target="#modal{{ $key }}" href="#" style="text-decoration:underline;">{{ trans('backend.index.view_button') }}</a></td>
                                    </tr>
                                    
                                    <div class="modal fade" tabindex="-1" id="modal{{ $key }}">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <a href="#" class="close" data-bs-dismiss="modal" aria-label="Close">
                                                    <em class="icon ni ni-cross"></em>
                                                </a>
                                                <div class="modal-header">
                                                    <h5 class="modal-title">{{ trans('backend.table.modul') }}: {{ $item->modulid->name }}</h5>
                                                </div>
                                                <div class="modal-body">
                                                    <b>{{ trans('backend.table.user_agent') }}:</b> {!! $item->agent !!}
                                                    <hr>
                                                    <b>{{ trans('backend.table.change_item') }}:</b> {!! $item->comment !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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