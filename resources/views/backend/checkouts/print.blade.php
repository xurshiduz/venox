<!DOCTYPE html>
<html> 
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" type="image/png" href="/assets/images/logo.png" />
    <title>{{ $item->name }}</title>
    <meta name="description" content="{{ $item->part_number }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="all,follow">

    <style type="text/css">
        @media  print {
            @page  { margin: 0; } body { margin: 0cm;} 
        }
    </style>
  </head>
<body style="margin: 0px;">
    <center>

<div id="receipt-data" style="padding: 5px;">
    <table style="border-collapse: collapse; width: 100%;" border="1">
        <tbody>
            <tr>
                <td colspan="2"><span style="width: 70%; float: left; padding: 5px;">{{ $item->name }} </span><span style="float: right; padding: 5px;"><img width="100px" src="/backend/logo.svg" srcset="/backend/logo.svg" alt=""></span></td>
            </tr>
            <tr>
                <td style="width: 50%; padding: 5px;"><span style="font-size: 12px;">Штрих-код:</span><br>{{ $item->part_number }}</td>
                <td rowspan="3" style="text-align: center;"><img style="padding: 10px" src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(100)->generate($item->part_number)) !!} "></td>
            </tr>
            <tr>
                <td style="width: 50%; padding: 5px;"><span style="font-size: 12px;">Категория:</span><br>{{ $item->catid->name }}</td>
            </tr>
            <tr>
                <td style="width: 50%; padding: 5px;"><span style="font-size: 12px;">Страна:</span><br>{{ $item->contryid->name }}</td>
            </tr>
        </tbody>
    </table>


</div>
</center>
<script src="{{ asset('/backend/qrcode.min.js') }}"></script>
<script type="text/javascript">
    function auto_print() {     
        window.print()
    }
    setTimeout(auto_print, 1000);
</script>

</body>
</html>
