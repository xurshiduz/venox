<form method="GET" action="{{ route('filter_param') }}">
    <div class="row gx-6 gy-3">
        <div class="col-lg-4 col-md-6">
            <div class="form-group">
                <label class="overline-title overline-title-alt">{{ trans('backend.table.from_date') }}</label>
                <div class="form-control-wrap">
                    <input type="text" value="{{ request('fromdate', $fromdate) }}" name="fromdate" class="form-control date-picker">
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="form-group">
                <label class="overline-title overline-title-alt">{{ trans('backend.table.to_date') }}</label>
                <div class="form-control-wrap">
                    <input type="text" value="{{ request('todate', $todate) }}" name="todate" class="form-control date-picker">
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6">
            <div class="form-group">
                <label class="overline-title overline-title-alt">{{ trans('backend.input.barcode_short') }}</label>
                <div class="form-control-wrap">
                    <input type="text" name="barcode" value="{{ request('barcode') }}" autocomplete="off" class="form-control">
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6">
            <div class="form-group">
                <label class="overline-title overline-title-alt">{{ trans('backend.input.warehouse') }}</label>
                <select class="form-select js-select2" name="warehouse">
                    <option value="all">{{ trans('backend.input.all_select') }}</option>
                    @foreach($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" {{ (string) request('warehouse') === (string) $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
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
                    <option value="{{ $manager->id }}" {{ (string) request('manager') === (string) $manager->id ? 'selected' : '' }}>{{ $manager->name }}</option>
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
                        <option value="{{ $client->id }}" {{ (string) request('client_id') === (string) $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        
        
        
        <div class="col-lg-4 col-md-6">
            <div class="custom-control custom-control-sm custom-checkbox">
                <input type="checkbox" class="custom-control-input" name="shipment" id="shipment" {{ request('shipment') ? 'checked' : '' }}>
                <label class="custom-control-label" for="shipment"> {{ trans('backend.index.check_shipme') }} </label>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="custom-control custom-control-sm custom-checkbox">
                <input type="checkbox" class="custom-control-input" name="finish" id="finish" {{ request('finish') ? 'checked' : '' }}>
                <label class="custom-control-label" for="finish"> {{ trans('backend.index.check_finish') }} </label>
            </div>
        </div>
        <div class="col-md-9">
            <div class="form-group">
                <button type="submit" class="btn btn-secondary btn-block">{{ trans('backend.table.filter') }}</button>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <button type="submit" formaction="{{ route('filter_excel') }}" class="btn btn-success btn-block"><em class="icon ni ni-file-xls"></em> Excel</button>
            </div>
        </div>
    </div>
</form>
