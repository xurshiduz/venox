@extends('layouts.backend')

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="components-preview mx-auto">
                    <div class="nk-block nk-block-lg">
                        <div class="card card-bordered">
                            <div class="card-inner">
                                <div class="preview-block">
                                    <form method="POST" action="{{ route('checkins_sverka_excel') }}">
                                    @csrf
                                    <div class="row gy-3">
                                        <div class="col-lg-2 col-md-2 col-sm-2">
                                            <div class="form-group">
                                                <label class="form-label">{{ trans('backend.table.from_date') }}</label>
                                                <div class="form-control-wrap">
                                                    <div class="form-icon form-icon-right">
                                                        <em class="icon ni ni-calendar-alt"></em>
                                                    </div>
                                                    <input type="text" name="fromdate" value="{{ Carbon\Carbon::now()->startOfMonth()->format('d.m.Y') }}" class="form-control date-picker" placeholder="дата">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-2 col-md-2 col-sm-2">
                                            <div class="form-group">
                                                <label class="form-label">{{ trans('backend.table.to_date') }}</label>
                                                <div class="form-control-wrap">
                                                    <div class="form-icon form-icon-right">
                                                        <em class="icon ni ni-calendar-alt"></em>
                                                    </div>
                                                    <input type="text" name="todate" value="{{ Carbon\Carbon::now()->endOfMonth()->format('d.m.Y')}}" class="form-control date-picker" placeholder="дата">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-3 col-sm-3">
                                            <div class="form-group">
                                                <label class="form-label">{{ trans('backend.table.type_print') }}</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2" name="type" required data-search="on">
                                                        <!--<option value="pdf">{{ trans('backend.table.print_and_pdf') }}</option>-->
                                                        <option value="excel">EXCEL</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-2 col-sm-2">
                                            <label class="form-label">&nbsp;</label>
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