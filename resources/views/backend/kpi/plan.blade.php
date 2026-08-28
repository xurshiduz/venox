@extends('layouts.backend')

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="components-preview mx-auto">
                    <div class="nk-block nk-block-lg">
                        <div class="row">
                            <div class="col-md-10 mb-2">
                            </div>
                            <div class="col-md-2 mb-2">
                               <a href="{{ route('kpi_form') }}" class="btn btn-primary btn-block">{{ trans('backend.table.add_from') }}</a> 
                            </div>
                        </div>

                        <div class="card"> 
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                  <thead>
                                    <tr class="text-center">
                                      <th scope="col">{{ trans('backend.table.year') }}</th>
                                      <th scope="col">{{ trans('backend.table.month') }}</th>
                                      <th scope="col">{{ trans('backend.table.plan') }}</th>
                                      <th colspan="2">{{ trans('backend.table.download') }} ({{ trans('backend.table.plan') }})</th>
                                      <th scope="col">{{ trans('backend.table.date') }}</th>
                                      <th scope="col">{{ trans('backend.table.add_user') }}</th>
                                      <th scope="col">{{ trans('backend.table.action') }}</th>
                                      <th scope="col">{{ trans('backend.table.plan') }}</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @foreach($data as $item)
                                    <tr class="text-center">
                                      <td>{{ Carbon\Carbon::parse($item->date)->format('Y') }}</td>
                                      <td>@if(LaravelLocalization::getCurrentLocaleNative() == 'RU') {{ Carbon\Carbon::parse($item->date)->locale('ru_RU')->monthName }} @else {{ Carbon\Carbon::parse($item->date)->locale('uz_UZ')->monthName }} @endif</td>
                                      <td>{{ number_format($item->details()->sum('plan_sum'), 0, '.', ' ') }} {{ trans('backend.table.sum_belgi') }}</td>
                                       <td><a href="{{ route('kpi_plan_print', ['id' => $item->code]) }}"><img width="22px" src="/upload/view-files.png"> PDF </a></td>
                                       <td><a href="#"><a href="{{ route('kpi_plan_excel', ['id' => $item->code]) }}"><img width="22px" src="/upload/excel.png"> Excel</a> </td>
                                      <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
                                      <td>{{ $item->userid->name }}</td>
                                      <td><a href="{{ route('kpi_form', ['id' => $item->code])}}" style="text-decoration:underline;">{{ trans('backend.table.post_edit') }}</a></td>
                                      <td><a href="{{ route('plan_index_show', ['id' => $item->code])}}" style="text-decoration:underline;">{{ trans('backend.index.view_button') }}</a></td>
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