@php
    $usdRate = App\Models\Currency::usdRate();
@endphp
<table>
    <tbody>
        <tr>
            @for($column = 0; $column < 11; $column++)
                <td></td>
            @endfor
        </tr>
        <tr>
            <td></td>
            <th>№</th>
            <th>Name</th>
            <th>Штрих-код</th>
            <th>O'lchov birligi</th>
            <th>Umumiy qoldiq</th>
            <th>Kirim narxi (UZS)</th>
            <th>Kirim summasi (UZS)</th>
            <th>Sotuv narxi (UZS)</th>
            <th>Sotuv summasi (UZS)</th>
            <th>Qancha ustiga qo'yilgani (%)</th>
        </tr>

        @php
            $stocks = App\Models\WarehouseStock::where('warehouse_id', $wareid->id)
                ->where('stock', '>', 0)
                ->whereHas('productid')
                ->with(['productid.unitid'])
                ->orderBy('product_id');

            if (isset($take, $pag)) {
                $stocks->skip((int) $take)->take((int) $pag);
            }

            $stocks = $stocks->get();

            $usdRate = $usdRate > 0 ? $usdRate : 1;

        @endphp

        @foreach($stocks as $item)
            @php
                $latestCheckin = $item->productid->checkindetails()
                    ->with('checkid')
                    ->where('warehouse_id', $wareid->id)
                    ->where('status', 1)
                    ->where('price', '>', 0)
                    ->latest('created_at')
                    ->latest('id')
                    ->first();

                $checkinRawPrice = $latestCheckin
                    ? (float) $latestCheckin->price
                    : (float) $item->checkin_price;
                $checkinCurrency = (int) ($latestCheckin->currency_type
                    ?? optional($latestCheckin->checkid ?? null)->currency_type
                    ?? 2);
                $checkinRate = (float) ($latestCheckin->currency_type_price
                    ?? optional($latestCheckin->checkid ?? null)->currency_type_price
                    ?? $usdRate);
                $checkinPrice = App\Models\Currency::toUzs(
                    $checkinRawPrice,
                    $checkinCurrency,
                    $checkinRate
                );

                $checkoutRawPrice = (float) $item->checkout_price;
                if ($checkoutRawPrice <= 0) {
                    $checkoutRawPrice = (float) ($item->productid->price ?? 0);
                }
                // Mahsulot kartasidagi valyuta turiga qarab UZSga o'tkaziladi.
                // USD narx kursga ko'payadi, UZS narx o'zgarmaydi.
                $checkoutPrice = App\Models\Currency::toUzs(
                    $checkoutRawPrice,
                    (int) ($item->productid->currency_type ?? 2),
                    $usdRate
                );

                $stock = (float) $item->stock;
                $checkinTotal = $checkinPrice * $stock;
                $checkoutTotal = $checkoutPrice * $stock;
                $markup = App\Models\Currency::markupPercent($checkinPrice, $checkoutPrice);
            @endphp
            <tr>
                <td></td>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->productid->name }}</td>
                <td data-type="s">{{ $item->productid->barcode }}</td>
                <td>{{ optional($item->productid->unitid)->name }}</td>
                <td>{{ $stock }}</td>
                <td>{{ round($checkinPrice, 2) }}</td>
                <td>{{ round($checkinTotal, 2) }}</td>
                <td>{{ round($checkoutPrice, 2) }}</td>
                <td>{{ round($checkoutTotal, 2) }}</td>
                <td>{{ $markup !== null ? round($markup, 2) . ' %' : '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
