@extends('layouts.backend')

@section('css')
<style>
    .reorder-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 16px;
    }
    .reorder-summary-item {
        padding: 14px 16px;
        border: 1px solid var(--ui-border);
        border-radius: 10px;
        background: var(--ui-surface);
    }
    .reorder-summary-item strong {
        display: block;
        margin-top: 3px;
        color: var(--ui-text);
        font-size: 18px;
    }
    .reorder-table { min-width: 1380px; }
    .reorder-product { min-width: 260px; }
    .reorder-comment { min-width: 390px; white-space: normal !important; }
    .reorder-urgent { color: #c63f36; font-weight: 800; }
    @media (max-width: 767.98px) {
        .reorder-summary { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('content')
<div class="nk-content">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="nk-block nk-block-lg">
                    <div class="nk-block-head">
                        <div class="nk-block-head-content">
                            <h4 class="nk-block-title">{{ trans('backend.ui.reorder_requests') }}</h4>
                            <div class="nk-block-des text-muted">{{ trans('backend.ui.reorder_description') }}</div>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('reorder_requests.index') }}" class="card card-bordered dashboard-filter mb-3">
                        <div class="card-inner">
                            <div class="row g-2 align-items-end">
                                <div class="col-xl-3 col-md-6">
                                    <label class="form-label">{{ trans('backend.ui.search') }}</label>
                                    <input type="search" name="search" value="{{ $keyword }}" class="form-control" placeholder="{{ trans('backend.ui.reorder_search_hint') }}">
                                </div>
                                <div class="col-xl-3 col-md-6">
                                    <label class="form-label">{{ trans('backend.ui.warehouse') }}</label>
                                    <select name="warehouse_id" class="form-select">
                                        <option value="">{{ trans('backend.ui.all_warehouses') }}</option>
                                        @foreach($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}" {{ $warehouseId === (int) $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-xl-2 col-md-4">
                                    <label class="form-label">{{ trans('backend.ui.remaining_limit') }}</label>
                                    <input type="number" min="1" max="50" step="1" name="remaining_percent" value="{{ $remainingPercent }}" class="form-control">
                                </div>
                                <div class="col-xl-2 col-md-4">
                                    <label class="form-label">{{ trans('backend.ui.sales_period_days') }}</label>
                                    <input type="number" min="1" max="365" name="max_days" value="{{ $maxDays }}" class="form-control">
                                </div>
                                <div class="col-xl-2 col-md-4">
                                    <label class="form-label">{{ trans('backend.ui.minimum_incoming') }}</label>
                                    <input type="number" min="1" step="0.01" name="min_received_qty" value="{{ $minReceivedQty }}" class="form-control">
                                </div>
                                <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                    <a href="{{ route('reorder_requests.index') }}" class="btn btn-light">{{ trans('backend.ui.clear') }}</a>
                                    <button type="submit" class="btn btn-primary">{{ trans('backend.ui.show_requests') }}</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="reorder-summary">
                        <div class="reorder-summary-item">
                            <span class="text-muted">{{ trans('backend.ui.request_count') }}</span>
                            <strong>{{ number_format($requests->total(), 0, '.', ' ') }}</strong>
                        </div>
                        <div class="reorder-summary-item">
                            <span class="text-muted">{{ trans('backend.ui.active_rule') }}</span>
                            <strong>≤ {{ number_format($remainingPercent, 0) }}%</strong>
                        </div>
                        <div class="reorder-summary-item">
                            <span class="text-muted">{{ trans('backend.ui.period') }}</span>
                            <strong>{{ $maxDays }} {{ trans('backend.ui.days') }}</strong>
                        </div>
                    </div>

                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-bordered reorder-table">
                                <thead>
                                    <tr class="text-center">
                                        <th>#</th>
                                        <th class="reorder-product">{{ trans('backend.ui.product') }}</th>
                                        <th>{{ trans('backend.ui.barcode') }}</th>
                                        <th>{{ trans('backend.ui.warehouse') }}</th>
                                        <th>{{ trans('backend.ui.incoming_date') }}</th>
                                        <th>{{ trans('backend.ui.incoming_qty') }}</th>
                                        <th>{{ trans('backend.ui.elapsed_days') }}</th>
                                        <th>{{ trans('backend.ui.sold_qty') }}</th>
                                        <th>{{ trans('backend.ui.remaining_qty') }}</th>
                                        <th>{{ trans('backend.ui.remaining_percent') }}</th>
                                        <th>{{ trans('backend.ui.recommended_qty') }}</th>
                                        <th class="reorder-comment">{{ trans('backend.ui.comment') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($requests as $item)
                                        <tr>
                                            <td class="text-center">{{ $requests->firstItem() + $loop->index }}</td>
                                            <td class="reorder-product"><strong>{{ $item->product_name }}</strong></td>
                                            <td class="text-nowrap">{{ $item->barcode ?: '—' }}</td>
                                            <td>{{ $item->warehouse_name }}</td>
                                            <td class="text-nowrap">{{ \Carbon\Carbon::parse($item->received_at)->format('d.m.Y') }}</td>
                                            <td class="text-right">{{ number_format($item->received_qty, 2, '.', ' ') }} {{ $item->unit_name }}</td>
                                            <td class="text-center">{{ $item->days_elapsed }}</td>
                                            <td class="text-right">{{ number_format($item->sold_qty, 2, '.', ' ') }}</td>
                                            <td class="text-right reorder-urgent">{{ number_format($item->remaining_qty, 2, '.', ' ') }}</td>
                                            <td class="text-center reorder-urgent">{{ number_format($item->remaining_percent, 1) }}%</td>
                                            <td class="text-right"><strong>{{ number_format($item->recommended_qty, 2, '.', ' ') }}</strong></td>
                                            <td class="reorder-comment">{{ $item->comment }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="12" class="text-center py-4 text-muted">{{ trans('backend.ui.no_reorder_requests') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @include('backend.nav', ['data' => $requests])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
