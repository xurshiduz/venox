<table>
    <thead>
        <tr>
            <th colspan="{{ count($productsList) + 4 }}" style="font-weight: bold; font-size: 14px; height: 35px;">
                {{ $monthYear }} oyi uchun hisobot
            </th>
        </tr>
        <tr>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #cccccc;">T/r</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #cccccc;">Mijoz nomi \ Mahsulotlar</th>
            
            @foreach($productsList as $product)
                <th style="font-weight: bold; border: 1px solid #000000; background-color: #cccccc;">
                    {{ $product }}
                </th>
            @endforeach
            
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #cccccc;">Umumiy narx (USD)</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #b4c6e7;">Shu oyda to'lagan summa (USD)</th>
        </tr>
    </thead>
    <tbody>
        @php $i = 1; @endphp
        @foreach($matrixData as $clientKey => $products)
            <tr>
                <td style="border: 1px solid #000000; text-align: center;">{{ $i++ }}</td>
                <td style="border: 1px solid #000000;">{{ $clientNames[$clientKey] ?? 'Noma\'lum mijoz' }}</td>
                
                @foreach($productsList as $product)
                    <td style="border: 1px solid #000000; text-align: center;">
                        {{ !empty($products[$product]) ? $products[$product] : '' }}
                    </td>
                @endforeach
                
                <td style="border: 1px solid #000000; font-weight: bold; text-align: right; background-color: #f2f2f2;">
                    {{ (float) ($clientTotalUsd[$clientKey] ?? 0) }}
                </td>
                <td style="border: 1px solid #000000; font-weight: bold; text-align: right; background-color: #eaf0f8;">
                    {{ (float) ($clientPaidTotals[$clientKey] ?? 0) }}
                </td>
            </tr>
        @endforeach
        
        {{-- USTUNLAR BO'YICHA UMUMIY NARX (Tovar qancha sotilgani) --}}
        <tr>
            <td style="border: 1px solid #000000; background-color: #d9edf7;"></td>
            <td style="border: 1px solid #000000; font-weight: bold; background-color: #d9edf7; text-align: right;">Umumiy narx:</td>
            
            @foreach($productsList as $product)
                <td style="border: 1px solid #000000; font-weight: bold; background-color: #d9edf7; text-align: right;">
                    {{ (float) ($productTotalUsd[$product] ?? 0) }}
                </td>
            @endforeach
            
            <td style="border: 1px solid #000000; font-weight: bold; background-color: #dce6f1; text-align: right; font-size: 14px;">
                {{ $grandSalesFormula }}
            </td>
            <td style="border: 1px solid #000000; font-weight: bold; background-color: #b4c6e7; text-align: right; font-size: 14px;">
                {{ $grandPaidFormula }}
            </td>
        </tr>
    </tbody>
</table>
