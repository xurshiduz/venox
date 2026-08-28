<table>
  <thead>
    <tr>
      <th>№</th>
      <th>Наиминования</th>
      <th>Штрихкод</th>
      <th>Ед-изм</th>
      <th>Кол-во</th>
      <th>Цена</th>
      <th>Сумма</th>
      <th>Поставщик</th>
      <th>Дата</th>
      <th>Накладной</th> <!-- Yangi sarlavha qo'shildi -->
    </tr>
  </thead>
  <tbody>
	@php($activeUserCounter = 1)
    @foreach($data as $item)
    <tr>
       <td>{{ $activeUserCounter }}</td>
       <td>{{ $item->prodid?->name }}</td>
       <td>{{ $item->prodid?->new_barcode }}</td>
       <td>{{ $item->prodid?->unitid?->name }}</td>
       <td>{{ $item->qty }}</td>
       <td>{{ $item->price }}</td>
       <td>{{ $item->price * $item->qty }}</td>
       <td>{{ $item->checkid?->supid?->name }}</td>
       <td>{{ $item->checkid?->date }}</td>
       
       <!-- Checkin stolidagi number_work maydoni -->
       <td>{{ $item->checkid?->number_work }}</td> 
    </tr>
    @php($activeUserCounter++)
    @endforeach
  </tbody>
</table>