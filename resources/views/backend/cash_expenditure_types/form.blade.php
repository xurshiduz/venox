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
                                  
                                   {!! Form::open() !!}
                                    
                                    <div class="row gy-3">
                                        
                                        
                                        <div class="col-lg-12 col-sm-12">
                                            <div class="form-group">
                                                <label class="form-label" for="default-01">Наименование</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" value="{{ $item ? $item->name : NULL }}" class="form-control" name="name" id="default-01"
                                                    placeholder="Пишите что нибудь ...">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <a href="{{ route('cash_expenditure_types_index') }}" class="btn btn-danger btn-block text-uppercase">Отменить</a>
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