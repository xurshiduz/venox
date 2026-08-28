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
                                    {!! Form::open(['class' => 'form-validate is-alter']) !!}
                                    @include('layouts.message.error')
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label" for="fva-full-name">Имя</label>
                                                <div class="form-control-wrap">
                                                    <div class="form-icon form-icon-right">
                                                        <em class="icon ni ni-user-circle"></em>
                                                    </div>
                                                    {!! Form::text('name', $item ? $item->name : null, ['class' => 'form-control', 'placeholder' => 'Имя', 'required' => 'required']) !!}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="form-label" for="fva-email">Номер телефона</label>
                                                <div class="form-control-wrap">
                                                    <div class="form-icon form-icon-right">
                                                        <em class="icon ni ni-call"></em>
                                                    </div>
                                                    {!! Form::text('phone', $item ? $item->phone : null, ['class' => 'form-control', 'placeholder' => 'Номер телефона']) !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <a class="btn btn-warning btn-block btn-sm" href="#">Отменить</a>
                                        </div>
                                        <div class="col-md-6">
                                            <button type="submit" class="btn btn-sm btn-block btn-primary">{{ $item == NULL ? 'Добавить' : 'Изменить' }}</button>
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

