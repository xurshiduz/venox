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
			
			@media print {
                .vendorListHeading {
                    background-color: #fff9ae !important;
                    print-color-adjust: exact; 
                }
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
				<div style="margin: 10px">
				<table width="100%">
					<tbody>
						<tr style="text-align: center;">
							<td style="padding: 0px 5px;">№</td>
							<td style="padding: 0px 5px; width: 250px">Наименование товаров (работ, услуг)</td>
							<td style="padding: 0px 5px;">Кол-во</td>
							<td style="padding: 0px 5px;">Цена</td>
							<td style="padding: 0px 5px;">Себестоимость</td>
							<td style="padding: 0px 5px; background-color: #fff9ae; -webkit-print-color-adjust: exact;">Разница за ед.</td>
							<td style="padding: 0px 5px;">Итого цена</td>
							<td style="padding: 0px 5px;">Итого себестоимость</td>
							<td style="padding: 0px 5px; background-color: #fff9ae; -webkit-print-color-adjust: exact;">Итого разница</td>
						</tr>
						<tr style="text-align: center;">
						    <td style="padding: 0px 5px;"></td>
							<td style="padding: 0px 5px;">1</td>
							<td style="padding: 0px 5px;">2</td>
							<td style="padding: 0px 5px;">3</td>
							<td style="padding: 0px 5px;">4</td>
							<td style="padding: 0px 5px; background-color: #fff9ae; -webkit-print-color-adjust: exact;">5</td>
							<td style="padding: 0px 5px;">6</td>
							<td style="padding: 0px 5px;">7</td>
							<td style="padding: 0px 5px; background-color: #fff9ae; -webkit-print-color-adjust: exact;">8</td>
						</tr>
						@php($itgsum = 0)
						@php($itgseb = 0)
						@foreach($data as $detail)
						    @php($qqty = $detail->first()->prodid->checkoutdetails()->whereBetween('created_at', [Carbon\Carbon::parse($fromdate)->startOfDay()->format('Y-m-d H:i:s'), Carbon\Carbon::parse($todate)->endOfDay()->format('Y-m-d H:i:s')])->sum('qty'))
    						@php($qavg = $detail->first()->prodid->checkoutdetails()->whereBetween('created_at', [Carbon\Carbon::parse($fromdate)->startOfDay()->format('Y-m-d H:i:s'), Carbon\Carbon::parse($todate)->endOfDay()->format('Y-m-d H:i:s')])->avg('price'))
    						@php($dtan = $detail->first()->prodid->checkoutdetails()->whereBetween('created_at', [Carbon\Carbon::parse($fromdate)->startOfDay()->format('Y-m-d H:i:s'), Carbon\Carbon::parse($todate)->endOfDay()->format('Y-m-d H:i:s')])->avg('tan_price'))
    						@php($totalavg = $qqty * $qavg)
    						@php($totaltan = $qqty * $dtan)
    						<tr>
    							<td style="padding: 0px 5px;">{{$loop->iteration}}</td>
    							<td style="padding: 0px 5px;">{{ $detail->first()->prodid->name }}</td>
    							<td style="padding: 0px 5px; text-align: center;">{{ number_format($qqty, 2, '.', ' ') }}</td>
    							<td style="padding: 0px 5px; text-align: center;">{{ number_format($qavg, 2, '.', ' ') }}</td>
    							<td style="padding: 0px 5px; text-align: center;">{{ number_format($dtan, 2, '.', ' ') }}</td>
    							<td style="padding: 0px 5px; background-color: #fff9ae; -webkit-print-color-adjust: exact;">{{ number_format($qavg - $dtan, 2, '.', ' ') }}</td>
    							<td style="padding: 0px 5px; text-align: center;">{{ number_format($totalavg, 2, '.', ' ') }}</td>
    							<td style="padding: 0px 5px; text-align: center;">{{ number_format($totaltan, 2, '.', ' ') }}</td>
    							<td style="padding: 0px 5px; background-color: #fff9ae; -webkit-print-color-adjust: exact;">{{ number_format($totalavg - $totaltan, 2, '.', ' ') }}</td>
    						</tr>
						@endforeach
					</tbody>
				</table>
				<table style="border: 0px; width: 100%;">
                <tbody>
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
