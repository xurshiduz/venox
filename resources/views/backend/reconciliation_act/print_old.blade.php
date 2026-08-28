<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="description" content="«Toshkent-Agro-Traktor» Mas՚uliyati cheklangan jamiyati">
		<title>Акт сверки взаиморасчетов</title>
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
			.title-order {
				text-align: center;
				font-size: 14px;
				font-family: 'Tinos', serif;
				font-weight: bold;
			}
			
		</style>
	</head>

	<body class="document" style="font-size: 12px">
	    <?php \Carbon\Carbon::setLocale('ru') ?> 
		<div class="page" contenteditable="false">
            
			<h1 style="margin: 0px" class="title-order">Акт сверки взаиморасчетов</h1><br>
			<p style="margin: 0px; text-align: center;">Мы, нижеподписавшиеся, {{ $comp }} в лице <br> и {{ $client->name }} в лице <br> произвели сверку взаиморасчетов за период {{ Carbon\Carbon::parse($from)->format('d.m.Y') }}-{{ Carbon\Carbon::parse($to)->format('d.m.Y') }} г.</p>
			<br>
			<span>{{ Carbon\Carbon::parse($from)->format('Y') }} г.</span>
		<div class="wordpdf">
				<table width="100%">
					<tbody>
						<tr style="text-align: center;">
							<td style="padding: 0px 5px;">Дата</td>
							<td style="padding: 0px 5px;">Операции</td>
							<td style="padding: 0px 5px;" colspan="2">{{ $comp }}</td>
							<td style="padding: 0px 5px;" colspan="2">{{ $client->name }}</td>
						</tr>
						<tr style="text-align: center;">
							<td style="padding: 0px 5px;"></td>
							<td style="padding: 0px 5px;"></td>
							<td style="padding: 0px 5px;">Дебет</td>
							<td style="padding: 0px 5px;">Кредит</td>
							<td style="padding: 0px 5px;">Дебет</td>
							<td style="padding: 0px 5px;">Кредит</td>
						</tr>	
						<tr style="text-align: center;">
							<td style="padding: 0px 5px; text-align: end;" colspan="2"><b>Сальдо на {{ Carbon\Carbon::parse($from)->format('d.m.Y') }}</b></td>
							<td style="padding: 0px 5px;" colspan="4"></td>
						</tr>
						@php($nak = 0)
						@php($pos = 0)
						@foreach($data as $item)
						<tr>
							<td style="padding: 0px 5px; text-align: center;">{{ $item->date }}</td>
							<td style="padding: 0px 5px;">
							    @if($item->checkout_tip_id) 
							        <a target="_blank" href="{{ route('checkout_form', ['id' => $item->code, 'view' => 'full']) }}" style="text-decoration: none; color: #000;"> Накладная - счет фактура {{ $item->number_work }};</a> 
							    @elseif($item->step) 
							        <a target="_blank" href="{{ route('checkin_form', ['id' => $item->code, 'view' => 'full']) }}" style="text-decoration: none; color: #000;"> Поступление товаров ИД; с/ф №{{ $item->number_work }};</a>
							    @else 
							        <a target="_blank" href="{{ route('cash_receipt_form', ['id' => $item->code, 'view' => 'full']) }}" style="text-decoration: none; color: #000;">  Учтена выручка Приходный кассовый ордер {{ $item->id }}; Вид оплата: {{ $item->tname ? $item->tname->name : NULL }}</a> 
							    @endif</td>
							<td style="padding: 0px 5px; text-align: center;">@if($item->checkout_tip_id) {{ number_format($item->sumtotal(), 0, '.', ' ') }} @else @endif</td>
							<td style="padding: 0px 5px; text-align: center;">@if($item->checkout_tip_id) @elseif($item->step) {{ number_format($item->sumtotal(), 0, '.', ' ') }} @else {{ number_format($item->price, 0, '.', ' ') }} @endif</td>
							<td style="padding: 0px 5px; text-align: center;">@if($item->checkout_tip_id) @elseif($item->step) @php($pos += $item->sumtotal()) {{ number_format($item->sumtotal(), 0, '.', ' ') }} @else @php($pos += $item->price) {{ number_format($item->price, 0, '.', ' ') }} @endif</td>
							<td style="padding: 0px 5px; text-align: center;">@if($item->checkout_tip_id) @php($nak += $item->sumtotal()) {{ number_format($item->sumtotal(), 0, '.', ' ') }} @else @endif</td>
						</tr>
						@endforeach
						<tr>
							<td style="padding: 0px 5px; text-align: end;" colspan="2">Обороты за период</td>
							<td style="padding: 0px 5px; text-align: center;">{{ number_format($nak, 0, '.', ' ') }}</td>
							<td style="padding: 0px 5px; text-align: center;">{{ number_format($pos, 0, '.', ' ') }}</td>
							<td style="padding: 0px 5px; text-align: center;">{{ number_format($pos, 0, '.', ' ') }}</td>
							<td style="padding: 0px 5px; text-align: center;">{{ number_format($nak, 0, '.', ' ') }}</td>
						</tr>
						<tr>
						    @php($nak_t = $nak - $pos)
						    @php($pos_t = $pos - $nak)
							<td style="padding: 0px 5px; text-align: end;" colspan="2"><b>Сальдо на {{ Carbon\Carbon::parse($to)->format('d.m.Y') }}</b></td>
							<td style="padding: 0px 5px; text-align: center;">@if($nak_t != 0) <b>{{ $nak_t > 0 ? number_format($nak_t, 0, '.', ' ') : null }}</b>@else @endif</td>
							<td style="padding: 0px 5px; text-align: center;">@if($nak_t != 0) <b>{{ $pos_t > 0 ? number_format($pos_t, 0, '.', ' ') : null }}</b>@else @endif</td>
							<td style="padding: 0px 5px; text-align: center;">@if($nak_t != 0) <b>{{ $pos_t > 0 ? number_format($pos_t, 0, '.', ' ') : null }}</b>@else @endif</td>
							<td style="padding: 0px 5px; text-align: center;">@if($nak_t != 0) <b>{{ $nak_t > 0 ? number_format($nak_t, 0, '.', ' ') : null }}</b>@else @endif</td>
						</tr>
						<tr>
							<td style="padding: 0px 5px;" colspan="6">&nbsp;</td>
						</tr>
						<tr>
							<td style="padding: 0px 5px;" colspan="6"> @if($nak_t != 0)  В пользу {{ $nak_t > 0 ? $comp : $client->name }} {{ $nak_t > 0 ? number_format($nak_t, 0, '.', ' ') : number_format($pos_t, 0, '.', ' ') }} сум ({{ (new \MessageFormatter('ru-RU', '{n, spellout}'))->format(['n' => $nak_t > 0 ? $nak_t : $pos_t]) }} сумов 00 тийин). @endif </td>
						</tr>
					</tbody>
				</table>
				
            <br>
            
            <table style="border: 0px; width: 100%;">
                <tbody>
                    <tr>
                        <td style="border: 0px; padding: 5px 0px; text-align: center;" width="50%">{{ $comp }}</td>
                        <td style="border: 0px; padding: 5px 0px; text-align: center;" width="50%">{{ $client->name }}</td>
                    </tr>
                    <tr>
                        <td style="border: 0px; padding: 5px 0px; text-align: center;" width="50%">_________________</td>
                        <td style="border: 0px; padding: 5px 0px; text-align: center;" width="50%">_________________</td>
                    </tr>
                </tbody>
            </table>
			</div>
		</div>

		</div>
		<script type="text/javascript">
		window.print();
		</script>


