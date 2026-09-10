@php
    $usdRate = App\Models\Currency::usdRate();
@endphp
<table>
    <tbody>
        <tr>
            <td></td>
            <th colspan="8">Ombor qoldig'i: {{ $wareid->name }}</th>
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
                $checkinQuery = $item->productid->checkindetails()
                    ->with('checkid')
                    ->where('status', 1)
                    // 1.00 bilan yozilgan eski inventar/ko'chirish qatorlari
                    // haqiqiy xarid tannarxi emas.
                    ->where('price', '>', 1)
                    ->whereHas('checkid', function ($query) {
                        $query->where('status', 1)->where('type_id', 1);
                    });

                $latestCheckin = (clone $checkinQuery)
                    ->where('warehouse_id', $wareid->id)
                    ->orderByDesc(App\Models\Checkin::select('date')
                        ->whereColumn('checkins.id', 'checkin_details.checkin_id')
                        ->limit(1))
                    ->latest('id')->first()
                    ?: (clone $checkinQuery)
                        ->orderByDesc(App\Models\Checkin::select('date')
                            ->whereColumn('checkins.id', 'checkin_details.checkin_id')
                            ->limit(1))
                        ->latest('id')->first();

                $checkinRawPrice = $latestCheckin
                    ? (((float) $latestCheckin->qty > 0 && (float) $latestCheckin->total_price > 0)
                        ? (float) $latestCheckin->total_price / (float) $latestCheckin->qty
                        : (float) $latestCheckin->price)
                    : (float) $item->checkin_price;
                $checkinDocument = optional($latestCheckin)->checkid;
                $checkinFallbackRate = App\Models\Currency::usdRateForDate(
                    optional($checkinDocument)->date ?? optional($latestCheckin)->created_at
                );
                $latestCheckout = $item->productid->checkoutdetails()
                    ->with('checkid')
                    ->where('status', 1)
                    ->where('price', '>', 0)
                    ->whereHas('checkid', function ($query) {
                        // Ombor marjasi uchun faqat USDda sotilgan narx olinadi.
                        $query->where('status', 1)->where('currency_type', 1);
                    })
                    ->orderByDesc(App\Models\Checkout::select('date')
                        ->whereColumn('checkouts.id', 'checkout_details.checkout_id')
                        ->limit(1))
                    ->latest('id')
                    ->first();

                $checkoutRawPrice = $latestCheckout
                    ? (((float) $latestCheckout->qty > 0 && (float) $latestCheckout->total_price > 0)
                        ? (float) $latestCheckout->total_price / (float) $latestCheckout->qty
                        : (float) $latestCheckout->price)
                    : 0;
                // Eski kirim sarlavhalarida valyuta noto'g'ri qolgan holatlar bor.
                // Qatorning o'z valyutasi haqiqiy narxni aniqroq ifodalaydi.
                $checkinPrice = App\Models\Currency::documentAmountToUzs(
                    $checkinRawPrice,
                    optional($latestCheckin)->currency_type ?? optional($checkinDocument)->currency_type,
                    optional($latestCheckin)->currency_type_price ?: $checkinFallbackRate,
                    optional($checkinDocument)->currency_type,
                    optional($checkinDocument)->currency_type_price
                );

                if ($latestCheckout) {
                    $checkoutDocument = $latestCheckout->checkid;
                    $checkoutFallbackRate = App\Models\Currency::usdRateForDate(
                        optional($checkoutDocument)->date ?? $latestCheckout->created_at
                    );
                    $checkoutPrice = App\Models\Currency::documentAmountToUzs(
                        $checkoutRawPrice,
                        optional($checkoutDocument)->currency_type,
                        optional($checkoutDocument)->currency_type_price ?: $checkoutFallbackRate,
                        $latestCheckout->currency_type,
                        $latestCheckout->currency_type_price
                    );
                } else {
                    $checkoutPrice = 0;
                    $checkoutFallbackRate = App\Models\Currency::usdRate();
                }

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
