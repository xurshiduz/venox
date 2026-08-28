<table>
	<tbody>
		<tr>
			<td>Наименование товаров (работ, услуг)</td>
			<td>Ед.из</td>
			<td>Кол-во</td>
			<td>Цена</td>
			<td>Итого сумма</td>
			<td>Клиент</td>
			<td>Склад</td>
			<td>Менеджер</td>
			<td>Тип оплаты</td>
			<td>Дата</td>
			<td>Накладной</td>
		</tr>
		<tr>
			<td>1</td>
			<td>2</td>
			<td>3</td>
			<td>4</td>
			<td>5</td>
			<td>6</td>
			<td>7</td>
			<td>8</td>
			<td>9</td>
			<td>10</td>
			<td>11</td>
		</tr>
		@php($itgsum = 0)
		@php($itgseb = 0)
		@foreach($data as $item)
			@foreach($item->details()->get() as $detail)
			@if($checkouttip != 'all')
			@if($item->payments()->where('status', 1)->where('cash_receipt_type', $checkouttip)->count())
			@php($itgsum += $detail->total_price)
			<tr>
				<!--<td>{{$loop->iteration}}</td>-->
				<td>{{ $detail->prodid->name }}</td>
				<td>{{ $detail->prodid->unitid->name }}</td>
				<td>{{ number_format($detail->qty, 0, '.', ' ') }}</td>
				<td>{{ number_format($detail->price, 2, '.', ' ') }}</td>
				<td>{{ number_format($detail->total_price, 2, '.', ' ') }}</td>
				<td>{{ $item->supid ? $item->supid->name : NULL }}</td>
				<td>{{ $detail->warehouseid ? $detail->warehouseid->num_code : NULL }}</td>
				<td>{{ $item->managerid ? $item->managerid->name : NULL }}</td>
				<td>{{ $item->payments()->where('status', 1)->where('cash_receipt_type', $checkouttip)->count() ? $item->payments()->where('status', 1)->where('cash_receipt_type', $checkouttip)->first()->tname->name : null}}</td>
				<td>{{ $item->date }}</td>
				<td>{{ $item->number_work ? $item->number_work : 'Черновик ID' . $item->id }}</td>
				
				
			</tr>
			@endif
			@else
			@php($itgsum += $detail->total_price)
			<tr>
				<td>{{ $detail->prodid->name }}</td>
				<td>{{ $detail->prodid->unitid->name }}</td>
				<td>{{ number_format($detail->qty, 0, '.', ' ') }}</td>
				<td>{{ number_format($detail->price, 2, '.', ' ') }}</td>
				<td>{{ number_format($detail->total_price, 2, '.', ' ') }}</td>
				<td>{{ $item->supid ? $item->supid->name : NULL }}</td>
				<td>{{ $detail->warehouseid ? $detail->warehouseid->num_code : NULL }}</td>
				<td>{{ $item->managerid ? $item->managerid->name : NULL }}</td>
				<td>{{ $item->payments()->where('status', 1)->count() ? $item->payments()->where('status', 1)->first()->tname->name : null}}</td>
				<td>{{ $item->date }}</td>
				<td>{{ $item->number_work ? $item->number_work : 'Черновик ID' . $item->id }}</td>
			</tr>
			@endif
			@endforeach
		@endforeach
		<tr>
			<td></td>
			<td>Всего к оплате</td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
	</tbody>
</table>