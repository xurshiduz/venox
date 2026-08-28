@extends('layouts.backend')

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="components-preview mx-auto">
                    <div class="nk-block nk-block-lg">
                        <div class="card card-bordered card-preview">
                            <div class="card-inner">
                                <div class="preview-block">
                                   
                                    {!! Form::open(['files' => true]) !!}
                                   
                                    <div class="row gy-3">
                                        
                                        <div class="col-lg-6 col-sm-6">
                                            <div class="form-group">
                                                <label class="form-label" for="default-01">Наименования валют</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" required class="form-control text-uppercase" id="default-01" name="name" placeholder="Наименования валют" value="{{ $item ? $item->name : NULL }}">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-6 col-sm-6">
                                            <div class="form-group">
                                                <label class="form-label" for="default-02">Знак (USD, EUR....)</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" required class="form-control" name="belgi" id="default-02" placeholder="Знак (USD, EUR....)" 
                                                    value="{{ $item ? $item->belgi : NULL }}">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <a href="{{ route('currencies_index')}}" class="btn btn-danger btn-block text-uppercase">Отменить</a>
                                        </div>
                                        <div class="col-md-6">
                                            <button type="submit" class="btn btn-primary btn-block text-uppercase">Сохранить</button>
                                        </div>
                                    </div>
                                    {!! Form::close() !!}
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