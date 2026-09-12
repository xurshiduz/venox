@extends('layouts.backend')
@section('content')
<div class="nk-content">
 <div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
   <div><h4 class="mb-1">{{ $client->name }}</h4><div class="text-soft">Shartnoma bonusi va foydalanish tarixi</div></div>
   <a href="{{ route('contract_bonuses.index') }}" class="btn btn-light">Orqaga</a>
  </div>
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
  <div class="row g-gs mb-4">
   <div class="col-md-4"><div class="card card-bordered"><div class="card-inner"><div class="text-soft">Mavjud Shartnoma bonusi</div><h3 class="text-success mt-1">${{ number_format($balance, 2, '.', ' ') }}</h3></div></div></div>
   <div class="col-md-4"><div class="card card-bordered"><div class="card-inner"><div class="text-soft">Mijozning jami qarzi</div><h3 class="text-danger mt-1">${{ number_format($debtUsd, 2, '.', ' ') }}</h3></div></div></div>
  </div>
  @hasanyrole('admin|cashier')
  <div class="card card-bordered mb-4"><div class="card-inner">
   <h5 class="mb-3">Bonusni ishlatish</h5>
   @if($balance < 0.01)
    <div class="alert alert-info mb-0">
     Bu mijozda foydalanish uchun Shartnoma bonusi mavjud emas.
    </div>
   @else
   <form method="post" action="{{ route('contract_bonuses.redeem', $client) }}" class="row g-3">@csrf
    <div class="col-md-3"><label class="form-label">Qanday ishlatiladi</label><select class="form-select" name="type" required><option value="gift">Sovg‘aga aylantirish</option><option value="cash">Naqd pul berish</option><option value="debt_offset">Qarzidan ayirish</option></select></div>
    <div class="col-md-3"><label class="form-label">Summa (USD)</label><input type="number" step="0.01" min="0.01" max="{{ $balance }}" name="amount_usd" class="form-control" required></div>
    <div class="col-md-4"><label class="form-label">Izoh</label><input name="note" class="form-control" maxlength="500" placeholder="Sovg‘a, to‘lov yoki qarz bo‘yicha izoh"></div>
    <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100" onclick="return confirm('Ushbu amalni tasdiqlaysizmi?')">Tasdiqlash</button></div>
   </form>
   <div class="text-soft mt-2">Qarzdan ayirilsa, summa eng eski qarzlardan boshlab avtomatik yopiladi.</div>
   @endif
  </div></div>
  @endhasanyrole
  <div class="card card-bordered"><div class="card-inner">
   <h5 class="mb-3">Harakatlar tarixi</h5><div class="table-responsive"><table class="table table-bordered align-middle">
    <thead><tr><th>Sana</th><th>Harakat</th><th>Kirim</th><th>Chiqim</th><th>Manba / izoh</th><th>Kim bajardi</th></tr></thead><tbody>
    @forelse($transactions as $transaction)
     @php($labels=['accrual'=>'Shartnomadan yig‘ildi','gift'=>'Sovg‘aga aylantirildi','cash'=>'Naqd berildi','debt_offset'=>'Qarzdan ayirildi'])
     <tr><td>{{ optional($transaction->transaction_date)->format('d.m.Y') }}</td><td>{{ $labels[$transaction->type] ?? $transaction->type }}</td>
      <td class="text-success">{{ $transaction->direction==='credit' ? '$'.number_format($transaction->amount_usd,2,'.',' ') : '—' }}</td>
      <td class="text-danger">{{ $transaction->direction==='debit' ? '$'.number_format($transaction->amount_usd,2,'.',' ') : '—' }}</td>
      <td>@if($transaction->checkout)Hujjat: {{ $transaction->checkout->number_work ?: $transaction->checkout->code }}<br>@endif{{ $transaction->note ?: '—' }}</td>
      <td>{{ optional($transaction->user)->name ?: 'Avtomatik' }}</td></tr>
    @empty<tr><td colspan="6" class="text-center text-soft">Tarix mavjud emas.</td></tr>@endforelse
    </tbody></table></div>{{ $transactions->links() }}
  </div></div>
 </div>
</div>
@endsection
