@php
    $sum = 0;
    $plan = $item->details()->sum('plan_sum');
    
    foreach(App\Models\Checkout::whereBetween('date', [Carbon\Carbon::parse($item->date)->format('Y-m' . '-01'), Carbon\Carbon::parse($item->date)->format('Y-m' . '-31')])->get() as $chek){
        $sum += $chek->sumtotal();
    }
    
    $fact = ($sum/$plan) * 100;

@endphp
        
<table>
	<tbody>
	    <tr>
			<td colspan="5"></td>
		</tr>
	    <tr>
			<td style="text-align: center; color: #6A2813" colspan="5"><b>KPI №{{ $item->id }} от {{ Carbon\Carbon::parse($item->date)->format('Y') }} @if(LaravelLocalization::getCurrentLocaleNative() == 'RU') {{ Carbon\Carbon::parse($item->date)->locale('ru_RU')->monthName }} @else {{ Carbon\Carbon::parse($item->date)->locale('uz_UZ')->monthName }} @endif</b></td>
		</tr>
	    <tr>
			<td colspan="5"></td>
		</tr>
		
		<tr>
			<td colspan="2" style="border-top: 1px solid #000000; border-left: 1px solid #000000;">{{ trans('backend.table.plan') }}</td>
			<td colspan="3" style="word-wrap: break-word; border-top: 1px solid #000000; border-right: 1px solid #000000; border-bottom: 1px dotted #000;"><b>{{ number_format($plan, 0, '.', ' ') }} {{ trans('backend.table.sum_belgi') }}</b></td>
		</tr>
		<tr>
			<td colspan="2" style="border-left: 1px solid #000000;">{{ trans('backend.table.fact') }}</td>
			<td colspan="3" style="word-wrap: break-word; border-right: 1px solid #000000; border-bottom: 1px dotted #000;">{{ number_format($sum, 0, '.', ' ') }} {{ trans('backend.table.sum_belgi') }}</td>
		</tr>
		<tr>
			<td colspan="2" style="border-left: 1px solid #000000;">{{ trans('backend.table.in_percentages') }}</td>
			<td colspan="3" style="word-wrap: break-word; border-right: 1px solid #000000; border-bottom: 1px dotted #000;">{{ number_format($fact, 0, '.', ' ') }} %</td>
		</tr>
		<tr>
			<td colspan="2" style="word-wrap: break-word; border-left: 1px solid #000000; border-bottom: 1px solid #000;">{{ trans('backend.input.comment') }}</td>
			<td colspan="3" style="word-wrap: break-word; border-right: 1px solid #000000; border-bottom: 1px solid #000;" >{{ $item->comment }}</td>
		</tr>
	</tbody>
</table>

<table width="100%">
	<tbody>
	    <tr class="text-center">
          <th width="120px" style="text-align: center; border: 1px solid #000000; background-color: #00FFFF; ">{{ trans('backend.table.name') }}</th>
          <th width="95px" style="text-align: center; border: 1px solid #000000; background-color: #00FFFF; ">{{ trans('backend.table.client_buy') }}</th>
          <th width="160px" style="text-align: center; border: 1px solid #000000; background-color: #00FFFF; ">{{ trans('backend.table.plan') }}</th>
          <th width="160px" style="text-align: center; border: 1px solid #000000; background-color: #00FFFF; ">{{ trans('backend.table.fact') }}</th>
          <th width="95px" style="text-align: center; border: 1px solid #000000; background-color: #00FFFF; ">{{ trans('backend.table.in_percentages') }}</th>
        </tr>
        
        @foreach($item->details as $key => $ditem)
        
        @php
        $dsum[$key] = 0;
        
        foreach(App\Models\Checkout::where('manager_id', $ditem->manager_id)->whereBetween('date', [Carbon\Carbon::parse($item->date)->format('Y-m' . '-01'), Carbon\Carbon::parse($item->date)->format('Y-m' . '-31')])->get() as $chek){
            $dsum[$key] += $chek->sumtotal();
        }
        $dfact[$key] = ($dsum[$key]/$ditem->plan_sum) * 100;
        
        $from1 = Carbon\Carbon::parse($item->date)->format('Y-m' . '-01 00:00:00');
        $to1 =  Carbon\Carbon::parse($item->date)->format('Y-m' . '-31 00:00:00');
        @endphp
        
        <tr class="text-center">
          <td style="text-align: center; border: 1px solid #000000;">{{ $ditem->managerid->name }}</td>
          <td style="text-align: center; border: 1px solid #000000;">{{ $ditem->managerid->checkouts()->whereBetween('created_at', [$from1, $to1])->count() }} {{ trans('backend.table.qty_short_t') }}</td>
          <td style="text-align: center; border: 1px solid #000000;">{{ number_format($ditem->plan_sum, 0, '.', ' ') }} {{ trans('backend.table.sum_belgi') }}</td>
          <td style="text-align: center; border: 1px solid #000000;">{{ number_format($dsum[$key], 0, '.', ' ') }} {{ trans('backend.table.sum_belgi') }}</td>
          <td style="text-align: center; border: 1px solid #000000; background-color: {{ $fact >= 100 ? '#eaffee' : ($fact >= 80 ? '#fffbea' : '#ffeaea') }}">{{ number_format($dfact[$key], 0, '.', ' ') }} % </td>
        </tr>
        @endforeach
    </tbody>
</table>