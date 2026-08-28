<table>
  <thead>
    <tr class="text-center">
      <th style="text-align: center;">Штрих код</th>
      <th style="text-align: center;">Цена</th>
      <th style="text-align: center;">Наименование</th>
    </tr>
  </thead>
  <tbody>
    @foreach($products as $item)
    <tr>
      <td style="text-align: center; width: 120px">{{ $item->first()->prodid->barcode }}</td>
      <td style="width: 120px"></td>
      <td style="text-align: left; width: 850px">{{ $item->first()->prodid->name }}</td>
    </tr>
    @endforeach
  </tbody>
</table>