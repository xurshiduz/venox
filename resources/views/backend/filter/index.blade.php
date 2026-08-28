@extends('layouts.backend')

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner"> 
            <div class="nk-content-body">
                <div class="components-preview mx-auto">
                    <div class="nk-block nk-block-lg">
                        <div class="card">
                            <div class="card-inner">
                                @include('layouts.message.success')
                                @include('layouts.message.error')
                                <form method="GET" action="{{ route('filter_param') }}">
                                    <div class="row gx-6 gy-3">
                                        <div class="col-lg-4 col-md-6">
                                            <div class="form-group">
                                                <label class="overline-title overline-title-alt">{{ trans('backend.table.from_date') }}</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" value="{{ $fromdate }}" name="fromdate" class="form-control date-picker">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6">
                                            <div class="form-group">
                                                <label class="overline-title overline-title-alt">{{ trans('backend.table.to_date') }}</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" value="{{ $todate }}" name="todate" class="form-control date-picker">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-4 col-md-6">
                                            <div class="form-group">
                                                <label class="overline-title overline-title-alt">{{ trans('backend.input.barcode_short') }}</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" name="barcode" autocomplete="off" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-4 col-md-6">
                                            <div class="form-group">
                                                <label class="overline-title overline-title-alt">{{ trans('backend.input.warehouse') }}</label>
                                                <select class="form-select js-select2" name="warehouse">
                                                    <option value="all">{{ trans('backend.input.all_select') }}</option>
                                                    @foreach($warehouses as $warehouse)
                                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-4 col-md-6">
                                            <div class="form-group">
                                                <label class="overline-title overline-title-alt">{{ trans('backend.table.manager') }}</label>
                                                <select class="form-select js-select2" name="manager">
                                                    <option value="all">{{ trans('backend.input.all_select') }}</option>
                                                    @foreach($managers as $manager)
                                                    <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-4 col-md-6">
                                            <div class="form-group">
                                                <label class="overline-title overline-title-alt">{{ trans('backend.index.clients') }}</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2" name="client_id" required data-search="on">
                                                        <option value="all">{{ trans('backend.input.all_select') }}</option>
                                                        @foreach($clients as $client)
                                                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        
                                        
                                        <div class="col-lg-4 col-md-6">
                                            <div class="custom-control custom-control-sm custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" name="shipment" id="shipment">
                                                <label class="custom-control-label" for="shipment"> {{ trans('backend.index.check_shipme') }} </label>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-md-6">
                                            <div class="custom-control custom-control-sm custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" name="finish" id="finish">
                                                <label class="custom-control-label" for="finish"> {{ trans('backend.index.check_finish') }} </label>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group">
                                                <button type="submit" class="btn btn-secondary btn-block">{{ trans('backend.table.filter') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

</script>
@endsection