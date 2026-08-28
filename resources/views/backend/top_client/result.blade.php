<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="description" content="«Toshkent-Agro-Traktor» Mas՚uliyati cheklangan jamiyati">
		<title>Отчет</title>
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
			.title-order {
				text-align: center;
				font-size: 14px;
				font-family: 'Tinos', serif;
				font-weight: bold;
			}
			
		</style>
	</head>

	<body class="document" style="font-size: 12px">
	    <?php \Carbon\Carbon::setLocale('ru') ?> 
		<div class="page" contenteditable="true">
            
			<h1 style="margin: 0px" class="title-order">Отчет "Топ-клиент" от {{ Carbon\Carbon::parse($from)->format('Y-m-d') }} до {{ Carbon\Carbon::parse($to)->format('Y-m-d') }} </h1><br>
			<br>
			<span>{{ Carbon\Carbon::now()->format('Y') }} г.</span>
		<div class="wordpdf">
				<table width="100%">
					<tbody>
						<tr style="text-align: center;">
							<td style="padding: 0px 5px;"></td>
							<td style="padding: 0px 5px;">Клиент</td>
							<td style="padding: 0px 5px;">Сумма</td>
						</tr>
						@php($key = 0)
						@foreach($data as $item)
						<tr>
							<td style="padding: 0px 5px;">{{ $key += 1}}</td>
							<td style="padding: 0px 5px; text-align: center;">{{ $item->mijoz_malumotlari->name }}</td>
							<td style="padding: 0px 5px;">{{ number_format($item->umumiy_sotuv_summasi, 0, '.', ' ')  }}</td>
						</tr>
						@endforeach
					</tbody>
				</table>
				
            <br>
			</div>
		</div>

		</div>
		<script type="text/javascript">
		window.print();
		</script>



	</body>
</html>
