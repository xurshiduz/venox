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
            <th>Kirim narxi</th>
            <th>Kirim summasi</th>
            <th>Sotuv narxi</th>
            <th>Sotuv summasi</th>
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

            $usdRate = (float) (App\Models\Currency::where('type_id', 1)
                ->latest('id')
                ->value('price') ?? 1);
        @endphp

        @foreach($stocks as $item)
            @php
                $checkinPrice = (float) $item->checkin_price;

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

                if ((int) $item->productid->currency_type === 1) {
                    $checkoutPrice *= $usdRate;
                }

                $stock = (float) $item->stock;
                $checkinTotal = $checkinPrice * $stock;
                $checkoutTotal = $checkoutPrice * $stock;
                $markup = $checkinPrice > 0 && $checkoutPrice > 0
                    ? (($checkoutPrice - $checkinPrice) / $checkinPrice) * 100
                    : null;
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
