<table width="100%">
	<tbody>
		<tr style="text-align: center;">
			<td style="padding: 0px 5px;" width="80px">Дата</td>
			<td style="padding: 0px 5px;" width="240px">Операции</td>
			<td style="padding: 0px 5px;" colspan="2">{{ $comp }}</td>
			<td style="padding: 0px 5px;" colspan="2">{{ $client->name }}</td>
		</tr>
		<tr style="text-align: center;">
			<td style="padding: 0px 5px;"></td>
			<td style="padding: 0px 5px;"></td>
			<td style="padding: 0px 5px;" width="110px">Дебет</td>
			<td style="padding: 0px 5px;" width="90px">Кредит</td>
			<td style="padding: 0px 5px;" width="90px">Дебет</td>
			<td style="padding: 0px 5px;" width="110px">Кредит</td>
		</tr>	
		<tr style="text-align: center;">
			<td style="padding: 0px 5px; text-align: end;" colspan="2"><b>Сальдо на 01.01.24</b></td>
			<td style="padding: 0px 5px;" colspan="4"></td>
		</tr>
		@php($nak = 0)
		@php($pos = 0)
		@foreach($data as $item)
		<tr>
			<td style="padding: 0px 5px; text-align: center;">{{ $item->date }}</td>
			<td style="padding: 0px 5px;">@if($item->step) Накладная - счет фактура {{ $item->number_work }}; @else Учтена выручка Приходный кассовый ордер {{ $item->id }}; Приход:@endif</td>
			<td style="padding: 0px 5px; text-align: center;">@if($item->step) {{ number_format($item->sumtotal(), 2, '.', ' ') }} @else @endif</td>
			<td style="padding: 0px 5px; text-align: center;">@if($item->step) @else {{ number_format($item->price, 2, '.', ' ') }} @endif</td>
			<td style="padding: 0px 5px; text-align: center;">@if($item->step) @else @php($pos += $item->price) {{ number_format($item->price, 2, '.', ' ') }} @endif</td>
			<td style="padding: 0px 5px; text-align: center;">@if($item->step) @php($nak += $item->sumtotal()) {{ number_format($item->sumtotal(), 2, '.', ' ') }} @else @endif</td>
		</tr>
		@endforeach
		<tr>
			<td style="padding: 0px 5px; text-align: end;" colspan="2">Обороты за период</td>
			<td style="padding: 0px 5px; text-align: center;">{{ number_format($nak, 2, '.', ' ') }}</td>
			<td style="padding: 0px 5px; text-align: center;">{{ number_format($pos, 2, '.', ' ') }}</td>
			<td style="padding: 0px 5px; text-align: center;">{{ number_format($pos, 2, '.', ' ') }}</td>
			<td style="padding: 0px 5px; text-align: center;">{{ number_format($nak, 2, '.', ' ') }}</td>
		</tr>
		<tr>
		    @php($nak_t = $nak - $pos)
		    @php($pos_t = $pos - $nak)
			<td style="padding: 0px 5px; text-align: end;" colspan="2"><b>Сальдо на 31.12.24</b></td>
			<td style="padding: 0px 5px; text-align: center;">@if($nak_t != 0) <b>{{ $nak_t > 0 ? number_format($nak_t, 2, '.', ' ') : null }}</b>@else @endif</td>
			<td style="padding: 0px 5px; text-align: center;">@if($nak_t != 0) <b>{{ $pos_t > 0 ? number_format($pos_t, 2, '.', ' ') : null }}</b>@else @endif</td>
			<td style="padding: 0px 5px; text-align: center;">@if($nak_t != 0) <b>{{ $pos_t > 0 ? number_format($pos_t, 2, '.', ' ') : null }}</b>@else @endif</td>
			<td style="padding: 0px 5px; text-align: center;">@if($nak_t != 0) <b>{{ $nak_t > 0 ? number_format($nak_t, 2, '.', ' ') : null }}</b>@else @endif</td>
		</tr>
		<tr>
			<td style="padding: 0px 5px;" colspan="6">&nbsp;</td>
		</tr>
		<tr>
			<td style="padding: 0px 5px;" colspan="6"> @if($nak_t != 0)  В пользу {{ $nak_t > 0 ? $comp : $client->name }} {{ $nak_t > 0 ? number_format($nak_t, 2, '.', ' ') : number_format($pos_t, 2, '.', ' ') }} сум ({{ (new \MessageFormatter('ru-RU', '{n, spellout}'))->format(['n' => $nak_t > 0 ? $nak_t : $pos_t]) }} сумов 00 тийин). @endif </td>
		</tr>
	</tbody>
</table>