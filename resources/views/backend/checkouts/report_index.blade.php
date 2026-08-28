@extends('layouts.backend')

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="components-preview mx-auto">
                    <div class="nk-block nk-block-lg">
                        <div class="card">
                            <div class="card-inner position-relative card-tools-toggle" style="padding: 0.75rem 0.75rem; border-top: 1px solid #dbdfea; border-left: 1px solid #dbdfea; border-right: 1px solid #dbdfea;">
                                <div class="card-title-group">
                                    <div class="card-tools">
                                        <div class="form-inline flex-nowrap gx-3">
                                            <div class="btn-wrap">
                                            </div>
                                        </div><!-- .form-inline -->
                                    </div><!-- .card-tools -->
                                    <div class="card-tools me-n1">
                                        <ul class="btn-toolbar gx-1">
                                            <li>
                                                <a href="#" class="btn btn-icon search-toggle toggle-search" data-target="search"><em class="icon ni ni-search"></em></a>
                                            </li><!-- li -->
                                            <!--<li class="btn-toolbar-sep"></li>-->
                                            <li>
                                                <div class="toggle-wrap">
                                                    <a href="#" class="btn btn-icon btn-trigger toggle" data-target="cardTools"><em class="icon ni ni-menu-right"></em></a>
                                                    <div class="toggle-content" data-content="cardTools">
                                                        <ul class="btn-toolbar gx-1">
                                                            <li class="toggle-close">
                                                                <a href="#" class="btn btn-icon btn-trigger toggle" data-target="cardTools"><em class="icon ni ni-arrow-left"></em></a>
                                                            </li><!-- li -->
                                                            <li>
                                                                <div class="dropdown">
                                                                    <a href="#" class="btn btn-trigger btn-icon dropdown-toggle" data-bs-toggle="dropdown">
                                                                        <div class="dot dot-primary"></div>
                                                                        <em class="icon ni ni-filter-alt"></em>
                                                                    </a>
                                                                    <div class="filter-wg dropdown-menu dropdown-menu-xl dropdown-menu-end">
                                                                        <div class="dropdown-head">
                                                                            <span class="sub-title dropdown-title">{{ trans('backend.table.filter') }}</span>
                                                                        </div>
                                                                        <div class="dropdown-body dropdown-body-rg">
                                                                            <form method="GET" action="{{ route('checkout_filter') }}">
                                                                                <div class="row gx-6 gy-3">
                                                                                    <div class="col-6">
                                                                                        <div class="form-group">
                                                                                            <label class="overline-title overline-title-alt">{{ trans('backend.table.from_date') }}</label>
                                                                                            <div class="form-control-wrap">
                                                                                                <input type="text" value="{{ $fromdate }}" name="fromdate" class="form-control date-picker">
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-6">
                                                                                        <div class="form-group">
                                                                                            <label class="overline-title overline-title-alt">{{ trans('backend.table.to_date') }}</label>
                                                                                            <div class="form-control-wrap">
                                                                                                <input type="text" value="{{ $todate }}" name="todate" class="form-control date-picker">
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    
                                                                                    
                                                                                    <div class="col-12">
                                                                                        <div class="form-group">
                                                                                            <label class="overline-title overline-title-alt">{{ trans('backend.table.manager') }}</label>
                                                                                            <select class="form-select js-select2" name="manager">
                                                                                                <option value="all">Все</option>
                                                                                                @foreach($managers as $manager)
                                                                                                <option {{ $selmanager  == $manager->id ? 'selected' : NULL }} value="{{ $manager->id }}">{{ $manager->name }}</option>
                                                                                                @endforeach
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                    
                                                                                    <div class="col-6">
                                                                                        <div class="custom-control custom-control-sm custom-checkbox">
                                                                                            <input type="checkbox" class="custom-control-input" {{ $shipment ? 'checked' : NULL }} name="shipment" id="shipment">
                                                                                            <label class="custom-control-label" for="shipment"> {{ trans('backend.index.check_shipme') }} </label>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-6">
                                                                                        <div class="custom-control custom-control-sm custom-checkbox">
                                                                                            <input type="checkbox" class="custom-control-input" {{ $finish ? 'checked' : NULL }} name="finish" id="finish">
                                                                                            <label class="custom-control-label" for="finish"> {{ trans('backend.index.check_finish') }} </label>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-12">
                                                                                        <div class="form-group">
                                                                                            <button type="submit" class="btn btn-secondary btn-block">{{ trans('backend.input.button_done') }}</button>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </form>
                                                                        </div>
                                                                        <div class="dropdown-foot between">
                                                                            <a class="clickable" href="{{ route('checkouts_index') }}">Reset Filter</a>
                                                                        </div>
                                                                    </div><!-- .filter-wg -->
                                                                </div><!-- .dropdown -->
                                                            </li><!-- li -->
                                                            <li class="d-none d-md-none">
                                                                <div class="dropdown">
                                                                    <a href="#" class="btn btn-trigger btn-icon dropdown-toggle" data-bs-toggle="dropdown">
                                                                        <em class="icon ni ni-setting"></em>
                                                                    </a>
                                                                    <div class="dropdown-menu dropdown-menu-xs dropdown-menu-end">
                                                                        <ul class="link-check">
                                                                            <li><span>Show</span></li>
                                                                            <li class="active"><a href="#">10</a></li>
                                                                            <li><a href="#">20</a></li>
                                                                            <li><a href="#">50</a></li>
                                                                        </ul>
                                                                        <ul class="link-check">
                                                                            <li><span>Order</span></li>
                                                                            <li class="active"><a href="#">DESC</a></li>
                                                                            <li><a href="#">ASC</a></li>
                                                                        </ul>
                                                                    </div>
                                                                </div><!-- .dropdown -->
                                                            </li><!-- li -->
                                                        </ul><!-- .btn-toolbar -->
                                                    </div><!-- .toggle-content -->
                                                </div><!-- .toggle-wrap -->
                                            </li><!-- li -->
                                        </ul><!-- .btn-toolbar -->
                                    </div><!-- .card-tools -->
                                </div><!-- .card-title-group -->
                                <div class="card-search search-wrap" data-search="search">
                                    <div class="card-body">
                                        <form method="POST" action="{{ route('checkouts_search') }}">
                                        @csrf
                                            <div class="search-content">
                                                <a href="#" class="search-back btn btn-icon toggle-search" data-target="search"><em class="icon ni ni-arrow-left"></em></a>
                                                <input type="text" class="form-control border-transparent form-focus-none" value="{{ $keyword ? $keyword : NULL }}" name="search" required placeholder="Поиск по № заявки и № спец-ии завода">
                                                <button class="search-submit btn btn-icon"><em class="icon ni ni-search"></em></button>
                                            </div>
                                        </form>
                                    </div>
                                </div><!-- .card-search -->
                            </div>
                            @include('layouts.message.success')
                            @include('layouts.message.error')
                            <div class="table-responsive">
                                <table class="table table-bordered text-nowrap">
                                  <thead>
                                    <tr class="text-center">
                                      <th width="160px">{{ trans('backend.table.doc_number') }}</th>
                                      <th colspan="2" width="140px">{{ trans('backend.table.nakladnoy') }} | 1 | 2</th>
                                      <th>Сравнителная</th>
                                      <th>{{ trans('backend.table.client') }}</th>
                                      <th width="160px">{{ trans('backend.table.manager') }}</th>
                                      <th width="100px">{{ trans('backend.table.vid_tovar') }}</th>
                                      <th width="50px">{{ trans('backend.table.edit') }}</th>
                                      <th>{{ trans('backend.table.summa_dog') }}</th>
                                      <th>{{ trans('backend.table.cash_pay') }}</th>
                                      <!--<th>{{ trans('backend.table.step') }}</th>-->
                                      <th width="150px">{{ trans('backend.table.data_add') }}</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @foreach($data as $item)
                                    <tr class="text-center">
                                      <td>
                                       @if($item->number_work)
                                       <a target="_blank" href="{{ route('checkout_check', ['id' => $item->code])}}">{{ $item->number_work }} <em class="icon ni ni-download"></em></a>
                                       @else
                                       <a target="_blank" href="{{ route('checkout_check', ['id' => $item->code])}}">{{ trans('backend.table.draft') }} #{{ $item->id }} <em class="icon ni ni-download"></em></a>
                                       @endif
                                      </td>
                                      <td style="min-width: 75px;"><a href="{{ route('checkout_print', ['id' => $item->code, 'view' => 'full']) }}"><img width="22px" src="/upload/view-files.png"></a> <a href="{{ route('checkout_excel', ['id' => $item->code]) }}"><img width="22px" src="/upload/excel.png"></a> </td>
                                      <td style="min-width: 75px;"><a href="{{ route('checkout_print', ['id' => $item->code, 'view' => 'short']) }}"><img width="22px" src="/upload/view-files.png"></a> <a href="{{ route('checkout_excel_null', ['id' => $item->code]) }}"><img width="22px" src="/upload/excel.png"></a> </td>
                                      <td><a href="{{ route('checkouts_report_print', ['id' => $item->code]) }}">{{ trans('backend.index.view_button') }}</a></td>
                                       <td>{{ $item->client_id ? $item->supid->name : NULL }}</td>
                                       <td>{{ $item->manager_id ? $item->managerid->name : NULL }}</td>
                                       <td>{{ $item->details()->count() }} </td>
                                       <td><a href="{{ route('checkout_form', ['id' => $item->code, 'page' => $data->currentPage()])}}" style="text-decoration:underline;">{{ trans('backend.table.post_edit') }}</a></td>
                                       <td>{{ number_format($item->sumtotal(), 2, '.', ' ') }} {{ $item->currencytypeid->belgi }}</td>
                                       <td>{{ number_format($item->payments()->where('status', 1)->sum('price'), 2, '.', ' ') }} сум</td>
                                       <!--<td style="padding: 0px;" id="tsendsuccess{{ $item->id }}">
                                        @if($item->number_work)
                                           @if($item->shipment_status == 0) 
                                           @elseif($item->shipment_status == 1) 
                                            {{ trans('backend.table.shipment_ok') }} 
                                           @endif
                                        @else
                                        
                                        @endif
                                       </td>-->
                                       <td>{{ $item->created_at->format('Y-m-d H:i') }} </td>
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

@section('script')
<script>
    $("#formattedNumberField").on('keyup', function(){
        var n = parseInt($(this).val().replace(/\D/g,''),10);
        $(this).val(n.toLocaleString());
    });
</script>
<script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $(document).on('click', '.sendsuccess', function (){
            var _ = $(this),
            datacid = _.data('id');

            $.ajax({
                type: "POST",
                url: '{{ route("checkout_send_success") }}',
                dataType: 'JSON',
                data: { datacid: datacid },
                success: function(data) {
                    
                    $('#tsendsuccess'+datacid).empty();
                    $('#tsendsuccess'+datacid).append("{{ trans('backend.table.shipment_ok') }} ");
                },
                error: function(ajaxContext) {
                    alert(ajaxContext.responseText)
                }
            });
        });
    </script>
    @endsection