<!DOCTYPE html>
<html> 
  <head>
    <title>{{ $item->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <style type="text/css">
        @media  print {
            @page  { margin: 10px 0 0 0; } body { margin: 0cm;} 
        }
        @page {
          size: 58mm 40mm;
        }
    </style>
    
    <style>
    body {
      font-family: 'Roboto', sans-serif;
      font-size: 48px;
    }
  </style>
  </head>
<body style="@if($vmm == 30) margin: 0 0 0 20px; font-size:9px; zoom: 1.6; width: 30mm @else margin: 0px; font-size:9px; zoom: 1.6; width: 40mm @endif">
<center>
    <table>
        <tr>
            <th><img src="data:image/png;base64,{{ DNS2D::getBarcodePNG($item->barcode, 'QRCODE') }}" alt="QR Code" /></th>
            <th>{{ $item->name }}</th>
        </tr>
        <tr>
            <th colspan="2">QR: {{ $item->barcode }}</th>
        </tr>
    </table>
</center>
<script type="text/javascript">
    function auto_print() {     
        window.print()
    }
    setTimeout(auto_print, 1000);
</script>

</body>
</html>
