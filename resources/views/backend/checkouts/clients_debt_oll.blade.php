@extends('layouts.backend')

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="components-preview mx-auto">
                    <div class="nk-block nk-block-sm">
                        <div class="card card-preview">
                            <div class="card-inner">
                               <a href="{{ route('checkout_downloadDebtReport') }}" class="btn btn-primary btn-sm mb-2">Excel yuklash</a> 
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered nowrap nk-tb-list nk-tb-ulist" data-auto-responsive="false">
                                        <thead>
                                            <tr class="nk-tb-item nk-tb-head">
                                                <th class="nk-tb-col">Мижоз исми</th>
                                                <th class="nk-tb-col">Тел рақами</th>
                                                <th class="nk-tb-col text-info">Бошланғич қарзи</th> <!-- YANGI USTUN -->
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
                                                <td class="nk-tb-col">
                                                    <span class="tb-lead">{{ $item->name }}</span>
                                                </td>
                                                <td class="nk-tb-col">
                                                    <span>{{ $item->phone }}</span>
                                                </td>
                                                <td class="nk-tb-col"> <!-- YANGI QATOR -->
                                                    <span>{{ number_format($item->initial_debt, 0, '.', ' ') }}</span>
                                                </td>
                                                <td class="nk-tb-col">
                                                    <span class="tb-amount fw-bold">{{ number_format($item->total_debt, 0, '.', ' ') }}</span>
                                                </td>
                                                <td class="nk-tb-col">
                                                    @if($item->debt_30 > 0)
                                                        <span class="badge badge-dim bg-warning">{{ number_format($item->debt_30, 0, '.', ' ') }}</span>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="nk-tb-col">
                                                    @if($item->debt_60 > 0)
                                                        <span class="badge badge-dim bg-danger">{{ number_format($item->debt_60, 0, '.', ' ') }}</span>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="nk-tb-col">
                                                    @if($item->debt_90 > 0)
                                                        <span class="badge badge-dim bg-danger fw-bold">{{ number_format($item->debt_90, 0, '.', ' ') }}</span>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="nk-tb-col">
                                                    @if($item->last_payment_amount > 0)
                                                        <span class="text-success">+{{ number_format($item->last_payment_amount, 0, '.', ' ') }}</span>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="nk-tb-col">
                                                    <span>{{ $item->last_payment_date }}</span>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="9" class="text-center">Qarzdor mijozlar topilmadi</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                        <tfoot>
                                            <tr class="fw-bold">
                                                <td colspan="2" class="text-end">JAMI:</td>
                                                <td>{{ number_format(collect($report_data)->sum('initial_debt'), 0, '.', ' ') }}</td> <!-- YANGI QATOR -->
                                                <td>{{ number_format(collect($report_data)->sum('total_debt'), 0, '.', ' ') }}</td>
                                                <td>{{ number_format(collect($report_data)->sum('debt_30'), 0, '.', ' ') }}</td>
                                                <td>{{ number_format(collect($report_data)->sum('debt_60'), 0, '.', ' ') }}</td>
                                                <td>{{ number_format(collect($report_data)->sum('debt_90'), 0, '.', ' ') }}</td>
                                                <td colspan="2"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection