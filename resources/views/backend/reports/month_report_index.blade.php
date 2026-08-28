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
                                    <form method="GET" action="{{ route('dashboard_print') }}">
                                        
                                        <div class="row gy-3">
                                    
                                            <div class="col-lg-3 col-md-3 col-sm-3">
                                                <div class="form-group">
                                                    <label class="form-label">Year / Month</label>
                                                    <input 
                                                        type="month" 
                                                        name="year_month" 
                                                        value="{{ date('Y-m') }}" 
                                                        class="form-control">
                                                </div>
                                            </div>
                                    
                                            <div class="col-lg-2 col-sm-2">
                                                <label class="form-label">&nbsp;</label>
                                                <button class="btn btn-primary btn-block" type="submit">
                                                    {{ trans('backend.table.confirm') }}
                                                </button>
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