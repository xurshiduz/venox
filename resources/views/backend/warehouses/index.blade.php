@extends('layouts.backend')

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="components-preview mx-auto">
                    <div class="nk-block nk-block-lg">
                        @hasanyrole('admin|arrival')
                        <div class="row">
                            <div class="col-md-9 mb-3">
                                <form method="GET" action="{{ route('warehouses_stock_excel_input') }}">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-control-wrap">
                                                <select class="form-select js-select2" name="id" required data-search="on">
                                                    @foreach($data as $warehouse)
                                                    <option value="{{ $warehouse->code }}">{{ $warehouse->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" class="form-control" name="take" required placeholder="{{ trans('backend.table.from_text') }} 0">
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <input type="text" class="form-control" name="pag" required placeholder="{{ trans('backend.table.to_text') }} {{ App\Models\Product::count() }}">
                                        </div>
                                        <div class="col-md-3">
                                           <button type="submit" class="btn btn-warning btn-block"><img width="18px" src="/upload/excel.png"> &nbsp; {{ trans('backend.table.download') }}</button> 
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-3 mb-3">
                               <a href="{{ route('warehouse_form') }}" class="btn btn-primary btn-block">{{ trans('backend.table.add_from') }}</a> 
                            </div>
                        </div>
                        @endhasanyrole

                        <div class="card">
                            
                            
                            <div class="table-responsive">
                                <table class="table table-bordered">
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
                                       <td>{{ $item->name }}</td>
                                       @hasanyrole('admin|arrival')
                                       <!--<td><a href="#"><img width="22px" src="/upload/view-files.png"> PDF </a></td>-->
                                       <td>
                                           <a href="{{ route('warehouses_stock_excel', ['id' => $item->code]) }}"><img width="22px" src="/upload/excel.png"> Excel FULL</a> <br>
                                           <!--<a href="{{ route('warehouses_stock_excel_param', ['id' => $item->code, 'take' => 0, 'pag' => 8000]) }}"><img width="22px" src="/upload/excel.png"> Excel 0-8000</a> <br>
                                           <a href="{{ route('warehouses_stock_excel_param', ['id' => $item->code, 'take' => 8000, 'pag' => 16000]) }}"><img width="22px" src="/upload/excel.png"> Excel 8001-16000</a> -->
                                       </td>
                                       <!--<td>
                                           <a href="{{ route('warehouses_excel_product_list', ['id' => $item->code]) }}"><img width="22px" src="/upload/excel.png"> {{ trans('backend.table.download') }}</a>
                                       </td>-->
                                       @endhasanyrole
                                       <td><em class="icon ni ni-printer"></em> <a href="{{ route('warehouse_inventory', ['id' => $item->code]) }}">Print</a></td>
                                       <td>{{ $item->address }} </td>
                                       <td>{{ $item->phone }} </td>
                                       @hasanyrole('admin|arrival')
                                       <td><a href="{{ route('warehouse_stock_refresh', ['id' => $item->code]) }}"><em class="icon ni ni-update"></em> </a></td>
                                       <!--<td><a href="{{ route('warehouse_blocks', ['id' => $item->code])}}">{{ $item->blocks->count() }}</a></td>-->
                                       <td>{{ $item->created_at->format('Y-m-d H:i') }} </td>
                                       <!-- <td><a href="{{ route('warehouse_select', ['warehouse' => $item->code])}}" style="text-decoration:underline;">Посмотреть</a></td> -->
                                       <td><a href="{{ route('warehouse_form', ['id' => $item->code])}}" style="text-decoration:underline;">{{ trans('backend.table.post_edit') }}</a></td>
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