<table>
    <thead>
        <tr>
            <th colspan="10" style="font-size: 14px; font-weight: bold; text-align: center;">
                {{ $wareid->name }} omboridagi mahsulotlar qoldig‘i
            </th>
        </tr>
        <tr>
            <th colspan="10" style="text-align: center;">
                {{ Carbon\Carbon::now()->format('d.m.Y') }} holatiga
            </th>
        </tr>
        <tr>
            <th style="width: 50px; height: 32px; background-color: #d9eaf7; border: 1px solid #000000; text-align: center; font-weight: bold;">№</th>
            <th style="width: 300px; background-color: #d9eaf7; border: 1px solid #000000; text-align: center; font-weight: bold;">Name</th>
            <th style="width: 125px; background-color: #d9eaf7; border: 1px solid #000000; text-align: center; font-weight: bold;">Штрих-код</th>
            <th style="width: 110px; background-color: #d9eaf7; border: 1px solid #000000; text-align: center; font-weight: bold;">O'lchov birligi</th>
            <th style="width: 115px; background-color: #d9eaf7; border: 1px solid #000000; text-align: center; font-weight: bold;">Umumiy qoldiq</th>
            <th style="width: 115px; background-color: #d9eaf7; border: 1px solid #000000; text-align: center; font-weight: bold;">Kirim narxi</th>
            <th style="width: 125px; background-color: #d9eaf7; border: 1px solid #000000; text-align: center; font-weight: bold;">Kirim summasi</th>
            <th style="width: 115px; background-color: #d9eaf7; border: 1px solid #000000; text-align: center; font-weight: bold;">Sotuv narxi</th>
            <th style="width: 125px; background-color: #d9eaf7; border: 1px solid #000000; text-align: center; font-weight: bold;">Sotuv summasi</th>
            <th style="width: 170px; background-color: #d9eaf7; border: 1px solid #000000; text-align: center; font-weight: bold;">Qancha ustiga qo'yilgani (%)</th>
        </tr>
    </thead>
    <tbody>
        @php
            $stocks = App\Models\WarehouseStock::where('warehouse_id', $wareid->id)
                ->where('stock', '>', 0)
                ->whereHas('productid')
                ->with(['productid.unitid'])
                ->orderBy('product_id')
                ->get();

            $totalCheckin = 0;
            $totalCheckout = 0;
            $usdRate = (float) (App\Models\Currency::where('type_id', 1)
                ->latest('id')
                ->value('price') ?? 1);
        @endphp

        @foreach($stocks as $item)
            @php
                // Ombor qoldig‘ida saqlangan kirim narxi asosiy manba hisoblanadi.
                $checkinPrice = (float) $item->checkin_price;

                // Eski yozuvlarda narx saqlanmagan bo‘lsa, faqat shu ombordagi
                // tasdiqlangan va musbat narxli eng oxirgi kirimga qaytamiz.
                if ($checkinPrice <= 0) {
                    $checkinPrice = (float) ($item->productid->checkindetails()
                        ->where('warehouse_id', $wareid->id)
                        ->where('status', 1)
                        ->where('price', '>', 0)
                        ->latest('created_at')
                        ->value('price') ?? 0);
                }

                $checkoutPrice = (float) $item->checkout_price;
                if ($checkoutPrice <= 0) {
                    $checkoutPrice = (float) ($item->productid->price ?? 0);
                }

                // Sotuv narxi mahsulot kartasida USDda saqlangan bo‘lsa,
                // Excelda barcha summalar bir xil — so‘m valyutasida chiqadi.
                if ((int) $item->productid->currency_type === 1) {
                    $checkoutPrice *= $usdRate;
                }

                $stock = (float) $item->stock;
                $checkinTotal = $checkinPrice * $stock;
                $checkoutTotal = $checkoutPrice * $stock;
                $markup = $checkinPrice > 0 && $checkoutPrice > 0
                    ? (($checkoutPrice - $checkinPrice) / $checkinPrice) * 100
                    : null;

                $totalCheckin += $checkinTotal;
                $totalCheckout += $checkoutTotal;
            @endphp
            <tr>
                <td style="border: 1px solid #000000; text-align: center;">{{ $loop->iteration }}</td>
                <td style="border: 1px solid #000000;">{{ $item->productid->name }}</td>
                <td data-type="s" style="border: 1px solid #000000; text-align: center;">{{ $item->productid->barcode }}</td>
                <td style="border: 1px solid #000000; text-align: center;">{{ optional($item->productid->unitid)->name }}</td>
                <td data-format="#,##0.00" style="border: 1px solid #000000; text-align: center;">{{ $stock }}</td>
                <td data-format="#,##0.00" style="border: 1px solid #000000; text-align: right;">{{ $checkinPrice }}</td>
                <td data-format="#,##0.00" style="border: 1px solid #000000; text-align: right;">{{ $checkinTotal }}</td>
                <td data-format="#,##0.00" style="border: 1px solid #000000; text-align: right;">{{ $checkoutPrice }}</td>
                <td data-format="#,##0.00" style="border: 1px solid #000000; text-align: right;">{{ $checkoutTotal }}</td>
                <td data-format="0.00\%" style="border: 1px solid #000000; text-align: center;">{{ $markup }}</td>
            </tr>
        @endforeach

        <tr>
            <td colspan="6" style="border: 1px solid #000000; text-align: right; font-weight: bold;">Jami:</td>
            <td data-format="#,##0.00" style="border: 1px solid #000000; text-align: right; font-weight: bold;">{{ $totalCheckin }}</td>
            <td style="border: 1px solid #000000;"></td>
            <td data-format="#,##0.00" style="border: 1px solid #000000; text-align: right; font-weight: bold;">{{ $totalCheckout }}</td>
            <td style="border: 1px solid #000000;"></td>
        </tr>
    </tbody>
</table>