<script>
var map2Numbers = {
    0 : [2, 1, "no'l"],
    1 : [0, 2, "bir", "bir"],
    2 : [1, 2, "ikki", "ikki"],
    3 : [1, 1, "uch"],
    4 : [1, 1, "to'rt"],
    5 : [2, 1, "besh"],
    6 : [2, 1, "olti"],
    7 : [2, 1, "yetti"],
    8 : [2, 1, "sakkiz"],
    9 : [2, 1, "to'qqiz"],
    10 : [2, 1, "o'n"],
    11 : [2, 1, "o'n bir"],
    12 : [2, 1, "o'n ikki"],
    13 : [2, 1, "o'n uch"],
    14 : [2, 1, "o'n to'rt"],
    15 : [2, 1, "o'n besh"],
    16 : [2, 1, "o'n olti"],
    17 : [2, 1, "o'n yetti"],
    18 : [2, 1, "o'n sakkiz"],
    19 : [2, 1, "o'n to'qqiz"],
    20 : [2, 1, "yigirma"],
    30 : [2, 1, "o'ttiz"],
    40 : [2, 1, "qirq"],
    50 : [2, 1, "ellik"],
    60 : [2, 1, "oltmish"],
    70 : [2, 1, "yetmish"],
    80 : [2, 1, "sakson"],
    90 : [2, 1, "to'qson"],
    100 : [2, 1, "bir yuz"],
    200 : [2, 1, "ikki yuz"],
    300 : [2, 1, "uch yuz"],
    400 : [2, 1, "to'rt yuz"],
    500 : [2, 1, "besh yuz"],
    600 : [2, 1, "olti yuz"],
    700 : [2, 1, "yetti yuz"],
    800 : [2, 1, "sakkiz yuz"],
    900 : [2, 1, "to'qqiz yuz"]
};

