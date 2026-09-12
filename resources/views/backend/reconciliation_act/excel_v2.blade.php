<table>
    <tbody>
        <tr>
            <td style="text-align: center;" width="80px" rowspan="2">Дата</td>
            <td style="text-align: center;" width="145px" rowspan="2">Тип операции</td>
            <td style="text-align: center;" width="500px" rowspan="2">Операции</td>
            <td style="text-align: center;" colspan="2">{{ $comp }}</td>
            <td style="text-align: center;" colspan="2">{{ $client->name }}</td>
        </tr>
        <tr>
            <td style="text-align: center;" width="95px">Дебет</td>
            <td style="text-align: center;" width="95px">Кредит</td>
            <td style="text-align: center;" width="95px">Дебет</td>
            <td style="text-align: center;" width="95px">Кредит</td>
        </tr>
        <tr>
            <td colspan="3"><b>Сальдо на {{ Carbon\Carbon::parse($from)->format('d.m.Y') }}</b></td>
            <td style="text-align: center;"><b>{{ $startSaldo > 0 ? number_format($startSaldo, 2, '.', ' ') : '' }}</b></td>
            <td style="text-align: center;"><b>{{ $startSaldo < 0 ? number_format(abs($startSaldo), 2, '.', ' ') : '' }}</b></td>
            <td style="text-align: center;"><b>{{ $startSaldo < 0 ? number_format(abs($startSaldo), 2, '.', ' ') : '' }}</b></td>
            <td style="text-align: center;"><b>{{ $startSaldo > 0 ? number_format($startSaldo, 2, '.', ' ') : '' }}</b></td>
        </tr>
        <?php
        $nak = 0;
        $pos = 0;
        foreach ($data as $item) {
            $isReturnedCheckout = $item instanceof \App\Models\Checkout && (int) $item->checkout_tip_id === 2;
            $cashExpenditureName = $item instanceof \App\Models\CashExpenditure
                ? optional($item->cename)->name
                : null;
            $companyDebit = null;
            $companyCredit = null;
            $clientDebit = null;
            $clientCredit = null;

            if ($item instanceof \App\Models\Checkout) {
                $companyDebit = $item->sumtotal();
                $clientCredit = $companyDebit;
                $nak += $companyDebit;
            } elseif ($item instanceof \App\Models\Checkin) {
                $companyCredit = $item->sumtotal();
                $clientDebit = $companyCredit;
                $pos += $companyCredit;
            } elseif ($item instanceof \App\Models\CashExpenditure) {
                $companyDebit = $item->price;
                $clientCredit = $companyDebit;
                $nak += $companyDebit;
            } elseif ($item instanceof \App\Models\CashReceipt) {
                $companyCredit = $item->price;
                $clientDebit = $companyCredit;
                $pos += $companyCredit;
            }
        ?>
        <tr>
            <td style="text-align: center;">{{ $item->date }}</td>
            <td style="text-align: center; font-weight: bold;">
                @if($item instanceof \App\Models\Checkout)
                    {{ $isReturnedCheckout ? 'Возврат товара' : (optional($item->checktypeid)->name ?: 'Продажа') }}
                @elseif($item instanceof \App\Models\Checkin)
                    Возврат товара
                @elseif($item instanceof \App\Models\CashExpenditure)
                    {{ $cashExpenditureName ?: 'Выдача денежных средств' }}
                @else
                    Оплата
                @endif
            </td>
            <td>
                @if($item instanceof \App\Models\Checkout)
                    {{ $isReturnedCheckout ? 'Возврат товара; накладная' : 'Накладная - счет фактура' }} №{{ $item->number_work }};
                @elseif($item instanceof \App\Models\Checkin)
                    Возврат товара ИД; с/ф №{{ $item->number_work }} ({{ $item->reference }});
                @elseif($item instanceof \App\Models\CashExpenditure)
                    Расходный кассовый ордер №{{ $item->id }}; {{ $cashExpenditureName ?: 'Выдача денежных средств' }}
                @else
                    Учтена выручка Приходный кассовый ордер {{ $item->id }}; Вид оплата: {{ $item->tname ? $item->tname->name : null }}
                @endif
            </td>
            <td style="text-align: center;">{{ $companyDebit !== null ? number_format($companyDebit, 2, '.', ' ') : '' }}</td>
            <td style="text-align: center;">{{ $companyCredit !== null ? number_format($companyCredit, 2, '.', ' ') : '' }}</td>
            <td style="text-align: center;">{{ $clientDebit !== null ? number_format($clientDebit, 2, '.', ' ') : '' }}</td>
            <td style="text-align: center;">{{ $clientCredit !== null ? number_format($clientCredit, 2, '.', ' ') : '' }}</td>
        </tr>
        <?php } ?>
        <tr>
            <td colspan="3">Обороты за период</td>
            <td style="text-align: center;">{{ number_format($nak, 2, '.', ' ') }}</td>
            <td style="text-align: center;">{{ number_format($pos, 2, '.', ' ') }}</td>
            <td style="text-align: center;">{{ number_format($pos, 2, '.', ' ') }}</td>
            <td style="text-align: center;">{{ number_format($nak, 2, '.', ' ') }}</td>
        </tr>
        <?php $endSaldo = $startSaldo + $nak - $pos; ?>
        <tr>
            <td colspan="3"><b>Сальдо на {{ Carbon\Carbon::parse($to)->format('d.m.Y') }}</b></td>
            <td style="text-align: center;"><b>{{ $endSaldo > 0 ? number_format($endSaldo, 2, '.', ' ') : '' }}</b></td>
            <td style="text-align: center;"><b>{{ $endSaldo < 0 ? number_format(abs($endSaldo), 2, '.', ' ') : '' }}</b></td>
            <td style="text-align: center;"><b>{{ $endSaldo < 0 ? number_format(abs($endSaldo), 2, '.', ' ') : '' }}</b></td>
            <td style="text-align: center;"><b>{{ $endSaldo > 0 ? number_format($endSaldo, 2, '.', ' ') : '' }}</b></td>
        </tr>
        <tr><td colspan="7">&nbsp;</td></tr>
        <tr>
            <td colspan="7">
                @if($endSaldo != 0)
                    В пользу {{ $endSaldo > 0 ? $comp : $client->name }} {{ number_format(abs($endSaldo), 2, '.', ' ') }} сум ({{ (new \MessageFormatter('ru-RU', '{n, spellout}'))->format(['n' => abs($endSaldo)]) }} сумов 00 тийин).
                @else
                    Сальдо ноль.
                @endif
            </td>
        </tr>
    </tbody>
</table>
