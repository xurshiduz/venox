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
            
            {{-- Umumiy narx ustuni enini biroz kattalashtirdik, ikkala valyuta sig'ishi uchun --}}
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #cccccc;">Umumiy narx</th>
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
                
                {{-- Ushbu mijoz bo'yicha JAMI SO'M va DOLLAR --}}
                <td style="border: 1px solid #000000; font-weight: bold; text-align: right; background-color: #f2f2f2;">
                    {{ number_format($clientTotalUzs[$clientKey] ?? 0, 0, '.', ' ') }} Сум
                    @if(!empty($clientTotalUsd[$clientKey]))
                        <br>({{ number_format($clientTotalUsd[$clientKey], 0, '.', ' ') }} $)
                    @endif
                </td>
                <td style="border: 1px solid #000000; font-weight: bold; text-align: right; background-color: #eaf0f8;">
                    {{ number_format($clientPaidTotals[$clientKey] ?? 0, 2, '.', ' ') }} $
                </td>
            </tr>
        @endforeach
        
        {{-- USTUNLAR BO'YICHA UMUMIY NARX (Tovar qancha sotilgani) --}}
        <tr>
            <td style="border: 1px solid #000000; background-color: #d9edf7;"></td>
            <td style="border: 1px solid #000000; font-weight: bold; background-color: #d9edf7; text-align: right;">Umumiy narx:</td>
            
            @foreach($productsList as $product)
                <td style="border: 1px solid #000000; font-weight: bold; background-color: #d9edf7; text-align: right;">
                    @if(!empty($productTotalUzs[$product]))
                        {{ number_format($productTotalUzs[$product], 0, '.', ' ') }} Сум
                        @if(!empty($productTotalUsd[$product]))
                            <br>({{ number_format($productTotalUsd[$product], 0, '.', ' ') }} $)
                        @endif
                    @endif
                </td>
            @endforeach
            
            {{-- Eng oxirgi Kesishma (Grand Total) ham So'm, ham Dollar --}}
            <td style="border: 1px solid #000000; font-weight: bold; background-color: #dce6f1; text-align: right; font-size: 14px;">
                {{ number_format($grandTotalUzs, 0, '.', ' ') }} Сум
                @if($grandTotalUsd > 0)
                    <br>({{ number_format($grandTotalUsd, 0, '.', ' ') }} $)
                @endif
            </td>
            <td style="border: 1px solid #000000; font-weight: bold; background-color: #b4c6e7; text-align: right; font-size: 14px;">
                {{ number_format($grandTotalPaid, 2, '.', ' ') }} $
            </td>
        </tr>
    </tbody>
</table>
