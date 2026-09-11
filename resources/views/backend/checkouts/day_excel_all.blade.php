<table>
	<tbody>
		<tr>
			<td>Наименование товаров (работ, услуг)</td>
			<td>Ед.из</td>
			<td>Кол-во</td>
			<td>Цена ({{ $targetCurrencyLabel }})</td>
			<td>Итого сумма ({{ $targetCurrencyLabel }})</td>
			<td>Клиент</td>
			<td>Склад</td>
			<td>Менеджер</td>
			<td>Тип оплаты</td>
			<td>Дата</td>
			<td>Накладной</td>
		</tr>
		<tr>
			<td>1</td>
			<td>2</td>
			<td>3</td>
			<td>4</td>
			<td>5</td>
			<td>6</td>
			<td>7</td>
			<td>8</td>
			<td>9</td>
			<td>10</td>
			<td>11</td>
		</tr>
		@php($itgsum = 0)
		@php($itgseb = 0)
		@php
			$convertAmount = function ($amount, $checkout, $detail) use ($targetCurrencyType) {
				$sourceType = (int) ($checkout->currency_type ?: ($detail->currency_type ?? $targetCurrencyType));
				$documentRate = (float) ($checkout->currency_type_price ?? 0);
				if ($documentRate <= 1) {
					$documentRate = (float) ($detail->currency_type_price ?? 0);
				}
				$date = $checkout->date ?: $checkout->created_at;
				if ($documentRate <= 1) {
					$documentRate = \App\Models\Currency::usdRateForDate($date);
				}

				if ($sourceType === $targetCurrencyType) {
					return (float) $amount;
				}

				return $targetCurrencyType === 2
					? \App\Models\Currency::toUzs((float) $amount, $sourceType, $documentRate)
					: \App\Models\Currency::documentAmountToUsd((float) $amount, $sourceType, $documentRate, $date);
			};
		@endphp
		@foreach($data as $item)
			@foreach($item->details()->get() as $detail)
			@if($checkouttip != 'all')
			@if($item->payments()->where('status', 1)->where('cash_receipt_type', $checkouttip)->count())
			@php($convertedPrice = $convertAmount($detail->price, $item, $detail))
			@php($convertedTotal = $convertAmount($detail->total_price, $item, $detail))
			@php($itgsum += $convertedTotal)
			<tr>
				<!--<td>{{$loop->iteration}}</td>-->
				<td>{{ $detail->prodid->name }}</td>
				<td>{{ $detail->prodid->unitid->name }}</td>
				<td>{{ (float) $detail->qty }}</td>
				<td>{{ $convertedPrice }}</td>
				<td>{{ $convertedTotal }}</td>
				<td>{{ $item->supid ? $item->supid->name : NULL }}</td>
				<td>{{ $detail->warehouseid ? $detail->warehouseid->num_code : NULL }}</td>
				<td>{{ $item->managerid ? $item->managerid->name : NULL }}</td>
				<td>{{ $item->payments()->where('status', 1)->where('cash_receipt_type', $checkouttip)->count() ? $item->payments()->where('status', 1)->where('cash_receipt_type', $checkouttip)->first()->tname->name : null}}</td>
				<td>{{ $item->date }}</td>
				<td>{{ $item->number_work ? $item->number_work : 'Черновик ID' . $item->id }}</td>
				
				
			</tr>
			@endif
			@else
			@php($convertedPrice = $convertAmount($detail->price, $item, $detail))
			@php($convertedTotal = $convertAmount($detail->total_price, $item, $detail))
			@php($itgsum += $convertedTotal)
			<tr>
				<td>{{ $detail->prodid->name }}</td>
				<td>{{ $detail->prodid->unitid->name }}</td>
				<td>{{ (float) $detail->qty }}</td>
				<td>{{ $convertedPrice }}</td>
				<td>{{ $convertedTotal }}</td>
				<td>{{ $item->supid ? $item->supid->name : NULL }}</td>
				<td>{{ $detail->warehouseid ? $detail->warehouseid->num_code : NULL }}</td>
				<td>{{ $item->managerid ? $item->managerid->name : NULL }}</td>
				<td>{{ $item->payments()->where('status', 1)->count() ? $item->payments()->where('status', 1)->first()->tname->name : null}}</td>
				<td>{{ $item->date }}</td>
				<td>{{ $item->number_work ? $item->number_work : 'Черновик ID' . $item->id }}</td>
			</tr>
			@endif
			@endforeach
		@endforeach
		<tr>
			<td></td>
			<td></td>
			<td></td>
			<td>Всего к оплате ({{ $targetCurrencyLabel }})</td>
			<td>{{ (float) $itgsum }}</td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
	</tbody>
</table>
