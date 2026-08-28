<div class="table-responsive">
    <table class="table table-striped table-bordered nowrap nk-tb-list nk-tb-ulist" data-auto-responsive="false">
        <thead>
            <tr class="nk-tb-item nk-tb-head">
                <th class="nk-tb-col">Мижоз исми</th>
                <th class="nk-tb-col">Тел рақами</th>
                <th class="nk-tb-col">Бошланғич қарз</th>
                <th class="nk-tb-col">Возврат</th>
                <th class="nk-tb-col">Умумий қарзи</th>
                <th class="nk-tb-col text-warning">30 кундан ошган</th>
                <th class="nk-tb-col text-danger">60 кундан ошган</th>
                <th class="nk-tb-col text-danger"><b>90 кундан ошган</b></th>
                <th class="nk-tb-col">Охирги тўлов</th>
                <th class="nk-tb-col">Санаси</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report_data as $item)
            <tr class="nk-tb-item">
                <td class="nk-tb-col"><span class="tb-lead">{{ $item->name }}</span></td>
                <td class="nk-tb-col"><span>{{ $item->phone }}</span></td>
                <td class="nk-tb-col"><span class="tb-amount fw-bold">{{ number_format($item->initial_debt, 0, '.', ' ') }} {{ $currency_label }}</span></td>
                <td class="nk-tb-col"><span class="tb-amount fw-bold">{{ number_format($item->total_return, 0, '.', ' ') }} {{ $currency_label }}</span></td>
                <td class="nk-tb-col"><span class="tb-amount fw-bold">{{ number_format($item->total_debt, 0, '.', ' ') }} {{ $currency_label }}</span></td>
                <td class="nk-tb-col">
                    @if($item->debt_30 > 0)
                        <span class="badge badge-dim bg-warning">{{ number_format($item->debt_30, 0, '.', ' ') }}</span>
                    @else - @endif
                </td>
                <td class="nk-tb-col">
                    @if($item->debt_60 > 0)
                        <span class="badge badge-dim bg-danger">{{ number_format($item->debt_60, 0, '.', ' ') }}</span>
                    @else - @endif
                </td>
                <td class="nk-tb-col">
                    @if($item->debt_90 > 0)
                        <span class="badge badge-dim bg-danger fw-bold">{{ number_format($item->debt_90, 0, '.', ' ') }}</span>
                    @else - @endif
                </td>
                <td class="nk-tb-col">
                    @if($item->last_payment_amount > 0)
                        <span class="text-success">+{{ number_format($item->last_payment_amount, 0, '.', ' ') }}</span>
                    @else - @endif
                </td>
                <td class="nk-tb-col"><span>{{ $item->last_payment_date }}</span></td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center">Қарздор мижозлар топилмади</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="fw-bold">
                <td colspan="2" class="text-end">ЖАМИ:</td>
                <td>{{ number_format(collect($report_data)->sum('initial_debt'), 0, '.', ' ') }}</td>
                <td>{{ number_format(collect($report_data)->sum('total_return'), 0, '.', ' ') }}</td>
                <td>{{ number_format(collect($report_data)->sum('total_debt'), 0, '.', ' ') }}</td>
                <td>{{ number_format(collect($report_data)->sum('debt_30'), 0, '.', ' ') }}</td>
                <td>{{ number_format(collect($report_data)->sum('debt_60'), 0, '.', ' ') }}</td>
                <td>{{ number_format(collect($report_data)->sum('debt_90'), 0, '.', ' ') }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
</div>