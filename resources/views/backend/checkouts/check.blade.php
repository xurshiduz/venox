<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chek</title>
    <style>
        /* Termal printer uchun umumiy sozlamalar */
        body {
            font-family: 'Courier New', Courier, monospace; /* Monospace shrift chek uchun yaxshi */
            font-size: 12px;
            font-weight: bold;
            width: 300px; /* 80mm printer uchun o'rtacha kenglik */
            margin: 0 auto;
            padding: 5px;
            color: #000;
            background: #fff;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        
        /* Flexbox yordamida qatorlarni tekislash */
        .row {
            display: flex;
            justify-content: space-between;
            width: 100%;
        }

        /* Chiziqlar */
        .dashed-line {
            border-top: 1px dashed #000;
            margin: 5px 0;
            width: 100%;
        }

        /* Mahsulot detallari uchun kichik shrift */
        .details {
            font-size: 10px;
            margin-left: 10px;
        }

        .big-total {
            font-size: 18px;
            font-weight: 900;
        }

        /* Printerda ortiqcha narsalarni yashirish */
        @media print {
            @page {
                margin: 0;
                padding: 0;
            }
            body {
                margin: 0;
                padding: 10px;
            }
            /* Tugmalarni yashirish */
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>

    <!-- Do'kon nomi -->
    <div class="text-center">
        <h3>XCMG</h3>
    </div>

    <!-- Chek ma'lumotlari -->
    <div class="row">
        <span>Kacca №1</span>
        <span>№@if($item->number_work) {{ $item->number_work }} @else Чер. #{{ $item->id }} @endif</span>
    </div>
    <div class="row">
        <span>{{ $item->created_at->format('Y-m-d') }}</span>
        <span>{{ $item->created_at->format('H:i') }}</span>
    </div>

    <div class="dashed-line"></div>

    <!-- Mahsulotlar ro'yxati -->
    @foreach($item->details()->orderBy('id', 'desc')->get() as $ditem)
    <div class="item-block" style="margin-bottom: 5px;">
        <!-- Mahsulot nomi -->
        <div>{{$loop->iteration}}. {{ $ditem->prodid->name }}</div>
        
        <!-- Hisob-kitob qatori -->
        <div class="row">
            <span style="font-size: 11px;">
            </span>
            <span>
               {{ $ditem->qty }}x{{ number_format($ditem->price, 0, '.', '\'') }} = {{ number_format($ditem->total_price, 0, '.', '\'') }}
            </span>
        </div>
        <div class="row">
            <span style="font-size: 11px;">
                12% НДС:
            </span>
            <span>
               {{ number_format($ditem->total_price * 12 / 112, 0, '.', '\'') }}
            </span>
        </div>
        
        <!-- Soliq va kodlar (Rasmdagidek) -->
        <div class="details">
            <div>sh.k: {{ $ditem->prodid->barcode }}</div>
            <!--<div>MXIK: </div>-->
        </div>
    </div>
    @endforeach

    <div class="dashed-line"></div>

    <!-- Jami hisob -->
    <div class="row">
        <span>Сумма</span>
        <span>{{ number_format($item->details()->sum('total_price'), 0, '.', '\'') }}</span>
    </div>
    <div class="row">
        <span>Сумма НДС</span>
        <span>{{ number_format($item->details()->sum('total_price') * 12 / 112, 0, '.', '\'') }}</span>
    </div>

    <div class="dashed-line"></div>

    <!-- Katta yakuniy summa -->
    <div class="row big-total">
        <span style="margin-left: auto;">{{ number_format($item->details()->sum('total_price'), 0, '.', '\'') }}</span>
    </div>

    <div class="dashed-line"></div>

    <!-- To'lov turi -->
    <div>
        Оплата--------------------<br>
        БАНК. КАРТА (F2) <span style="float: right;">{{ number_format($item->details()->sum('total_price'), 0, '.', '\'') }}</span>
    </div>
        @if($item->transaction)
        <br>
        <center> <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($item->transaction, 'C128') }}" alt="barcode"   /><br>{{ $item->transaction }}
        </center>
        @endif
    <br>
    
    <div class="text-center">
        БЛАГОДАРИМ ЗА ПОКУПКУ!<br>
        Менежер
    </div>
    <br><br>

    <!-- Avtomatik print skripti -->
    <script>
        window.onload = function() {
            window.print();
            
            // Ixtiyoriy: Printdan keyin oynani yopish yoki orqaga qaytish
            // window.onafterprint = function() { window.close(); }
        }
    </script>
</body>
</html>