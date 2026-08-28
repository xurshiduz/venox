@extends('layouts.backend')

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="nk-block">
                    <div class="row g-gs">
                        <div class="col-md-6 col-lg-6 col-xl-4">
                            <div class="row g-gs">
                                <div class="col-md-12">
                                    <div class="card card-bordered pricing">
                                        <div class="pricing-head" style="padding: 0.75rem 0.5rem;">
                                            <div class="pricing-title">
                                                <h4 class="card-title title">Все склады</h4>
                                                <p class="sub-text">4 склада</p>
                                            </div>
                                            <div class="card-text">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <span class="h4 fw-500">02.11.2024</span>
                                                        <span class="sub-text">Дата начало</span>
                                                    </div>
                                                    <div class="col-6">
                                                        <span class="h4 fw-500">07.01.2025</span>
                                                        <span class="sub-text">Дата окончание</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="pricing-body" style="padding: 0.75rem 0.75rem 1rem;">
                                            <ul class="pricing-features" style="font-size: 18px;">
                                                <li><span class="w-50">Вид зачастей</span> - <span class="ms-auto">{{ number_format(App\Models\InventoryDetail::whereIn('warehouse_id', [1,2,3,4])->get()->groupBy('barcode')->count(), 0, '.', ' ') }}</span></li>
                                                <li><span class="w-50">Итого остаток</span> - <span class="ms-auto">{{ number_format(App\Models\InventoryDetail::whereIn('warehouse_id', [1,2,3,4])->sum('qty'), 0, '.', ' ') }}</span></li>
                                                <li><span class="w-50">Остаток в суммах (без маржа)</span> - <span class="ms-auto">{{ number_format(App\Models\InventoryDetail::whereIn('warehouse_id', [1,2,3,4])->sum('qty_tan_price'), 0, '.', ' ') }} сум</span></li>
                                                <li><span class="w-50"><i>Не внесено себестоимость</i></span> - <span class="ms-auto">{{ number_format(App\Models\InventoryDetail::whereIn('warehouse_id', [1,2,3,4])->whereNull('tan_price')->get()->groupBy('barcode')->count(), 0, '.', ' ') }} / {{ number_format(App\Models\InventoryDetail::whereIn('warehouse_id', [1,2,3,4])->whereNull('tan_price')->sum('qty'), 0, '.', ' ') }}</span></li>
                                                <li><span class="w-50">Остаток в суммах (с маржой)</span> - <span class="ms-auto">{{ number_format(App\Models\InventoryDetail::whereIn('warehouse_id', [1,2,3,4])->sum('qty_price'), 0, '.', ' ') }} сум</span></li>
                                                <li><span class="w-50"><i>Не внесено продажная цена</i></span> - <span class="ms-auto">{{ number_format(App\Models\InventoryDetail::whereIn('warehouse_id', [1,2,3,4])->whereNull('qty_price')->get()->groupBy('barcode')->count(), 0, '.', ' ') }} / {{ number_format(App\Models\InventoryDetail::whereIn('warehouse_id', [1,2,3,4])->whereNull('qty_price',)->sum('qty'), 0, '.', ' ') }}</span></li>
                                                <li><span class="w-50">Сотрудник</span> - <span class="ms-auto">6</span></li>
                                            </ul>
                                            <div class="pricing-action">
                                                <button class="btn btn-outline-light">Посмотреть отчет</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                        
                                <div class="col-md-12">
                                    <div class="card card-bordered card-full">
                                        <div class="card-inner">
                                            <div class="card-title-group align-start mb-0">
                                                <div class="card-title">
                                                    <h6 class="subtitle">Статистика о расходе</h6>
                                                </div>
                                            </div>
                                            <div class="card-amount">
                                                <span class="amount"> С 30.09.2024 по настоящее время продано {{ number_format(App\Models\CheckoutDetail::whereIn('warehouse_id', [1,2,3,4])->sum('qty'), 0, '.', ' ') }} запчастей.
                                                </span>
                                            </div>
                                            <div class="invest-data">
                                                <div class="invest-data-amount g-2">
                                                    <div class="invest-data-history">
                                                        <div class="title">Общая сумма</div>
                                                        <div class="amount">{{ number_format(App\Models\CheckoutDetail::whereIn('warehouse_id', [1,2,3,4])->sum('total_price'), 0, '.', ' ') }} сум </div>
                                                    </div>
                                                    <div class="invest-data-history">
                                                        <div class="title">Не внесено продажная цена</div>
                                                        <div class="amount">{{ number_format(App\Models\CheckoutDetail::whereIn('warehouse_id', [1,2,3,4])->whereNull('total_price')->sum('qty'), 0, '.', ' ') }} </div>
                                                    </div>
                                                </div>
                                                <div class="invest-data-ck">
                                                    <canvas class="iv-data-chart" id="totalDeposit"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!-- .card -->
                                </div><!-- .col -->
                            </div><!-- .col -->
                        </div>
                        <div class="col-md-6 col-lg-6 col-xl-4">
                            <div class="row g-gs">
                                <div class="col-md-12">
                                    <div class="card card-bordered pricing">
                                        <div class="pricing-head" style="padding: 0.75rem 0.5rem;">
                                            <div class="pricing-title">
                                                <h4 class="card-title title">Dizel Motors</h4>
                                                <p class="sub-text">Зав. склад: Шохжахон</p>
                                            </div>
                                            <div class="card-text">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <span class="h4 fw-500">02.11.2024</span>
                                                        <span class="sub-text">Дата начало</span>
                                                    </div>
                                                    <div class="col-6">
                                                        <span class="h4 fw-500">28 дней</span>
                                                        <span class="sub-text">Выполнено за</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="pricing-body" style="padding: 0.75rem 0.75rem 1rem;">
                                            <ul class="pricing-features" style="font-size: 18px;">
                                                <li><span class="w-50">Вид зачастей</span> - <span class="ms-auto">{{ number_format(App\Models\InventoryDetail::where('warehouse_id', 1)->get()->groupBy('barcode')->count(), 0, '.', ' ') }}</span></li>
                                                <li><span class="w-50">Итого остаток</span> - <span class="ms-auto">{{ number_format(App\Models\InventoryDetail::where('warehouse_id', 1)->sum('qty'), 0, '.', ' ') }}</span></li>
                                                <li><span class="w-50">Остаток в суммах (без маржа)</span> - <span class="ms-auto">{{ number_format(App\Models\InventoryDetail::where('warehouse_id', 1)->sum('qty_tan_price'), 0, '.', ' ') }} сум</span></li>
                                                <li><span class="w-50"><i>Не внесено себестоимость</i></span> - <span class="ms-auto">{{ number_format(App\Models\InventoryDetail::where('warehouse_id', 1)->whereNull('tan_price')->get()->groupBy('barcode')->count(), 0, '.', ' ') }} / {{ number_format(App\Models\InventoryDetail::where('warehouse_id', 1)->whereNull('tan_price')->sum('qty'), 0, '.', ' ') }}</span></li>
                                                <li><span class="w-50">Остаток в суммах (с маржой)</span> - <span class="ms-auto">{{ number_format(App\Models\InventoryDetail::where('warehouse_id', 1)->sum('qty_price'), 0, '.', ' ') }} сум</span></li>
                                                <li><span class="w-50"><i>Не внесено продажная цена</i></span> - <span class="ms-auto">{{ number_format(App\Models\InventoryDetail::where('warehouse_id', 1)->whereNull('qty_price')->get()->groupBy('barcode')->count(), 0, '.', ' ') }} / {{ number_format(App\Models\InventoryDetail::where('warehouse_id', 1)->whereNull('qty_price',)->sum('qty'), 0, '.', ' ') }}</span></li>
                                                <li><span class="w-50">Сотрудник</span> - <span class="ms-auto">6</span></li>
                                            </ul>
                                            <div class="pricing-action">
                                                <button class="btn btn-outline-light">Посмотреть отчет</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                        
                                <div class="col-md-12">
                                    <div class="card card-bordered card-full">
                                        <div class="card-inner">
                                            <div class="card-title-group align-start mb-0">
                                                <div class="card-title">
                                                    <h6 class="subtitle">Статистика о расходе</h6>
                                                </div>
                                            </div>
                                            <div class="card-amount">
                                                <span class="amount"> С 30.09.2024 по настоящее время продано {{ number_format(App\Models\CheckoutDetail::where('warehouse_id', 1)->sum('qty'), 0, '.', ' ') }} запчастей.
                                                </span>
                                            </div>
                                            <div class="invest-data">
                                                <div class="invest-data-amount g-2">
                                                    <div class="invest-data-history">
                                                        <div class="title">Общая сумма</div>
                                                        <div class="amount">{{ number_format(App\Models\CheckoutDetail::where('warehouse_id', 1)->sum('total_price'), 0, '.', ' ') }} сум </div>
                                                    </div>
                                                    <div class="invest-data-history">
                                                        <div class="title">Не внесено продажная цена</div>
                                                        <div class="amount">{{ number_format(App\Models\CheckoutDetail::where('warehouse_id', 1)->whereNull('total_price')->sum('qty'), 0, '.', ' ') }} </div>
                                                    </div>
                                                </div>
                                                <div class="invest-data-ck">
                                                    <canvas class="iv-data-chart" id="totalDeposit"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!-- .card -->
                                </div><!-- .col -->
                            </div><!-- .col -->
                        </div>
                        
                        <div class="col-md-6 col-lg-6 col-xl-4">
                            <div class="row g-gs">
                                <div class="col-md-12">
                                    <div class="card card-bordered pricing">
                                        <div class="pricing-head" style="padding: 0.75rem 0.5rem;">
                                            <div class="pricing-title">
                                                <h4 class="card-title title">Simma Auto Star</h4>
                                                <p class="sub-text">Зав. склад: Абдулла</p>
                                            </div>
                                            <div class="card-text">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <span class="h4 fw-500">02.12.2024</span>
                                                        <span class="sub-text">Дата начало</span>
                                                    </div>
                                                    <div class="col-6">
                                                        <span class="h4 fw-500">18 дней</span>
                                                        <span class="sub-text">Выполнено за</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="pricing-body" style="padding: 0.75rem 0.75rem 1rem;">
                                            <ul class="pricing-features" style="font-size: 18px;">
                                                <li><span class="w-50">Вид зачастей</span> - <span class="ms-auto">{{ number_format(App\Models\InventoryDetail::where('warehouse_id', 2)->get()->groupBy('barcode')->count(), 0, '.', ' ') }}</span></li>
                                                <li><span class="w-50">Итого остаток</span> - <span class="ms-auto">{{ number_format(App\Models\InventoryDetail::where('warehouse_id', 2)->sum('qty'), 0, '.', ' ') }}</span></li>
                                                <li><span class="w-50">Остаток в суммах (без маржа)</span> - <span class="ms-auto">{{ number_format(App\Models\InventoryDetail::where('warehouse_id', 2)->sum('qty_tan_price'), 0, '.', ' ') }} сум</span></li>
                                                <li><span class="w-50"><i>Не внесено себестоимость</i></span> - <span class="ms-auto">{{ number_format(App\Models\InventoryDetail::where('warehouse_id', 2)->whereNull('tan_price')->get()->groupBy('barcode')->count(), 0, '.', ' ') }} / {{ number_format(App\Models\InventoryDetail::where('warehouse_id', 2)->whereNull('tan_price')->sum('qty'), 0, '.', ' ') }}</span></li>
                                                <li><span class="w-50">Остаток в суммах (с маржой)</span> - <span class="ms-auto">{{ number_format(App\Models\InventoryDetail::where('warehouse_id', 2)->sum('qty_price'), 0, '.', ' ') }} сум</span></li>
                                                <li><span class="w-50"><i>Не внесено продажная цена</i></span> - <span class="ms-auto">{{ number_format(App\Models\InventoryDetail::where('warehouse_id', 2)->whereNull('qty_price')->get()->groupBy('barcode')->count(), 0, '.', ' ') }} / {{ number_format(App\Models\InventoryDetail::where('warehouse_id', 2)->whereNull('qty_price',)->sum('qty'), 0, '.', ' ') }}</span></li>
                                                <li><span class="w-50">Сотрудник</span> - <span class="ms-auto">6</span></li>
                                            </ul>
                                            <div class="pricing-action">
                                                <button class="btn btn-outline-light">Посмотреть отчет</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                        
                                <div class="col-md-12">
                                    <div class="card card-bordered card-full">
                                        <div class="card-inner">
                                            <div class="card-title-group align-start mb-0">
                                                <div class="card-title">
                                                    <h6 class="subtitle">Статистика о расходе</h6>
                                                </div>
                                            </div>
                                            <div class="card-amount">
                                                <span class="amount"> С 30.09.2024 по настоящее время продано {{ number_format(App\Models\CheckoutDetail::where('warehouse_id', 2)->sum('qty'), 0, '.', ' ') }} запчастей.
                                                </span>
                                            </div>
                                            <div class="invest-data">
                                                <div class="invest-data-amount g-2">
                                                    <div class="invest-data-history">
                                                        <div class="title">Общая сумма</div>
                                                        <div class="amount">{{ number_format(App\Models\CheckoutDetail::where('warehouse_id', 2)->sum('total_price'), 0, '.', ' ') }} сум </div>
                                                    </div>
                                                    <div class="invest-data-history">
                                                        <div class="title">Не внесено продажная цена</div>
                                                        <div class="amount">{{ number_format(App\Models\CheckoutDetail::where('warehouse_id', 2)->whereNull('total_price')->sum('qty'), 0, '.', ' ') }} </div>
                                                    </div>
                                                </div>
                                                <div class="invest-data-ck">
                                                    <canvas class="iv-data-chart" id="totalDeposit"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!-- .card -->
                                </div><!-- .col -->
                            </div><!-- .col -->
                        </div>
                        
                        <div class="col-md-6 col-lg-6 col-xl-4">
                            <div class="row g-gs">
                                <div class="col-md-12">
                                    <div class="card card-bordered pricing">
                                        <div class="pricing-head" style="padding: 0.75rem 0.5rem;">
                                            <div class="pricing-title">
                                                <h4 class="card-title title">Zapchast Baza</h4>
                                                <p class="sub-text">Зав. склад: Мехрож</p>
                                            </div>
                                            <div class="card-text">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <span class="h4 fw-500">20.11.2024</span>
                                                        <span class="sub-text">Дата начало</span>
                                                    </div>
                                                    <div class="col-6">
                                                        <span class="h4 fw-500">10 дней</span>
                                                        <span class="sub-text">Выполнено за</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="pricing-body" style="padding: 0.75rem 0.75rem 1rem;">
                                            <ul class="pricing-features" style="font-size: 18px;">
                                                <li><span class="w-50">Вид зачастей</span> - <span class="ms-auto">{{ number_format(App\Models\InventoryDetail::where('warehouse_id', 3)->get()->groupBy('barcode')->count(), 0, '.', ' ') }}</span></li>
                                                <li><span class="w-50">Итого остаток</span> - <span class="ms-auto">{{ number_format(App\Models\InventoryDetail::where('warehouse_id', 3)->sum('qty'), 0, '.', ' ') }}</span></li>
                                                <li><span class="w-50">Остаток в суммах (без маржа)</span> - <span class="ms-auto">{{ number_format(App\Models\InventoryDetail::where('warehouse_id', 3)->sum('qty_tan_price'), 0, '.', ' ') }} сум</span></li>
                                                <li><span class="w-50"><i>Не внесено себестоимость</i></span> - <span class="ms-auto">{{ number_format(App\Models\InventoryDetail::where('warehouse_id', 3)->whereNull('tan_price')->get()->groupBy('barcode')->count(), 0, '.', ' ') }} / {{ number_format(App\Models\InventoryDetail::where('warehouse_id', 3)->whereNull('tan_price')->sum('qty'), 0, '.', ' ') }}</span></li>
                                                <li><span class="w-50">Остаток в суммах (с маржой)</span> - <span class="ms-auto">{{ number_format(App\Models\InventoryDetail::where('warehouse_id', 3)->sum('qty_price'), 0, '.', ' ') }} сум</span></li>
                                                <li><span class="w-50"><i>Не внесено продажная цена</i></span> - <span class="ms-auto">{{ number_format(App\Models\InventoryDetail::where('warehouse_id', 3)->whereNull('qty_price')->get()->groupBy('barcode')->count(), 0, '.', ' ') }} / {{ number_format(App\Models\InventoryDetail::where('warehouse_id', 3)->whereNull('qty_price',)->sum('qty'), 0, '.', ' ') }}</span></li>
                                                <li><span class="w-50">Сотрудник</span> - <span class="ms-auto">12</span></li>
                                            </ul>
                                            <div class="pricing-action">
                                                <button class="btn btn-outline-light">Посмотреть отчет</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                        
                                <div class="col-md-12">
                                    <div class="card card-bordered card-full">
                                        <div class="card-inner">
                                            <div class="card-title-group align-start mb-0">
                                                <div class="card-title">
                                                    <h6 class="subtitle">Статистика о расходе</h6>
                                                </div>
                                            </div>
                                            <div class="card-amount">
                                                <span class="amount"> С 30.09.2024 по настоящее время продано {{ number_format(App\Models\CheckoutDetail::where('warehouse_id', 3)->sum('qty'), 0, '.', ' ') }} запчастей.
                                                </span>
                                            </div>
                                            <div class="invest-data">
                                                <div class="invest-data-amount g-2">
                                                    <div class="invest-data-history">
                                                        <div class="title">Общая сумма</div>
                                                        <div class="amount">{{ number_format(App\Models\CheckoutDetail::where('warehouse_id', 3)->sum('total_price'), 0, '.', ' ') }} сум </div>
                                                    </div>
                                                    <div class="invest-data-history">
                                                        <div class="title">Не внесено продажная цена</div>
                                                        <div class="amount">{{ number_format(App\Models\CheckoutDetail::where('warehouse_id', 3)->whereNull('total_price')->sum('qty'), 0, '.', ' ') }} </div>
                                                    </div>
                                                </div>
                                                <div class="invest-data-ck">
                                                    <canvas class="iv-data-chart" id="totalDeposit"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!-- .card -->
                                </div><!-- .col -->
                            </div><!-- .col -->
                        </div>
                        
                        <div class="col-md-6 col-lg-6 col-xl-4">
                            <div class="row g-gs">
                                <div class="col-md-12">
                                    <div class="card card-bordered pricing">
                                        <div class="pricing-head" style="padding: 0.75rem 0.5rem;">
                                            <div class="pricing-title">
                                                <h4 class="card-title title">Plashatka</h4>
                                                <p class="sub-text">Зав. склад: Мехрож</p>
                                            </div>
                                            <div class="card-text">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <span class="h4 fw-500">02.01.2025</span>
                                                        <span class="sub-text">Дата начало</span>
                                                    </div>
                                                    <div class="col-6">
                                                        <span class="h4 fw-500">4 дней</span>
                                                        <span class="sub-text">Выполнено за</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="pricing-body" style="padding: 0.75rem 0.75rem 1rem;">
                                            <ul class="pricing-features" style="font-size: 18px;">
                                                <li><span class="w-50">Вид зачастей</span> - <span class="ms-auto">{{ number_format(App\Models\InventoryDetail::where('warehouse_id', 4)->get()->groupBy('barcode')->count(), 0, '.', ' ') }}</span></li>
                                                <li><span class="w-50">Итого остаток</span> - <span class="ms-auto">{{ number_format(App\Models\InventoryDetail::where('warehouse_id', 4)->sum('qty'), 0, '.', ' ') }}</span></li>
                                                <li><span class="w-50">Остаток в суммах (без маржа)</span> - <span class="ms-auto">{{ number_format(App\Models\InventoryDetail::where('warehouse_id', 4)->sum('qty_tan_price'), 0, '.', ' ') }} сум</span></li>
                                                <li><span class="w-50"><i>Не внесено себестоимость</i></span> - <span class="ms-auto">{{ number_format(App\Models\InventoryDetail::where('warehouse_id', 4)->whereNull('tan_price')->get()->groupBy('barcode')->count(), 0, '.', ' ') }} / {{ number_format(App\Models\InventoryDetail::where('warehouse_id', 4)->whereNull('tan_price')->sum('qty'), 0, '.', ' ') }}</span></li>
                                                <li><span class="w-50">Остаток в суммах (с маржой)</span> - <span class="ms-auto">{{ number_format(App\Models\InventoryDetail::where('warehouse_id', 4)->sum('qty_price'), 0, '.', ' ') }} сум</span></li>
                                                <li><span class="w-50"><i>Не внесено продажная цена</i></span> - <span class="ms-auto">{{ number_format(App\Models\InventoryDetail::where('warehouse_id', 4)->whereNull('qty_price')->get()->groupBy('barcode')->count(), 0, '.', ' ') }} / {{ number_format(App\Models\InventoryDetail::where('warehouse_id', 4)->whereNull('qty_price',)->sum('qty'), 0, '.', ' ') }}</span></li>
                                                <li><span class="w-50">Сотрудник</span> - <span class="ms-auto">6</span></li>
                                            </ul>
                                            <div class="pricing-action">
                                                <button class="btn btn-outline-light">Посмотреть отчет</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                        
                                <div class="col-md-12">
                                    <div class="card card-bordered card-full">
                                        <div class="card-inner">
                                            <div class="card-title-group align-start mb-0">
                                                <div class="card-title">
                                                    <h6 class="subtitle">Статистика о расходе</h6>
                                                </div>
                                            </div>
                                            <div class="card-amount">
                                                <span class="amount"> С 30.09.2024 по настоящее время продано {{ number_format(App\Models\CheckoutDetail::where('warehouse_id', 4)->sum('qty'), 0, '.', ' ') }} запчастей.
                                                </span>
                                            </div>
                                            <div class="invest-data">
                                                <div class="invest-data-amount g-2">
                                                    <div class="invest-data-history">
                                                        <div class="title">Общая сумма</div>
                                                        <div class="amount">{{ number_format(App\Models\CheckoutDetail::where('warehouse_id', 4)->sum('total_price'), 0, '.', ' ') }} сум </div>
                                                    </div>
                                                    <div class="invest-data-history">
                                                        <div class="title">Не внесено продажная цена</div>
                                                        <div class="amount">{{ number_format(App\Models\CheckoutDetail::where('warehouse_id', 4)->whereNull('total_price')->sum('qty'), 0, '.', ' ') }} </div>
                                                    </div>
                                                </div>
                                                <div class="invest-data-ck">
                                                    <canvas class="iv-data-chart" id="totalDeposit"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div><!-- .card -->
                                </div><!-- .col -->
                            </div><!-- .col -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    
        $(document).ready(function (){
            $('body').on('click', '#alltime2', function () {
            $.ajax({
                type: 'GET',
                url: '{{ route("topclienttwo_api") }}', 
                success:function (data) {
                    $.each(data, function (index, item){
                        $(".modelListNew").append($(
                            '<div class="invest-ov-details">
                                <div class="invest-ov-info">
                                    <div class="amount">{ item.name }</div>
                                    <div class="title">{ item.name }</div>
                                </div>
                                <div class="invest-ov-stats ms-1">
                                    <div><span class="amount">{ item.name }</span></div>
                                    <div class="title">шт. накладной</div>
                                </div>
                            </div>'));
                    });
                }
            });
            });
        });
    </script>
    @endsection