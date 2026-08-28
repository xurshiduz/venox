@extends('layouts.backend')

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="components-preview mx-auto">
                    <div class="nk-block nk-block-lg">
                        <div class="card card-bordered card-preview">
                            @include('layouts.message.success')
                            @include('layouts.message.error')
                            <div class="card-inner">
                                <div class="preview-block">
                                    {!! Form::open() !!}
                                    <div class="row gy-3">
                                        <div class="col-lg-6 col-sm-6">
                                            <div class="form-group">
                                                <label class="form-label" for="default-01">Атрибут</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" class="form-control" id="default-01" name="atribute" placeholder="Атрибут" {{ $item ? 'disabled' : NULL }} value="{{ $item ? $item->atribute : NULL }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-sm-6">
                                            <div class="form-group">
                                                <label class="form-label" for="default-02">Значение</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" class="form-control" name="value" id="default-02" placeholder="Значение" value="{{ $item ? $item->value : NULL }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <a href="#" class="btn btn-warning btn-block">Отменить</a>
                                        </div>
                                        <div class="col-md-6">
                                            <button id="register" type="submit" class="btn btn-primary btn-block">Сохранить</button>
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