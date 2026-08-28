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
			<h1 style="margin: 0px" class="title-order">Инвентаризация склада DIZEL от 01.11.2024 до 30.11.2024</h1>
			<p style="font-weight: bold; margin: 0px; text-align: center;"></p>
		<div class="wordpdf">
				<div style="margin: 10px">
				<table width="100%" style="font-size: 9px">
					<tbody>
						<tr style="text-align: center;">
							<!--<td style="padding: 0px 5px;">№ п/п</td>-->
							<td style="padding: 0px 5px;">Наименование товаров (работ, услуг)</td>
							<td style="padding: 0px 5px;">Продукт ИД</td>
							<td style="padding: 0px 5px;">Кол-во</td>
							<td style="padding: 0px 5px;">Штрих</td>
							<td style="padding: 0px 5px; width:50px">Ячейка</td>
							<td style="padding: 0px 5px;">Гурух</td>
							<td style="padding: 0px 5px;">Варок</td>
							<!--<td style="padding: 0px 5px;">Боши бирхил</td>
							<td style="padding: 0px 5px;">Охири бирхил</td>-->
						</tr>
						@foreach($data as $item)
						@if($item->prodid)
    						<tr>
    							<td style="padding: 0px 5px;">{{ $item->prodid ? $item->prodid->name : null }}</td>
    							<td style="padding: 0px 5px;">{{ $item->prodid ? $item->prodid->id : null }}</td>
    							<td style="padding: 0px 5px;">{{ $item->qty }}</td>
    							<td style="padding: 0px 5px;">{{ $item->barcode }}</td>
    							<td style="padding: 0px 5px;">{{ $item->yach }}</td>
    							<td style="padding: 0px 5px;">{{ $item->gur }}</td>
    							<td style="padding: 0px 5px;">{{ $item->var }}</td>
    						</tr>
    					@endif
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
