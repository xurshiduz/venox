<table>
	<tbody>
	    <tr>
			<td colspan="9"></td>
		</tr>
	    <tr>
			<td style="text-align: center; color: #6A2813" colspan="9"><b>СЧЕТ-ФАКТУРА-НАКЛАДНАЯ №{{ $item->number_work }} от {{ Carbon\Carbon::parse($item->date)->format('d.m.y') }}г.</b></td>
		</tr>
	    <tr>
			<td style="text-align: center; color: #6A2813" colspan="9"><b>к товарно-отгрузочные документам:</b></td>
		</tr>
	    <tr>
			<td colspan="9"></td>
		</tr>
		
		<tr>
			<td colspan="2" style="border-top: 1px solid #000000; border-left: 1px solid #000000;" width="100px">Поставщик</td>
			<td style="word-wrap: break-word; border-top: 1px solid #000000; border-right: 1px solid #000000; border-bottom: 1px dotted #000;" width="300px"><b>{{ $comp->where('atribute', 'comp_name')->first()->value }}</b></td>
			<td></td>
			<td></td>
			<td style="border-top: 1px solid #000000; border-left: 1px solid #000000;" width="100px">Получатель</td>
			<td colspan="3" style="word-wrap: break-word; border-top: 1px solid #000000; border-right: 1px solid #000000; border-bottom: 1px dotted #000;" width="300px"><b>{{ $item->supid->name }}</b></td>
		</tr>
		<tr>
			<td colspan="2" style="border-left: 1px solid #000000;" width="100px">Адрес</td>
			<td style="word-wrap: break-word; border-right: 1px solid #000000; border-bottom: 1px dotted #000;" width="300px">{{ $comp->where('atribute', 'address_ru')->first()->value }}</td>
			<td></td>
			<td></td>
			<td style="border-left: 1px solid #000000;" width="100px">Адрес</td>
			<td colspan="3" style="word-wrap: break-word; border-right: 1px solid #000000; border-bottom: 1px dotted #000;" width="300px">{{ $item->supid->address }}</td>
		</tr>
		<tr>
			<td colspan="2" style="border-left: 1px solid #000000;" width="100px">Телефон</td>
			<td style="word-wrap: break-word; border-right: 1px solid #000000; border-bottom: 1px dotted #000;" width="300px">{{ $comp->where('atribute', 'comp_phone')->first()->value }}</td>
			<td></td>
			<td></td>
			<td style="border-left: 1px solid #000000;" width="100px">Телефон</td>
			<td colspan="3" style="word-wrap: break-word; border-right: 1px solid #000000; border-bottom: 1px dotted #000;" width="300px">{{ $item->supid->phone }}</td>
		</tr>
		<tr>
			<td colspan="2" style="border-left: 1px solid #000000;" width="100px">Р/сч</td>
			<td style="word-wrap: break-word; border-right: 1px solid #000000; border-bottom: 1px dotted #000;" width="300px">{{ $comp->where('atribute', 'comp_schet')->first()->value }}</td>
			<td></td>
			<td></td>
			<td style="border-left: 1px solid #000000;" width="100px">Р/сч</td>
			<td colspan="3" style="word-wrap: break-word; border-right: 1px solid #000000; border-bottom: 1px dotted #000;" width="300px">{{ $item->supid->schet }}</td>
		</tr>
		<tr>
			<td colspan="2" style="border-left: 1px solid #000000;" width="100px">Банк</td>
			<td style="word-wrap: break-word; border-right: 1px solid #000000; border-bottom: 1px dotted #000;" width="300px">{{ $comp->where('atribute', 'comp_bank')->first()->value }}</td>
			<td></td>
			<td></td>
			<td style="border-left: 1px solid #000000;" width="100px">Банк</td>
			<td colspan="3" style="word-wrap: break-word; border-right: 1px solid #000000; border-bottom: 1px dotted #000;" width="300px"></td>
		</tr>
		<tr>
			<td colspan="2" style="border-left: 1px solid #000000;" width="100px">МФО</td>
			<td style="word-wrap: break-word; border-right: 1px solid #000000; border-bottom: 1px dotted #000;" width="300px">{{ $comp->where('atribute', 'comp_mfo')->first()->value }}</td>
			<td></td>
			<td></td>
			<td style="border-left: 1px solid #000000;" width="100px">МФО</td>
			<td colspan="3" style="word-wrap: break-word; border-right: 1px solid #000000; border-bottom: 1px dotted #000;" width="300px">{{ $item->supid->mfo }}</td>
		</tr>
		<tr>
			<td colspan="2" style="border-left: 1px solid #000000;" width="100px">ИНН</td>
			<td style="word-wrap: break-word; border-right: 1px solid #000000; border-bottom: 1px dotted #000;" width="300px">{{ $comp->where('atribute', 'comp_inn')->first()->value }}</td>
			<td></td>
			<td></td>
			<td style="border-left: 1px solid #000000;" width="100px">ИНН</td>
			<td colspan="3" style="word-wrap: break-word; border-right: 1px solid #000000; border-bottom: 1px dotted #000;" width="300px">{{ $item->supid->inn }}</td>
		</tr>
		<tr>
			<td colspan="2" style="border-bottom: 1px solid #000000; border-left: 1px solid #000000;" width="100px">ОКЭД</td>
			<td style="word-wrap: break-word; border-right: 1px solid #000000; border-bottom: 1px solid #000;" width="300px">{{ $comp->where('atribute', 'comp_oked')->first()->value }}</td>
			<td></td>
			<td></td>
			<td style="border-bottom: 1px solid #000000; border-left: 1px solid #000000;" width="100px">ОКЭД</td>
			<td colspan="3" style="word-wrap: break-word; border-right: 1px solid #000000; border-bottom: 1px solid #000;" width="300px">{{ $item->supid->oked }}</td>
		</tr>
	</tbody>
