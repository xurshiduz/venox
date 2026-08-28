<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <title>Foyda va Zararlar Hisoboti</title>
    <style>
        body {
            font-family: "Arial", sans-serif;
            font-size: 12px;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 20px;
        }

        /* Jadval asosiy strukturasi */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .report-table th, 
        .report-table td {
            padding: 6px 4px;
            vertical-align: bottom;
        }

        /* Header qismi */
        .table-header {
            border-bottom: 2px solid #000;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 14px;
        }

        /* Oddiy qatorlar */
        .row-line td {
            border-bottom: 1px solid #ccc;
        }

        /* Qalin qatorlar (Jami) */
        .row-bold td {
            font-weight: bold;
            font-size: 13px;
            border-bottom: 2px solid #000;
            padding-top: 10px;
            padding-bottom: 10px;
        }

        /* Italic / Kichik yozuvlar (Foizlar) */
        .row-sub td {
            font-style: italic;
            color: #555;
            font-size: 11px;
            border-bottom: none;
            padding-top: 0;
        }

        /* Yashil rang (Sof foyda uchun) */
        .text-green {
            color: #15803d; /* To'q yashil */
        }
        
        .text-red {
            color: #b91c1c;
        }

        .text-right {
            text-align: right;
        }

        .currency-col {
            width: 50px;
            text-align: center;
            color: #666;
            font-size: 10px;
        }

        /* Print tugmasi */
        .no-print {
            margin-bottom: 20px;
            text-align: right;
        }
        button {
            padding: 5px 15px;
            cursor: pointer;
            background: #333;
            color: #fff;
            border: none;
        }

        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()">Print (Ctrl+P)</button>
    </div>

    <!-- Sarlavha -->
    <table class="report-table">
        <thead>
            <tr class="table-header">
                <th style="text-align: left;">Фойда ва Зарарлар Ҳисоботи (P&L)</th>
                <th>Валюта</th>
                <th class="text-right">{{ $date['month'] }} {{ $date['year'] }}</th>
            </tr>
        </thead>
        <tbody>
            
            <!-- Tushum -->
            <tr class="row-bold">
                <td>Тушум (Выручка)</td>
                <td class="currency-col">UZS</td>
                <td class="text-right">{{ number_format($revenue, 0, ' ', ' ') }}</td>
            </tr>

            <!-- Tannarx -->
            <tr class="row-line">
                <td>Сотилган товарлар таннархи (Себестоимость)</td>
                <td class="currency-col">UZS</td>
                <td class="text-right">{{ number_format($cogs, 0, ' ', ' ') }}</td>
            </tr>
            <tr class="row-sub">
                <td>Маржа (%)</td>
                <td class="currency-col">%</td>
                <td class="text-right">{{ number_format($margin_percent, 1) }}%</td>
            </tr>

            <!-- Yalpi Foyda -->
            <tr class="row-bold">
                <td>Ялпи фойда (Валовая прибыль)</td>
                <td class="currency-col">UZS</td>
                <td class="text-right">{{ number_format($gross_profit, 0, ' ', ' ') }}</td>
            </tr>
            <tr class="row-sub">
                <td class="text-green">Таннарх улуши (%)</td>
                <td class="currency-col">%</td>
                <td class="text-right text-green">{{ number_format($cost_percent, 1) }}%</td>
            </tr>

            <!-- Xarajatlar Header -->
            <tr class="row-bold" style="background-color: #f9f9f9;">
                <td colspan="3" style="padding-top: 20px;">Операцион харажатлар</td>
            </tr>

            <!-- Xarajatlar ro'yxati -->
            <tr class="row-line">
                <td>Иш ҳақи фонди</td>
                <td class="currency-col">UZS</td>
                <td class="text-right">{{ number_format($expenses['salary'], 0, ' ', ' ') }}</td>
            </tr>
            <tr class="row-line">
                <td>Аренда</td>
                <td class="currency-col">UZS</td>
                <td class="text-right">{{ number_format($expenses['rent'], 0, ' ', ' ') }}</td>
            </tr>
            <tr class="row-line">
                <td>Коммунал тўловлар</td>
                <td class="currency-col">UZS</td>
                <td class="text-right">{{ number_format($expenses['communal'], 0, ' ', ' ') }}</td>
            </tr>
            <tr class="row-line">
                <td>Транспорт харажатлари</td>
                <td class="currency-col">UZS</td>
                <td class="text-right">{{ number_format($expenses['transport'], 0, ' ', ' ') }}</td>
            </tr>
            <tr class="row-line">
                <td>Бошқа харажатлар</td>
                <td class="currency-col">UZS</td>
                <td class="text-right">{{ number_format($expenses['other'], 0, ' ', ' ') }}</td>
            </tr>
            
            <!-- Jami Xarajat -->
            <tr class="row-bold">
                <td>Жами Операцион харажатлар</td>
                <td class="currency-col">UZS</td>
                <td class="text-right text-red">{{ number_format($total_expenses, 0, ' ', ' ') }}</td>
            </tr>

            <!-- Operatsion Foyda -->
            <tr class="row-bold">
                <td style="padding-top: 15px;">Операцион фойда (Прибыль)</td>
                <td class="currency-col">UZS</td>
                <td class="text-right" style="padding-top: 15px;">{{ number_format($operating_profit, 0, ' ', ' ') }}</td>
            </tr>
            <tr class="row-sub">
                <td>Рентабельность по прибыли (%)</td>
                <td class="currency-col">%</td>
                <td class="text-right">{{ number_format($operating_profit_percent, 1) }}%</td>
            </tr>

            <!-- Soliqlar -->
            <tr class="row-line">
                <td style="padding-top: 10px; color: #b91c1c;">Солиқлар ва йиғимлар</td>
                <td class="currency-col">UZS</td>
                <td class="text-right text-red">{{ number_format($taxes, 0, ' ', ' ') }}</td>
            </tr>

            <!-- SOF FOYDA -->
            <tr class="row-bold" style="border-top: 3px solid #000; border-bottom: 3px solid #000;">
                <td class="text-green" style="font-size: 16px; text-transform: uppercase;">Соф Фойда (Чистая Прибыль)</td>
                <td class="currency-col text-green">UZS</td>
                <td class="text-right text-green" style="font-size: 16px;">{{ number_format($net_profit, 0, ' ', ' ') }}</td>
            </tr>

        </tbody>
    </table>

    <!-- Qo'shimcha ma'lumot (Footer) -->
    <div style="margin-top: 30px; border-top: 1px dotted #000; padding-top: 10px; display: flex; justify-content: space-between; font-size: 10px;">
        <div>Ҳисобот яратилди: {{ date('d.m.Y H:i') }}</div>
        <div>Нуқтаи назар: {{ $operating_profit > 0 ? 'Фойдали' : 'Зарарли' }}</div>
    </div>

</body>
</html>