<!doctype html><html><head><meta charset="utf-8"><title>Kassa hisoboti</title><style>
@page{size:A4 landscape;margin:10mm}body{font-family:Arial,sans-serif;font-size:9px;color:#111}h2{text-align:center;margin:0 0 4px}.meta{text-align:center;margin-bottom:12px}table{border-collapse:collapse;width:100%}th,td{border:1px solid #222;padding:4px;vertical-align:top}th{background:#eee}.no-print{margin-bottom:10px}@media print{.no-print{display:none}}
</style></head><body><button class="no-print" onclick="window.print()">PDF / Chop etish</button><h2>Kassa hisoboti</h2><div class="meta">{{ $filters['from'] }} — {{ $filters['to'] }}</div>
@include('backend.checkouts.accounting_cash_report_table', ['rows'=>$rows, 'startNumber'=>1])
<script>window.addEventListener('load',function(){window.print();});</script></body></html>
