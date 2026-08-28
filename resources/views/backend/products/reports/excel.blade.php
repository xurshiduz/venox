<table>
    <thead>
        <tr>
            <th>Наименование</th>
            <th>Штрих-код</th>
            <th>Ед.изм.</th>
            <th>Количество</th>
        </tr>
    </thead>
    <tbody>
        @foreach($mostSoldProducts as $product)
            <tr>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->barcode }}</td>
                    <td>{{ $product->unit_name }}</td>
                    <td>{{ $product->total_qty }}</td>
                </tr>
        @endforeach
    </tbody>
</table>