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
                // Valyuta hujjat sarlavhasida tanlanadi. Eski detail qatorlarida
                // noto'g'ri standart qiymat bo'lishi mumkin, shu sabab header ustun.
                $checkinDocument = optional($latestCheckin)->checkid;
                $checkinFallbackRate = App\Models\Currency::usdRateForDate(
                    optional($checkinDocument)->date ?? optional($latestCheckin)->created_at
                );
                $checkinPrice = App\Models\Currency::documentAmountToUzs(
                    $checkinRawPrice,
                    optional($checkinDocument)->currency_type,
                    optional($checkinDocument)->currency_type_price ?: $checkinFallbackRate,
                    optional($latestCheckin)->currency_type,
                    optional($latestCheckin)->currency_type_price
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
                if ($latestCheckout) {
                    $checkoutDocument = $latestCheckout->checkid;
                    $checkoutFallbackRate = App\Models\Currency::usdRateForDate(
                        optional($checkoutDocument)->date ?? $latestCheckout->created_at
                    );
                    // Checkout sarlavhasi foydalanuvchi sotuvda tanlagan valyuta va
                    // o'sha kundagi kursni saqlaydi. Eski detail qatorlarida noto'g'ri
                    // currency_type uchragani uchun sarlavha doim birinchi olinadi.
                    $checkoutPrice = App\Models\Currency::saleUnitPriceToUzs(
                        $checkoutRawPrice,
                        $checkinPrice,
                        optional($checkoutDocument)->currency_type,
                        optional($checkoutDocument)->currency_type_price ?: $checkoutFallbackRate,
                        $latestCheckout->currency_type,
                        $latestCheckout->currency_type_price,
                        $checkoutFallbackRate
                    );
                } else {
                    // Ayrim eski mahsulotlarda ombor uchun oxirgi checkout qatori
                    // topilmaydi, mahsulot kartasidagi narx esa USD bo'lsa ham
                    // currency_type UZS bo'lib qolgan. Bunday holatda ham kirim
                    // tannarxiga nisbatan xavfsiz tekshiruv bilan USDni tiklaymiz.
                    $checkoutPrice = App\Models\Currency::saleUnitPriceToUzs(
                        $checkoutRawPrice,
                        $checkinPrice,
                        (int) ($item->productid->currency_type ?? 1),
                        $usdRate,
                        null,
                        null,
                        $usdRate
                    );
                }

                // Ikkala valyuta belgisi ham tarixda noto'g'ri UZS bo'lib qolgan
                // kirimlarni faqat iqtisodiy jihatdan mantiqli bo'lsa USDdan tiklaydi.
                $checkinPrice = App\Models\Currency::purchaseUnitPriceToUzs(
                    $checkinRawPrice,
                    $checkoutPrice,
                    optional($checkinDocument)->currency_type,
                    optional($checkinDocument)->currency_type_price ?: $checkinFallbackRate,
                    $latestCheckin->currency_type ?? null,
                    $latestCheckin->currency_type_price ?? null,
                    $checkinFallbackRate
                );

                // Kirim tiklangach, sotuvning legacy USD tekshiruvini yakuniy
                // kirim narxiga nisbatan yana bir marta aniq hisoblaymiz.
                if ($latestCheckout) {
                    $checkoutPrice = App\Models\Currency::saleUnitPriceToUzs(
                        $checkoutRawPrice,
                        $checkinPrice,
                        optional($checkoutDocument)->currency_type,
                        optional($checkoutDocument)->currency_type_price ?: $checkoutFallbackRate,
                        $latestCheckout->currency_type,
                        $latestCheckout->currency_type_price,
                        $checkoutFallbackRate
                    );
                }

                // Eski bazada ba'zan kirim yoki sotuv valyutasi teskarisiga
                // yozilgan. Natija -90% yoki juda katta bo'lsagina ikkala narx
                // tarixiy kurslari bilan birgalikda xavfsiz qayta tekshiriladi.
                [$checkinPrice, $checkoutPrice] = App\Models\Currency::reconcileLegacyUnitPrices(
                    $checkinRawPrice,
                    $checkoutRawPrice,
                    $checkinPrice,
                    $checkoutPrice,
                    $checkinFallbackRate,
                    $latestCheckout ? $checkoutFallbackRate : $usdRate
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
