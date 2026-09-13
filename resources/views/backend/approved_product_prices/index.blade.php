@extends('layouts.backend')

@section('content')
<div class="nk-content">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="nk-block-head nk-block-head-sm">
                    <div class="nk-block-between g-3">
                        <div class="nk-block-head-content">
                            <h3 class="nk-block-title page-title">BOSS tasdiqlagan narxlar</h3>
                            <div class="nk-block-des text-soft">
                                <p>Sotuv va zavod narxlari UZSda. Excel hisobida 1 USD = {{ number_format($usdRate, 2, '.', ' ') }} UZS.</p>
                            </div>
                        </div>
                        <div class="nk-block-head-content">
                            <a href="{{ route('home') }}" class="btn btn-outline-light bg-white">
                                <em class="icon ni ni-arrow-left"></em><span>Bosh sahifa</span>
                            </a>
                        </div>
                    </div>
                </div>

                @include('layouts.message.success')
                @include('layouts.message.error')

                <div class="card card-bordered mb-4">
                    <div class="card-inner">
                        <form method="GET" action="{{ route('approved_product_prices.index') }}" class="row g-2 align-items-end">
                            <div class="col-md-9">
                                <label class="form-label">Qidirish</label>
                                <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Masalan: 5W-30, ATF yoki antifreeze">
                            </div>
                            <div class="col-md-3 d-flex" style="gap:8px">
                                <button class="btn btn-primary flex-grow-1" type="submit">Qidirish</button>
                                @if($search !== '')
                                    <a href="{{ route('approved_product_prices.index') }}" class="btn btn-light">Tozalash</a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card card-bordered">
                    <div class="card-inner border-bottom">
                        <h5 class="card-title mb-1">Amaldagi narxlar</h5>
                        <div class="text-soft">Saqlangan yangi qiymatlar keyingi Excel eksportlariga darhol qo‘llanadi.</div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead>
                                <tr class="text-center">
                                    <th style="width:55px">№</th>
                                    <th style="min-width:240px">Mahsulot turi</th>
                                    <th style="min-width:150px">Moslik kalitlari</th>
                                    <th style="min-width:155px">Sotuv narxi (UZS)</th>
                                    <th style="min-width:115px">Sotuv (USD)</th>
                                    <th style="min-width:155px">Zavod narxi (UZS)</th>
                                    <th style="min-width:115px">Zavod (USD)</th>
                                    <th style="min-width:105px">Ustama</th>
                                    <th style="width:120px">Amal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($prices as $index => $price)
                                    @php
                                        $saleUsd = $price->sale_price_uzs !== null ? (float) $price->sale_price_uzs / $usdRate : null;
                                        $factoryUsd = $price->factory_price_uzs !== null ? (float) $price->factory_price_uzs / $usdRate : null;
                                        $markup = $saleUsd && $factoryUsd ? (($saleUsd - $factoryUsd) / $factoryUsd) * 100 : null;
                                        $formId = 'approved-price-' . $price->id;
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $price->name }}</strong>
                                            <div class="text-soft small">{{ $price->code }}</div>
                                        </td>
                                        <td>{{ collect($price->match_tokens)->implode(' + ') }}</td>
                                        <td>
                                            <input form="{{ $formId }}" type="number" min="0" step="0.01" name="sale_price_uzs"
                                                value="{{ $price->sale_price_uzs !== null ? number_format((float) $price->sale_price_uzs, 2, '.', '') : '' }}"
                                                class="form-control" placeholder="Kiritilmagan">
                                        </td>
                                        <td class="text-end">{{ $saleUsd !== null ? number_format($saleUsd, 2, '.', ' ') : '—' }}</td>
                                        <td>
                                            <input form="{{ $formId }}" type="number" min="0" step="0.01" name="factory_price_uzs"
                                                value="{{ $price->factory_price_uzs !== null ? number_format((float) $price->factory_price_uzs, 2, '.', '') : '' }}"
                                                class="form-control" placeholder="Kiritilmagan">
                                        </td>
                                        <td class="text-end">{{ $factoryUsd !== null ? number_format($factoryUsd, 2, '.', ' ') : '—' }}</td>
                                        <td class="text-end {{ $markup !== null && $markup < 0 ? 'text-danger' : '' }}">
                                            {{ $markup !== null ? number_format($markup, 2, '.', ' ') . '%' : '—' }}
                                        </td>
                                        <td class="text-center">
                                            <form id="{{ $formId }}" method="POST" action="{{ route('approved_product_prices.update', $price) }}">
                                                @csrf
                                                @method('PUT')
                                            </form>
                                            <button form="{{ $formId }}" type="submit" class="btn btn-sm btn-primary">Saqlash</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="text-center py-5 text-soft">Narx topilmadi</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card card-bordered mt-4">
                    <div class="card-inner border-bottom">
                        <h5 class="card-title mb-1">O‘zgarishlar tarixi</h5>
                        <div class="text-soft">Har bir tahrirning eski va yangi qiymati, foydalanuvchi va vaqti saqlanadi.</div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead>
                                <tr class="text-center">
                                    <th>Sana</th>
                                    <th>Mahsulot turi</th>
                                    <th>Sotuv narxi: eski → yangi</th>
                                    <th>Zavod narxi: eski → yangi</th>
                                    <th>Kim o‘zgartirdi</th>
                                    <th>IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($audits as $audit)
                                    <tr>
                                        <td class="text-nowrap">{{ optional($audit->created_at)->format('Y-m-d H:i:s') }}</td>
                                        <td>{{ $audit->price_name }}</td>
                                        <td class="text-center">
                                            {{ $audit->old_sale_price_uzs !== null ? number_format((float) $audit->old_sale_price_uzs, 2, '.', ' ') : '—' }}
                                            →
                                            {{ $audit->new_sale_price_uzs !== null ? number_format((float) $audit->new_sale_price_uzs, 2, '.', ' ') : '—' }}
                                        </td>
                                        <td class="text-center">
                                            {{ $audit->old_factory_price_uzs !== null ? number_format((float) $audit->old_factory_price_uzs, 2, '.', ' ') : '—' }}
                                            →
                                            {{ $audit->new_factory_price_uzs !== null ? number_format((float) $audit->new_factory_price_uzs, 2, '.', ' ') : '—' }}
                                        </td>
                                        <td>{{ $audit->user_name ?: optional($audit->user)->name ?: '—' }}</td>
                                        <td>{{ $audit->ip_address ?: '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-5 text-soft">Hali o‘zgarish qilinmagan</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($audits->hasPages())
                        <div class="card-inner">{{ $audits->links('pagination::bootstrap-4') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
