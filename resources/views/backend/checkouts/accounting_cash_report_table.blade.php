@php $schemeLabels = [''=>'Belgilanmagan','special'=>'Spes','contract'=>'Shartnoma','venox_bonus'=>'Venox bonus']; @endphp
<div class="table-responsive"><table class="table table-bordered table-striped align-middle">
<thead class="table-light"><tr><th>№</th><th>Sana</th><th>Agent</th><th>Tovar</th><th>Klient</th><th>Bonus / bez bonus</th><th>Prihod summa (USD)</th><th>Summa USD</th><th>KPI</th><th>Fiksa agent</th><th>Venox bonus kassa</th><th>Zavod kassa</th></tr></thead>
<tbody>@forelse($rows as $row)<tr>
<td>{{ $startNumber + $loop->index }}</td><td>{{ \Carbon\Carbon::parse($row['date'])->format('d.m.Y') }}</td><td>{{ $row['agent'] }}</td>
<td style="min-width:260px">@forelse($row['products'] as $product)<div>{{ $product['name'] }} — <b>{{ number_format($product['qty'], 3, '.', ' ') }} {{ $product['unit'] }}</b></div>@empty<span class="text-danger">To‘lovga mos tovar qolmagan</span>@endforelse</td>
<td>{{ $row['client'] }}</td><td>{{ $schemeLabels[$row['scheme']] ?? $row['scheme'] }}</td>
<td>{{ number_format($row['purchase_cost_usd'],2,'.',' ') }}</td><td><b>{{ number_format($row['payment_usd'],2,'.',' ') }}</b></td>
<td>{{ number_format($row['kpi'],2,'.',' ') }} <small>({{ $row['kpi_percent'] }}%)</small></td><td>{{ number_format($row['agent_amount'],2,'.',' ') }} <small>({{ $row['agent_percent'] }}%)</small></td>
<td>{{ number_format($row['venox'],2,'.',' ') }} <small>({{ $row['venox_percent'] }}%)</small></td><td>{{ number_format($row['factory'],2,'.',' ') }}</td>
</tr>@empty<tr><td colspan="12" class="text-center py-5 text-soft">Tanlangan filtr bo‘yicha ma’lumot yo‘q.</td></tr>@endforelse</tbody>
@if($rows->isNotEmpty())<tfoot class="table-light"><tr class="fw-bold"><td colspan="6" class="text-end">Jami:</td><td>{{ number_format($rows->sum('purchase_cost_usd'),2,'.',' ') }}</td><td>{{ number_format($rows->sum('payment_usd'),2,'.',' ') }}</td><td>{{ number_format($rows->sum('kpi'),2,'.',' ') }}</td><td>{{ number_format($rows->sum('agent_amount'),2,'.',' ') }}</td><td>{{ number_format($rows->sum('venox'),2,'.',' ') }}</td><td>{{ number_format($rows->sum('factory'),2,'.',' ') }}</td></tr></tfoot>@endif
</table></div>
