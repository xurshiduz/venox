@extends('layouts.backend')

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="nk-block">
                    @hasanyrole('manufacturer_admin|manufacturer_gp')
                        @include('backend.layouts.manufacture')
                    @endhasanyrole
                        
                    <div class="row g-gs" style="padding-top: 20px;">
                        
                        @hasanyrole('admin|sale')
                        <div class="col-sm-6 col-lg-4 col-xxl-3 mt-1">
                            <div class="card card-bordered h-100">
                                <div class="card-inner">
                                    <div class="project">
                                        <a href="{{ route('checkouts_index') }}" class="project-title">
                                            <div class="user-avatar sq md">
                                                <img src="/backend/images/sys_icon/kassa_icon.png" alt="">
                                            </div>
                                            <div class="project-info">
                                                <h2 style="font-size: 18px; text-transform: uppercase; font-family: inherit;" class="title">{{ trans('backend.index.kassa') }}</h2>
                                                <span class="sub-text">{{ trans('backend.index.kassa_desc') }}</span>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endhasanyrole
                        
                        

                        @hasanyrole('admin|sale')
                        <div class="col-sm-6 col-lg-4 col-xxl-3 mt-1">
                            <div class="card card-bordered h-100">
                                <div class="card-inner">
                                    <div class="project">
                                        <a href="{{ route('product_barcode') }}" class="project-title">
                                            <div class="user-avatar sq md">
                                                <img src="/backend/images/sys_icon/bar_icon.png" alt="">
                                            </div>
                                            <div class="project-info">
                                                <h2 style="font-size: 18px; text-transform: uppercase; font-family: inherit;" class="title">{{ trans('backend.index.bar_code') }}</h2>
                                                <span class="sub-text">{{ trans('backend.index.bar_code_desc') }}</span>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endhasanyrole
                        
                        @hasanyrole('admin|employee')
                        <div class="col-sm-6 col-lg-4 col-xxl-3 mt-1">
                            <div class="card card-bordered h-100">
                                <div class="card-inner">
                                    <div class="project">
                                        <a href="{{ route('cash_expenditures_index') }}" class="project-title">
                                            <div class="user-avatar sq md">
                                                <img src="/backend/images/sys_icon/icon_cashty.png" alt="">
                                            </div>
                                            <div class="project-info">
                                                <h2 style="font-size: 18px; text-transform: uppercase; font-family: inherit;" class="title">{{ trans('savdo.home.cash_expenditure') }}</h2>
                                                <span class="sub-text">{{ trans('savdo.home.cash_expenditure_desc') }}</span>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endhasanyrole
                        
                        @hasanyrole('admin|sale')
                        <div class="col-sm-6 col-lg-4 col-xxl-3 mt-1">
                            <div class="card card-bordered h-100">
                                <div class="card-inner">
                                    <div class="project">
                                        <a href="{{ route('products_index') }}" class="project-title">
                                            <div class="user-avatar sq md">
                                                <img src="/backend/images/sys_icon/inventory_icon.png" alt="">
                                            </div>
                                            <div class="project-info">
                                                <h2 style="font-size: 18px; text-transform: uppercase; font-family: inherit;" class="title">{{ trans('backend.index.price') }}</h2>
                                                <span class="sub-text">{{ trans('backend.index.price_desc') }}</span>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endhasanyrole

                        @hasanyrole('admin|sale|report')
                        <div class="col-sm-6 col-lg-4 col-xxl-3 mt-1">
                            <div class="card card-bordered h-100">
                                <div class="card-inner">
                                    <div class="project">
                                        <a href="{{ route('products_stock') }}" class="project-title">
                                            <div class="user-avatar sq md">
                                                <img src="/backend/images/sys_icon/cart_icon.png" alt="">
                                            </div>
                                            <div class="project-info">
                                                <h2 style="font-size: 18px; text-transform: uppercase; font-family: inherit;" class="title">{{ trans('backend.index.stock') }}</h2>
                                                <span class="sub-text">{{ trans('backend.index.stock_desc') }}</span>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endhasanyrole
                        
                        @hasanyrole('admin|sale')
                        <div class="col-sm-6 col-lg-4 col-xxl-3 mt-1">
                            <div class="card card-bordered h-100">
                                <div class="card-inner">
                                    <div class="project">
                                        <a href="{{ route('checkins_index') }}" class="project-title">
                                            <div class="user-avatar sq md">
                                                <img src="/backend/images/sys_icon/incart_icon.png" alt="">
                                            </div>
                                            <div class="project-info">
                                                <h2 style="font-size: 18px; text-transform: uppercase; font-family: inherit;" class="title">{{ trans('backend.index.checkin') }}</h2>
                                                <span class="sub-text">{{ trans('backend.index.checkin_desc') }}</span>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endhasanyrole
                        
                        @hasanyrole('admin')
                        <div class="col-sm-6 col-lg-4 col-xxl-3 mt-1">
                            <div class="card card-bordered h-100">
                                <div class="card-inner">
                                    <div class="project">
                                        <a href="{{ route('cash_receipts_index') }}" class="project-title">
                                            <div class="user-avatar sq md">
                                                <img src="/backend/images/sys_icon/wallet_icon.png" alt="">
                                            </div>
                                            <div class="project-info">
                                                <h2 style="font-size: 18px; text-transform: uppercase; font-family: inherit;" class="title">{{ trans('backend.index.cash_receipt') }}</h2>
                                                <span class="sub-text">{{ trans('backend.index.cash_receipt_desc') }}</span>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endhasanyrole
                        
                        @hasanyrole('admin')
                        <div class="col-sm-6 col-lg-4 col-xxl-3 mt-1">
                            <div class="card card-bordered h-100">
                                <div class="card-inner">
                                    <div class="project">
                                        <a href="{{ route('currencies_index') }}" class="project-title">
                                            <div class="user-avatar sq md">
                                                <img src="/backend/images/sys_icon/clients_icon.png" alt="">
                                            </div>
                                            <div class="project-info">
                                                <h2 style="font-size: 18px; text-transform: uppercase; font-family: inherit;" class="title">{{ trans('backend.menu.currencies_index') }}</h2>
                                                <span class="sub-text">{{ trans('backend.menu.currencies_index') }}</span>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endhasanyrole
                        
                        
                        @hasanyrole('admin')
                        <div class="col-sm-6 col-lg-4 col-xxl-3 mt-1">
                            <div class="card card-bordered h-100">
                                <div class="card-inner">
                                    <div class="project">
                                        <a href="{{ route('users_index') }}" class="project-title">
                                            <div class="user-avatar sq md">
                                                <img src="/backend/images/sys_icon/users_icon.png" alt="">
                                            </div>
                                            <div class="project-info">
                                                <h2 style="font-size: 18px; text-transform: uppercase; font-family: inherit;" class="title">{{ trans('backend.index.employee') }}</h2>
                                                <span class="sub-text">{{ trans('backend.index.employee_desc') }}</span>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endhasanyrole
                        
                        @hasanyrole('admin222')
                        <div class="col-sm-6 col-lg-4 col-xxl-3 mt-1">
                            <div class="card card-bordered h-100">
                                <div class="card-inner">
                                    <div class="project">
                                        <a href="{{ route('suppliers_index') }}" class="project-title">
                                            <div class="user-avatar sq md">
                                                <img src="/backend/images/sys_icon/sup_icon.png" alt="">
                                            </div>
                                            <div class="project-info">
                                                <h2 style="font-size: 18px; text-transform: uppercase; font-family: inherit;" class="title">{{ trans('backend.index.shipper') }}</h2>
                                                <span class="sub-text">{{ trans('backend.index.shipper_desc') }}</span>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endhasanyrole
                        

                        @hasanyrole('admin|sale')
                        <div class="col-sm-6 col-lg-4 col-xxl-3 mt-1">
                            <div class="card card-bordered h-100">
                                <div class="card-inner">
                                    <div class="project">
                                        <a href="{{ route('clients_index') }}" class="project-title">
                                            <div class="user-avatar sq md">
                                                <img src="/backend/images/sys_icon/clients_icon.png" alt="">
                                            </div>
                                            <div class="project-info">
                                                <h2 style="font-size: 18px; text-transform: uppercase; font-family: inherit;" class="title">{{ trans('backend.index.clients') }}</h2>
                                                <span class="sub-text">{{ trans('backend.index.clients_desc') }}</span>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endhasanyrole
                        
                        
                        @hasanyrole('admin|sale|report')
                        <!-- <div class="col-sm-6 col-lg-4 col-xxl-3 mt-1">
                            <div class="card card-bordered h-100">
                                <div class="card-inner">
                                    <div class="project">
                                        <a href="#" class="project-title">
                                            <div class="user-avatar sq md">
                                                <img src="/backend/images/sys_icon/inventory_icon.png" alt="">
                                            </div>
                                            <div class="project-info">
                                                <h2 style="font-size: 18px; text-transform: uppercase; font-family: inherit;" class="title">Инвентаризация</h2>
                                                <span class="sub-text">Инвентаризация товаров на складе</span>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div> -->
                        @endhasanyrole

                        
                        
                        @hasanyrole('admin')
                        <div class="col-sm-6 col-lg-4 col-xxl-3 mt-1">
                            <div class="card card-bordered h-100">
                                <div class="card-inner">
                                    <div class="project">
                                        <a href="{{ route('warehouses_index') }}" class="project-title">
                                            <div class="user-avatar sq md">
                                                <img src="/backend/images/sys_icon/warehouse_icon.png" alt="">
                                            </div>
                                            <div class="project-info">
                                                <h2 style="font-size: 18px; text-transform: uppercase; font-family: inherit;" class="title">{{ trans('backend.index.warehouse') }}</h2>
                                                <span class="sub-text">{{ trans('backend.index.warehouse_desc') }}</span>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endhasanyrole
                        
                        @hasanyrole('admin|sale')
                        <div class="col-sm-6 col-lg-4 col-xxl-3 mt-1">
                            <div class="card card-bordered h-100">
                                <div class="card-inner">
                                    <div class="project">
                                        <a href="{{ route('returns_index') }}" class="project-title">
                                            <div class="user-avatar sq md">
                                                <img src="/backend/images/sys_icon/undo_icon.png" alt="">
                                            </div>
                                            <div class="project-info">
                                                <h2 style="font-size: 18px; text-transform: uppercase; font-family: inherit;" class="title">{{ trans('backend.index.returns') }}</h2>
                                                <span class="sub-text">{{ trans('backend.index.returns_desc') }}</span>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endhasanyrole
                        
                        
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="/backend/js/echarts.min.js"></script>
<script>
var dom = document.getElementById('chart-container-year');
var myChart = echarts.init(dom, null, {
  renderer: 'canvas',
  useDirtyRect: false
});
var app = {};

