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
                                    <div class="row">
                                        @hasanyrole('admin')
                                        <div class="col-lg-3 col-sm-3">
                                            <div class="form-group">
                                                <label class="form-label" for="unit">Дилер (филиал)</label>
                                                <select class="form-select js-select2" name="dealer_id" required data-search="on">
                                                    @foreach($dealers as $dealer)
                                                    <option @if($item && $item->dealer_id == $dealer->id) selected @endif value="{{ $dealer->id }}">{{ $dealer->name }}</option>
                                                    @endforeach
                                                </select>
                                                    
                                            </div>
                                        </div>
                                        @endhasanyrole
                                        <div class="col-lg-3 col-sm-3">
                                            <div class="form-group">
                                                <label class="form-label" for="default-01">Наименование</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" class="form-control" id="default-01" name="name" placeholder="Наименование"  value="{{ $item ? $item->name : NULL }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-3 col-sm-3">
                                            <div class="form-group">
                                                <label class="form-label" for="director">Имя директора</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" class="form-control" id="director" name="director" placeholder="Имя директора"  value="{{ $item ? $item->director : NULL }}">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-3 col-sm-3">
                                            <div class="form-group">
                                                <label class="form-label" for="address">Адрес</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" class="form-control" id="address" name="address" placeholder="Адрес"  value="{{ $item ? $item->address : NULL }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-3 col-sm-3">
                                            <div class="form-group">
                                                <label class="form-label" for="phone">Телефон</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" class="form-control" id="phone" name="phone" placeholder="Телефон"  value="{{ $item ? $item->phone : NULL }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-3 col-sm-3">
                                            <div class="form-group">
                                                <label class="form-label" for="schet">Р/сч</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" class="form-control" id="schet" name="schet" placeholder="Р/сч"  value="{{ $item ? $item->schet : NULL }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-3 col-sm-3">
                                            <div class="form-group">
                                                <label class="form-label" for="region">Город</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" class="form-control" id="region" name="region" placeholder="Город"  value="{{ $item ? $item->region : NULL }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-3 col-sm-3">
                                            <div class="form-group">
                                                <label class="form-label" for="mfo">МФО</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" class="form-control" id="mfo" name="mfo" placeholder="МФО"  value="{{ $item ? $item->mfo : NULL }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-3 col-sm-3">
                                            <div class="form-group">
                                                <label class="form-label" for="inn">ИНН</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" class="form-control" id="inn" name="inn" placeholder="ИНН"  value="{{ $item ? $item->inn : NULL }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-3 col-sm-3">
                                            <div class="form-group">
                                                <label class="form-label" for="inn">ОКЭД</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" class="form-control" id="inn" name="inn" placeholder="ОКЭД"  value="{{ $item ? $item->inn : NULL }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-6 col-sm-6 mb-3">
                                            <div class="form-group">
                                                <label class="form-label" for="default-01">Пимичание</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" class="form-control" name="comment" id="default-01" placeholder="Пимичание"  value="{{ $item ? $item->comment : NULL }}">
                                                </div>
                                            </div>
                                        </div>


                                    </div>
                                    <div class="row gy-3">
                                        <div class="col-md-6">
                                            <a href="{{ route('warehouses_index') }}" class="btn btn-warning btn-block">Отменить</a>
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