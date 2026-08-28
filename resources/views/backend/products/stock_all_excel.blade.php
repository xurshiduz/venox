<table>
  <thead>
    <tr>
      <th>Наименование</th>
      <th>Штрих-код товара</th>
      <th>Остатка</th>
    </tr>
  </thead>
  <tbody>
    @foreach($data as $item)
    <tr>
      <td>{{ $item->first()->productid->name }}</td>
      <td>{{ $item->first()->productid->barcode }}</td>
      <td>{{ $item->where('stock', '>', 0)->sum('stock') }} {{ $item->first()->productid->unitid ? $item->first()->productid->unitid->name : null}}</td>
    </tr>
    @endforeach
  </tbody>
</table>