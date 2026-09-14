@extends('layouts.backend')
@section('content')
<div class="nk-content"><div class="container-fluid"><div class="card"><div class="card-inner">
<h5>LIDAZga qaytarish — {{ $checkin->reference }}</h5>
<form method="POST" action="{{ route('supplier_return_store', $checkin->code) }}">@csrf
<div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Mahsulot</th><th>Kirim</th><th>Qaytariladigan miqdor</th></tr></thead><tbody>
@foreach($checkin->details as $detail)<tr><td>{{ $detail->prodid?->name }}</td><td>{{ $detail->qty }}</td><td><input class="form-control" type="number" min="0" max="{{ $detail->qty }}" step="0.01" name="qty[{{ $detail->id }}]" value="0"></td></tr>@endforeach
</tbody></table></div><button class="btn btn-danger" onclick="return confirm('LIDAZga qaytarish so‘rovini yuborasizmi?')">So‘rov yuborish</button>
</form></div></div></div></div>
@endsection
