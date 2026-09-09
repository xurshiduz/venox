<table>
    <thead>
        <tr>
            <th colspan="12" style="font-weight:bold; font-size:14px; text-align:left;">{{ $periodLabel }} оралиғидаги мижозлар ҳисоботи (USD)</th>
        </tr>
        <tr>
            <th>Сана</th>
            <th>Мижоз номи</th>
            <th>Умумий қарзи</th>
            <th>Товар Тўлиқ Номи ва Ҳажми</th>
            <th>Миқдори (шт/л)</th>
            <th>Нархи</th>
            <th>Завод нархи</th>
            <th>Устига қўйилган фоиз (%)</th>
            <th>Жами Сумма</th>
            <th>Тўланган</th>
            <th>Қолдиқ Қарз</th>
            <th>Мижоз учун сарфланган бонус</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
            <tr>
                <td>{!! nl2br(e($row['date'])) !!}</td>
                <td>{{ $row['client'] }}</td>
                <td>{{ $row['debt_before_payment'] }}</td>
                <td>{!! nl2br(e($row['product'])) !!}</td>
                <td>{!! nl2br(e($row['qty'])) !!}</td>
                <td>{!! nl2br(e($row['unit_price_usd'])) !!}</td>
                <td>{{ $row['factory_price_usd'] ?: null }}</td>
                <td>{{ $row['markup_percent'] !== null ? $row['markup_percent'] / 100 : null }}</td>
                <td>{{ $row['total_usd'] }}</td>
                <td>{{ $row['paid_usd'] }}</td>
                <td>{{ $row['closing_debt_usd'] }}</td>
                <td>{{ $row['bonus_expense_usd'] }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="2" style="font-weight:bold; text-align:right;">Жами:</td>
            <td>{{ $totals['debt_before_payment'] }}</td>
            <td></td>
            <td>{{ $totals['qty'] }}</td>
            <td></td>
            <td></td>
            <td></td>
            <td>{{ count($rows) ? '=SUM(I3:I'.(count($rows) + 2).')' : 0 }}</td>
            <td>{{ $totals['paid_usd'] }}</td>
            <td>{{ $totals['closing_debt_usd'] }}</td>
            <td>{{ $totals['bonus_expense_usd'] }}</td>
        </tr>
    </tbody>
</table>
