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
                                    {!! Form::open(['id' => 'appointment_form']) !!}
                                    <div class="row gy-3">
                                        <div class="col-lg-4 col-sm-4">
                                            <div class="form-group">
                                                <label class="form-label">Выберите регион</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2" name="region_id" data-search="on">
                                                        @foreach($regions as $region)
                                                        <option @if($item && $item->region_id == $region->id) selected @endif value="{{ $region->id }}">{{ $region->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-4 col-sm-4">
                                            <div class="form-group">
                                                <label class="form-label" for="default-name">Наименование</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" class="form-control" id="default-name" name="name" placeholder="Наименование" value="{{ $item ? $item->name : NULL }}">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-4 col-sm-4">
                                            <div class="form-group">
                                                <label class="form-label" for="default-address">Адрес</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" class="form-control" id="default-address" name="address" placeholder="Адрес" value="{{ $item ? $item->address : NULL }}">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-4 col-sm-4">
                                            <div class="form-group">
                                                <label class="form-label" for="default-phone">Тел. номер</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" class="form-control" id="default-phone" name="phone" placeholder="Тел. номер" value="{{ $item ? $item->phone : NULL }}">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-4 col-sm-4">
                                            <div class="form-group">
                                                <label class="form-label" for="default-location">Локация (линк)</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" class="form-control" id="default-location" name="location" placeholder="Локация (линк)" value="{{ $item ? $item->location : NULL }}">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-4 col-sm-4">
                                            <div class="form-group">
                                                <label class="form-label" for="default-comment">Пимичание</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" class="form-control" name="comment" id="default-comment" placeholder="Пимичание" value="{{ $item ? $item->comment : NULL }}">
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

@section('script')
<script>
    $('#appointment_form').on('submit', function () {
       $('#register').attr('disabled', 'true'); 
    });
</script>
@endsection