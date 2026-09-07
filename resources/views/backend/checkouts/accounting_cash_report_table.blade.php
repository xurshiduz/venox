@php
    $schemeLabels = ['' => 'Belgilanmagan', 'special' => 'Spes', 'contract' => 'Shartnoma', 'venox_bonus' => 'Venox bonus'];
@endphp

<div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
        <thead class="table-light">
            <tr>
                <th>№</th><th>Sana</th><th>Agent</th><th style="min-width:260px">Tovar</th><th>Klient</th>
                <th>Bonus / bez bonus</th><th>Prihod summa (USD)</th><th>Summa USD</th><th>KPI</th>
                <th>Fiksa agent</th><th>Venox bonus kassa</th><th>Zavod kassa</th><th>Amal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                @php
                    $rowProducts = collect($row['products']);
                    $previewProducts = $rowProducts->take(2);
                    $productModalId = 'cash-products-' . ($row['receipt_id'] ?? $loop->index) . '-' . $loop->index;
                    $checkoutCodes = collect(explode(',', (string) ($row['checkout_code'] ?? '')))->map(fn ($code) => trim($code))->filter()->values();
                    $checkoutModalId = 'cash-checkouts-' . ($row['receipt_id'] ?? $loop->index) . '-' . $loop->index;
                @endphp
                <tr>
                    <td>{{ $startNumber + $loop->index }}</td>
                    <td>{{ \Carbon\Carbon::parse($row['date'])->format('d.m.Y') }}</td>
                    <td>{{ $row['agent'] }}</td>
                    <td>
                        @forelse($previewProducts as $product)
                            <div class="text-truncate" style="max-width:310px" title="{{ $product['name'] }}">
                                {{ $product['name'] }} — <b>{{ number_format($product['qty'], 3, '.', ' ') }} {{ $product['unit'] }}</b>
                            </div>
                        @empty
                            <span class="text-danger">To‘lovga mos tovar qolmagan</span>
                        @endforelse

                        @if($rowProducts->isNotEmpty())
                            <button type="button" class="btn btn-sm btn-outline-primary mt-1"
                                    data-bs-toggle="modal" data-bs-target="#{{ $productModalId }}"
                                    title="Barcha tovarlarni ko‘rish">
                                <em class="icon ni ni-eye"></em>
                                <span>{{ $rowProducts->count() }} ta tovar</span>
                            </button>
                        @endif
                    </td>
                    <td>{{ $row['client'] }}</td>
                    <td>{{ $schemeLabels[$row['scheme']] ?? $row['scheme'] }}</td>
                    <td>{{ number_format($row['purchase_cost_usd'], 2, '.', ' ') }}</td>
                    <td><b>{{ number_format($row['payment_usd'], 2, '.', ' ') }}</b></td>
                    <td>{{ number_format($row['kpi'], 2, '.', ' ') }} <small>({{ $row['kpi_percent'] }}%)</small></td>
                    <td>{{ number_format($row['agent_amount'], 2, '.', ' ') }} <small>({{ $row['agent_percent'] }}%)</small></td>
                    <td>{{ number_format($row['venox'], 2, '.', ' ') }} <small>({{ $row['venox_percent'] }}%)</small></td>
                    <td>{{ number_format($row['factory'], 2, '.', ' ') }}</td>
                    <td>
                        @if($checkoutCodes->count() === 1)
                            <a class="btn btn-sm btn-warning" href="{{ route('checkout_form', ['id' => $checkoutCodes->first(), 'page' => 1]) }}">Foizlarni kiritish</a>
                        @elseif($checkoutCodes->count() > 1)
                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#{{ $checkoutModalId }}">Prodajani tanlash</button>
                        @else
                            <span class="text-muted">Savdo topilmadi</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="13" class="text-center py-5 text-soft">Tanlangan filtr bo‘yicha ma’lumot yo‘q.</td></tr>
            @endforelse
        </tbody>
        @php $reportTotals = $totals ?? $rows; @endphp
        @if($rows->isNotEmpty())
            <tfoot class="table-light">
                <tr class="fw-bold">
                    <td colspan="6" class="text-end">Umumiy jami:</td>
                    <td>{{ number_format($reportTotals->sum('purchase_cost_usd'), 2, '.', ' ') }}</td>
                    <td>{{ number_format($reportTotals->sum('payment_usd'), 2, '.', ' ') }}</td>
                    <td>{{ number_format($reportTotals->sum('kpi'), 2, '.', ' ') }}</td>
                    <td>{{ number_format($reportTotals->sum('agent_amount'), 2, '.', ' ') }}</td>
                    <td>{{ number_format($reportTotals->sum('venox'), 2, '.', ' ') }}</td>
                    <td>{{ number_format($reportTotals->sum('factory'), 2, '.', ' ') }}</td><td></td>
                </tr>
            </tfoot>
        @endif
    </table>
</div>

@foreach($rows as $row)
    @php
        $rowProducts = collect($row['products']);
        $productModalId = 'cash-products-' . ($row['receipt_id'] ?? $loop->index) . '-' . $loop->index;
        $checkoutCodes = collect(explode(',', (string) ($row['checkout_code'] ?? '')))->map(fn ($code) => trim($code))->filter()->values();
        $checkoutModalId = 'cash-checkouts-' . ($row['receipt_id'] ?? $loop->index) . '-' . $loop->index;
    @endphp
    @if($rowProducts->isNotEmpty())
        <div class="modal fade" tabindex="-1" id="{{ $productModalId }}">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title mb-1">Tovarlar ro‘yxati</h5>
                            <div class="text-soft">{{ $row['client'] }} · {{ \Carbon\Carbon::parse($row['date'])->format('d.m.Y') }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Yopish"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped mb-0">
                                <thead class="table-light"><tr><th style="width:55px">№</th><th>Tovar nomi</th><th class="text-end">Miqdori</th></tr></thead>
                                <tbody>
                                    @foreach($rowProducts as $product)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $product['name'] }}</td>
                                            <td class="text-end"><b>{{ number_format($product['qty'], 3, '.', ' ') }} {{ $product['unit'] }}</b></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Yopish</button></div>
                </div>
            </div>
        </div>
    @endif

    @if($checkoutCodes->count() > 1)
        <div class="modal fade" tabindex="-1" id="{{ $checkoutModalId }}">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <div><h5 class="modal-title mb-1">Prodajani tanlang</h5><div class="text-soft">Bu to‘lov bir nechta prodajaga taqsimlangan</div></div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Yopish"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-grid" style="gap:8px">
                            @foreach($checkoutCodes as $checkoutCode)
                                <a class="btn btn-outline-primary text-start" href="{{ route('checkout_form', ['id' => $checkoutCode, 'page' => 1]) }}">Prodaja: {{ $checkoutCode }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

@endforeach
