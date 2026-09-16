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
                        <div class="card dashboard-filter mb-3">
                            <div class="card-inner py-3">
                                <form method="GET" action="{{ route('checkins_index') }}">
                                    <div class="row gy-2 align-items-end">
                                        <div class="col-lg-4 col-md-6">
                                            <label class="form-label mb-1">{{ trans('backend.ui.search') }}</label>
                                            <input type="text" class="form-control" value="{{ $keyword }}" name="search" placeholder="{{ trans('backend.ui.checkin_search_hint') }}">
                                        </div>
                                        <div class="col-lg-2 col-md-6">
                                            <label class="form-label mb-1">{{ trans('backend.ui.date_from') }}</label>
                                            <input type="date" class="form-control" name="date_from" value="{{ $dateFrom }}">
                                        </div>
                                        <div class="col-lg-2 col-md-6">
                                            <label class="form-label mb-1">{{ trans('backend.ui.date_to') }}</label>
                                            <input type="date" class="form-control" name="date_to" value="{{ $dateTo }}">
                                        </div>
                                        <div class="col-lg-4 col-md-6">
                                            <div class="d-flex" style="gap: 8px;">
                                                <button type="submit" class="btn btn-primary flex-grow-1"><em class="icon ni ni-search"></em>{{ trans('backend.ui.search_action') }}</button>
                                                <a href="{{ route('checkins_index') }}" class="btn btn-outline-light"><em class="icon ni ni-reload"></em>{{ trans('backend.ui.clear') }}</a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="row dashboard-actions">
                            <div class="col-md-2 mb-2">
                               <a href="{{ route('checkin_form_excel') }}" class="btn btn-warning btn-block"><em class="icon ni ni-download"></em>{{ trans('backend.ui.excel') }}</a>
                            </div>
                            @hasanyrole('admin|sale|cashier')
                            <div class="col-md-2 mb-2">
                               <a href="{{ route('checkin_form') }}" class="btn btn-primary btn-block">{{ trans('backend.table.add_from') }}</a> 
                            </div>
                            @endhasanyrole
                        </div>

                        <div class="card dashboard-table-card">
                            <div class="table-responsive">
                                <table class="table table-bordered text-nowrap">
                                  <thead>
                                    <tr class="text-center">
                                      <th width="160px">{{ trans('backend.table.doc_number') }}</th>
                                      <th>{{ trans('backend.input.warehouse') }}</th>
                                      <th>{{ trans('backend.ui.type') }}</th>
                                      <th>{{ trans('backend.table.supplier') }}</th>
                                      <th>{{ trans('backend.table.vid_tovar') }}</th>
                                      <th>{{ trans('backend.ui.rate') }}</th>
                                      <th width="50px">{{ trans('backend.table.edit') }}</th>
                                      <th>{{ trans('backend.ui.note') }}</th>
                                      <th>{{ trans('backend.ui.label') }}</th>
                                      <th>{{ trans('backend.table.in_summs') }}</th>
                                      <th width="150px">{{ trans('backend.table.data_add') }}</th>
                                      <th width="110px">{{ trans('backend.table.add_user') }}</th>
                                      <th width="50px">{{ trans('backend.table.delete') }}</th>
                                      <th>{{ trans('backend.ui.status') }}</th>
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
                                       <td>{{ $item->details->count() }} </td>
                                       <td>{{ number_format($item->currency_type_price, 0, '.', ' ') }}</td>
                                       <td>
                                           @if(!$item->source_system)
                                           <a href="{{ route('checkin_form', ['id' => $item->code])}}" style="text-decoration:underline;">{{ trans('backend.table.post_edit_short') }}</a>
                                           @else
                                           <span class="text-muted">LIDAZ</span>
                                           @endif
                                           @if($item->file_excel) | <a href="{{ route('checkin_form', ['id' => $item->code])}}" style="text-decoration:underline;">EXCEL</a> @endif
                                       </td>
                                       <td>{{ $item->reference }} </td>
                                       <td><a target="_blank" href="{{ route('checkin_excel', ['id' => $item->code]) }}">{{ trans('backend.table.download') }}</a></td>
                                       <td>
                                           @php
                                               $totalsByCurrency = $item->details
                                                   ->groupBy(fn ($detail) => $detail->currency_type ?: $item->currency_type)
                                                   ->map(fn ($details) => $details->sum('total_price'));
                                           @endphp
                                           @forelse($totalsByCurrency as $currencyType => $total)
                                               <div>
                                                   {{ number_format($total, 2, '.', ' ') }}
                                                   {{ $currencyTypes[$currencyType] ?? $item->currencytypeid?->name }}
                                               </div>
                                           @empty
                                               0.00 {{ $item->currencytypeid?->name }}
                                           @endforelse
                                       </td>
                                      <td>{{ Carbon\Carbon::parse($item->date)->format('Y-m-d') . ' ' .  $item->created_at->format('H:i') }} </td>
                                      <td>{{ $item->userid ? $item->userid->name : null  }}</td>
                                      <td>
                                          @if(!$item->source_system)
                                          <a href="{{ route('delete_checkin', ['id' => $item->code])}}" style="text-decoration:underline;">{{ trans('backend.table.delete') }}</a>
                                          @endif
                                      </td>
                                      <td>
                                          @if($item->source_system === 'lidaz' && (int)$item->status === 0)
                                              <a class="btn btn-sm btn-success" href="{{ route('checkin_done_status', ['id' => $item->code]) }}" onclick="return confirm({{ json_encode(trans('backend.ui.accept_confirm')) }})">{{ trans('backend.ui.accept') }}</a>
                                              <a class="btn btn-sm btn-danger" href="{{ route('checkin_cancel_status', ['id' => $item->code]) }}" onclick="return confirm({{ json_encode(trans('backend.ui.reject_confirm')) }})">{{ trans('backend.ui.reject') }}</a>
                                          @elseif((int)$item->status === 1)
                                              <span class="badge badge-success">{{ trans('backend.ui.accepted') }}</span>
                                              @if($item->source_system === 'lidaz')
                                                  <a class="btn btn-sm btn-outline-danger ml-1" href="{{ route('supplier_return_form', $item->code) }}">{{ trans('backend.ui.return_to_lidaz') }}</a>
                                              @endif
                                          @elseif((int)$item->status === 2)
                                              <span class="badge badge-danger">{{ trans('backend.ui.rejected') }}</span>
                                          @else
                                              <span class="badge badge-warning">{{ trans('backend.ui.pending') }}</span>
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
