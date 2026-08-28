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
                                   <a href="{{ route('dealer_form') }}" class="btn btn-primary btn-block">{{ trans('backend.table.add_from') }}</a> 
                                </div>
                            </div>

                        <div class="card">
                            
                            
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                  <thead>
                                    <tr class="text-center">
                                      <th>{{ trans('backend.input.name') }}</th>
                                      <th>{{ trans('backend.table.region') }}</th>
                                      <th>{{ trans('backend.table.phone') }}</th>
                                      <th>{{ trans('backend.table.address') }}</th>
                                      <th>{{ trans('backend.input.comment') }}</th>
                                      <th>{{ trans('backend.table.data_add') }}</th>
                                      <th>{{ trans('backend.table.edit') }}</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @foreach($data as $item)
                                    <tr class="text-center">
                                       <td>{{ $item->name }}</td>
                                       <td>{{ $item->regionid ? $item->regionid->name : null }}</td>
                                       <td>{{ $item->phone }}</td>
                                       <td>{{ $item->address }}</td>
                                       <td>{{ $item->comment }}</td>
                                       <td>{{ $item->created_at->format('Y-m-d') }} </td>
                                       <td><a href="{{ route('dealer_form', ['id' => $item->code])}}" style="text-decoration:underline;">{{ trans('backend.table.edit') }}</a></td>
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