var option;

const colors = ['#5470C6', '#91CC75', '#EE6666'];
option = {
  color: colors,
  tooltip: {
    trigger: 'axis',
    axisPointer: {
      type: 'cross'
    }
  },
  grid: {
    right: '20%'
  },
  toolbox: {
    feature: {
      dataView: { show: true, readOnly: false },
      restore: { show: true },
      saveAsImage: { show: true }
    }
  },
  legend: {
    data: ['Производства', 'Реализация', 'Temperature']
  },
  xAxis: [
    {
      type: 'category',
      axisTick: {
        alignWithLabel: true
      },
      // prettier-ignore
      data: ['Янв', 'Фев', 'Март', 'Апр', 'Май', 'Июнь', 'Июль', 'Авг', 'Сен', 'Окт', 'Ной', 'Дек']
    }
  ],
  yAxis: [
    {
      type: 'value',
      name: 'Производства',
      position: 'right',
      alignTicks: true,
      axisLine: {
        show: true,
        lineStyle: {
          color: colors[0]
        }
      },
      axisLabel: {
        formatter: '{value} т.'
      }
    },
    {
      type: 'value',
      name: 'Реализация',
      position: 'right',
      alignTicks: true,
      offset: 80,
      axisLine: {
        show: true,
        lineStyle: {
          color: colors[1]
        }
      },
      axisLabel: {
        formatter: '{value} т.'
      }
    }
  ],
  series: [
    {
      name: 'Производства',
      type: 'bar',
      data: [
        2.0, 4.9, 7.0, 23.2, 25.6, 76.7, 135.6, 162.2, 32.6, 20.0, 6.4, 3.3
      ]
    },
    {
      name: 'Реализация',
      type: 'bar',
      yAxisIndex: 1,
      data: [
        2.6, 5.9, 9.0, 26.4, 28.7, 70.7, 175.6, 182.2, 48.7, 18.8, 6.0, 2.3
      ]
    }
  ]
};

if (option && typeof option === 'object') {
  myChart.setOption(option);
}

window.addEventListener('resize', myChart.resize);
</script>
@endsection