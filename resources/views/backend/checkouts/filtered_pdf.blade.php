<!doctype html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <title>Sotuvlar hisoboti</title>
    <style>
        @page { margin: 10mm; }
        body { font-family: "DejaVu Sans", sans-serif; font-size: 9px; color: #111; }
        h2 { margin: 0 0 5px; text-align: center; }
        .filters { margin-bottom: 10px; text-align: center; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #555; padding: 4px; vertical-align: top; }
        th { background: #e9ecef; text-align: center; }
        .number { text-align: right; white-space: nowrap; }
        .center { text-align: center; }
        .summary { margin-top: 10px; }
        .summary td { font-weight: bold; }
        .pdf-status { padding: 12px; margin-bottom: 10px; background: #eef6ff; text-align: center; }
    </style>
</head>
<body>
@if($clientSidePdf ?? false)
    <div id="pdf-status" class="pdf-status">PDF tayyorlanmoqda, iltimos kuting...</div>
@endif
<div id="pdf-report">
    <h2>Sotuvlar hisoboti</h2>
    <div class="filters">
        Agent: <b>{{ $selectedAgentName ?: 'Barcha agentlar' }}</b>
        &nbsp; | &nbsp;
        Sana: <b>{{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d.m.Y') : 'Boshidan' }}</b>
        — <b>{{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d.m.Y') : 'Hozirgacha' }}</b>
        &nbsp; | &nbsp;
        Sotuvlar soni: <b>{{ $data->count() }}</b>
    </div>

    <table>
        <thead>
            <tr>
                <th>№</th>
                <th>Sana</th>
                <th>Shartnoma</th>
                <th>Mijoz</th>
                <th>Agent</th>
                <th>Tovar turi</th>
                <th>Jami</th>
                <th>To‘langan</th>
                <th>Qarz</th>
                <th>Izoh</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $item)
                @php
                    $currency = optional($item->currencytypeid)->name;
                    $paid = (float) $item->payments->sum('price');
                @endphp
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td class="center">{{ \Carbon\Carbon::parse($item->date)->format('d.m.Y') }}</td>
                    <td>{{ $item->number_work ?: 'Qoralama #' . $item->id }}</td>
                    <td>{{ optional($item->supid)->name ?: '—' }}</td>
                    <td>{{ optional($item->managerid)->name ?: '—' }}</td>
                    <td class="center">{{ $item->details_count }}</td>
                    <td class="number">{{ number_format((float) $item->total_price, 2, '.', ' ') }} {{ $currency }}</td>
                    <td class="number">{{ number_format($paid, 2, '.', ' ') }} {{ $currency }}</td>
                    <td class="number">{{ number_format((float) $item->total_price_debt, 2, '.', ' ') }} {{ $currency }}</td>
                    <td>{{ $item->reference ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="10" class="center">Tanlangan filter bo‘yicha ma’lumot topilmadi.</td></tr>
            @endforelse
        </tbody>
    </table>

    @foreach($data->groupBy('currency_type') as $currencyRows)
        @php
            $currencyName = optional($currencyRows->first()->currencytypeid)->name;
            $totalPaid = $currencyRows->sum(fn ($checkout) => (float) $checkout->payments->sum('price'));
        @endphp
        <table class="summary">
            <tr>
                <td>{{ $currencyName ?: 'Valyuta' }} bo‘yicha jami</td>
                <td class="number">Sotuv: {{ number_format((float) $currencyRows->sum('total_price'), 2, '.', ' ') }}</td>
                <td class="number">To‘langan: {{ number_format($totalPaid, 2, '.', ' ') }}</td>
                <td class="number">Qarz: {{ number_format((float) $currencyRows->sum('total_price_debt'), 2, '.', ' ') }}</td>
            </tr>
        </table>
    @endforeach
</div>

@if($clientSidePdf ?? false)
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        window.addEventListener('load', function () {
            var status = document.getElementById('pdf-status');
            if (typeof html2pdf === 'undefined') {
                status.textContent = 'PDF moduli yuklanmadi. Brauzerning PDF saqlash oynasi ochilmoqda...';
                window.print();
                return;
            }

            html2pdf().set({
                margin: 5,
                filename: @json($filename),
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' },
                pagebreak: { mode: ['css', 'legacy'], avoid: ['tr'] }
            }).from(document.getElementById('pdf-report')).save().then(function () {
                status.textContent = 'PDF yuklandi. Bu oynani yopishingiz mumkin.';
            }).catch(function () {
                status.textContent = 'Avtomatik yuklashda xato. Brauzerning PDF saqlash oynasi ochilmoqda...';
                window.print();
            });
        });
    </script>
@endif
</body>
</html>
