<!DOCTYPE html>
<html lang="zxx" class="js">

<head>
    <meta charset="utf-8">
    <meta name="author" content="Softnio">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Fav Icon  -->
    <link rel="shortcut icon" href="/backend/images/favicon.png">
    <!-- Page Title  -->
    <title>Остаток - {{ $ware->name }}</title>
    <!-- StyleSheets  -->
    <link rel="stylesheet" href="/backend/assets/css/dashlite.css?ver=3.0.3">
    <link id="skin-default" rel="stylesheet" href="/backend/assets/css/theme.css?ver=3.0.3">
    <style>
        .invoice-head {
            padding-bottom: 1rem;
            display: flex;
            flex-direction: row;
            justify-content: space-between;
        }
        .invoice-brand {
            padding-bottom: 0.5rem;
        }
        .invoice-print {
            max-width: 940px;
            margin: 0rem auto;
        }
        /* Jadval chiziqlarini aniqroq qilish uchun */
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0,0,0,.05);
        }
        .table td, .table th {
            vertical-align: middle;
        }
    </style>
</head>

<body class="bg-white" onload="printPromot()">
    <div class="nk-block">
        <div class="invoice invoice-print">
            <div class="invoice-wrap">
                <div class="invoice-brand text-center">
                    <img width="250px" src="/xcmg_logo_h.png" srcset="/xcmg_logo_h.png" alt="">
                </div>
                <div class="invoice-head">
                    <div class="invoice-contact">
                        <span class="overline-title">Склад</span>
                        <div class="invoice-contact-info">
                            <h4 class="title">{{ $ware->name }}</h4>
                            <ul class="list-plain">
                                <li><em class="icon ni ni-map-pin-fill fs-18px"></em><span>{{ $ware->address }}</span></li>
                                <li><em class="icon ni ni-call-fill fs-14px"></em><span>{{ $ware->phone }}</span></li>
                            </ul>
                        </div>
                    </div>
                    <div class="invoice-desc">
                        <h3 class="title">Отчет</h3>
                        <ul class="list-plain">
                            <li class="invoice-id"><span>Invoice ID</span>:<span>{{ rand(10000, 99999) }}</span></li>
                            <li class="invoice-date"><span>дата отчета</span>:<span>{{ \Carbon\Carbon::now()->format('Y-m-d H:i') }}</span></li>
                        </ul>
                    </div>
                </div><!-- .invoice-head -->
                <div class="invoice-bills">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th class="w-150px">Штрих-код</th>
                                    <th class="w-60">Наименование</th>
                                    <th>Остаток</th>
                                    <th style="width: 80px; text-align: center;">Факт</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- $data endi WarehouseStock kolleksiyasi --}}
                                @foreach($data as $stockItem)
                                    {{-- Agar productid mavjud bo'lsa (relation ishlasa) --}}
                                    @if($stockItem->productid)
                                    <tr>
                                        <td>{{ $stockItem->productid->barcode }}</td>
                                        <td>{{ $stockItem->productid->name }}</td>
                                        {{-- Stock to'g'ridan-to'g'ri olinadi --}}
                                        <td>
                                            {{-- Butun son qilib chiqarish (agar kerak bo'lsa number_format ishlating) --}}
                                            {{ $stockItem->stock * 1 }} 
                                        </td>
                                        <td style="text-align: center; border: 1px solid #ddd;"></td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div><!-- .invoice-bills -->
            </div><!-- .invoice-wrap -->
        </div><!-- .invoice -->
    </div><!-- .nk-block -->
    <script>
        function printPromot() {
            window.print();
        }
    </script>
</body>
</html>