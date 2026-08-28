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
                                    <form method="POST" action="{{ route('checkouts_month_filter_post') }}">
                                        @csrf
                                        <div class="row gy-4">
                                            <div class="col-sm-6 col-md-4">
                                                <div class="form-group">
                                                    <label class="form-label">Yil va Oyni tanlang</label>
                                                    <div class="form-control-wrap">
                                                        <input type="month" name="month_year" class="form-control" required value="{{ date('Y-m') }}">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-12 mt-4">
                                                <button class="btn btn-primary" type="submit">
                                                    <em class="icon ni ni-file-xls"></em> 
                                                    <span>Excel yuklab olish</span>
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