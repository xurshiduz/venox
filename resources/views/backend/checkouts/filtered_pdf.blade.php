<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Продажа КПИ</title>
<style>
@page{size:A4 landscape;margin:8mm}*{box-sizing:border-box}body{margin:0;color:#000;background:#fff;font-family:Arial,"DejaVu Sans",sans-serif;font-size:11pt;line-height:1.3}.page{width:100%}.title-order{text-align:center;font-size:16pt;margin:0}.filter-line{text-align:center;font-size:11pt;margin:5px 0 14px}.summary-wrap{width:100%;margin-bottom:12px}.summary-wrap:after{content:"";display:block;clear:both}.summary-box{width:49%;display:inline-table;vertical-align:top}.summary-box+.summary-box{float:right}table{width:100%;border-collapse:collapse;table-layout:auto}th,td{border:1px solid #000;padding:5px 6px;font-size:10pt}th{text-align:center;font-weight:600;background:#eee}.summary-box td{border:0;padding:2px 6px;font-size:10.5pt}.summary-box td:last-child{border-bottom:1px dotted #000}.contract-row td{background:#e8e8e8;text-align:center;font-size:10.5pt;padding:6px}.center{text-align:center}.number{text-align:right;white-space:nowrap}.signatures{margin-top:16px}.signatures td{border:0;padding:6px 0;font-size:10.5pt}.pdf-status{margin:10px auto;padding:12px;max-width:900px;background:#eef6ff;text-align:center;font-size:11pt}.print-button{margin-left:10px;padding:7px 14px;border:0;border-radius:4px;background:#1976d2;color:#fff;cursor:pointer}thead{display:table-header-group}tr{page-break-inside:avoid}@media print{.pdf-status{display:none!important}body{-webkit-print-color-adjust:exact;print-color-adjust:exact}}
</style></head><body class="document">
@if($clientSidePdf ?? false)<div id="pdf-status" class="pdf-status">PDF oynasi ochilmoqda... <button type="button" class="print-button" onclick="window.print()">Qayta ochish</button></div>@endif
<div id="pdf-report" class="page">
@php
    $actualFrom = $dateFrom ?: optional($data->sortBy('date')->first())->date;
    $actualTo = $dateTo ?: optional($data->sortByDesc('date')->first())->date;
    $details = $data->pluck('checkoutDetails')->filter()->flatten();
@endphp
<br><h1 class="title-order">ПРОДАЖА @if($actualFrom || $actualTo) от {{ $actualFrom ? \Carbon\Carbon::parse($actualFrom)->format('d.m.y') : 'начала' }}г. до {{ $actualTo ? \Carbon\Carbon::parse($actualTo)->format('d.m.y') : 'сегодня' }}г.@endif</h1>
<p class="filter-line">Менеджер: <b>{{ $selectedAgentName ?: 'Все' }}</b></p>
<div class="summary-wrap">
<table class="summary-box">
<tr><td width="55%">Количество договоров</td><td>{{ $data->count() }}</td></tr>
<tr><td>Количество менеджеров</td><td>{{ $data->pluck('manager_id')->filter()->unique()->count() }}</td></tr>
<tr><td>Вид продуктов</td><td>{{ $details->pluck('product_id')->filter()->unique()->count() }}</td></tr>
<tr><td>Количество товаров</td><td>{{ number_format((float)$details->sum('qty'),2,'.',' ') }}</td></tr>
</table>
<table class="summary-box">
@forelse($data->groupBy('currency_type') as $currencyRows)
@php($currencyName=optional($currencyRows->first()->currencytypeid)->name ?: 'Валюта')
<tr><td width="55%">Итого сумма ({{ $currencyName }})</td><td><b>{{ number_format((float)$currencyRows->sum('total_price'),2,'.',' ') }}</b></td></tr>
<tr><td>Итого себестоимость ({{ $currencyName }})</td><td>{{ number_format((float)$currencyRows->sum(fn($c)=>optional($c->checkoutDetails)->sum(fn($d)=>(float)$d->tan_price*(float)$d->qty) ?: 0),2,'.',' ') }}</td></tr>
@empty<tr><td>Итого сумма</td><td>0.00</td></tr>@endforelse
</table></div>
<table><thead><tr><th style="width:27%">Наименование товаров (работ, услуг)</th><th>Кол-во</th><th>Цена</th><th>Себестоимость</th><th>Разница</th><th>Итого сумма</th><th>Итого себ-сть</th><th>Итого разница</th></tr><tr>@for($i=1;$i<=8;$i++)<th>{{ $i }}</th>@endfor</tr></thead><tbody>
@forelse($data as $item)
<tr class="contract-row"><td colspan="8"><b>Договор:</b> {{ $item->number_work ?: 'Чер. #'.$item->id }} от {{ \Carbon\Carbon::parse($item->date)->format('d.m.Y') }} &nbsp; <b>Менеджер:</b> {{ optional($item->managerid)->name ?: '—' }} &nbsp; <b>Клиент:</b> {{ optional($item->supid)->name ?: '—' }} @if(optional($item->currencytypeid)->name)&nbsp; <b>Валюта:</b> {{ $item->currencytypeid->name }}@endif</td></tr>
@forelse(($item->checkoutDetails ?: collect()) as $detail)
@php($cost=(float)$detail->tan_price) @php($price=(float)$detail->price) @php($qty=(float)$detail->qty) @php($total=(float)$detail->total_price) @php($costTotal=$cost*$qty)
<tr><td>{{ optional($detail->prodid)->name ?: '—' }}</td><td class="center">{{ number_format($qty,2,'.',' ') }}</td><td class="number">{{ number_format($price,2,'.',' ') }}</td><td class="number">{{ $cost?number_format($cost,2,'.',' '):'' }}</td><td class="number">{{ $cost?number_format($price-$cost,2,'.',' '):'' }}</td><td class="number">{{ number_format($total,2,'.',' ') }}</td><td class="number">{{ $cost?number_format($costTotal,2,'.',' '):'' }}</td><td class="number">{{ $cost?number_format($total-$costTotal,2,'.',' '):'' }}</td></tr>
@empty<tr><td colspan="8" class="center">Товары не найдены</td></tr>@endforelse
@empty<tr><td colspan="8" class="center">По выбранному фильтру данные не найдены.</td></tr>@endforelse
</tbody></table>
@foreach($data->groupBy('currency_type') as $currencyRows)
@php($currencyName=optional($currencyRows->first()->currencytypeid)->name ?: 'Валюта') @php($sales=(float)$currencyRows->sum('total_price')) @php($cost=(float)$currencyRows->sum(fn($c)=>optional($c->checkoutDetails)->sum(fn($d)=>(float)$d->tan_price*(float)$d->qty) ?: 0))
<table class="signatures"><tr><td width="50%">Руководитель: ____________________</td><td>Итого сумма ({{ $currencyName }}): {{ number_format($sales,2,'.',' ') }}</td></tr><tr><td>Главный бухгалтер: ____________________</td><td>Итого себ-сть: {{ number_format($cost,2,'.',' ') }}</td></tr><tr><td><b>М.П.</b></td><td>Итого разница: {{ number_format($sales-$cost,2,'.',' ') }}</td></tr></table>
@endforeach
</div>
@if($clientSidePdf ?? false)
<script>window.addEventListener('load',function(){setTimeout(function(){window.print()},300)});</script>
@endif
</body></html>
