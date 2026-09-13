<table>
    <thead>
        <tr>
            <th colspan="16" style="font-weight:bold; font-size:14px; text-align:left;">{{ $periodLabel }} оралиғидаги мижозлар ҳисоботи (USD) — 1 USD = {{ number_format($reportUsdRate, 0, '.', ' ') }} сўм; жами тўланган = {{ number_format($totalPaidUzs, 0, '.', ' ') }} сўм</th>
        </tr>
        <tr>
            <th>Сана</th>
            <th>Мижоз номи</th>
            <th>Мижоз телефони</th>
            <th>Сотган агент</th>
            <th>Умумий қарзи</th>
            <th>Товар Тўлиқ Номи ва Ҳажми</th>
            <th>Миқдори (шт/л)</th>
            <th>Сотув Нархи</th>
            <th>Завод нархи</th>
            <th>Устига қўйилган фоиз (%)</th>
            <th>Тасдиқланган прайс бўйича жами</th>
            <th>Жами Сумма (реал сотув)</th>
            <th>Тўланган</th>
            <th>Қолдиқ Қарз</th>
            <th>Мижоз учун сарфланган бонус</th>
            <th>Venox касса</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
            <tr>
                <td>{!! nl2br(e($row['date'])) !!}</td>
                <td>{{ $row['client'] }}</td>
                <td>{{ $row['client_phone'] }}</td>
                <td>{{ $row['agent'] }}</td>
                <td>{{ $row['debt_before_payment'] }}</td>
                <td>{!! nl2br(e($row['product'])) !!}</td>
                <td>{!! nl2br(e($row['qty'])) !!}</td>
                <td>{!! nl2br(e($row['unit_price_usd'])) !!}</td>
                <td>{{ $row['factory_price_usd'] ?: null }}</td>
                <td>{{ $row['markup_percent'] !== null ? $row['markup_percent'] / 100 : null }}</td>
                <td>{{ $row['approved_total_usd'] }}</td>
                <td>{{ $row['actual_total_usd'] }}</td>
                <td>{{ $row['paid_usd'] }}</td>
                <td>{{ $row['closing_debt_usd'] }}</td>
                <td>{{ $row['bonus_expense_usd'] }}</td>
                <td>{{ $row['venox_cash_usd'] }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="4" style="font-weight:bold; text-align:right;">Жами:</td>
            <td>{{ $totals['debt_before_payment'] }}</td>
            <td></td>
            <td>{{ $totals['qty'] }}</td>
            <td></td>
            <td></td>
            <td></td>
            <td>{{ count($rows) ? '=SUM(K3:K'.(count($rows) + 2).')' : 0 }}</td>
            <td>{{ count($rows) ? '=SUM(L3:L'.(count($rows) + 2).')' : 0 }}</td>
            <td>{{ $totals['paid_usd'] }}</td>
            <td>{{ $totals['closing_debt_usd'] }}</td>
            <td>{{ $totals['bonus_expense_usd'] }}</td>
            <td>{{ count($rows) ? '=SUM(P3:P'.(count($rows) + 2).')' : 0 }}</td>
        </tr>
    </tbody>
</table>
