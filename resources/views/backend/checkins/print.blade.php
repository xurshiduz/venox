<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="description" content="«Toshkent-Agro-Traktor» Mas՚uliyati cheklangan jamiyati">
		<title>Спецификация №</title>
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
		<div class="page" contenteditable="true">
            
			<h1 style="margin: 0px" class="title-order">СЧЕТ-ФАКТУРА-НАКЛАДНАЯ №{{ $item->number_work }} от {{ Carbon\Carbon::parse($item->date)->format('d.m.y') }}г.</h1>
			<p style="font-weight: bold; margin: 0px; text-align: center;">к товарно-отгрузочные документам:</p>
			
		<div class="wordpdf">
				<div style="display: flex;">
					<table width="50%" style="margin: 0 10px;">
						<tbody>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">Поставщик</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%"></td>
							</tr>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">Адрес</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%"></td>
							</tr>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">Телефон</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%"></td>
							</tr>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">Р/сч</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%"></td>
							</tr>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">в</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%"></td>
							</tr>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">город</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%"></td>
							</tr>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">МФО</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%"></td>
							</tr>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">ИНН</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%"></td>
							</tr>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">ОКЭД</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%"></td>
							</tr>
						</tbody>
					</table>
				
					<table class="table" width="50%" style="margin: 0 10px;">
						<tbody>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">Получатель</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%"></td>
							</tr>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">Адрес</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%"></td>
							</tr>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">Телефон</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%"></td>
							</tr>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">Р/сч</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%"></td>
							</tr>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">в</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%"></td>
							</tr>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">город</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%"></td>
							</tr>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">МФО</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%"></td>
							</tr>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">ИНН</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%"></td>
							</tr>
							<tr>
								<td style="border: 0px; padding: 0px 5px;" width="30%">ОКЭД</td>
								<td style="border: 0px; padding: 0px 5px; border-bottom: 1px dotted #000;" width="70%"></td>
							</tr>
						</tbody>
					</table>
				</div>
				<div style="margin: 10px">
				<table width="100%">
					<tbody>
						<tr style="text-align: center;">
							<td style="padding: 0px 5px;" rowspan="2">№ п/п</td>
							<td style="padding: 0px 5px;" rowspan="2">Наименование товаров (работ, услуг)</td>
							<td style="padding: 0px 5px;" rowspan="2">Ед. изм</td>
							<td style="padding: 0px 5px;" rowspan="2">Кол-во</td>
							<td style="padding: 0px 5px;" rowspan="2">Цена</td>
							<td style="padding: 0px 5px;" rowspan="2">Стоимость поставки</td>
							<td style="padding: 0px 5px;" colspan="2">Акциз</td>
							<td style="padding: 0px 5px;" colspan="2">НДС</td>
							<td style="padding: 0px 5px;" rowspan="2">Стоим. постаки с учетом НДС</td>
						</tr>
						<tr style="text-align: center;">
							<td style="padding: 0px 5px;">Ставка</td>
							<td style="padding: 0px 5px;">Сумма</td>
							<td style="padding: 0px 5px;">Ставка</td>
							<td style="padding: 0px 5px;">Сумма</td>
						</tr>	
						<tr style="text-align: center;">
							<td style="padding: 0px 5px;"></td>
							<td style="padding: 0px 5px;">1</td>
							<td style="padding: 0px 5px;">2</td>
							<td style="padding: 0px 5px;">3</td>
							<td style="padding: 0px 5px;">4</td>
							<td style="padding: 0px 5px;">5</td>
							<td style="padding: 0px 5px;">6</td>
							<td style="padding: 0px 5px;">7</td>
							<td style="padding: 0px 5px;">8</td>
							<td style="padding: 0px 5px;">9</td>
							<td style="padding: 0px 5px;">10</td>
						</tr>
						@php
						App\Models\CheckinDetail::where('checkin_id', $item->id)->orderBy('id')->chunk(100, function ($details) {
                             foreach ($details as $key => $detail) {
                                echo "<tr>";
        						echo "<td style='padding: 0px 5px;'> $key</td>";
        						echo "<td style='padding: 0px 5px;'> $detail->barcode </td>";
        						echo "<td style='padding: 0px 5px; text-align: center;'> $detail->product_id </td>";
        						echo "<td style='padding: 0px 5px; text-align: center;'> $detail->qty </td>";
        						echo "<td style='padding: 0px 5px;'> $detail->price </td>";
        						echo "<td style='padding: 0px 5px;'> $detail->total_price </td>";
        						echo "<td style='padding: 0px; text-align: center;' width='130' colspan='2'>Без акцизного налога</td>";
        						echo "<td style='padding: 0px 5px; text-align: center;' colspan='2'>Без НДС</td>";
        						echo "<td style='padding: 0px 5px;'></td>";
        						echo "</tr>";
                             }
                          });
                        @endphp
						<tr>
							<td style="padding: 0px 5px;"></td>
							<td style="padding: 0px 5px;">Всего к оплате</td>
							<td style="padding: 0px 5px;"> </td>
							<td style="padding: 0px 5px;"> </td>
							<td style="padding: 0px 5px;" colspan="2"> </td>
							<td style="padding: 0px 5px; text-align: end;" colspan="2">6</td>
							<td style="padding: 0px 5px;" colspan="2"></td>
							<td style="padding: 0px 5px;"></td>
						</tr>
					</tbody>
				</table>
				<span>Всего отпущено на сумму: <input type="button" name="num2bs" value="{{ $item->details()->sum('total_price') }}" id="nummber" />
                <div class="rezult2cena"></div></span>
				
            <br>
            
            <table style="border: 0px; width: 100%;">
                <tbody>
                    <tr>
                        <td style="border: 0px; padding: 5px 0px;" width="50%">Руководитель:</td>
                        <td style="border: 0px; padding: 5px 0px;" width="50%">Получил</td>
                    </tr>
                    <tr>
                        <td style="border: 0px; padding: 5px 0px;" width="50%">Главный бухгалтер:</td>
                        <td style="border: 0px; padding: 5px 0px;" width="50%">Доверенность</td>
                    </tr>
                    <tr>
                        <td style="border: 0px; padding: 5px 0px;" width="50%">Товар отпустил</td>
                        <td style="border: 0px; padding: 5px 0px;" width="50%">ч/з</td>
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
