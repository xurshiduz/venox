<table>
	<tbody>
		<tr>
			<td style="text-align: center;" width="80px" rowspan="2">Дата</td>
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
			<td colspan="2"><b>Сальдо на {{ Carbon\Carbon::parse($from)->format('d.m.Y') }}</b></td>
			<td colspan="4"></td>
		</tr>
		@php($nak = 0)
		@php($pos = 0)
		@foreach($data as $item)
		<tr>
			<td style="text-align: center;">{{ $item->date }}</td>
			<td>
			    @if($item->checkout_tip_id) 
			        Накладная - счет фактура {{ $item->number_work }};
			    @elseif($item->step) 
			        Поступление товаров ИД; с/ф №{{ $item->number_work }} ({{ $item->reference }});
			    @else 
			        Учтена выручка Приходный кассовый ордер {{ $item->id }}; Вид оплата: {{ $item->tname ? $item->tname->name : NULL }}
			    @endif</td>
			<td style="text-align: center;">@if($item->checkout_tip_id) {{ number_format($item->sumtotal(), 2, '.', ' ') }} @else @endif</td>
			<td style="text-align: center;">@if($item->checkout_tip_id) @elseif($item->step) {{ number_format($item->sumtotal(), 2, '.', ' ') }} @else {{ number_format($item->price, 2, '.', ' ') }} @endif</td>
			<td style="text-align: center;">@if($item->checkout_tip_id) @elseif($item->step) @php($pos += $item->sumtotal()) {{ number_format($item->sumtotal(), 2, '.', ' ') }} @else @php($pos += $item->price) {{ number_format($item->price, 2, '.', ' ') }} @endif</td>
			<td style="text-align: center;">@if($item->checkout_tip_id) @php($nak += $item->sumtotal()) {{ number_format($item->sumtotal(), 2, '.', ' ') }} @else @endif</td>
		</tr>
		@endforeach
		<tr>
			<td colspan="2">Обороты за период</td>
			<td style="text-align: center;">{{ number_format($nak, 2, '.', ' ') }}</td>
			<td style="text-align: center;">{{ number_format($pos, 2, '.', ' ') }}</td>
			<td style="text-align: center;">{{ number_format($pos, 2, '.', ' ') }}</td>
			<td style="text-align: center;">{{ number_format($nak, 2, '.', ' ') }}</td>
		</tr>
		<tr>
		    @php($nak_t = $nak - $pos)
		    @php($pos_t = $pos - $nak)
			<td colspan="2"><b>Сальдо на {{ Carbon\Carbon::parse($to)->format('d.m.Y') }}</b></td>
			<td style="text-align: center;">@if($nak_t != 0) <b>{{ $nak_t > 0 ? number_format($nak_t, 2, '.', ' ') : null }}</b>@else @endif</td>
			<td style="text-align: center;">@if($nak_t != 0) <b>{{ $pos_t > 0 ? number_format($pos_t, 2, '.', ' ') : null }}</b>@else @endif</td>
			<td style="text-align: center;">@if($nak_t != 0) <b>{{ $pos_t > 0 ? number_format($pos_t, 2, '.', ' ') : null }}</b>@else @endif</td>
			<td style="text-align: center;">@if($nak_t != 0) <b>{{ $nak_t > 0 ? number_format($nak_t, 2, '.', ' ') : null }}</b>@else @endif</td>
		</tr>
		<tr>
			<td colspan="6">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="6"> @if($nak_t != 0)  В пользу {{ $nak_t > 0 ? $comp : $client->name }} {{ $nak_t > 0 ? number_format($nak_t, 2, '.', ' ') : number_format($pos_t, 2, '.', ' ') }} сум ({{ (new \MessageFormatter('ru-RU', '{n, spellout}'))->format(['n' => $nak_t > 0 ? $nak_t : $pos_t]) }} сумов 00 тийин). @endif </td>
		</tr>
	</tbody>
</table>