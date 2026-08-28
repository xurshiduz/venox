<thead>
  <tr class="text-center">
    <th scope="col">{{ trans('backend.table.name') }}</th>
    <th scope="col">{{ trans('backend.table.plan') }}</th>
    <th scope="col">{{ trans('backend.table.order_sum') }}</th>
    @foreach($cars as $ptype)
    <th scope="col">{{ trans('backend.table.fact') }} ({{ $ptype->name }})</th>
    @endforeach
    <th scope="col">{{ trans('backend.table.in_percentages') }} ({{ trans('backend.table.pay_naqd') }})</th>
  </tr>
</thead>
<tbody>
  @foreach($item->details as $key => $ditem)
  
  @php
  $dsum[$key] = 0;
  
  foreach($cars as $pptype){
    $psum[$key . $pptype->id] = 0;
  }
  
  foreach(App\Models\Checkout::where('manager_id', $ditem->manager_id)->whereBetween('date', [Carbon\Carbon::parse($item->date)->format('Y-m' . '-01'), Carbon\Carbon::parse($item->date)->format('Y-m' . '-31')])->get() as $chek){
      $dsum[$key] += $chek->sumtotal();
      foreach($cars as $pptype){
        $psum[$key . $pptype->id] += $chek->payments()->where('cash_receipt_type', $pptype->id)->sum('price');
      }
  }
  $dfact[$key] = ($dsum[$key]/$ditem->plan_sum) * 100;
  
  @endphp

  <tr class="text-center">
    <td>{{ $ditem->managerid->name }}</td>
    <td>{{ number_format($ditem->plan_sum, 0, '.', ' ') }} {{ trans('backend.table.sum_belgi') }}</td>
    <td>{{ number_format($dsum[$key], 0, '.', ' ') }} {{ trans('backend.table.sum_belgi') }}</td>
    @foreach($cars as $sptype)
    <td>{{ number_format($psum[$key . $sptype->id], 0, '.', ' ') }} {{ trans('backend.table.sum_belgi') }}</td>
    @endforeach
    
    <td style="background-color: {{ $dfact[$key] >= 100 ? '#eaffee' : ($dfact[$key] >= 80 ? '#fffbea' : '#ffeaea') }}">{{ number_format($dfact[$key], 0, '.', ' ') }} % </td>
  </tr>
  @endforeach
</tbody>