<table>
    <tbody>
        <tr><td colspan="6"></td></tr>
        <tr>
            <td style="color: #6A2813" colspan="6"><b>Остатки товара по складу {{ $wareid->name }}</b></td>
        </tr>
        <tr>
            <td style="color: #6A2813" colspan="6"><b>на конец {{ Carbon\Carbon::now()->format('d.m.y') }}г.</b></td>
        </tr>
        <tr><td colspan="6"></td></tr>
        
        <tr>
            <td style="background-color: #00FFFF; border: 1px solid #000000;">Код</td>
            <td style="background-color: #00FFFF; border: 1px solid #000000;">ТМЦ</td>
            <td style="background-color: #00FFFF; border: 1px solid #000000;">Ед. изм</td>
            <td style="background-color: #00FFFF; border: 1px solid #000000;">Остаток</td>
            <td style="background-color: #00FFFF; border: 1px solid #000000;">Кirim narxi</td>
            <td style="background-color: #00FFFF; border: 1px solid #000000;">Кirim summasi</td>
        </tr>
        
        @php
            $stocks = App\Models\WarehouseStock::where('warehouse_id', $wareid->id)
                        ->with(['productid.unitid', 'productid.checkindetails'])
                        ->get();
            $totalSum = 0; // Jami summani hisoblash uchun
        @endphp

        @foreach($stocks as $item)
            @if($item->productid)
                @php
                    // Eng oxirgi kirim narxini olish (yoki o'rtacha narx ham qo'shishingiz mumkin)
                    $lastPrice = $item->productid->checkindetails()->latest()->first()->price ?? 0;
                    $rowSum = $lastPrice * $item->stock;
                    $totalSum += $rowSum;
                @endphp
                <tr>
                    <td style="border: 1px solid #000000;">{{ $item->productid->barcode }} </td>
                    <td style="border: 1px solid #000000;">{{ $item->productid->name }} </td>
                    <td style="border: 1px solid #000000;">{{ $item->productid->unitid->name ?? '' }}</td>
                    <td style="border: 1px solid #000000;">{{ $item->stock }}</td>
                    <td style="border: 1px solid #000000;">{{ number_format($lastPrice, 2, '.', ' ') }}</td>
                    <td style="border: 1px solid #000000;">{{ number_format($rowSum, 2, '.', ' ') }}</td>
                </tr>
            @endif
        @endforeach

        {{-- Jami qatori --}}
        <tr>
            <td colspan="5" style="border: 1px solid #000000; text-align: right;"><b>Jami:</b></td>
            <td style="border: 1px solid #000000;"><b>{{ number_format($totalSum, 2, '.', ' ') }}</b></td>
        </tr>
    </tbody>
</table>