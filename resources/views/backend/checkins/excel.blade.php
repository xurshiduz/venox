<table>
    <tr>
      <td>Наименование</td>
      <td>Штрих-код</td>
      <td>Количество</td>
      <td>Категория</td>
      <td>Дата прихода</td>
      <td>Страна</td>
    </tr>
  @foreach($data->details()->orderBy('id', 'desc')->get() as $item)
    <tr>
      <td>{{ $item->prodid->name }}</td>
      <td>{{ $item->barcode }}</td>
      <td>{{ $item->qty }}</td>
      <td>{{ $item->prodid->catid ? $item->prodid->catid->name : NULL }}</td>
      <td>{{ $item->checkid->date }}</td>
      <td>{{ $item->prodid->contryid ? $item->prodid->contryid->name : NULL}}</td>
    </tr>
  @endforeach
</table>