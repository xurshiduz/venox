<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Молиявий Ҳисобот - {{ $year }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        
        body { font-family: 'Inter', sans-serif; background: #f3f4f6; -webkit-print-color-adjust: exact; }
        .paper { background: white; width: 210mm; min-height: 297mm; margin: 10px auto; padding: 8mm; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .row-item { border-bottom: 1px dashed #e5e7eb; padding: 6px 0; }
        .row-item:last-child { border-bottom: none; }

        @media print {
            body { background: none; margin: 0; padding: 0; }
            .paper { width: 100%; margin: 0; box-shadow: none; padding: 10mm; }
            .no-print { display: none !important; }
            .bg-gray-800 { background-color: #1f2937 !important; color: white !important; }
            .bg-blue-50 { background-color: #eff6ff !important; }
            .bg-red-600 { background-color: #dc2626 !important; color: white !important; }
            .bg-green-600 { background-color: #16a34a !important; color: white !important; }
        }
        .text-sm {
            font-size: 0.875rem;
            line-height: 0.75rem !important;
        }
    </style>
</head>
<body>

    <!-- Print Tugmasi -->
    <div class="max-w-4xl mx-auto mt-4 mb-4 text-right no-print">
        <a href="{{ route('checkout_calculateCost', ['year_month' => $year . '-' . $month]) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow inline-flex items-center gap-2">Тан нарни хисоблаш</a>
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow inline-flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Чоп этиш
        </button>
    </div>

    <!-- Qog'oz -->
    <div class="paper">
        
        <!-- Sarlavha -->
        <div class="text-center border-b-2 border-gray-800 pb-4 mb-3">
            <h1 class="text-2xl font-bold uppercase tracking-wide text-gray-900">Молиявий Ҳисобот</h1>
            <p class="text-gray-600 mt-1">
                Давр: <span class="font-bold text-black">{{ $year }} йил, {{ $monthName }}</span>
            </p>
        </div>

        <!-- 1. Асосий Савдо Блоки (Ўзгартирилган қисм) -->
       <div class="mb-6 border border-gray-300 rounded-lg overflow-hidden">
            <!-- Умумий сумма -->
            <div class="flex justify-between items-center bg-gray-100 p-3 border-b border-gray-200">
                <span class="text-gray-800 font-bold text-lg">📄 Умумий накладнойлар суммаси:</span>
                <span class="font-bold text-xl">{{ number_format($total_invoice_sum, 0, ' ', ' ') }} сўм</span>
            </div>

            <!-- Таннарх ва Маржа -->
            <div class="flex bg-white divide-x divide-gray-200">
    <!-- Таннарх -->
    <div class="w-1/2 p-3 text-center">
        <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">
            Товарлар таннархи 
            <!-- Agar taxminiy hisoblangan bo'lsa, ogohlantirish -->
            @if(isset($unknown_count) && $unknown_count > 0)
                <!-- title atributi qo'shildi, sichqoncha olib borganda ID lar chiqadi -->
                <span class="text-orange-500 font-bold normal-case cursor-help" 
                      title="Таннархи йўқлар: {{ implode(', ', $unknown_ids) }}">
                    ({{ $unknown_count }} таси тахминий*)
                </span>
            @endif
        </div>
        <div class="text-xl font-bold text-red-600">
            - {{ number_format($total_cost, 0, ' ', ' ') }}
        </div>
        
        @if(isset($unknown_count) && $unknown_count > 0)
            <div class="text-[10px] text-gray-400 mt-1 leading-tight">
                *{{ $unknown_count }} та товарнинг таннархи ўртача маржа асосида ҳисобланди.
                
                <!-- Qaysi ID lar ekanligini ro'yxat qilib chiqarish -->
                <div class="mt-1 text-gray-300 truncate hover:whitespace-normal hover:text-gray-500 transition-all cursor-pointer">
                    ID лар: {{ implode(', ', $unknown_ids) }}
                </div>
            </div>
        @endif
    </div>

    <!-- Маржа -->
    <div class="w-1/2 p-3 text-center bg-blue-50">
        <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Маржа (Фойда)</div>
        <div class="flex justify-center items-end gap-2">
            <span class="text-2xl font-bold text-green-600 leading-none">
                {{ number_format($margin_percent, 1) }}%
            </span>
            <span class="text-xs text-gray-400 mb-1">
                ({{ number_format($total_invoice_sum - $total_cost, 0, ' ', ' ') }})
            </span>
        </div>
    </div>
</div>
        </div>

        <!-- Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-3">
            
            <!-- 2. Кассага тушган жами маблағ -->
            <div class="border border-gray-300 rounded-lg overflow-hidden flex flex-col">
                <div class="bg-gray-800 text-white p-2 font-bold text-center uppercase text-sm tracking-wider">Кассага тушган жами маблағ</div>
                <div class="p-3 flex-grow bg-white">
                    <div class="flex justify-between font-bold mb-3 text-lg border-b-2 border-gray-100 pb-2">
                        <span>Жами:</span>
                        <span>{{ number_format($total_income['total'], 0, ' ', ' ') }}</span>
                    </div>
                    @foreach($paymentTypes as $type)
                    <div class="flex justify-between text-sm row-item text-gray-600">
                        <span>{{ $type->name_ru }}:</span>
                        <span class="font-medium text-gray-900">{{ number_format($total_income['by_type'][$type->id] ?? 0, 0, ' ', ' ') }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- 3. Накладнойлар бўйича тушум (Тозаланди) -->
            <div class="border border-gray-300 rounded-lg overflow-hidden flex flex-col">
                <div class="bg-gray-800 text-white p-2 font-bold text-center uppercase text-sm tracking-wider">Накладнойлар бўйича тушум</div>
                
                <div class="p-3 bg-white flex-grow">
                    <!-- Бу ердан Таннарх олиб ташланди -->
                    <div class="flex justify-between font-bold mb-3 text-lg border-b-2 border-gray-100 pb-2">
                         <span>Жами тушум:</span>
                         <span>{{ number_format($invoice_income['total'], 0, ' ', ' ') }}</span>
                    </div>
                    
                    @foreach($paymentTypes as $type)
                    <div class="flex justify-between text-sm row-item text-gray-600">
                        <span>{{ $type->name_ru }}:</span>
                        <span class="font-medium text-gray-900">{{ number_format($invoice_income['by_type'][$type->id] ?? 0, 0, ' ', ' ') }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- 5. Қарздорликлар -->
        <div class="mb-3">
            <div class="flex justify-between items-center bg-red-50 p-3 rounded-md font-bold text-red-700 border border-red-200">
                <span>⚠️ Қарздорликлар суммаси:</span>
                <span>{{ number_format($debt_sum, 0, ' ', ' ') }} сўм</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-3">
            <!-- 6. Харажатлар -->
            <div class="border border-gray-300 rounded-lg overflow-hidden">
                <div class="bg-red-600 text-white p-2 font-bold text-center uppercase text-sm tracking-wider">Харажатлар</div>
                <div class="p-3">
                    <div class="flex justify-between font-bold mb-3 text-lg border-b pb-2">
                        <span>Жами:</span>
                        <span>{{ number_format($expenses['total'], 0, ' ', ' ') }}</span>
                    </div>
                    @foreach($paymentTypes as $type)
                    <div class="flex justify-between text-sm row-item text-gray-600">
                        <span>{{ $type->name_ru }}:</span>
                        <span class="font-medium text-gray-900">{{ number_format($expenses['by_type'][$type->id] ?? 0, 0, ' ', ' ') }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- 7. Касса қолдиқ -->
            <div class="border border-gray-300 rounded-lg overflow-hidden">
                <div class="bg-green-600 text-white p-2 font-bold text-center uppercase text-sm tracking-wider">Касса қолдиқ</div>
                <div class="p-3">
                    <div class="flex justify-between font-bold mb-3 text-lg border-b pb-2">
                        <span>Жами:</span>
                        <span>{{ number_format($balance['total'], 0, ' ', ' ') }}</span>
                    </div>
                    @foreach($paymentTypes as $type)
                    <div class="flex justify-between text-sm row-item text-gray-600">
                        <span>{{ $type->name_ru }}:</span>
                        @php $val = $balance['by_type'][$type->id] ?? 0; @endphp
                        <span class="font-medium {{ $val < 0 ? 'text-red-600' : 'text-gray-900' }}">
                            {{ number_format($val, 0, ' ', ' ') }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Bottom Summary -->
        <div class="border-t-4 border-double border-gray-300 pt-4 space-y-3">
    
    <!-- 1. Kassa bo'yicha real foyda -->
    <div class="flex justify-between items-center text-lg font-bold text-blue-700 bg-blue-50 p-3 rounded border border-blue-200">
        <div class="flex flex-col">
            <span>📈 Соф фойда (Кассага тушган жами маблағ бўйича):</span>
            <span class="text-[10px] text-blue-500 font-normal">* Ушбу кўрсаткич жами касса кирими ва харажатлар айирмаси асосида (таннарх чегирилган ҳолда)</span>
        </div>
        <span>{{ number_format($net_profit_actual_cash, 0, ' ', ' ') }} сўм</span>
    </div>

    <!-- 2. Shu oygi nakladnoylar tushumi bo'yicha -->
    <div class="flex justify-between items-center text-md font-semibold text-gray-700 bg-gray-50 p-3 rounded border border-gray-200">
        <span>📊 Соф фойда ({{ $monthName }} ой накладнойлари бўйича тушумдан):</span>
        <span>{{ number_format($net_profit_invoice_cash, 0, ' ', ' ') }} сўм</span>
    </div>

    <!-- 3. Potensial foyda (Agar hamma to'laganda) -->
    <div class="flex justify-between items-center text-md font-semibold text-green-700 bg-green-50 p-3 rounded border border-green-200">
        <div class="flex flex-col">
            <span>💰 Соф фойда (Агарда тўлиқ тўлов амалга оширилганда):</span>
            <span class="text-[10px] text-green-500 font-normal italic">* Накладнойларнинг умумий суммасидан келиб чиқилган потенциал фойда</span>
        </div>
        <span>{{ number_format($net_profit_potential, 0, ' ', ' ') }} сўм</span>
    </div>
    
    <!-- 4. Egasi olgan pullar -->
    <div class="flex justify-between items-center text-sm text-gray-600 px-4 pt-2">
        <span>👤 Эгаси томонидан шахсий эҳтиёж учун олинган пул:</span>
        <span class="font-bold text-gray-800 italic">- {{ number_format($owner_withdrawal_sum, 0, ' ', ' ') }} сўм</span>
    </div>
</div>

        <!-- Footer -->
        <div class="mt-12 flex justify-between text-xs text-gray-400 pt-8 border-t border-gray-200">
            <div>Сана: {{ date('d.m.Y H:i') }}</div>
            <div class="text-right">
                <div>Имзо: _________________</div>
            </div>
        </div>

    </div>
    
    <div class="paper page-break">
        
        <div class="text-center border-b-2 border-gray-800 pb-4 mb-3">
            <h1 class="text-2xl font-bold uppercase tracking-wide text-gray-900">Харажатлар Таҳлили</h1>
            <p class="text-gray-600 mt-1">
                Давр: <span class="font-bold text-black">{{ $year }} йил, {{ $monthName }}</span>
            </p>
        </div>

        <!-- 1. Xarajat turlari bo'yicha jadval -->
        <div class="mb-10">
            <h3 class="text-lg font-bold text-gray-700 mb-3 border-l-4 border-red-600 pl-2">1. Харажат турлари ва тўлов усуллари</h3>
            
            <table class="w-full text-sm" style="font-size: 12px;">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="py-2 px-3">Харажат тури</th>
                        <th class="py-2 px-3 text-right bg-gray-200 text-gray-900">Жами</th>
                        @foreach($paymentTypes as $type)
                            <th class="py-2 px-3 text-right">{{ $type->name_ru }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($expenseTypes as $expType)
                        <!-- Shu turdagi xarajatlarni yig'amiz -->
                        @php
                            // Shu type_id ga tegishli barcha xarajatlar
                            $typeExpenses = $expenditures->where('cash_expenditure_types', $expType->id);
                            $rowTotal = $typeExpenses->sum('price');
                        @endphp

                        @if($rowTotal > 0) <!-- Faqat xarajati borlarni chiqaramiz -->
                        <tr class="hover:bg-gray-50">
                            <td class="font-medium text-gray-800 py-2 px-3">{{ $expType->name_ru ?? $expType->name }}</td>
                            
                            <!-- Umumiy -->
                            <td class="text-right font-bold text-gray-900 bg-gray-50 py-2 px-3">
                                {{ number_format($rowTotal, 0, ' ', ' ') }}
                            </td>

                            <!-- To'lov turlari bo'yicha -->
                            @foreach($paymentTypes as $payType)
                                @php
                                    $val = $typeExpenses->where('cash_receipt_type_id', $payType->id)->sum('price');
                                @endphp
                                <td class="text-right text-gray-600 py-2 px-3">
                                    {{ $val > 0 ? number_format($val, 0, ' ', ' ') : '-' }}
                                </td>
                            @endforeach
                        </tr>
                        @endif
                    @endforeach

                    <!-- Jami Qator (Footer) -->
                    <tr class="bg-gray-100 font-bold border-t-2 border-gray-400">
                        <td class="py-2 px-3 text-right">УМУМИЙ:</td>
                        <td class="text-right py-2 px-3">{{ number_format($expenses['total'], 0, ' ', ' ') }}</td>
                        @foreach($paymentTypes as $payType)
                            <td class="text-right py-2 px-3">
                                {{ number_format($expenses['by_type'][$payType->id] ?? 0, 0, ' ', ' ') }}
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>

        <hr class="border-gray-200 my-8">

        <!-- 2. Egasi olgan pullar ro'yxati -->
        <div>
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-lg font-bold text-gray-700 border-l-4 border-blue-600 pl-2">
                    2. Эгаси томонидан шахсий эҳтиёж учун олинган пул
                </h3>
                <span class="text-sm bg-blue-100 text-blue-800 py-1 px-2 rounded font-bold">
                    Жами: {{ number_format($owner_withdrawal_sum, 0, ' ', ' ') }} сўм
                </span>
            </div>

            <table class="w-full">
                <thead class="bg-blue-50 text-blue-900">
                    <tr>
                        <th class="w-10 text-center">#</th>
                        <th class="w-32">Сана</th>
                        <th>Изоҳ (Коммент)</th>
                        <th class="w-40 text-right">Сумма</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($owner_withdrawals_list as $index => $item)
                    <tr class="hover:bg-gray-50">
                        <td class="text-center text-gray-500">{{ $index + 1 }}</td>
                        <td class="text-gray-700">
                            {{ \Carbon\Carbon::parse($item->date)->format('d.m.Y') }}
                        </td>
                        <td class="text-gray-600">
                            {{ $item->comment ?? '-' }}
                        </td>
                        <td class="text-right font-bold text-gray-900">
                            {{ number_format($item->price, 0, ' ', ' ') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-gray-400">
                            Ушбу ойда маблағ олинмаган.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($owner_withdrawals_list->count() > 0)
                <tfoot class="bg-gray-100 font-bold">
                    <tr>
                        <td colspan="3" class="text-right py-2 px-3">ЖАМИ:</td>
                        <td class="text-right py-2 px-3 text-blue-700">
                            {{ number_format($owner_withdrawal_sum, 0, ' ', ' ') }}
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        <!-- Footer -->
        <div class="mt-12 flex justify-between text-xs text-gray-400 pt-8 border-t border-gray-200">
            <div>Сана: {{ date('d.m.Y H:i') }}</div>
            <div class="text-right">
                <div>Имзо: _________________</div>
            </div>
        </div>

    </div>
</body>
</html>