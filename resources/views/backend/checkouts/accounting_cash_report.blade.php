@extends('layouts.backend')
@section('content')
<div class="nk-content"><div class="container-fluid"><div class="card"><div class="card-inner">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap:12px">
        <div><h5 class="title mb-1">Kassa hisoboti</h5><div class="text-soft">To‘lovlar mahsulotlarga ketma-ket taqsimlangan. 1 USD = {{ number_format($usdRate, 2, '.', ' ') }} UZS</div></div>
        <div class="d-flex" style="gap:8px">
            <a class="btn btn-success" href="{{ route('accounting_cash_report_excel', request()->query()) }}"><em class="icon ni ni-file-xls"></em><span>Excel</span></a>
            <a class="btn btn-danger" target="_blank" href="{{ route('accounting_cash_report_pdf', request()->query()) }}"><em class="icon ni ni-file-pdf"></em><span>PDF</span></a>
        </div>
    </div>
    <form method="GET" class="row gy-2 gx-2 align-items-end mb-4">
        <div class="col-md-2"><label class="form-label">Oy dan</label><input type="month" name="from_month" value="{{ $filters['from_month'] }}" class="form-control"></div>
        <div class="col-md-2"><label class="form-label">Oy gacha</label><input type="month" name="to_month" value="{{ $filters['to_month'] }}" class="form-control"></div>
        <div class="col-md-3"><label class="form-label">Tovar</label><select name="product_id" class="form-select"><option value="">Barcha tovarlar</option>@foreach($products as $product)<option value="{{ $product->id }}" @if((string)$filters['product_id']===(string)$product->id) selected @endif>{{ $product->name }}</option>@endforeach</select></div>
        <div class="col-md-2"><label class="form-label">Bonus turi</label><select name="scheme" class="form-select"><option value="">Barchasi</option><option value="special" @if($filters['scheme']==='special') selected @endif>Spes</option><option value="contract" @if($filters['scheme']==='contract') selected @endif>Shartnoma</option><option value="venox_bonus" @if($filters['scheme']==='venox_bonus') selected @endif>Venox bonus</option></select></div>
        <div class="col-md-3 d-flex" style="gap:8px"><button class="btn btn-primary flex-grow-1">Ko‘rsatish</button><a href="{{ route('accounting_cash_report') }}" class="btn btn-light">Tozalash</a></div>
    </form>
    @include('backend.checkouts.accounting_cash_report_table', ['rows' => collect($rows->items()), 'startNumber' => $rows->firstItem() ?: 1])
    <div class="mt-3">{{ $rows->links() }}</div>
</div></div></div></div>
@endsection
