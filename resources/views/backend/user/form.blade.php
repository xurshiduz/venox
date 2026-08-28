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
                                    
                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    
                                    {!! Form::open() !!}
                                    
                                    <div class="row gy-2 pb-4">
                                        <div class="col-lg-4">
                                            <label class="form-label" for="name">{{ trans('backend.input.fish_input') }}</label>
                                            {!! Form::text('name', $item ? $item->name : null,
                                            [   'class' => 'form-control',
                                                'required' => 'required',
                                                'autocomplete'=>'off', 
                                                'placeholder' => 'Ф.И.О.'
                                            ]) !!}
                                        </div>
                                        <div class="col-lg-4">
                                            <label class="form-label" for="name">Тел</label>
                                            {!! Form::text('phone', $item ? $item->phone : null,
                                            [   'class' => 'form-control',
                                                'autocomplete'=>'off', 
                                                'placeholder' => 'Тел'
                                            ]) !!}
                                        </div>
                                        
                                        <div class="col-lg-4">
                                            <label  class="form-label" for="email">{{ trans('backend.input.login') }}</label>
                                            {!! Form::text('username', $item ? $item->username : null, ['class' => 'form-control', 'placeholder' => 'Логин', 'disabled' => $item ? 'true' : false, 'required' => 'required']) !!}
                                        </div>
                                        @hasanyrole('admin')
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label class="form-label">Выберите филиал</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2" name="dealer_id" data-search="on">
                                                        @foreach($dealers as $dealer)
                                                        <option @if($item && $item->dealer_id == $dealer->id) selected @endif value="{{ $dealer->id }}">{{ $dealer->name }} {{ $dealer->regionid ? '(' . $dealer->regionid->name . ')' : null }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <label class="form-label">Выберите склада</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2" name="warehouse_id" data-search="on">
                                                        <option value="0">Все</option>
                                                        @foreach($categories as $warehouse)
                                                        <option @if($item && $item->warehouse_id == $warehouse->id) selected @endif value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        @endhasanyrole
                                        
                                        <div class="col-lg-4">
                                            <label class="form-label" for="password">{{ trans('backend.input.password') }}</label>
                                            @if($item)
                                            {!! Form::password('password', ['class' => 'form-control text_fiel', 'id'=>'password', 'placeholder'=> trans('backend.input.password')]) !!}
                                            @else
                                            {!! Form::password('password', ['class' => 'form-control text_fiel', 'id'=>'password', 'placeholder'=> trans('backend.input.password'),'required']) !!}
                                            @endif
                                          
                                        </div>
                                        <div class="col-lg-4">
                                            <label class="form-label"  for="password-confirm">{{ trans('backend.input.con_password') }}</label>
                                                <input id="password-confirm" @if($item) @else required @endif type="password" class="form-control text_fiel" placeholder="{{ trans('backend.input.con_password') }}" name="password_confirmation">
                                        </div>
                                        @hasanyrole('admin')
                                        <div class="col-sm-12">
                                            <label class="form-label">Выберите право доступа</label>
                                            <div class="form-group">
                                                @foreach(App\Models\Role::where('status', 1)->get() as $country)
                                                    <div class="custom-control custom-checkbox pb-1">
                                                        <input type="checkbox" class="custom-control-input" {{ $item && $item->uroles()->where('role_id', $country->id)->count() ? 'checked' : NULL }} name="type[]" value="{{ $country->name }}" id="{{ $country->name }}">
                                                        <label class="custom-control-label" for="{{ $country->name }}">{{ $country->name_full }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        @else
                                        <div class="col-sm-12">
                                            <label class="form-label">Выберите право доступа</label>
                                            <div class="form-group">
                                                @foreach(App\Models\Role::all() as $country)
                                                    <div class="custom-control custom-checkbox pb-1">
                                                        <input type="checkbox" class="custom-control-input" {{ $item && $item->uroles()->where('role_id', $country->id)->count() ? 'checked' : NULL }} name="type[]" value="{{ $country->name }}" id="{{ $country->name }}">
                                                        <label class="custom-control-label" for="{{ $country->name }}">{{ $country->name_full }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        @endhasanyrole
                                    </div>
                                    
                                    <div class="row">
                                         <div class="col-md-6">
                                            <a href="{{ route('users_index') }}" class="btn btn-block btn-danger text-uppercase">{{ trans('backend.input.button_cancel') }}</a>
                                        </div>
                                        <div class="col-md-6">
                                            <button type="submit" class="btn btn-primary btn-block text-uppercase">{{ trans('backend.input.button_done') }}</button>
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

@section('script')
<script>
    $('#appointment_form').on('submit', function () {
       $('#register').attr('disabled', 'true'); 
    });
</script>
@endsection