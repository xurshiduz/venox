@extends('layouts.backend')

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="components-preview mx-auto">
                    <div class="nk-block nk-block-lg">
                        <div class="row">
                            <div class="col-md-10 mb-2">
                            </div>
                            <div class="col-md-2 mb-2">
                               <a href="{{ route('kpi_plan_index') }}" class="btn btn-warning btn-block">{{ trans('backend.input.priv') }}</a> 
                            </div>
                        </div>
                        @php
                        $sum = 0;
                        $plan = $item->details()->sum('plan_sum');
                        
                        foreach(App\Models\Checkout::whereBetween('date', [Carbon\Carbon::parse($item->date)->format('Y-m' . '-01'), Carbon\Carbon::parse($item->date)->format('Y-m' . '-31')])->get() as $chek){
                            $sum += $chek->sumtotal();
                        }
                        
                        $fact = ($sum/$plan) * 100;
                        
                        @endphp
                        
                        <form action="{{ route('plan_index_show', ['id' => $item->code])}}" method="GET">
                        <div class="row">
                            <div class="col-md-10 mb-2">
                                @foreach($alltypes as $type_key => $ptype)
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input js-filter" name="type_id[]" value="{{ $ptype->id }}" id="type_{{ $type_key }}" type="checkbox"
    									{{ isset($inputs['type_id']) && in_array($ptype->id, $inputs['type_id']) ? ' checked="checked"' : '' }}>
                                        <label class="custom-control-label" for="type_{{ $type_key }}">{{ $ptype->name }}</label>   
                                </div>
                                @endforeach
                            </div>
                            <!--<div class="col-md-2 mb-2">
                               <button type="submit" class="btn btn-primary btn-block">{{ trans('backend.input.button_done') }}</button> 
                            </div>-->
                        </div>
                        </form>

                        <div class="card">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                  <thead>
                                    <tr class="text-center">
                                      <th scope="col">{{ trans('backend.table.date') }}</th>
                                      <th scope="col">{{ trans('backend.table.plan') }}</th>
                                      <th scope="col">{{ trans('backend.table.fact') }}</th>
                                      <th scope="col">{{ trans('backend.table.in_percentages') }}</th>
                                      <th scope="col">{{ trans('backend.input.comment') }}</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    <tr class="text-center">
                                      <td>{{ Carbon\Carbon::parse($item->date)->format('Y') }} @if(LaravelLocalization::getCurrentLocaleNative() == 'RU') {{ Carbon\Carbon::parse($item->date)->locale('ru_RU')->monthName }} @else {{ Carbon\Carbon::parse($item->date)->locale('uz_UZ')->monthName }} @endif</td>
                                      <td>{{ number_format($plan, 0, '.', ' ') }} {{ trans('backend.table.sum_belgi') }}</td>
                                      <td>{{ number_format($sum, 0, '.', ' ') }} {{ trans('backend.table.sum_belgi') }}</td>
                                      <td style="background-color: {{ $fact >= 100 ? '#eaffee' : ($fact >= 80 ? '#fffbea' : '#ffeaea') }}">{{ number_format($fact, 0, '.', ' ') }} %</td>
                                      <td>{{ $item->comment }}</td>
                                    </tr>
                                  </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card">
                            <div class="table-responsive">
                                
                                <table class="table table-bordered js-cars-container">
                                  <thead>
                                    <tr class="text-center">
                                      <th scope="col">{{ trans('backend.table.name') }}</th>
                                      <th scope="col">{{ trans('backend.table.plan') }}</th>
                                      <th scope="col">{{ trans('backend.table.order_sum') }}</th>
                                      @foreach($ptypes as $ptype)
                                      <th scope="col">{{ trans('backend.table.fact') }} ({{ $ptype->name }})</th>
                                      @endforeach
                                      <th scope="col">{{ trans('backend.table.in_percentages') }} ({{ trans('backend.table.pay_naqd') }})</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @foreach($item->details as $key => $ditem)
                                    
                                    @php
                                    $dsum[$key] = 0;
                                    
                                    foreach($ptypes as $pptype){
                                      $psum[$key . $pptype->id] = 0;
                                    }
                                    
                                    foreach(App\Models\Checkout::where('manager_id', $ditem->manager_id)->whereBetween('date', [Carbon\Carbon::parse($item->date)->format('Y-m' . '-01'), Carbon\Carbon::parse($item->date)->format('Y-m' . '-31')])->get() as $chek){
                                        $dsum[$key] += $chek->sumtotal();
                                        foreach($ptypes as $pptype){
                                          $psum[$key . $pptype->id] += $chek->payments()->where('cash_receipt_type', $pptype->id)->sum('price');
                                        }
                                    }
                                    $dfact[$key] = ($dsum[$key]/$ditem->plan_sum) * 100;
                                    
                                    @endphp
                        
                                    <tr class="text-center">
                                      <td>{{ $ditem->managerid->name }}</td>
                                      <td>{{ number_format($ditem->plan_sum, 0, '.', ' ') }} {{ trans('backend.table.sum_belgi') }}</td>
                                      <td>{{ number_format($dsum[$key], 0, '.', ' ') }} {{ trans('backend.table.sum_belgi') }}</td>
                                      @foreach($ptypes as $sptype)
                                      <td>{{ number_format($psum[$key . $sptype->id], 0, '.', ' ') }} {{ trans('backend.table.sum_belgi') }}</td>
                                      @endforeach
                                      
                                      <td style="background-color: {{ $dfact[$key] >= 100 ? '#eaffee' : ($dfact[$key] >= 80 ? '#fffbea' : '#ffeaea') }}">{{ number_format($dfact[$key], 0, '.', ' ') }} % </td>
                                    </tr>
                                    @endforeach
                                  </tbody>
                                </table>
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

 $(document).ready(function() {

	new Filter({
		container: '.js-cars-container',
		current_url: '{!! URL::current() !!}',
		url: '{{ route('kpi_plan_filter', ['id' => $item->code]) }}',
		trans: {
			btn_reset: 'Сбросить'
		}
	})
});
</script>
@endsection
