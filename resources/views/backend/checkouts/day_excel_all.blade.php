<table>
    <thead>
        <tr>
            <th colspan="14" style="font-weight: bold; font-size: 14px; text-align: left;">
                {{ Carbon\Carbon::parse($fromdate)->format('d.m.Y') }} — {{ Carbon\Carbon::parse($todate)->format('d.m.Y') }} кунлик сотув ҳисоботи (USD)
            </th>
        </tr>
        <tr>
            <th>Товар номи</th>
            <th>Ўлчов бирлиги</th>
            <th>Миқдори</th>
            <th>Завод нархи (USD)</th>
            <th>Venox нархи (USD)</th>
            <th>Умумий сотилган нархи (USD)</th>
            <th>Клиент</th>
            <th>Клиентнинг олдинги қарзи (USD)</th>
            <th>Савдодан кейинги қарзи (USD)</th>
            <th>Склад</th>
            <th>Менежер</th>
            <th>Тўлов тури</th>
            <th>Сана</th>
            <th>Накладной</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
            <tr>
                <td>{{ $row['product'] }}</td>
                <td>{{ $row['unit'] }}</td>
                <td>{{ $row['qty'] }}</td>
                <td>{{ $row['factory_price_usd'] }}</td>
                <td>{{ $row['venox_price_usd'] }}</td>
                <td>{{ $row['actual_total_usd'] }}</td>
                <td>{{ $row['client'] }}</td>
                <td>{{ $row['debt_before_usd'] }}</td>
                <td>{{ $row['debt_after_usd'] }}</td>
                <td>{{ $row['warehouse'] }}</td>
                <td>{{ $row['manager'] }}</td>
                <td>{{ $row['payment_type'] }}</td>
                <td>{{ $row['date'] }}</td>
                <td>{{ $row['invoice'] }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="5" style="font-weight: bold; text-align: right;">Жами сотилган:</td>
            <td>{{ (float) $rows->sum('actual_total_usd') }}</td>
            <td colspan="8"></td>
        </tr>
    </tbody>
</table>
