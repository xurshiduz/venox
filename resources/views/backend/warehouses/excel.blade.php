@php
    $usdRate = App\Models\Currency::usdRate();
@endphp
<table>
    <tbody>
        <tr>
            @for($column = 0; $column < 9; $column++)
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
            <th>Sotuv narxi (UZS)</th>
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
                if ($checkinRate <= 1 && $latestCheckin && $latestCheckin->checkid) {
                    $checkinRate = (float) ($latestCheckin->checkid->currency_type_price ?: $usdRate);
                }
                $checkinPrice = App\Models\Currency::toUzs(
                    $checkinRawPrice,
                    $checkinCurrency,
                    $checkinRate
                );

                // Sotuv narxi mahsulot kartasidagi bugungi narx/kursdan emas,
                // aynan oxirgi yakunlangan sotuv qatori va o'sha hujjat kursidan olinadi.
                $latestCheckout = $item->productid->checkoutdetails()
                    ->with('checkid')
                    ->where('warehouse_id', $wareid->id)
                    ->where('status', 1)
                    ->where('price', '>', 0)
                    ->whereHas('checkid', function ($query) {
                        $query->where('status', 1);
                    })
                    ->latest('created_at')
                    ->latest('id')
                    ->first();

                $checkoutRawPrice = $latestCheckout
                    ? (float) $latestCheckout->price
                    : (float) ($item->checkout_price ?: ($item->productid->price ?? 0));
                $checkoutCurrency = (int) ($latestCheckout->currency_type
                    ?? optional($latestCheckout->checkid ?? null)->currency_type
                    ?? $item->productid->currency_type
                    ?? 1);
                $checkoutRate = (float) ($latestCheckout->currency_type_price
                    ?? optional($latestCheckout->checkid ?? null)->currency_type_price
                    ?? $usdRate);
                if ($checkoutRate <= 1 && $latestCheckout && $latestCheckout->checkid) {
                    $checkoutRate = (float) ($latestCheckout->checkid->currency_type_price ?: $usdRate);
                }
                $checkoutPrice = App\Models\Currency::toUzs(
                    $checkoutRawPrice,
                    $checkoutCurrency,
                    $checkoutRate
                );

                $stock = (float) $item->stock;
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
                <td>{{ round($checkoutPrice, 2) }}</td>
                <td>{{ $markup !== null ? round($markup, 2) . ' %' : '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
