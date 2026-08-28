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

                                <a href="{{ route('checkout_downloadDebtReport') }}" class="btn btn-primary btn-sm mb-3">Excel yuklash</a>

                                {{-- TABLAR --}}
                                <ul class="nav nav-tabs mb-3" id="debtTabs" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="usd-tab" data-bs-toggle="tab" href="#usd-content" role="tab">
                                            USD qarzdorliklar
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="sum-tab" data-bs-toggle="tab" href="#sum-content" role="tab">
                                            SUM qarzdorliklar
                                        </a>
                                    </li>
                                </ul>

                                <div class="tab-content">
                                    {{-- USD TAB --}}
                                    <div class="tab-pane fade show active" id="usd-content" role="tabpanel">
                                        @include('backend.checkouts._debt_table', ['report_data' => $report_data_usd, 'currency_label' => '$'])
                                    </div>

                                    {{-- SUM TAB --}}
                                    <div class="tab-pane fade" id="sum-content" role="tabpanel">
                                        @include('backend.checkouts._debt_table', ['report_data' => $report_data_sum, 'currency_label' => 'сум'])
                                    </div>
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