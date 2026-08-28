<!DOCTYPE html>
<html>
<head>
    <title>Eng Ko'p Sotilgan Mahsulotlar</title>
	<style type="text/css">
			ul li {
				line-height:18px;
				font-family: 'Tinos', serif;
				padding-left: 20px;

			}
			table, th, td {
			  border: 1px solid black;
			  border-collapse: collapse;
			  padding: 4px;
			  font-family: 'Tinos', serif;

			}
			
		</style>
</head>
<body>
    <h1>Eng Ko'p Sotilgan Mahsulotlar ({{ $fromDate }} dan {{ $toDate }} gacha)</h1>

    <table width="100%">
        <thead>
            <tr>
                <th>Наименование</th>
                <th>Штрих-код</th>
                <th>Ед.изм.</th>
                <th>Количество</th>
            </tr>
        </thead>
        <tbody>
            @foreach($mostSoldProducts as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->barcode }}</td>
                    <td>{{ $product->unit_name }}</td>
                    <td>{{ $product->total_qty }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <a href="{{ route('product_report_form') }}">Orqaga</a>
</body>
</html>