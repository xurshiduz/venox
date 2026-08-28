<table width="100%" style="font-size: 9px">
	<tbody>
		<tr style="text-align: center;">
			<td style="padding: 0px 5px;">Наименование товаров (работ, услуг)</td>
			<td style="padding: 0px 5px;">Кол-во</td>
			<td style="padding: 0px 5px;">Продажная цена</td>
			<td style="padding: 0px 5px;">Итого по продажная цена</td>
			<td style="padding: 0px 5px;">Себестоимость</td>
			<td style="padding: 0px 5px;">Итого по себестоимость</td>
			<td style="padding: 0px 5px;">Штрих</td>
			<td style="padding: 0px 5px;">Склад</td>
			<td style="padding: 0px 5px; width:50px">Ячейка</td>
		</tr>
		@foreach($data as $item)
		@if($item->prodid)
			<tr>
				<td style="padding: 0px 5px;">{{ $item->prodid ? $item->prodid->name : null }}</td>
				<td style="padding: 0px 5px;">{{ $item->qty }}</td>
				<td style="padding: 0px 5px;">{{ $item->price }}</td>
				<td style="padding: 0px 5px;">{{ $item->qty_price }}</td>
				<td style="padding: 0px 5px;">{{ $item->tan_price }}</td>
				<td style="padding: 0px 5px;">{{ $item->qty_tan_price }}</td>
				<td style="padding: 0px 5px;">{{ $item->barcode }}</td>
				<td style="padding: 0px 5px;">{{ $item->wareid->name }}</td>
				<td style="padding: 0px 5px;">{{ $item->yach }}</td>
			</tr>
		@endif
		@endforeach
	</tbody>
</table>