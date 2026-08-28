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
                                    <form method="POST" action="{{ route('top_client_filter_post') }}">
                                        @csrf
                                    <div class="row gy-3">
                                        <div class="col-lg-2 col-md-2 col-sm-2">
                                            <div class="form-group">
                                                <label class="form-label">{{ trans('backend.table.from_date') }}</label>
                                                <div class="form-control-wrap">
                                                    <div class="form-icon form-icon-right">
                                                        <em class="icon ni ni-calendar-alt"></em>
                                                    </div>
                                                    <input type="text" name="from" value="01.01.{{ Carbon\Carbon::now()->format('Y') }}" class="form-control date-picker" placeholder="дата">
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
                                                    <input type="text" name="to" value="{{ Carbon\Carbon::now()->endOfYear()->format('d.m.Y')}}" class="form-control date-picker" placeholder="дата">
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