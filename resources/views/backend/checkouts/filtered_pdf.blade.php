<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Продажа КПИ</title>
<style>
@page{size:A4 landscape;margin:6mm 14mm}*{box-sizing:border-box}body{margin:0;color:#000;background:#fff;font-family:Arial,"DejaVu Sans",sans-serif;font-size:11pt;line-height:1.3}.page{width:100%}table{width:100%;border-collapse:collapse;table-layout:auto;background:#fff}th,td{border:1px solid #000;padding:5px 6px;font-size:10pt;background:#fff;color:#000}th{text-align:center;font-weight:600}.contract-row td{background:#fff;text-align:center;font-size:10.5pt;padding:6px}.center{text-align:center}.number{text-align:right;white-space:nowrap}thead{display:table-header-group}tr{page-break-inside:avoid}
</style></head><body class="document">
<div id="pdf-report" class="page">
<table><thead><tr><th style="width:27%">Наименование товаров (работ, услуг)</th><th>Кол-во</th><th>Цена</th><th>Себестоимость</th><th>Разница</th><th>Итого сумма</th><th>Итого себ-сть</th><th>Итого разница</th></tr><tr>@for($i=1;$i<=8;$i++)<th>{{ $i }}</th>@endfor</tr></thead><tbody>
@forelse($data as $item)
<tr class="contract-row"><td colspan="8"><b>Договор:</b> {{ $item->number_work ?: 'Чер. #'.$item->id }} от {{ \Carbon\Carbon::parse($item->date)->format('d.m.Y') }} &nbsp; <b>Менеджер:</b> {{ optional($item->managerid)->name ?: '—' }} &nbsp; <b>Клиент:</b> {{ optional($item->supid)->name ?: '—' }} @if(optional($item->currencytypeid)->name)&nbsp; <b>Валюта:</b> {{ $item->currencytypeid->name }}@endif</td></tr>
@forelse(($item->checkoutDetails ?: collect()) as $detail)
@php($cost=(float)$detail->tan_price) @php($price=(float)$detail->price) @php($qty=(float)$detail->qty) @php($total=(float)$detail->total_price) @php($costTotal=$cost*$qty)
<tr><td>{{ optional($detail->prodid)->name ?: '—' }}</td><td class="center">{{ number_format($qty,2,'.',' ') }}</td><td class="number">{{ number_format($price,2,'.',' ') }}</td><td class="number">{{ $cost?number_format($cost,2,'.',' '):'' }}</td><td class="number">{{ $cost?number_format($price-$cost,2,'.',' '):'' }}</td><td class="number">{{ number_format($total,2,'.',' ') }}</td><td class="number">{{ $cost?number_format($costTotal,2,'.',' '):'' }}</td><td class="number">{{ $cost?number_format($total-$costTotal,2,'.',' '):'' }}</td></tr>
@empty<tr><td colspan="8" class="center">Товары не найдены</td></tr>@endforelse
@empty<tr><td colspan="8" class="center">По выбранному фильтру данные не найдены.</td></tr>@endforelse
</tbody></table>
</div>
@if($clientSidePdf ?? false)
<script>window.addEventListener('load',function(){setTimeout(function(){window.print()},300)});</script>
@endif
</body></html>
