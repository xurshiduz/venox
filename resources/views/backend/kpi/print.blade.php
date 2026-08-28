<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="description" content="«KPI">
		<title>KPI</title>
		<link rel="stylesheet" type="text/css" href="/css/sheets-of-paper-a4.css">
		<link rel="preconnect" href="http://fonts.googleapis.com">
		<link rel="preconnect" href="http://fonts.gstatic.com" crossorigin>
		<link href="http://fonts.googleapis.com/css2?family=Tinos:ital@0;1&display=swap" rel="stylesheet">
		
		<style type="text/css">
			@media print {
			  .hidden-print {
			    display: none !important;
			  }
			}
			.wordpdf p {
				text-indent: 40px;
				margin-top: 0px;
				text-align: justify;
				font-family: 'Tinos', serif;
			
			}
			
			ul li {
				line-height:18px;
				font-family: 'Tinos', serif;
				padding-left: 20px;

			}
			.page {
				font-family: 'Tinos', serif;
			}
			table, th, td {
			  border: 1px solid black;
			  border-collapse: collapse;
			  padding: 4px;
			  font-family: 'Tinos', serif;

			}
			.zakaz-cars > tbody > tr > td {
			  font-size: 14px;
			  text-align: center;
			  font-family: 'Tinos', serif;
			}
			.zakaz-cars > tfoot > tr > td {
			  font-size: 12px;
			  text-align: center;
			  font-family: 'Tinos', serif;
			}
			.title-order {
				text-align: center;
				font-size: 14px;
				font-family: 'Tinos', serif;
				font-weight: bold;
			}
			.title-order >span{
				font-weight: normal;
				font-family: 'Tinos', serif;
			}
			
			.zakaz-cars > thead > tr > th {
			  font-size: 14px;
			  text-align: center;
			  font-family: 'Tinos', serif;
			}
			
		</style>
	</head>

	<body class="document" style="font-size: 12px">
	    <?php \Carbon\Carbon::setLocale('ru') ?> 
	    
	    @php
            $sum = 0;
            $plan = $item->details()->sum('plan_sum');
            
            foreach(App\Models\Checkout::whereBetween('date', [Carbon\Carbon::parse($item->date)->format('Y-m' . '-01'), Carbon\Carbon::parse($item->date)->format('Y-m' . '-31')])->get() as $chek){
                $sum += $chek->sumtotal();
            }
            
            $fact = ($sum/$plan) * 100;
        
        @endphp
                        
		<div class="page" contenteditable="true">
            <br>
			<h1 style="margin: 0px" class="title-order">KPI №{{ $item->id }} от {{ Carbon\Carbon::parse($item->date)->format('Y') }} @if(LaravelLocalization::getCurrentLocaleNative() == 'RU') {{ Carbon\Carbon::parse($item->date)->locale('ru_RU')->monthName }} @else {{ Carbon\Carbon::parse($item->date)->locale('uz_UZ')->monthName }} @endif</h1>
			<br>
		<div class="wordpdf">
				<div style="display: flex;">
					<table width="100%" style="margin: 0 10px;">
						<tbody>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">{{ trans('backend.table.plan') }}</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%"><b>{{ number_format($plan, 0, '.', ' ') }} {{ trans('backend.table.sum_belgi') }}</b></td>
							</tr>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">{{ trans('backend.table.fact') }}</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%">{{ number_format($sum, 0, '.', ' ') }} {{ trans('backend.table.sum_belgi') }}</td>
							</tr>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">{{ trans('backend.table.in_percentages') }}</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%">{{ number_format($fact, 0, '.', ' ') }} %</td>
							</tr>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">{{ trans('backend.input.comment') }}</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%">{{ $item->comment }}</td>
							</tr>
						</tbody>
					</table>
				</div>
				<div style="margin: 10px">
				<table width="100%" class="table table-bordered">
                  <thead>
                    <tr class="text-center">
                      <th scope="col">{{ trans('backend.table.name') }}</th>
                      <th scope="col">{{ trans('backend.table.client_buy') }}</th>
                      <th scope="col">{{ trans('backend.table.plan') }}</th>
                      <th scope="col">{{ trans('backend.table.fact') }}</th>
                      <th scope="col">{{ trans('backend.table.in_percentages') }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($item->details as $key => $ditem)
                    
                    @php
                    $dsum[$key] = 0;
                    
                    foreach(App\Models\Checkout::where('manager_id', $ditem->manager_id)->whereBetween('date', [Carbon\Carbon::parse($item->date)->format('Y-m' . '-01'), Carbon\Carbon::parse($item->date)->format('Y-m' . '-31')])->get() as $chek){
                        $dsum[$key] += $chek->sumtotal();
                    }
                    $dfact[$key] = ($dsum[$key]/$ditem->plan_sum) * 100;
                    
                    $from1 = Carbon\Carbon::parse($item->date)->format('Y-m' . '-01 00:00:00');
                    $to1 =  Carbon\Carbon::parse($item->date)->format('Y-m' . '-31 00:00:00');
                    @endphp
                
                    <tr class="text-center">
                      <td>{{ $ditem->managerid->name }}</td>
                      <td>{{ $ditem->managerid->checkouts()->whereBetween('created_at', [$from1, $to1])->count() }} {{ trans('backend.table.qty_short_t') }}</td>
                      <td>{{ number_format($ditem->plan_sum, 0, '.', ' ') }} {{ trans('backend.table.sum_belgi') }}</td>
                      <td>{{ number_format($dsum[$key], 0, '.', ' ') }} {{ trans('backend.table.sum_belgi') }}</td>
                      <td style="background-color: {{ $dfact[$key] >= 100 ? '#eaffee' : ($dfact[$key] >= 80 ? '#fffbea' : '#ffeaea') }}">{{ number_format($dfact[$key], 0, '.', ' ') }} % </td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
			</div>
		</div>

		</div>
		<script type="text/javascript">
		window.print();
		</script>
	</body>
</html>
