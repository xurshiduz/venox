
<!DOCTYPE html>
<html> 
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" type="image/png" href="/assets/images/logo.png" />
    <title>{{ config('app.sitename') }}</title>
    <meta name="description" content="{{ config('app.sitename') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="all,follow">

    <style type="text/css">
        * {
            font-size: 12px;
            font-weight: bold;
            line-height: 16px;
            font-family: 'Ubuntu', sans-serif;
        }
        .btn {
            padding: 7px 10px;
            text-decoration: none;
            border: none;
            display: block;
            text-align: center;
            margin: 7px;
            cursor:pointer;
        }

        .btn-info {
            background-color: #999;
            color: #FFF;
        }

        .btn-primary {
            background-color: #6449e7;
            color: #FFF;
            width: 100%;
        }
        

        .centered {
            text-align: center;
            align-content: center;
        }
        small{font-size:11px;}

        @media  print {
            * {
                font-size:12px;
                line-height: 16px;
            }
            
            @page  { margin: 0; } body { margin: 0cm;} 
        }
    </style>
  </head>
<body style="margin: 0px; width: 80mm;">

<div style="margin: 10px auto">
    <div id="receipt-data">
        <center>
            <img src="/backend/logo.svg"  width="150px">
        </center>
        <br>
        <table style="width: 100%;">
            <tr>
                <td>Клиент:</td>
                <td style="float: right;">{{ $item->client_id ? $item->supid->name : null }}</td>
            </tr>
            <tr>
                <td>Дата:</td>
                <td style="float: right;">{{ $item->created_at->format('Y-m-d H:i') }}</td>
            </tr>
            <tr style="text-align: center;">
                <td colspan="2"> <b style="font-size: 38px; font-weight: 900;">{{ $item->id }}</b><br>Ваш номер очереди</td>
            </tr>
        </table>
        <center>*******************</center>
        <table style="width: 100%;">
            <tr>
                <td>Наименование:</td>
                <td style="float: right;">Кол.во</td>
            </tr>
            @foreach($item->details()->get()->groupBy('product_id') as $ditem)
            <tr>
                <td>{{ $ditem->first()->prodid->name }} - {{ $ditem->first()->prodid->contryid->name }}<br><span style="font-weight: 100;">{{ $ditem->first()->prodid->part_number }}</span></td>
                <td style="float: right;">{{ $ditem->sum('qty') }} {{ $ditem->first()->prodid->unitid->name }}</td>
            </tr>
            @endforeach
        </table>
        <center>*******************</center>
        <table style="width: 100%;">
            <tr>
                <td>Контакт:</td>
                <td style="float: right;">98 314 70 80</td>
            </tr>
            <tr>
                <td></td>
                <td style="float: right;">98 314 80 90</td>
            </tr>
            <tr>
                <td></td>
                <td style="float: right;">98 314 22 29</td>
            </tr>
            <tr>
                <td></td>
                <td style="float: right;">98 115 88 85</td>
            </tr>
            <tr>
                <td>Время работы:</td>
                <td style="float: right;">08:00-19:00</td>
            </tr>
        </table>
        <br>
        <center>*******************</center>
        <table style="width: 100%;">
            <tr>
                <td>Магазин:</td>
                <td style="float: right;">www.simmaautostar.uz</td>
            </tr>
            <tr>
                <td>Telegram:</td>
                <td style="float: right;">@simma_autoStar</td>
            </tr>
            <tr>
                <td>Instagram:</td>
                <td style="float: right;">@simma_auto_star_</td>
            </tr>
            <tr>
                <td>Facebook:</td>
                <td style="float: right;">@simma.auto.star</td>
            </tr>
            <tr>
                <td>Telegram Bot:</td>
                <td style="float: right;">@zapchastbaza_bot</td>
            </tr>
            
            
            
        </table>
        <br>
        <center> <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($item->transaction, 'C128') }}" alt="barcode"   /><br>{{ $item->transaction }}
        </center>
        
    </div>
</div>
<script type="text/javascript">
    function auto_print() {     
        window.print()
    }
    setTimeout(auto_print, 500);
</script>
</body>
</html>
