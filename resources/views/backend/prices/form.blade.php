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
                                        <div class="col-lg-4 col-sm-4">
                                            <div class="form-group">
                                                <label class="form-label" for="name">Наименование</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" value="{{ $item ? $item->name : NULL}}" class="form-control" name="name" id="name" placeholder="Наименование">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-sm-4">
                                            <div class="form-group">
                                                <label class="form-label" for="1c_code">Парт номер</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" value="{{ $item ? $item->part_number : NULL }}" class="form-control" name="part_number" id="1c_code" placeholder="Парт номер">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-sm-4">
                                            <div class="form-group">
                                                <label class="form-label" for="1c_code">1C код</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" value="{{ $item ? $item->onec_code : NULL }}" class="form-control" name="onec_code" id="1c_code" placeholder="1C код">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-sm-4">
                                            <div class="form-group">
                                                <label class="form-label" for="unit">Ед. изм</label>
                                                <input list="units" class="form-control" type="text" name="unit_id" placeholder="Ед. изм" id="unit" autocomplete="off" value="{{ $item ? ( $item->unit_id ? $item->unitid->name : NULL) : NULL }}" />
                                                    <datalist id="units">
                                                        @foreach($units as $unit)
                                                        <option value="{{ $unit->name }}">{{ $unit->name }}</option>
                                                        @endforeach
                                                    </datalist>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-sm-4">
                                            <div class="form-group">
                                                <label class="form-label" for="unit">Категория</label>
                                                <input list="categories" class="form-control" type="text" name="category_id" placeholder="Категория" autocomplete="off" id="unit" value="{{ $item ? ( $item->category_id ? $item->catid->name : NULL) : NULL }}" />
                                                    <datalist id="categories">
                                                        @foreach($categories as $category)
                                                        <option value="{{ $category->name }}">{{ $category->name }}</option>
                                                        @endforeach
                                                    </datalist>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 col-sm-4">
                                            <div class="form-group">
                                                <label class="form-label" for="default-06">Изображения</label>
                                                <div class="form-control-wrap">
                                                    <div class="form-file">
                                                        <input type="file" name="file" multiple class="form-file-input" id="customFile">
                                                        <label class="form-file-label" for="customFile"></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-12 col-sm-12">
                                            <button class="btn btn-primary btn-block" type="submit">Сохранить</button>
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