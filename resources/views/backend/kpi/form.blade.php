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
                                    {!! Form::open(['id' => 'appointment_form']) !!}
                                    <div class="row gy-3">
                                        <div class="col-lg-2 col-md-3 col-sm-4 d-none d-md-block">
                                            <div class="form-group">
                                                <label class="form-label">{{ trans('backend.input.select_date') }}</label>
                                                <div class="form-control-wrap">
                                                    <input type="month" name="date" value="{{ $item && $item->date ? Carbon\Carbon::parse($item->date)->format('Y-m') : Carbon\Carbon::now()->format('Y-m') }}" class="form-control" placeholder="дата">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-10 col-sm-10">
                                            <div class="form-group">
                                                <label class="form-label" for="default-02">{{ trans('backend.input.comment') }}</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" class="form-control" name="comment" id="default-02" placeholder="Пимичание">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row mt-1">
                                        <div class="col-lg-12 col-sm-12">
                                            <table class="table table-bordered">
                                              <thead>
                                                <tr class="text-center">
                                                  <th scope="col">{{ trans('backend.input.seller') }}</th>
                                                  <th scope="col">{{ trans('backend.table.plan') }}</th>
                                                </tr>
                                              </thead>
                                              <tbody>
                                                @foreach($managers as $manager)
                                                <input type="hidden" name="managers[]" value="{{ $manager->id }}">
                                                <tr class="text-center">
                                                  <td>{{ $manager->name }}</td>
                                                  <td style="padding: 0px;">
                                                      @php($psum = $item ? (App\Models\KpiPlanDetail::where('plan_id', $item->id)->where('manager_id', $manager->id)->count() ? App\Models\KpiPlanDetail::where('plan_id', $item->id)->where('manager_id', $manager->id)->first()->plan_sum : 0 ) : 0)
                                                      <input style="min-width: 110px; border: 0px;" type="text" data-id="{{ $manager->id }}" class="form-control price" name="plans[]" value="{{ number_format($psum, 0, '.', ' ') }}">
                                                  </td>
                                                </tr>
                                                @endforeach
                                              </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    
                                    <div class="row gy-3 mt-1">
                                        <div class="col-md-6">
                                            <a href="{{ route('kpi_plan_index') }}" class="btn btn-warning btn-block">Отменить</a> 
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