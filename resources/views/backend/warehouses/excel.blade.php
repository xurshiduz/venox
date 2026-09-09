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
                    ? (((float) $latestCheckin->qty > 0 && (float) $latestCheckin->total_price > 0)
                        ? (float) $latestCheckin->total_price / (float) $latestCheckin->qty
                        : (float) $latestCheckin->price)
                    : (float) $item->checkin_price;
                // Valyuta hujjat sarlavhasida tanlanadi. Eski detail qatorlarida
                // noto'g'ri standart qiymat bo'lishi mumkin, shu sabab header ustun.
                $checkinDocument = optional($latestCheckin)->checkid;
                $checkinFallbackRate = App\Models\Currency::usdRateForDate(
                    optional($checkinDocument)->date ?? optional($latestCheckin)->created_at
                );
                // Sotuv narxi faqat USDda rasmiylashtirilgan, yakunlangan sotuvdan olinadi.
                // UZS sotuvlar bu hisobotga aralashmaydi.
                $latestCheckout = $item->productid->checkoutdetails()
                    ->with('checkid')
                    ->where('status', 1)
                    ->where('price', '>', 0)
                    ->whereHas('checkid', function ($query) {
                        $query->where('status', 1)
                            ->where('currency_type', 1);
                    })
                    ->latest('created_at')
                    ->latest('id')
                    ->first();

                $checkoutRawPrice = $latestCheckout
                    ? (((float) $latestCheckout->qty > 0 && (float) $latestCheckout->total_price > 0)
                        ? (float) $latestCheckout->total_price / (float) $latestCheckout->qty
                        : (float) $latestCheckout->price)
                    : 0;
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
                }

                // Kirim narxi faqat kirim hujjatining valyutasi va o'sha
                // hujjatda saqlangan tarixiy kurs asosida UZSga o'tkaziladi.
                $checkinPrice = App\Models\Currency::documentAmountToUzs(
                    $checkinRawPrice,
                    optional($checkinDocument)->currency_type,
                    optional($checkinDocument)->currency_type_price ?: $checkinFallbackRate,
                    $latestCheckin->currency_type ?? null,
                    $latestCheckin->currency_type_price ?? null
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
