<table>
	<tbody>
	    <tr>
			<td colspan="4"></td>
		</tr>
	    <tr>
			<td style="color: #6A2813" colspan="4"><b>Остатки товара по складу {{ $wareid->name }}</b></td>
		</tr>
	    <tr>
			<td style="color: #6A2813" colspan="4"><b>на конец {{ Carbon\Carbon::now()->format('d.m.y') }}г.</b></td>
		</tr>
	    <tr>
			<td colspan="4"></td>
		</tr>
		
		<tr>
		    <td style="background-color: #00FFFF; border: 1px solid #000000;" width="110px">Код</td>
			<td style="background-color: #00FFFF; border: 1px solid #000000;" width="1000px">ТМЦ</td>
		    <td style="background-color: #00FFFF; border: 1px solid #000000;">Ед. изм</td>
			<td style="background-color: #00FFFF; border: 1px solid #000000;">Остаток</td>
		</tr>
		
		@foreach(App\Models\Product::select('id', 'barcode','unit_id','name')->skip($take)->take($pag)->get() as $product)
		@php($stock = $product->checkindetails()->where('warehouse_id', $wareid->id)->where('status', 1)->sum('qty') - $product->checkoutdetails()->where('warehouse_id', $wareid->id)->where('status', 1)->sum('qty'))
		@if($stock > 0)
		<tr>
			<td style="border: 1px solid #000000;">{{ $product->barcode }} </td>
			<td style="border: 1px solid #000000;">{{ $product->name }} </td>
			<td style="border: 1px solid #000000;">{{ $product->unitid ? $product->unitid->name : null }}</td>
			<td style="border: 1px solid #000000;">{{ $stock }}</td>
		</tr>
		@endif
		@endforeach
    </tbody>
</table>