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
                                    {!! Form::open(['id' => 'appointment_form']) !!}
                                    <div class="row gy-3">
                                        
                                        <div class="col-lg-2 col-sm-2">
                                            <div class="form-group">
                                                <label class="form-label" for="default-price">{{ trans('backend.table.block') }}</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" name="row" id="value1" class="form-control" required placeholder="{{ trans('backend.table.block') }}">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-1">
                                            <label class="form-label" style="color: white;">.</label>
                                            <button id="register" type="submit" class="btn btn-primary btn-block">OK</button>
                                        </div>
                                    
                                    </div>
                                    {!! Form::close() !!}    
                                    <hr style="margin-top: 10px;">
                                    @if($data->count())
                                    <div class="table-responsive">
                                         <table class="table table-bordered">
                                            <tbody>
                                                @foreach($data as $detail)
                                                @if($detail->cellsall->count())
                                                <tr>
                                                    <td rowspan="{{ ($detail->cellsall->groupBy('cell')->count() + 1) }}" style="vertical-align: middle; text-align: center;">{{ $detail->row }}-блок</td>
                                                    <td>{{ trans('backend.table.block') }}</td>
                                                    <td>Ячейка</td>
                                                </tr>
                                                @foreach($detail->cellsall->groupBy('cell') as $cell)
                                                <tr>
                                                    <td>{{ $cell->first()->cell }}</td>
                                                    <td>@foreach($detail->cellsall->where('cell', $cell->first()->cell) as $ncell) {{ $ncell->cell_number }},@endforeach</td>
                                                </tr>
                                                @endforeach
                                                @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @endif
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