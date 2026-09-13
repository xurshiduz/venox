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
            <th>Аввалги қарзи</th>
            <th>Товар Тўлиқ Номи ва Ҳажми</th>
            <th>Миқдори (шт/л)</th>
            <th>Сотув Нархи</th>
            <th>Завод нархи</th>
            <th>Устига қўйилган фоиз (%)</th>
            <th>Тасдиқланган прайс бўйича жами</th>
            <th>Завод нархи жами</th>
            <th>Тўланган</th>
            <th>Бонус харажатлар (Venox bonus)</th>
            <th>Қолдиқ умумий қарз</th>
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
                <td>{{ $row['markup_percent_formula'] ?? ($row['markup_percent'] !== null ? $row['markup_percent'] / 100 : null) }}</td>
                <td>{{ $row['approved_total_usd_formula'] ?? $row['approved_total_usd'] }}</td>
                <td>{{ $row['factory_total_usd_formula'] ?? $row['factory_total_usd'] }}</td>
                <td>{{ $row['paid_usd'] }}</td>
                <td>{{ $row['bonus_expense_usd'] }}</td>
                <td>{{ $row['closing_debt_usd_formula'] ?? $row['closing_debt_usd'] }}</td>
                <td>{{ $row['venox_cash_usd_formula'] ?? $row['venox_cash_usd'] }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="4" style="font-weight:bold; text-align:right;">Жами:</td>
            <td>{{ count($rows) ? '=SUM(E3:E'.(count($rows) + 2).')' : 0 }}</td>
            <td></td>
            <td>{{ count($rows) ? '=SUM(G3:G'.(count($rows) + 2).')' : 0 }}</td>
            <td></td>
            <td></td>
            <td></td>
            <td>{{ count($rows) ? '=SUM(K3:K'.(count($rows) + 2).')' : 0 }}</td>
            <td>{{ count($rows) ? '=SUM(L3:L'.(count($rows) + 2).')' : 0 }}</td>
            <td>{{ count($rows) ? '=SUM(M3:M'.(count($rows) + 2).')' : 0 }}</td>
            <td>{{ count($rows) ? '=SUM(N3:N'.(count($rows) + 2).')' : 0 }}</td>
            <td>{{ count($rows) ? '=SUM(O3:O'.(count($rows) + 2).')' : 0 }}</td>
            <td>{{ count($rows) ? '=SUM(P3:P'.(count($rows) + 2).')' : 0 }}</td>
        </tr>
    </tbody>
</table>
