@extends('layouts.backend')

@section('content')
<style>
    .warehouse-toolbar { border: 1px solid #e5e9f2; border-radius: 8px; background: #fff; padding: 18px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(31, 43, 58, .05); }
    .warehouse-toolbar .form-label { display: block; margin-bottom: 6px; font-size: 12px; font-weight: 600; color: #526484; }
    .warehouse-toolbar .form-control, .warehouse-toolbar .form-select, .warehouse-toolbar .btn { min-height: 42px; }
    .warehouse-table-card { border: 1px solid #e5e9f2; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(31, 43, 58, .05); }
    .warehouse-table { margin-bottom: 0; }
    .warehouse-table thead th { background: #f5f6fa; color: #364a63; font-weight: 600; vertical-align: middle; white-space: nowrap; }
    .warehouse-table tbody td { vertical-align: middle; }
    .warehouse-table tbody tr:hover { background: #f8f9fc; }
    .warehouse-name { font-weight: 600; color: #364a63; }
    .warehouse-action { display: inline-flex; align-items: center; justify-content: center; gap: 6px; min-width: 96px; }
    @media (max-width: 767px) { .warehouse-toolbar .form-group { margin-bottom: 12px; } }
</style>
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="components-preview mx-auto">
                    <div class="nk-block nk-block-lg">
                        @hasanyrole('admin|arrival')
                        <div class="warehouse-toolbar">
                          <div class="row align-items-end">
                            <div class="col-md-9">
                                <form method="GET" action="{{ route('warehouses_stock_excel_input') }}">
                                    <div class="row align-items-end">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-label">{{ trans('backend.input.name') }}</label>
                                                <select class="form-select js-select2" name="id" required data-search="on">
                                                    @foreach($data as $warehouse)
                                                    <option value="{{ $warehouse->code }}">{{ $warehouse->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                              <label class="form-label">{{ trans('backend.table.from_text') }}</label>
                                              <input type="number" min="0" class="form-control" name="take" required placeholder="0">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <div class="form-group">
                                              <label class="form-label">{{ trans('backend.table.to_text') }}</label>
                                              <input type="number" min="1" class="form-control" name="pag" required placeholder="{{ App\Models\Product::count() }}">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                           <button type="submit" class="btn btn-success btn-block warehouse-action"><em class="icon ni ni-download"></em>{{ trans('backend.table.download') }}</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-3 mt-3 mt-md-0">
                               <a href="{{ route('warehouse_form') }}" class="btn btn-primary btn-block warehouse-action"><em class="icon ni ni-plus"></em>{{ trans('backend.table.add_from') }}</a>
                            </div>
                          </div>
                        </div>
                        @endhasanyrole

                        <div class="card warehouse-table-card">
                            
                            
                            <div class="table-responsive">
                                <table class="table table-bordered warehouse-table">
                                  <thead>
                                    <tr class="text-center">
                                      <th>{{ trans('backend.menu.dealers') }}</th>
                                      <th>{{ trans('backend.input.name') }}</th>
                                      @hasanyrole('admin|arrival')
                                      <th>{{ trans('backend.table.download') }} ({{ trans('backend.table.stock_name') }})</th>
                                      <!--<th>{{ trans('backend.table.part_lists') }}</th>-->
                                      @endhasanyrole
                                      <th>{{ trans('backend.table.inv') }}</th>
                                      <th>{{ trans('backend.table.address') }}</th>
                                      <th>{{ trans('backend.table.contact') }}</th>
                                      @hasanyrole('admin|arrival')
                                      <th>{{ trans('backend.table.stock_name') }}</th>
                                      <!--<th>{{ trans('backend.table.blocks') }}</th>-->
                                      <th>{{ trans('backend.table.date_add') }}</th>
                                      <!-- <th>Хранилище</th> -->
                                      <th>{{ trans('backend.table.edit') }}</th>
                                      @endhasanyrole
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @foreach($data as $item)
                                    <tr class="text-center">
                                       <td>{{ $item->dealerid ? $item->dealerid->name . ' ('  . $item->dealerid->regionid->name . ')' : null }}</td>
                                       <td class="warehouse-name">{{ $item->name }}</td>
                                       @hasanyrole('admin|arrival')
                                       <!--<td><a href="#"><img width="22px" src="/upload/view-files.png"> PDF </a></td>-->
                                       <td>
                                           <a class="btn btn-sm btn-success warehouse-action" href="{{ route('warehouses_stock_excel', ['id' => $item->code]) }}"><em class="icon ni ni-download"></em> Excel</a>
                                           <!--<a href="{{ route('warehouses_stock_excel_param', ['id' => $item->code, 'take' => 0, 'pag' => 8000]) }}"><img width="22px" src="/upload/excel.png"> Excel 0-8000</a> <br>
                                           <a href="{{ route('warehouses_stock_excel_param', ['id' => $item->code, 'take' => 8000, 'pag' => 16000]) }}"><img width="22px" src="/upload/excel.png"> Excel 8001-16000</a> -->
                                       </td>
                                       <!--<td>
                                           <a href="{{ route('warehouses_excel_product_list', ['id' => $item->code]) }}"><img width="22px" src="/upload/excel.png"> {{ trans('backend.table.download') }}</a>
                                       </td>-->
                                       @endhasanyrole
                                       <td><a class="btn btn-sm btn-light warehouse-action" href="{{ route('warehouse_inventory', ['id' => $item->code]) }}"><em class="icon ni ni-printer"></em> Print</a></td>
                                       <td>{{ $item->address }} </td>
                                       <td>{{ $item->phone }} </td>
                                       @hasanyrole('admin|arrival')
                                       <td><a class="btn btn-sm btn-light" title="Yangilash" href="{{ route('warehouse_stock_refresh', ['id' => $item->code]) }}"><em class="icon ni ni-update"></em></a></td>
                                       <!--<td><a href="{{ route('warehouse_blocks', ['id' => $item->code])}}">{{ $item->blocks->count() }}</a></td>-->
                                       <td>{{ $item->created_at->format('Y-m-d H:i') }} </td>
                                       <!-- <td><a href="{{ route('warehouse_select', ['warehouse' => $item->code])}}" style="text-decoration:underline;">Посмотреть</a></td> -->
                                       <td><a class="btn btn-sm btn-outline-primary warehouse-action" href="{{ route('warehouse_form', ['id' => $item->code])}}"><em class="icon ni ni-edit"></em>{{ trans('backend.table.post_edit') }}</a></td>
                                       @endhasanyrole
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
