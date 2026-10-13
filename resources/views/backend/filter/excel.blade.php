@php($th = 'font-weight: bold; text-align: center; background-color: #d9d9d9; border: 1px solid #000000;')
@php($td = 'border: 1px solid #000000;')
<table>
    <thead>
        <tr>
            <th colspan="15" style="font-weight: bold; text-align: center; font-size: 14px;">Приход и расход: {{ $fromdate }} — {{ $todate }}</th>
        </tr>
        <tr>
            <th style="{{ $th }}">№</th>
            <th style="{{ $th }}">Тип</th>
            <th style="{{ $th }}">Дата</th>
            <th style="{{ $th }}">Товар</th>
            <th style="{{ $th }}">Штрих-код товара</th>
            <th style="{{ $th }}">Приход</th>
            <th style="{{ $th }}">Расход</th>
            <th style="{{ $th }}">Бонус</th>
            <th style="{{ $th }}">Цена</th>
            <th style="{{ $th }}">Сумма</th>
            <th style="{{ $th }}">Валюта</th>
            <th style="{{ $th }}">Склад</th>
            <th style="{{ $th }}">Поставщик / Клиент</th>
            <th style="{{ $th }}">Менеджер</th>
            <th style="{{ $th }}">Документ</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $item)
        <tr>
            <td style="text-align: center; {{ $td }}">{{ $loop->iteration }}</td>
            <td style="text-align: center; {{ $td }} color: {{ $item->type === 'kirim' ? '#1e7e34' : '#c00000' }};">{{ $item->type === 'kirim' ? 'Приход' : 'Расход' }}</td>
            <td style="text-align: center; {{ $td }}">{{ $item->date ? \Carbon\Carbon::parse($item->date)->format('d.m.Y') : '' }}</td>
            <td style="{{ $td }}">{{ $item->product_name }}</td>
            <td style="text-align: center; {{ $td }}" data-format="@">{{ $item->product_barcode ?: $item->unique_barcode }}</td>
            <td style="text-align: center; {{ $td }}">{{ $item->type === 'kirim' ? (float) $item->qty : '' }}</td>
            <td style="text-align: center; {{ $td }}">{{ $item->type === 'chiqim' ? (float) $item->qty : '' }}</td>
            <td style="text-align: center; {{ $td }}">{{ (float) $item->bonus ?: '' }}</td>
            <td style="text-align: right; {{ $td }}" data-format="#,##0.00">{{ (float) $item->price }}</td>
            <td style="text-align: right; {{ $td }}" data-format="#,##0.00">{{ (float) $item->total_price }}</td>
            <td style="text-align: center; {{ $td }}">{{ $item->currency }}</td>
            <td style="{{ $td }}">{{ $item->warehouse_name }}</td>
            <td style="{{ $td }}">{{ $item->client_name }}</td>
            <td style="{{ $td }}">{{ $item->manager_name }}</td>
            <td style="{{ $td }}">{{ $item->number_work ?: ($item->reference ?: 'Черновик #' . $item->document_id) }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="5" style="font-weight: bold; text-align: right; {{ $td }}">Jami</td>
            <td style="font-weight: bold; text-align: center; {{ $td }}">{{ $totals['in_qty'] }}</td>
            <td style="font-weight: bold; text-align: center; {{ $td }}">{{ $totals['out_qty'] }}</td>
            <td style="font-weight: bold; text-align: center; {{ $td }}">{{ $totals['out_bonus'] ?: '' }}</td>
            <td colspan="7" style="{{ $td }}"></td>
        </tr>
        <tr>
            <td colspan="5" style="font-weight: bold; text-align: right; {{ $td }}">Разница (приход − расход − бонус)</td>
            <td colspan="3" style="font-weight: bold; text-align: center; {{ $td }}">{{ $totals['diff'] }}</td>
            <td colspan="7" style="{{ $td }}"></td>
        </tr>
    </tbody>
</table>
