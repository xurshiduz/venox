<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Кунлик сотув ҳисоботи</title>
    <style>
        @page { size: A4 landscape; margin: 8mm; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #111; }
        h1 { margin: 0 0 10px; text-align: center; font-size: 16px; }
        .summary { margin-bottom: 10px; }
        .summary span { display: inline-block; margin-right: 25px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 4px; vertical-align: middle; }
        th { text-align: center; font-weight: bold; }
        td.number { text-align: right; white-space: nowrap; }
        td.center { text-align: center; }
        tfoot td { font-weight: bold; }
        @media print { .hidden-print { display: none !important; } }
    </style>
</head>
<body>
    <h1>
        КУНЛИК СОТУВ ҲИСОБОТИ:
        {{ Carbon\Carbon::parse($fromdate)->format('d.m.Y') }} — {{ Carbon\Carbon::parse($todate)->format('d.m.Y') }}
    </h1>

    <div class="summary">
        <span>Ҳужжатлар сони: <b>{{ $rows->pluck('checkout_id')->unique()->count() }}</b></span>
        <span>Менежерлар сони: <b>{{ $rows->pluck('manager')->filter()->unique()->count() }}</b></span>
        <span>Жами сотилган: <b>{{ number_format((float) $rows->sum('actual_total_usd'), 2, '.', ' ') }} USD</b></span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Товар номи</th>
                <th>Ўлчов</th>
                <th>Миқдори</th>
                <th>Завод нархи<br>(USD)</th>
                <th>Venox нархи<br>(USD)</th>
                <th>Умумий сотилган<br>(USD)</th>
                <th>Клиент</th>
                <th>Олдинги қарзи<br>(USD)</th>
                <th>Савдодан кейинги қарзи<br>(USD)</th>
                <th>Склад</th>
                <th>Менежер</th>
                <th>Тўлов тури</th>
                <th>Сана</th>
                <th>Накладной</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows->groupBy('checkout_id') as $checkoutRows)
                @foreach($checkoutRows as $row)
                    <tr>
                        <td>{{ $row['product'] }}</td>
                        <td class="center">{{ $row['unit'] }}</td>
                        <td class="number">{{ number_format((float) $row['qty'], 3, '.', ' ') }}</td>
                        <td class="number">{{ number_format((float) $row['factory_price_usd'], 2, '.', ' ') }}</td>
                        <td class="number">{{ number_format((float) $row['venox_price_usd'], 2, '.', ' ') }}</td>
                        <td class="number">{{ $row['actual_total_usd'] === null ? '—' : number_format((float) $row['actual_total_usd'], 2, '.', ' ') }}</td>
                        @if($loop->first)
                            <td rowspan="{{ $checkoutRows->count() }}">{{ $row['client'] }}</td>
                            <td rowspan="{{ $checkoutRows->count() }}" class="number">{{ number_format((float) $row['debt_before_usd'], 2, '.', ' ') }}</td>
                            <td rowspan="{{ $checkoutRows->count() }}" class="number">{{ number_format((float) $row['debt_after_usd'], 2, '.', ' ') }}</td>
                            <td rowspan="{{ $checkoutRows->count() }}" class="center">{{ $row['warehouse'] }}</td>
                            <td rowspan="{{ $checkoutRows->count() }}">{{ $row['manager'] }}</td>
                            <td rowspan="{{ $checkoutRows->count() }}">{{ $row['payment_type'] }}</td>
                            <td rowspan="{{ $checkoutRows->count() }}" class="center">{{ $row['date'] }}</td>
                            <td rowspan="{{ $checkoutRows->count() }}">{{ $row['invoice'] }}</td>
                        @endif
                    </tr>
                @endforeach
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" style="text-align: right;">Жами сотилган:</td>
                <td class="number">{{ number_format((float) $rows->sum('actual_total_usd'), 2, '.', ' ') }}</td>
                <td colspan="8"></td>
            </tr>
        </tfoot>
    </table>

    <script>window.print();</script>
</body>
</html>