var map2Orders = [
    { _Gender : true, _arrStates : ["so'm", "so'm", "so'm"] },
    { _Gender : true, _arrStates : ["ming", "ming", "ming"] },
    { _Gender : true, _arrStates : ["million", "million", "million"] },
    { _Gender : true, _arrStates : ["milliard", "milliard", "milliard"] },
    { _Gender : true, _arrStates : ["trillion", "trillion", "trillion"] }
];

var obj2Kop = { _Gender : true, _arrStates : ["tiyin", "tiyin", "tiyin"] };

function Value(dVal, bGender) {
    var xVal = map2Numbers[dVal];
    if (xVal[1] == 1) {
        return xVal[2];
    } else {
        return xVal[3 + (bGender ? 0 : 1)];
    }
}

function From10To999(fValue, oObjDesc, fnAddNum, fnAddDesc)
{
    var nCurrState = 2;
    if (Math.floor(fValue/100) > 0) {
        var fCurr = Math.floor(fValue/100)*100;
        fnAddNum(Value(fCurr, oObjDesc._Gender));
        nCurrState = map2Numbers[fCurr][0];
        fValue -= fCurr;
    }

    if (fValue < 20) {
        if (Math.floor(fValue) > 0) {
            fnAddNum(Value(fValue, oObjDesc._Gender));
            nCurrState = map2Numbers[fValue][0];
        }
    } else {
        var fCurr = Math.floor(fValue/10)*10;
        fnAddNum(Value(fCurr, oObjDesc._Gender));
        nCurrState = map2Numbers[fCurr][0];
        fValue -= fCurr;

        if (Math.floor(fValue) > 0) {
            fnAddNum(Value(fValue, oObjDesc._Gender));
            nCurrState = map2Numbers[fValue][0];
        }
    }

    fnAddDesc(oObjDesc._arrStates[nCurrState]);
}

function FloatToSamplesInWords2Rus(f2Amount)
{
    var f2Int = Math.floor(f2Amount + 0.005);
    var fDec = Math.floor(((f2Amount - f2Int) * 100) + 0.5);

    var arrRet = [];
    var iOrder = 0;
    var arrThousands = [];
    for (; f2Int > 0.9999; f2Int/=1000) {
        arrThousands.push(Math.floor(f2Int % 1000));
    }
    if (arrThousands.length == 0) {
        arrThousands.push(0);
    }

    function PushToRes(strVal) {
        arrRet.push(strVal);
    }

    for (var iSouth = arrThousands.length-1; iSouth >= 0; --iSouth) {
        if (arrThousands[iSouth] == 0) {
            continue;
        }
        From10To999(arrThousands[iSouth], map2Orders[iSouth], PushToRes, PushToRes);
    }

    if (arrThousands[0] == 0) {
        //  Handle zero amount
        if (arrThousands.length == 1) {
            PushToRes(Value(0, map2Orders[0]._Gender));
        }

        var nCurrState = 2;
        PushToRes(map2Orders[0]._arrStates[nCurrState]);
    }

    if (arrRet.length > 0) {
        // Capitalize first letter
        arrRet[0] = arrRet[0].match(/^(.)/)[1].toLocaleUpperCase() + arrRet[0].match(/^.(.*)$/)[1];
    }

    arrRet.push((fDec < 10) ? ("0" + fDec) : ("" + fDec));
    From10To999(fDec, obj2Kop, function() {}, PushToRes);

    return arrRet.join(" ");
}


//динамически переводит цифры
$(document).ready(function(){
$('[name=num2bs]').on('change keyup input click', function(){
$(".rezult2cena").html(FloatToSamplesInWords2Rus(parseFloat($("[name=num2bs]").val())));
}); });
</script>

	</body>
</html>
