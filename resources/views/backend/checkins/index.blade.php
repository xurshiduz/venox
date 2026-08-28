@extends('layouts.backend')

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="components-preview mx-auto">
                    <div class="nk-block nk-block-lg">
                        @include('layouts.message.success')
                        @include('layouts.message.error')
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <form method="POST" action="{{ route('checkins_search') }}">
                                    @csrf
                                    <input type="text" class="form-control" value="{{ $keyword ? $keyword : NULL }}" name="search" required placeholder="Поиск по № заявки и № спец-ии завода">
                                </form>
                            </div>
                            <div class="col-md-2 mb-2">
                               <a href="{{ route('checkin_form_excel') }}" class="btn btn-warning btn-block">Excel</a> 
                            </div>
                            @hasanyrole('admin|sale|cashier')
                            <div class="col-md-2 mb-2">
                               <a href="{{ route('checkin_form') }}" class="btn btn-primary btn-block">{{ trans('backend.table.add_from') }}</a> 
                            </div>
                            @endhasanyrole
                        </div>

                        <div class="card">
                            <div class="table-responsive">
                                <table class="table table-bordered text-nowrap">
                                  <thead>
                                    <tr class="text-center">
                                      <th width="160px">{{ trans('backend.table.doc_number') }}</th>
                                      <th>{{ trans('backend.input.warehouse') }}</th>
                                      <th>Тип</th>
                                      <th>{{ trans('backend.table.supplier') }}</th>
                                      <th>{{ trans('backend.table.vid_tovar') }}</th>
                                      <th>Курс</th>
                                      <th width="50px">{{ trans('backend.table.edit') }}</th>
                                      <th>Примечание</th>
                                      <th>Этикетка</th>
                                      <th>{{ trans('backend.table.in_summs') }}</th>
                                      <th width="150px">{{ trans('backend.table.data_add') }}</th>
                                      <th width="110px">{{ trans('backend.table.add_user') }}</th>
                                      <th width="50px">{{ trans('backend.table.delete') }}</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @foreach($data as $item)
                                    <tr class="text-center">
                                      <td>
                                        @if($item->number_work)
                                        <a target="_blank" href="{{ route('checkin_print', ['id' => $item->code])}}">{{ $item->number_work }} <em class="icon ni ni-download"></em></a>
                                        @else
                                        {{ trans('backend.table.draft') }}
                                        @endif
                                       </td>
                                       <td>{{ $item->warid?->name }} </td>
                                       <td>{{ $item->typeid?->name }} </td>
                                       <td>{{ $item->client_id ? $item->supid->name : NULL }}</td>
                                       <td>{{ $item->details()->count() }} </td>
                                       <td>{{ number_format($item->currency_type_price, 0, '.', ' ') }}</td>
                                       <td>
                                           <a href="{{ route('checkin_form', ['id' => $item->code])}}" style="text-decoration:underline;">{{ trans('backend.table.post_edit_short') }}</a>
                                           @if($item->file_excel) | <a href="{{ route('checkin_form', ['id' => $item->code])}}" style="text-decoration:underline;">EXCEL</a> @endif
                                       </td>
                                       <td>{{ $item->reference }} </td>
                                       <td><a target="_blank" href="{{ route('checkin_excel', ['id' => $item->code]) }}">{{ trans('backend.table.download') }}</a></td>
                                       <td>{{ number_format($item->details()->where('currency_type', 2)->sum('total_price'), 2, '.', ' ') }} {{ $item->currencytypeid?->name }}</td>
                                      <td>{{ Carbon\Carbon::parse($item->date)->format('Y-m-d') . ' ' .  $item->created_at->format('H:i') }} </td>
                                      <td>{{ $item->userid ? $item->userid->name : null  }}</td>
                                      <td><a href="{{ route('delete_checkin', ['id' => $item->code])}}" style="text-decoration:underline;">{{ trans('backend.table.delete') }}</a></td>
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