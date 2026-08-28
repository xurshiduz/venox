<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="description" content="«Toshkent-Agro-Traktor» Mas՚uliyati cheklangan jamiyati">
		<title>Продажа КПИ</title>
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
			<h1 style="margin: 0px" class="title-order">ПРОДАЖА от {{ Carbon\Carbon::parse($fromdate)->format('d.m.y') }}г. до {{ Carbon\Carbon::parse($todate)->format('d.m.y') }}г. </h1>
			<p style="font-weight: bold; margin: 0px; text-align: center;"></p>
			<br>
		<div class="wordpdf">
				<div style="display: flex;">
					@php($sitgsum = 0)
				    @foreach($data as $item)
    					@foreach($item->details()->get() as $detail)
        					@if($checkouttip != 'all')
            					@if($item->payments()->where('status', 1)->where('cash_receipt_type', $checkouttip)->count())
            					    @php($sitgsum += $detail->total_price)
            					@endif
        					@else
        					    @php($sitgsum += $detail->total_price)
        					@endif
    					@endforeach
					@endforeach
						
					<table width="50%" style="margin: 0 10px;">
						<tbody>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">Количество договоров</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%">{{ $data->count() }}</td>
							</tr>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">Количество менеджеров</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%">{{ $data->groupBy('manager_id')->count() }}</td>
							</tr>
						</tbody>
					</table>
				
					<table width="50%" style="margin: 0 10px;">
						<tbody>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">Итого сумма</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%"><b>{{ $sitgsum ? number_format($sitgsum, 2, '.', ' ') : null }}</b></td>
							</tr>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">Вид продуктов</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%"></td>
							</tr>
						</tbody>
					</table>
				</div>
				<div style="margin: 10px">
				<table width="100%">
					<tbody>
						<tr style="text-align: center;">
							<!--<td style="padding: 0px 5px;">№ п/п</td>-->
							<td style="padding: 0px 5px; width: 280px">Наименование товаров (работ, услуг)</td>
							<td style="padding: 0px 5px;">Ед.из</td>
							<td style="padding: 0px 5px;">Кол-во</td>
							<td style="padding: 0px 5px;">Тан нарх</td>
							<td style="padding: 0px 5px;">Цена</td>
							<td style="padding: 0px 5px;">Итого сумма</td>
							<td style="padding: 0px 5px;">Клиент</td>
							<td style="padding: 0px 5px;">Склад</td>
							<td style="padding: 0px 5px;">Менеджер</td>
							<td style="padding: 0px 5px;">Тип оплаты</td>
							<!--<td style="padding: 0px 5px;">Тип продаж</td>-->
						</tr>
						<tr style="text-align: center;">
							<!--<td style="padding: 0px 5px;"></td>-->
							<td style="padding: 0px 5px;">1</td>
							<td style="padding: 0px 5px;">2</td>
							<td style="padding: 0px 5px;">3</td>
							<td style="padding: 0px 5px;">4</td>
							<td style="padding: 0px 5px;">4</td>
							<td style="padding: 0px 5px;">5</td>
							<td style="padding: 0px 5px;">6</td>
							<td style="padding: 0px 5px;">7</td>
							<td style="padding: 0px 5px;">8</td>
							<td style="padding: 0px 5px;">9</td>
							<!--<td style="padding: 0px 5px;">10</td>-->
						</tr>
						@php($itgsum = 0)
						@php($itgseb = 0)
						@foreach($data as $item)
    						@foreach($item->details()->get() as $detail)
    						@if($checkouttip != 'all')
        						@if($item->payments()->where('status', 1)->where('cash_receipt_type', $checkouttip)->count())
        						
        						<tr>
        							<!--<td style="padding: 0px 5px;">{{$loop->iteration}}</td>-->
        							<td style="padding: 0px 5px;">{{ $detail->prodid->name }}</td>
        							<td style="padding: 0px 5px;">{{ $detail->prodid->unitid->name }}</td>
        							<td style="padding: 0px 5px; text-align: center;">{{ number_format($detail->qty, 0, '.', ' ') }}</td>
        							<td style="padding: 0px 5px; text-align: center;white-space: nowrap">{{ number_format($detail->tan_price, 0, '.', ' ') }}</td>
        							<td style="padding: 0px 5px; text-align: center;white-space: nowrap">{{ number_format($detail->price, 0, '.', ' ') }}</td>
        							<td style="padding: 0px 5px; text-align: center;white-space: nowrap">
        							    @if($item->payments()->where('status', 1)->count() >= 2)
        							    @php($sss = ($detail->total_price * ($item->payments()->where('status', 1)->where('cash_receipt_type', $checkouttip)->sum('price')/$item->details()->sum('total_price'))*100)/100)
        							    @php($itgsum += $sss)
        							    {{ number_format($sss, 0, '.', ' ') }}
        							    @else
        							    @php($itgsum += $detail->total_price)
        							    {{ number_format($detail->total_price, 0, '.', ' ') }}
        							    @endif
        							</td>
        							<td style="padding: 0px 5px; text-align: center;">{{ $item->supid ? $item->supid->name : NULL }}</td>
        							<td style="padding: 0px 5px; text-align: center;">{{ $detail->warehouseid ? $detail->warehouseid->num_code : NULL }}</td>
        							<td style="padding: 0px 5px; text-align: center;">{{ $item->managerid ? $item->managerid->name : NULL }}</td>
        							<td>
        							    {{ $item->payments()->where('status', 1)->where('cash_receipt_type', $checkouttip)->count() ? $item->payments()->where('status', 1)->where('cash_receipt_type', $checkouttip)->first()->tname->name : null }} {{ $item->payments()->where('status', 1)->count() >= 2 ? '+' : null }}
        							</td>
        						</tr>
        						@endif
    						@else
    						@php($itgsum += $detail->total_price)
    						<tr>
    							<!--<td style="padding: 0px 5px;">{{$loop->iteration}}</td>-->
    							<td style="padding: 0px 5px;">{{ $detail->prodid->name }}</td>
    							<td style="padding: 0px 5px;">{{ $detail->prodid->unitid->name }}</td>
    							<td style="padding: 0px 5px; text-align: center;">{{ number_format($detail->qty, 0, '.', ' ') }}</td>
    							<td style="padding: 0px 5px; text-align: center;white-space: nowrap">{{ number_format($detail->tan_price, 0, '.', ' ') }}</td>
    							<td style="padding: 0px 5px; text-align: center;white-space: nowrap">{{ number_format($detail->price, 0, '.', ' ') }}</td>
    							<td style="padding: 0px 5px; text-align: center;white-space: nowrap">{{ number_format($detail->total_price, 0, '.', ' ') }}</td>
    							<td style="padding: 0px 5px; text-align: center;">{{ $item->supid ? $item->supid->name : NULL }}</td>
    							<td style="padding: 0px 5px; text-align: center;">{{ $detail->warehouseid ? $detail->warehouseid->num_code : NULL }}</td>
    							<td style="padding: 0px 5px; text-align: center;">{{ $item->managerid ? $item->managerid->name : NULL }}</td>
    							<td style="padding: 0px 5px; text-align: center;">
    							   {{ $item->payments()->where('status', 1)->count() ? $item->payments()->where('status', 1)->first()->tname->name : null }} {{ $item->payments()->where('status', 1)->count() >= 2 ? '+' : null }}
    							 </td>
    							<!--<td style="padding: 0px 5px; text-align: center;">{{ $detail->checkid->checktypeid ? $detail->checkid->checktypeid->name_ru : NULL }}</td>-->
    						</tr>
    						@endif
    						@endforeach
						@endforeach
						<tr>
							<!--<td style="padding: 0px 5px;"></td>-->
							<td style="padding: 0px 5px;"></td>
							<td style="padding: 0px 5px;"></td>
							<td style="padding: 0px 5px;">Всего к оплате</td>
							<td style="padding: 0px 5px;"></td>
							<td style="padding: 0px 5px;"></td>
							<td style="padding: 0px 5px;"></td>
							<td style="padding: 0px 5px;"></td>
							<td style="padding: 0px 5px;"></td>
							<td style="padding: 0px 5px;"></td>
							<td style="padding: 0px 5px;"></td>
						</tr>
					</tbody>
				</table>
				<table style="border: 0px; width: 100%;">
                <tbody>
                </tbody>
            </table>
            <br>
            
            <table style="border: 0px; width: 100%;">
                <tbody>
                    <tr>
                        <td style="border: 0px; padding: 5px 0px;" width="50%">Руководитель: ____________________</td>
                        <td style="border: 0px; padding: 5px 0px;" width="50%">Итого сумма: {{ $itgsum && $itgsum != 0 ? number_format($itgsum, 2, '.', ' ') : null }}</td>
                    </tr>
                    <tr>
                        <td style="border: 0px; padding: 5px 0px;" width="50%">Главный бухгалтер: ____________________</td>
                        <td style="border: 0px; padding: 5px 0px;" width="50%"></td>
                    </tr>
                    <tr>
                        <td style="border: 0px; padding: 5px 0px;" width="50%"><b>М.П.</b></td>
                        <td style="border: 0px; padding: 5px 0px;" width="50%"></td>
                    </tr>
                    <!--<tr>
                        <td style="border: 0px; padding: 5px 0px;" width="50%">Товар отпустил ____________________</td>
                        <td style="border: 0px; padding: 0px; vertical-align: text-bottom;" width="50%">&nbsp; &nbsp; &nbsp; &nbsp;ФИО получателя</td>
                    </tr>-->
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
