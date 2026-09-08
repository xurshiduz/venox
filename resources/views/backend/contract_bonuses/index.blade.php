@extends('layouts.backend')
@section('content')
<div class="nk-content">
    <div class="container-fluid">
        <div class="card card-bordered">
            <div class="card-inner">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                    <div><h5 class="title mb-1">Shartnoma jamg‘armasi</h5><div class="text-soft">Mijozlar bo‘yicha yig‘ilgan bonus, ishlatilgan summa va qoldiq.</div></div>
                    <form method="get" class="d-flex" style="gap:8px">
                        <input class="form-control" name="search" value="{{ $search }}" placeholder="Mijoz nomi bo‘yicha qidirish">
                        <button class="btn btn-primary">Qidirish</button>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead><tr><th>№</th><th>Mijoz</th><th>Yig‘ilgan</th><th>Ishlatilgan</th><th>Mavjud bonus</th><th>Jami qarzi</th><th>Amal</th></tr></thead>
                        <tbody>
                        @forelse($clients as $client)
                            <tr>
                                <td>{{ $clients->firstItem() + $loop->index }}</td>
                                <td><b>{{ $client->name }}</b></td>
                                <td>${{ number_format((float)$client->contract_credit_usd, 2, '.', ' ') }}</td>
                                <td>${{ number_format((float)$client->contract_debit_usd, 2, '.', ' ') }}</td>
                                <td class="text-success"><b>${{ number_format($client->contract_balance_usd, 2, '.', ' ') }}</b></td>
                                <td class="text-danger">${{ number_format($client->debt_usd, 2, '.', ' ') }}</td>
                                <td><a class="btn btn-sm btn-primary" href="{{ route('contract_bonuses.show', $client) }}">Ko‘rish / ishlatish</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-soft py-4">Shartnoma bonusi mavjud mijoz topilmadi.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $clients->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