</table>

<table width="100%">
	<tbody>
		<tr style="text-align: center;">
			<td width="40px" style="word-wrap: break-word;border: 1px solid #000000; vertical-align: middle;">№ п/п</td>
			<td colspan="2" style="border: 1px solid #000000; vertical-align: middle;">Наименование товаров (работ, услуг)</td>
			<td style="border: 1px solid #000000; vertical-align: middle;">Штрих-код</td>
			<td width="60px" style="border: 1px solid #000000; vertical-align: middle;">Ед. изм</td>
			<td width="60px" style="border: 1px solid #000000; vertical-align: middle;">Кол-во</td>
			<td width="110px" style="border: 1px solid #000000; vertical-align: middle;">Цена</td>
			<td width="110px" style="word-wrap: break-word; border: 1px solid #000000; vertical-align: middle;">Стоимость поставки</td>
			<td width="110px" style="word-wrap: break-word; border: 1px solid #000000; vertical-align: middle;">Стоим. поставки с учетом НДС</td>
		</tr>
		<tr>
			<td style="text-align: center; border: 1px solid #000000;"></td>
			<td colspan="2" style="text-align: center; border: 1px solid #000000;">1</td>
			<td style="text-align: center; border: 1px solid #000000;">2</td>
			<td style="text-align: center; border: 1px solid #000000;">3</td>
			<td style="text-align: center; border: 1px solid #000000;">4</td>
			<td style="text-align: center; border: 1px solid #000000;">5</td>
			<td style="text-align: center; border: 1px solid #000000;">6</td>
			<td style="text-align: center; border: 1px solid #000000;">7</td>
		</tr>
		
		@foreach($item->details()->get() as $detail)
		<tr>
			<td style="text-align: center; border: 1px solid #000000;">{{$loop->iteration}}</td>
			<td colspan="2" style="word-wrap: break-word; border: 1px solid #000000;" >{{ $detail->prodid->name }}</td>
			<td style="word-wrap: break-word; border: 1px solid #000000;" >{{ $detail->prodid->barcode }}</td>
			<td style="padding: 0px 5px; text-align: center; border: 1px solid #000000;">{{ $detail->prodid->unit_id ? $detail->prodid->unitid->name : NULL }}</td>
			<td style="padding: 0px 5px; text-align: center; border: 1px solid #000000;">{{ $detail->qty }}</td>
			<td style="padding: 0px 5px; text-align: center; border: 1px solid #000000;">{{ $detail->price }}</td>
			<td style="padding: 0px 5px; text-align: center; border: 1px solid #000000;">{{ $detail->total_price }}</td>
			<td style="border: 1px solid #000000;"></td>
		</tr>
		@endforeach
		<tr>
			<td style="text-align: center; border: 1px solid #000000;"></td>
			<td colspan="2" style="border: 1px solid #000000;"><b>Всего к оплате</b></td>
			<td style="text-align: center; border: 1px solid #000000;"> </td>
			<td style="text-align: center; border: 1px solid #000000;"> </td>
			<td style="text-align: center; border: 1px solid #000000;"> </td>
			<td style="text-align: center; border: 1px solid #000000;"> </td>
			<td style="padding: 0px 5px; text-align: center;  border: 1px solid #000000;"><b>{{ number_format($item->details()->sum('total_price'), 2, '.', ' ') }}</b></td>
			<td style="text-align: center; border: 1px solid #000000;"></td>
		</tr>
        <tr>
            <td></td>
            <td colspan="8" style="border: 0px; padding: 5px 0px;">Всего отпущено на сумму: 
            {{ (new \MessageFormatter('ru-RU', '{n, spellout}'))->format(['n' => $item->details()->sum('total_price')]) }} 
            сумов 00 тийин.  Без НДС.</td>
        </tr>
    </tbody>
</table>

<table style="border: 0px; width: 100%;">
    <tbody>
        <tr>
            <td></td>
            <td style="border: 0px; padding: 5px 0px;">Руководитель: ____________________</td>
            <td></td>
            <td></td>
            <td></td>
            <td style="border: 0px; padding: 5px 0px;">Получил ____________________</td>
        </tr>
        <tr>
            <td></td>
            <td style="border: 0px; padding: 5px 0px;">Главный бухгалтер: ____________________</td>
            <td></td>
            <td></td>
            <td></td>
            <td style="border: 0px; padding: 5px 0px;">Доверенность ____________________</td>
        </tr>
        <tr>
            <td></td>
            <td style="border: 0px; padding: 5px 0px;"><b>М.П.</b></td>
            <td></td>
            <td></td>
            <td></td>
            <td style="border: 0px; padding: 5px 0px;">ч/з ____________________</td>
        </tr>
        <tr>
            <td></td>
            <td style="border: 0px; padding: 5px 0px;">Товар отпустил ____________________</td>
            <td></td>
            <td></td>
            <td></td>
            <td style="border: 0px; padding: 0px; vertical-align: text-bottom;">&nbsp; &nbsp; &nbsp; &nbsp;ФИО получателя</td>
        </tr>
    </tbody>
</table>