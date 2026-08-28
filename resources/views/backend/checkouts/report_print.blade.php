<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="description" content="«Toshkent-Agro-Traktor» Mas՚uliyati cheklangan jamiyati">
		<title>Спецификация №</title>
		<link rel="stylesheet" type="text/css" href="/css/sheets-of-paper-a4.css">
		<link rel="preconnect" href="http://fonts.googleapis.com">
		<link rel="preconnect" href="http://fonts.gstatic.com" crossorigin>
		<link href="http://fonts.googleapis.com/css2?family=Tinos:ital@0;1&display=swap" rel="stylesheet">
		
		<style type="text/css">
			@media print {
			  .hidden-print {
			    display: none !important;
			  }
			}
			.wordpdf p {
				text-indent: 40px;
				margin-top: 0px;
				text-align: justify;
				font-family: 'Tinos', serif;
			
			}
			
			ul li {
				line-height:18px;
				font-family: 'Tinos', serif;
				padding-left: 20px;

			}
			.page {
				font-family: 'Tinos', serif;
			}
			table, th, td {
			  border: 1px solid black;
			  border-collapse: collapse;
			  padding: 4px;
			  font-family: 'Tinos', serif;

			}
			.zakaz-cars > tbody > tr > td {
			  font-size: 14px;
			  text-align: center;
			  font-family: 'Tinos', serif;
			}
			.zakaz-cars > tfoot > tr > td {
			  font-size: 12px;
			  text-align: center;
			  font-family: 'Tinos', serif;
			}
			.title-order {
				text-align: center;
				font-size: 14px;
				font-family: 'Tinos', serif;
				font-weight: bold;
			}
			.title-order >span{
				font-weight: normal;
				font-family: 'Tinos', serif;
			}
			
			.zakaz-cars > thead > tr > th {
			  font-size: 14px;
			  text-align: center;
			  font-family: 'Tinos', serif;
			}
			
		</style>
	</head>

	<body class="document" style="font-size: 12px">
	    <?php \Carbon\Carbon::setLocale('ru') ?> 
		<div class="page" contenteditable="true">
            <br>
			<h1 style="margin: 0px" class="title-order">СЧЕТ-ФАКТУРА-НАКЛАДНАЯ №{{ $item->number_work }} от {{ Carbon\Carbon::parse($item->date)->format('d.m.y') }}г.</h1>
			<p style="font-weight: bold; margin: 0px; text-align: center;">к товарно-отгрузочные документам:</p>
			<br>
		<div class="wordpdf">
				<div style="display: flex;">
					<table width="50%" style="margin: 0 10px;">
						<tbody>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">Поставщик</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%"><b>{{ $comp->where('atribute', 'comp_name')->first()->value }}</b></td>
							</tr>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">Адрес</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%">{{ $comp->where('atribute', 'address_ru')->first()->value }}</td>
							</tr>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">Телефон</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%">{{ $comp->where('atribute', 'comp_phone')->first()->value }}</td>
							</tr>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">Р/сч</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%">{{ $comp->where('atribute', 'comp_schet')->first()->value }}</td>
							</tr>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">Банк</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%">{{ $comp->where('atribute', 'comp_bank')->first()->value }}</td>
							</tr>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">МФО</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%">{{ $comp->where('atribute', 'comp_mfo')->first()->value }}</td>
							</tr>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">ИНН</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%">{{ $comp->where('atribute', 'comp_inn')->first()->value }}</td>
							</tr>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">ОКЭД</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%">{{ $comp->where('atribute', 'comp_oked')->first()->value }}</td>
							</tr>
						</tbody>
					</table>
				
					<table class="table" width="50%" style="margin: 0 10px;">
						<tbody>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">Получатель</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%"><b>{{ $item->supid->name }}</b></td>
							</tr>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">Адрес</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%">{{ $item->supid->address }}</td>
							</tr>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">Телефон</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%">{{ $item->supid->phone }}</td>
							</tr>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">Р/сч</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%">{{ $item->supid->schet }}</td>
							</tr>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">Банк</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%"></td>
							</tr>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">МФО</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%">{{ $item->supid->mfo }}</td>
							</tr>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">ИНН</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%">{{ $item->supid->inn }}</td>
							</tr>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">ОКЭД</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%">{{ $item->supid->oked }}</td>
							</tr>
						</tbody>
					</table>
				</div>
				<div style="margin: 10px">
				<table width="100%">
					<tbody>
						<tr style="text-align: center;">
							<!--<td style="padding: 0px 5px;">№ п/п</td>-->
							<td style="padding: 0px 5px; width: 250px">Наименование товаров (работ, услуг)</td>
							<td style="padding: 0px 5px;">Ед. изм</td>
							<td style="padding: 0px 5px;">Кол-во</td>
							<td style="padding: 0px 5px;">Цена</td>
							<td style="padding: 0px 5px;">Себестоимость</td>
							<td style="padding: 0px 5px;">Разница</td>
							<td style="padding: 0px 5px;">Итого сумма</td>
							<td style="padding: 0px 5px;">Итого себ-сть</td>
							<td style="padding: 0px 5px;">Итого разница</td>
						</tr>
						<tr style="text-align: center;">
							<!--<td style="padding: 0px 5px;"></td>-->
							<td style="padding: 0px 5px;">1</td>
							<td style="padding: 0px 5px;">2</td>
							<td style="padding: 0px 5px;">3</td>
							<td style="padding: 0px 5px;">4</td>
							<td style="padding: 0px 5px;">5</td>
							<td style="padding: 0px 5px;">6</td>
							<td style="padding: 0px 5px;">7</td>
							<td style="padding: 0px 5px;">8</td>
						</tr>
						
						@foreach($item->details()->get() as $detail)
						<tr>
							<!--<td style="padding: 0px 5px;">{{$loop->iteration}}</td>-->
							<td style="padding: 0px 5px;">{{ $detail->prodid->name }}</td>
							<td style="padding: 0px 5px; text-align: center;">{{ $detail->prodid->unit_id ? $detail->prodid->unitid->name : NULL }}</td>
							<td style="padding: 0px 5px; text-align: center;">{{ number_format($detail->qty, 2, '.', ' ') }}</td>
							<td style="padding: 0px 5px; text-align: center;">{{ number_format($detail->price, 2, '.', ' ') }}</td>
							<td style="padding: 0px 5px; text-align: center;">{{ $detail->tan_price && $detail->tan_price != 0 ? number_format($detail->tan_price, 2, '.', ' '): null }}</td>
							<td style="padding: 0px 5px;">{{ $detail->tan_price && $detail->tan_price != 0 ? number_format($detail->price - $detail->tan_price, 2, '.', ' ') : null}}</td>
							<td style="padding: 0px 5px; text-align: center;">{{ number_format($detail->total_price, 2, '.', ' ') }}</td>
							<td style="padding: 0px 5px; text-align: center;">{{ $detail->tan_price && $detail->tan_price != 0 ? number_format($detail->tan_price * $detail->qty, 2, '.', ' '): null }}</td>
							<td style="padding: 0px 5px;">{{ $detail->tan_price && $detail->tan_price != 0 ? number_format($detail->total_price - ($detail->tan_price * $detail->qty), 2, '.', ' ') : null}}</td>
						</tr>
						@endforeach
						<tr>
							<!--<td style="padding: 0px 5px;"></td>-->
							<td style="padding: 0px 5px;">Всего к оплате</td>
							<td style="padding: 0px 5px;"></td>
							<td style="padding: 0px 5px;"></td>
							<td style="padding: 0px 5px;"></td>
							<td style="padding: 0px 5px;"></td>
							<td style="padding: 0px 5px;"></td>
							<td style="padding: 0px 5px; text-align: center;">{{ number_format($item->details()->sum('total_price'), 2, '.', ' ') }}</td>
							<td style="padding: 0px 5px;"></td>
							<td style="padding: 0px 5px;"></td>
						</tr>
					</tbody>
				</table>
				<table style="border: 0px; width: 100%;">
                <tbody>
                    <tr>
                        <td style="border: 0px; padding: 5px 0px;">Всего отпущено на сумму: 
                        @if($view == 'short')
                        {{ (new \MessageFormatter('ru-RU', '{n, spellout}'))->format(['n' => $item->details()->sum('qty')]) }} 
                        @else
                        {{ (new \MessageFormatter('ru-RU', '{n, spellout}'))->format(['n' => $item->details()->sum('total_price')]) }} 
                        @endif
                        сумов 00 тийин.  Без НДС.</td>
                    </tr>
                </tbody>
            </table>
            <br>
            
            <table style="border: 0px; width: 100%;">
                <tbody>
                    <tr>
                        <td style="border: 0px; padding: 5px 0px;" width="50%">Руководитель: ____________________</td>
                        <td style="border: 0px; padding: 5px 0px;" width="50%">Получил ____________________</td>
                    </tr>
                    <tr>
                        <td style="border: 0px; padding: 5px 0px;" width="50%">Главный бухгалтер: ____________________</td>
                        <td style="border: 0px; padding: 5px 0px;" width="50%">Доверенность ____________________</td>
                    </tr>
                    <tr>
                        <td style="border: 0px; padding: 5px 0px;" width="50%"><b>М.П.</b></td>
                        <td style="border: 0px; padding: 5px 0px;" width="50%">ч/з ____________________</td>
                    </tr>
                    <tr>
                        <td style="border: 0px; padding: 5px 0px;" width="50%">Товар отпустил ____________________</td>
                        <td style="border: 0px; padding: 0px; vertical-align: text-bottom;" width="50%">&nbsp; &nbsp; &nbsp; &nbsp;ФИО получателя</td>
                    </tr>
                </tbody>
            </table>
			</div>
		</div>

		</div>
		<script type="text/javascript">
		window.print();
		</script>
	</body>
</html>
