<table>
    <thead>
        <tr>
            <th colspan="11" style="font-weight: bold; text-align: center; font-size: 14px;">Kirim: {{ $fromdate }} — {{ $todate }}</th>
        </tr>
        <tr>
            <th style="font-weight: bold; text-align: center; background-color: #d9d9d9; border: 1px solid #000000;">№</th>
            <th style="font-weight: bold; text-align: center; background-color: #d9d9d9; border: 1px solid #000000;">Дата прихода</th>
            <th style="font-weight: bold; text-align: center; background-color: #d9d9d9; border: 1px solid #000000;">Товар</th>
            <th style="font-weight: bold; text-align: center; background-color: #d9d9d9; border: 1px solid #000000;">Штрих-код товара</th>
            <th style="font-weight: bold; text-align: center; background-color: #d9d9d9; border: 1px solid #000000;">Уникал баркод</th>
            <th style="font-weight: bold; text-align: center; background-color: #d9d9d9; border: 1px solid #000000;">Кол-во</th>
            <th style="font-weight: bold; text-align: center; background-color: #d9d9d9; border: 1px solid #000000;">Цена</th>
            <th style="font-weight: bold; text-align: center; background-color: #d9d9d9; border: 1px solid #000000;">Сумма</th>
            <th style="font-weight: bold; text-align: center; background-color: #d9d9d9; border: 1px solid #000000;">Склад</th>
            <th style="font-weight: bold; text-align: center; background-color: #d9d9d9; border: 1px solid #000000;">Поставщик</th>
            <th style="font-weight: bold; text-align: center; background-color: #d9d9d9; border: 1px solid #000000;">Документ</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $item)
        <tr>
            <td style="text-align: center; border: 1px solid #000000;">{{ $loop->iteration }}</td>
            <td style="text-align: center; border: 1px solid #000000;">{{ $item->checkid ? \Carbon\Carbon::parse($item->checkid->date)->format('d.m.Y') : '' }}</td>
            <td style="border: 1px solid #000000;">{{ optional($item->prodid)->name }}</td>
            <td style="text-align: center; border: 1px solid #000000;" data-format="@">{{ optional($item->prodid)->barcode ?: $item->product_barcode }}</td>
            <td style="text-align: center; border: 1px solid #000000;" data-format="@">{{ $item->product_barcode ?: $item->barcode }}</td>
            <td style="text-align: center; border: 1px solid #000000;">{{ (float) $item->qty }}</td>
            <td style="text-align: right; border: 1px solid #000000;" data-format="#,##0.00">{{ (float) $item->price }}</td>
            <td style="text-align: right; border: 1px solid #000000;" data-format="#,##0.00">{{ (float) $item->total_price }}</td>
            <td style="border: 1px solid #000000;">{{ optional($item->warehouseid)->name }}</td>
            <td style="border: 1px solid #000000;">{{ optional(optional($item->checkid)->supid)->name }}</td>
            <td style="border: 1px solid #000000;">{{ optional($item->checkid)->reference ?: optional($item->checkid)->code }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="5" style="font-weight: bold; text-align: right; border: 1px solid #000000;">Jami</td>
            <td style="font-weight: bold; text-align: center; border: 1px solid #000000;">{{ (float) $data->sum('qty') }}</td>
            <td style="border: 1px solid #000000;"></td>
            <td style="font-weight: bold; text-align: right; border: 1px solid #000000;" data-format="#,##0.00">{{ (float) $data->sum('total_price') }}</td>
            <td colspan="3" style="border: 1px solid #000000;"></td>
        </tr>
    </tbody>
</table>
