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
                                @include('layouts.message.success')
                                @include('layouts.message.error')
                                    {!! Form::open(['class' => 'form-validate is-alter']) !!}
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <label class="form-label" for="website">Введите новый пароль</label>
                                            <div class="form-control-wrap">
                                                <a tabindex="-1" href="#" class="form-icon form-icon-right passcode-switch is-hidden" data-target="password">
                                                    <em class="passcode-icon icon-show icon ni ni-eye"></em>
                                                    <em class="passcode-icon icon-hide icon ni ni-eye-off"></em>
                                                </a>
                                                {!! Form::password('password', ['class' => 'form-control is-hidden', 'required' => 'required', 'id'=>'password', 'placeholder'=> 'Введите новый пароль']) !!}
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <label class="form-label" for="website">Подтвердите новый пароль</label>
                                                <input id="password-confirm" type="password" class="form-control text_fiel" placeholder="Подтвердите новый пароль" name="password_confirmation" required>
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