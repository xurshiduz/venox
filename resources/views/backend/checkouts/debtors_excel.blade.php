<table>
  <thead>
    <tr class="text-center">
      <th width="100px">{{ trans('backend.table.doc_number') }}</th>
      <th width="250px">{{ trans('backend.table.client') }}</th>
      <th width="160px">{{ trans('backend.table.manager') }}</th>
      <th width="80px">{{ trans('backend.table.vid_tovar') }}</th>
      <th width="150px">{{ trans('backend.table.summa_dog') }}</th>
      <th width="150px">{{ trans('backend.table.pay_title') }}</th>
      <th width="150px">{{ trans('backend.table.data_add') }}</th>
    </tr>
  </thead>
  <tbody>
    @foreach($data as $item)
    <tr class="text-center">
       <td>
            @if($item->number_work) {{ $item->number_work }} @else Чер. #{{ $item->id }} @endif
       </td>
       <td>{{ $item->client_id ? Str::limit($item->supid->name, 30, '') : NULL }} {{ $item->checkout_tip_id == 3 ? '*' : NULL }}</td>
       <td>{{ $item->managerid ? $item->managerid->name : NULL }}</td>
       <td>{{ $item->details()->count() }} </td>
       <td>{{ number_format($item->sumtotal(), 0, '.', ' ') }}</td>
       <td>
            @foreach($item->payments()->where('status', 1)->get() as $pays)
               {{ $pays->tname ? Str::limit($pays->tname->name, 4, ''): 'tulov turi yuq' }}: {{ number_format($pays->price, 0, '.', ' ') }}
               @if(!$loop->last)
                   <br>
               @endif
            @endforeach
       </td>
      <td>{{ Carbon\Carbon::parse($item->date)->format('Y-m-d') . ' ' .  $item->created_at->format('H:i') }} </td>
    </tr>
    @endforeach
  </tbody>
</table>