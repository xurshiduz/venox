@extends('layouts.backend')

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="nk-block">
                    <div class="row g-gs">
                        <div class="col-md-4">
                            <div class="card card-bordered card-full">
                                <div class="card-inner">
                                    <div class="card-title-group align-start mb-0">
                                        <div class="card-title">
                                            <h6 class="subtitle">Статистика о приходе</h6>
                                        </div>
                                    </div>
                                    <div class="card-amount">
                                        <span class="amount"> 49,595.34 
                                        </span>
                                    </div>
                                    <div class="invest-data">
                                        <div class="invest-data-amount g-2">
                                            <div class="invest-data-history">
                                                <div class="title">За месяц</div>
                                                <div class="amount">2,940.59 </div>
                                            </div>
                                            <div class="invest-data-history">
                                                <div class="title">Всего</div>
                                                <div class="amount">1,259.28 </div>
                                            </div>
                                        </div>
                                        <div class="invest-data-ck">
                                            <canvas class="iv-data-chart" id="totalDeposit"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- .card -->
                        </div><!-- .col -->
                        <div class="col-md-4">
                            <div class="card card-bordered card-full">
                                <div class="card-inner">
                                    <div class="card-title-group align-start mb-0">
                                        <div class="card-title">
                                            <h6 class="subtitle">Статистика о расходе</h6>
                                        </div>
                                    </div>
                                    <div class="card-amount">
                                        <span class="amount"> 49,595.34 
                                        </span>
                                    </div>
                                    <div class="invest-data">
                                        <div class="invest-data-amount g-2">
                                            <div class="invest-data-history">
                                                <div class="title">За месяц</div>
                                                <div class="amount">2,940.59 </div>
                                            </div>
                                            <div class="invest-data-history">
                                                <div class="title">Всего</div>
                                                <div class="amount">1,259.28 </div>
                                            </div>
                                        </div>
                                        <div class="invest-data-ck">
                                            <canvas class="iv-data-chart" id="totalWithdraw"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- .card -->
                        </div><!-- .col -->
                        <div class="col-md-4">
                            <div class="card card-bordered  card-full">
                                <div class="card-inner">
                                    <div class="card-title-group align-start mb-0">
                                        <div class="card-title">
                                            <h6 class="subtitle">Финансовый отчет</h6>
                                        </div>
                                    </div>
                                    <div class="card-amount">
                                        <span class="amount"> 79,358.50 <span class="currency currency-usd">USD</span>
                                        </span>
                                    </div>
                                    <div class="invest-data">
                                        <div class="invest-data-amount g-2">
                                            <div class="invest-data-history">
                                                <div class="title">За месяц</div>
                                                <div class="amount">2,940.59 <span class="currency currency-usd">USD</span></div>
                                            </div>
                                            <div class="invest-data-history">
                                                <div class="title">Всего</div>
                                                <div class="amount">1,259.28 <span class="currency currency-usd">USD</span></div>
                                            </div>
                                        </div>
                                        <div class="invest-data-ck">
                                            <canvas class="iv-data-chart" id="totalBalance"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- .card -->
                        </div><!-- .col -->
                        <div class="col-md-6 col-xxl-4">
                            <div class="card card-bordered card-full">
                                <div class="card-inner">
                                    <div class="card-title-group mb-1">
                                        <div class="card-title">
                                            <h6 class="title">Финансовый отчет</h6>
                                        </div>
                                    </div>
                                    <ul class="nav nav-tabs nav-tabs-card nav-tabs-xs">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-bs-toggle="tab" href="#overview">Overview</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#thisyear">This Year</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#alltime">All Time</a>
                                        </li>
                                    </ul>
                                    <div class="tab-content mt-0">
                                        <div class="tab-pane active" id="overview">
                                            <div class="invest-ov gy-2">
                                                <div class="subtitle">Currently Actived Investment</div>
                                                <div class="invest-ov-details">
                                                    <div class="invest-ov-info">
                                                        <div class="amount">49,395.395 <span class="currency currency-usd">USD</span></div>
                                                        <div class="title">Amount</div>
                                                    </div>
                                                    <div class="invest-ov-stats ms-1">
                                                        <div><span class="amount">56</span><span class="change up text-danger"><em class="icon ni ni-arrow-long-up"></em>1.93%</span></div>
                                                        <div class="title">Plans</div>
                                                    </div>
                                                </div>
                                                <div class="invest-ov-details">
                                                    <div class="invest-ov-info">
                                                        <div class="amount">49,395.395 <span class="currency currency-usd">USD</span></div>
                                                        <div class="title">Paid Profit</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="invest-ov gy-2">
                                                <div class="subtitle">Investment in this Month</div>
                                                <div class="invest-ov-details">
                                                    <div class="invest-ov-info">
                                                        <div class="amount">49,395.395 <span class="currency currency-usd">USD</span></div>
                                                        <div class="title">Amount</div>
                                                    </div>
                                                    <div class="invest-ov-stats ms-1">
                                                        <div><span class="amount">23</span><span class="change down text-danger"><em class="icon ni ni-arrow-long-down"></em>1.93%</span></div>
                                                        <div class="title">Plans</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane" id="thisyear">
                                            <div class="invest-ov gy-2">
                                                <div class="subtitle">Currently Actived Investment</div>
                                                <div class="invest-ov-details">
                                                    <div class="invest-ov-info">
                                                        <div class="amount">89,395.395 <span class="currency currency-usd">USD</span></div>
                                                        <div class="title">Amount</div>
                                                    </div>
                                                    <div class="invest-ov-stats ms-1">
                                                        <div><span class="amount">96</span><span class="change up text-danger"><em class="icon ni ni-arrow-long-up"></em>1.93%</span></div>
                                                        <div class="title">Plans</div>
                                                    </div>
                                                </div>
                                                <div class="invest-ov-details">
                                                    <div class="invest-ov-info">
                                                        <div class="amount">99,395.395 <span class="currency currency-usd">USD</span></div>
                                                        <div class="title">Paid Profit</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="invest-ov gy-2">
                                                <div class="subtitle">Investment in this Month</div>
                                                <div class="invest-ov-details">
                                                    <div class="invest-ov-info">
                                                        <div class="amount">149,395.395 <span class="currency currency-usd">USD</span></div>
                                                        <div class="title">Amount</div>
                                                    </div>
                                                    <div class="invest-ov-stats ms-1">
                                                        <div><span class="amount">83</span><span class="change down text-danger"><em class="icon ni ni-arrow-long-down"></em>1.93%</span></div>
                                                        <div class="title">Plans</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane" id="alltime">
                                            <div class="invest-ov gy-2">
                                                <div class="subtitle">Currently Actived Investment</div>
                                                <div class="invest-ov-details">
                                                    <div class="invest-ov-info">
                                                        <div class="amount">249,395.395 <span class="currency currency-usd">USD</span></div>
                                                        <div class="title">Amount</div>
                                                    </div>
                                                    <div class="invest-ov-stats ms-1">
                                                        <div><span class="amount">556</span><span class="change up text-danger"><em class="icon ni ni-arrow-long-up"></em>1.93%</span></div>
                                                        <div class="title">Plans</div>
                                                    </div>
                                                </div>
                                                <div class="invest-ov-details">
                                                    <div class="invest-ov-info">
                                                        <div class="amount">149,395.395 <span class="currency currency-usd">USD</span></div>
                                                        <div class="title">Paid Profit</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="invest-ov gy-2">
                                                <div class="subtitle">Investment in this Month</div>
                                                <div class="invest-ov-details">
                                                    <div class="invest-ov-info">
                                                        <div class="amount">249,395.395 <span class="currency currency-usd">USD</span></div>
                                                        <div class="title">Amount</div>
                                                    </div>
                                                    <div class="invest-ov-stats ms-1">
                                                        <div><span class="amount">223</span><span class="change down text-danger"><em class="icon ni ni-arrow-long-down"></em>1.93%</span></div>
                                                        <div class="title">Plans</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div><!-- .col -->
                        <div class="col-md-6 col-xxl-4">
                            <div class="card card-bordered card-full">
                                <div class="card-inner d-flex flex-column h-100">
                                    <div class="card-title-group mb-3">
                                        <div class="card-title">
                                            <h6 class="title">Статистика по статусе</h6>
                                        </div>
                                    </div>
                                    <div class="progress-list gy-3">
                                        <div class="progress-wrap">
                                            <div class="progress-text">
                                                <div class="progress-label">Итого</div>
                                                <div class="progress-amount">58%</div>
                                            </div>
                                            <div class="progress progress-md">
                                                <div class="progress-bar" data-progress="58"></div>
                                            </div>
                                        </div>
                                        <div class="progress-wrap">
                                            <div class="progress-text">
                                                <div class="progress-label">Остаток</div>
                                                <div class="progress-amount">18.49%</div>
                                            </div>
                                            <div class="progress progress-md">
                                                <div class="progress-bar bg-orange" data-progress="18.49"></div>
                                            </div>
                                        </div>
                                        <div class="progress-wrap">
                                            <div class="progress-text">
                                                <div class="progress-label">Израсходован</div>
                                                <div class="progress-amount">16%</div>
                                            </div>
                                            <div class="progress progress-md">
                                                <div class="progress-bar bg-teal" data-progress="16"></div>
                                            </div>
                                        </div>
                                        <div class="progress-wrap">
                                            <div class="progress-text">
                                                <div class="progress-label">На броне</div>
                                                <div class="progress-amount">29%</div>
                                            </div>
                                            <div class="progress progress-md">
                                                <div class="progress-bar bg-pink" data-progress="29"></div>
                                            </div>
                                        </div>
                                        <div class="progress-wrap">
                                            <div class="progress-text">
                                                <div class="progress-label">Брак</div>
                                                <div class="progress-amount">33%</div>
                                            </div>
                                            <div class="progress progress-md">
                                                <div class="progress-bar bg-azure" data-progress="33"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="invest-top-ck mt-auto">
                                        <canvas class="iv-plan-purchase" id="planPurchase"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div><!-- .col -->
                        
                        <div class="col-md-6 col-xxl-4">
                            <div class="card card-bordered h-100">
                                <div class="card-inner border-bottom">
                                    <div class="card-title-group">
                                        <div class="card-title">
                                            <h6 class="title">История</h6>
                                        </div>
                                        <div class="card-tools">
                                            <a href="#" class="link">Посмотреть все</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-inner">
                                    <div class="timeline">
                                        <ul class="timeline-list">
                                            <li class="timeline-item">
                                                <div class="timeline-status bg-primary is-outline"></div>
                                                <div class="timeline-date">13.01.2022 08:00</div>
                                                <div class="timeline-data">
                                                    <h6 class="timeline-title">Submited KYC Application</h6>
                                                    <div class="timeline-des">
                                                        <p>Re-submitted KYC Application form.</p>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="timeline-item">
                                                <div class="timeline-status bg-primary"></div>
                                                <div class="timeline-date">13.01.2022 08:00</div>
                                                <div class="timeline-data">
                                                    <h6 class="timeline-title">Submited KYC Application</h6>
                                                    <div class="timeline-des">
                                                        <p>Re-submitted KYC Application form.</p>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="timeline-item">
                                                <div class="timeline-status bg-primary"></div>
                                                <div class="timeline-date">13.01.2022 08:00</div>
                                                <div class="timeline-data">
                                                    <h6 class="timeline-title">Submited KYC Application</h6>
                                                    <div class="timeline-des">
                                                        <p>Re-submitted KYC Application form.</p>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="timeline-item">
                                                <div class="timeline-status bg-pink"></div>
                                                <div class="timeline-date">13.01.2022 08:00</div>
                                                <div class="timeline-data">
                                                    <h6 class="timeline-title">Submited KYC Application</h6>
                                                    <div class="timeline-des">
                                                        <p>Re-submitted KYC Application form.</p>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div><!-- .card -->
                        </div><!-- .col -->
                        <div class="col-xl-12 col-xxl-12">
                            <div class="card card-bordered card-full">
                                <div class="card-inner border-bottom">
                                    <div class="card-title-group">
                                        <div class="card-title">
                                            <h6 class="title">Склады</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="nk-tb-list">
                                    <div class="nk-tb-item nk-tb-head">
                                        <div class="nk-tb-col"><span>Наименование</span></div>
                                        <div class="nk-tb-col"><span>Итого</span></div>
                                        <div class="nk-tb-col"><span>Остаток</span></div>
                                        <div class="nk-tb-col"><span>Брак</span></div>
                                        <div class="nk-tb-col"><span>&nbsp;</span></div>
                                    </div>
                                    @foreach($warehouses as $warehouse)
                                    <div class="nk-tb-item">
                                        <div class="nk-tb-col">
                                            {{ $warehouse->name }}
                                        </div>
                                        <div class="nk-tb-col">
                                            0
                                        </div>
                                        <div class="nk-tb-col">
                                            0
                                        </div>
                                        <div class="nk-tb-col">
                                            0
                                        </div>
                                        
                                        <div class="nk-tb-col nk-tb-col-action">
                                            <div class="dropdown">
                                                <a class="text-soft dropdown-toggle btn btn-sm btn-icon btn-trigger" data-bs-toggle="dropdown"><em class="icon ni ni-chevron-right"></em></a>
                                                <div class="dropdown-menu dropdown-menu-end dropdown-menu-xs">
                                                    <ul class="link-list-plain">
                                                        <li><a href="#">View</a></li>
                                                        <li><a href="#">Invoice</a></li>
                                                        <li><a href="#">Print</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                    
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