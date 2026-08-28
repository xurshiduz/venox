@extends('layouts.backend')

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="components-preview mx-auto">
                    <div class="nk-block nk-block-lg">
                        <div class="card card-bordered">
                            @include('layouts.message.success')
                            @include('layouts.message.error')
                            <div class="card-inner">
                                <div class="preview-block">
                                    <form method="POST" action="{{ route('checkouts_day_print_filter') }}">
                                        @csrf
                                    <div class="row gy-1">
                                        <div class="col">
                                            <div class="form-group">
                                                <label class="form-label">{{ trans('backend.index.type_checkout') }}</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2" name="ch_tip_id" required data-search="on">
                                                        <option value="all">Все</option>
                                                        @foreach($chtypes as $chtype)
                                                        <option value="{{ $chtype->id }}">{{ $chtype->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="form-group">
                                                <label class="form-label">{{ trans('backend.input.type_pay') }}</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2" name="checkout_tip_id" required data-search="on">
                                                        <option value="all">Все</option>
                                                        @foreach($types as $type)
                                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="form-group">
                                                <label class="form-label">{{ trans('backend.table.in_select_men') }}</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2" name="manager_id" required data-search="on">
                                                        <option value="all">Все</option>
                                                        @foreach($managers as $manager)
                                                        <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="form-group">
                                                <label class="form-label">{{ trans('backend.input.select_client') }}</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2" name="client_id" required data-search="on">
                                                        <option value="all">Все</option>
                                                        @foreach($clients as $client)
                                                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="form-group">
                                                <label class="form-label">{{ trans('backend.table.from_date') }}</label>
                                                <div class="form-control-wrap">
                                                    <div class="form-icon form-icon-right">
                                                        <em class="icon ni ni-calendar-alt"></em>
                                                    </div>
                                                    <input type="text" name="fromdate" value="{{ Carbon\Carbon::now()->format('d.m.Y')}}" class="form-control date-picker" placeholder="дата">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="form-group">
                                                <label class="form-label">{{ trans('backend.table.to_date') }}</label>
                                                <div class="form-control-wrap">
                                                    <div class="form-icon form-icon-right">
                                                        <em class="icon ni ni-calendar-alt"></em>
                                                    </div>
                                                    <input type="text" name="todate" value="{{ Carbon\Carbon::now()->format('d.m.Y')}}" class="form-control date-picker" placeholder="дата">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="form-group">
                                                <label class="form-label">{{ trans('backend.table.type_print') }}</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2" name="type" required data-search="on">
                                                        <option value="pdf">{{ trans('backend.table.print_and_pdf') }}</option>
                                                        <option value="excel">EXCEL</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-12 col-sm-12 mt-4">
                                            <button class="btn btn-primary btn-block" type="submit">{{ trans('backend.table.confirm') }}</button>
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
</div>
@endsection