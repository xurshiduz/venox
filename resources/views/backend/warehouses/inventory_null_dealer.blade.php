<!DOCTYPE html>
<html lang="zxx" class="js">

<head>
    <meta charset="utf-8">
    <meta name="author" content="Softnio">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="A powerful and conceptual apps base dashboard template that especially build for developers and programmers.">
    <!-- Fav Icon  -->
    <link rel="shortcut icon" href="/backend/images/favicon.png">
    <!-- Page Title  -->
    <title>Остаток - {{ $ware->name }}</title>
    <!-- StyleSheets  -->
    <link rel="stylesheet" href="/backend/assets/css/dashlite.css?ver=3.0.3">
    <link id="skin-default" rel="stylesheet" href="/backend/assets/css/theme.css?ver=3.0.3">
<style>
    .invoice-head {
    padding-bottom: 1.5rem;
    display: flex;
    flex-direction: row;
    justify-content: space-between;
}
</style></head>

<body class="bg-white" onload="printPromot()">
    <div class="nk-block">
        <div class="invoice invoice-print">
            <div class="invoice-wrap">
                <div class="invoice-brand text-center">
                    <img width="250px" src="/backend/logo.svg" srcset="/backend/logo.svg" alt="">
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
                            <li class="invoice-id"><span>Invoice ID</span>:<span>66K5W3</span></li>
                            <li class="invoice-date"><span>дата отчета</span>:<span>{{ Carbon\Carbon::now()->format('Y-m-d') }}</span></li>
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
                                    <th style="width: 80px; text-align: center;">10 дней</th>
                                    <th style="width: 80px; text-align: center;">30 дней</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data as $item)
                                <tr>
                                    <td>{{ $item->first()->prodid->barcode }}</td>
                                    <td>{{ $item->first()->prodid->name }}</td>
                                    <td>{{ $item->first()->prodid->dealertransferdetails()->where('warehouse_id', $ware->id)->where('status', 1)->sum('qty') - $item->first()->prodid->checkoutdetails()->where('warehouse_id', $ware->id)->where('status', 1)->sum('qty') }}</td>
                                    <td style="text-align: center;">{{ $item->first()->prodid->checkoutdetails()->where('warehouse_id', $ware->id)->where('status', 1)->whereBetween('created_at', [Carbon\Carbon::now()->subDays(7)->format('Y-m-d'), Carbon\Carbon::now()->format('Y-m-d')])->sum('qty') }}</td>
                                    <td style="text-align: center;">{{ $item->first()->prodid->checkoutdetails()->where('warehouse_id', $ware->id)->where('status', 1)->whereBetween('created_at', [Carbon\Carbon::now()->subDays(30)->format('Y-m-d'), Carbon\Carbon::now()->format('Y-m-d')])->sum('qty') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                       <!-- <table class="table table-striped">
                            <tfoot>
                                <tr>
                                    <td colspan="2"></td>
                                    <td colspan="2">Subtotal</td>
                                    <td>$435.00</td>
                                </tr>
                                <tr>
                                    <td colspan="2"></td>
                                    <td colspan="2">Processing fee</td>
                                    <td>$10.00</td>
                                </tr>
                                <tr>
                                    <td colspan="2"></td>
                                    <td colspan="2">TAX</td>
                                    <td>$43.50</td>
                                </tr>
                                <tr>
                                    <td colspan="2"></td>
                                    <td colspan="2">Grand Total</td>
                                    <td>$478.50</td>
                                </tr>
                            </tfoot>
                        </table>-->
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