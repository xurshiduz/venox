@extends('layouts.backend')

@section('content')
<style type="text/css">
    * {
  margin: 0;
  padding: 0;
}
#chart-container {
  position: relative;
  height: 60vh;
  overflow: hidden;
  padding-bottom: 10px;
}

#kpi-plans{
  position: relative;
  height: 60vh;
  overflow: hidden;
  padding-top: 10px;
}

#chart-container-year {
  position: relative;
  height: 60vh;
  overflow: hidden;
  padding-top: 10px;
}
</style>
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="nk-block">
                    <div class="row g-gs mt-5">
                        <div class="col-md-6">
                            <div class="card card-bordered card-full">
                                <div class="card-inner border-bottom">
                                    <div class="card-title-group">
                                        <div class="card-title">
                                            <h6 class="title">Shu oy sotuvlar omborhona kesmida</h6>
                                        </div>
                                    </div>
                                </div>
                                <div id="chart-container"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-bordered card-full">
                                <div class="card-inner border-bottom">
                                    <div class="card-title-group">
                                        <div class="card-title">
                                            <h6 class="title">Tuzilgan shartnomalar soni bo'yicha hisobot sotuv menedjeri kesmida (Xozirgi va o'tgan oy)</h6>
                                        </div>
                                    </div>
                                </div>
                                <div id="chart-container-year"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-bordered card-full">
                                <div class="card-inner border-bottom">
                                    <div class="card-title-group">
                                        <div class="card-title">
                                            <h6 class="title">KPI</h6>
                                        </div>
                                    </div>
                                </div>
                                <div id="kpi-plans"></div>
                            </div>
                        </div>
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
var dom = document.getElementById('chart-container');
var myChart = echarts.init(dom, null, {
  renderer: 'canvas',
  useDirtyRect: false
});
var app = {};

var option;

option = {
  legend: {
    top: 'bottom'
  },
  toolbox: {
    show: true,
    feature: {
      mark: { show: true },
      dataView: { show: true, readOnly: false },
      restore: { show: true },
      saveAsImage: { show: true }
    }
  },
  series: [
    {
      name: 'Nightingale Chart',
      type: 'pie',
      radius: [50, 150],
      center: ['50%', '50%'],
      roseType: 'area',
      itemStyle: {
        borderRadius: 8
      },
      data: [
        @foreach($warehouses as $warehouse)
        { value: {{ $warehouse->checkoutsdetail()->whereBetween('created_at', [Carbon\Carbon::now()->startOfMonth(), Carbon\Carbon::now()->endOfMonth()])->sum('qty') }}, name: "{{ $warehouse->name }} - {{ $warehouse->checkoutsdetail()->whereBetween('created_at', [Carbon\Carbon::now()->startOfMonth(), Carbon\Carbon::now()->endOfMonth()])->sum('qty') }}" },
        @endforeach
      ]
    }
  ]
};


if (option && typeof option === 'object') {
  myChart.setOption(option);
}

window.addEventListener('resize', myChart.resize);
</script>

<script>
var dom = document.getElementById('chart-container-year');
var myChart = echarts.init(dom, null, {
  renderer: 'canvas',
  useDirtyRect: false
});
var app = {};

var option;
option = {
  tooltip: {
    trigger: 'axis',
    axisPointer: {
      type: 'shadow'
    }
  },
  legend: {},
  grid: {
    left: '3%',
    right: '4%',
    bottom: '3%',
    containLabel: true
  },
  xAxis: {
    type: 'value',
    boundaryGap: [0, 0.01]
  },
  yAxis: {
    type: 'category',
    data: [
        @foreach($managers as $manager)
        '{{ $manager->name }}',
        @endforeach
    ]
  },
  series: [
    {
      name: "{{ Carbon\Carbon::now()->startOfMonth()->format('Y-m') }}",
      type: 'bar',
      data: [
          @foreach($managers as $manager)
          {{ $manager->checkouts()->whereBetween('created_at', [Carbon\Carbon::now()->startOfMonth(), Carbon\Carbon::now()->endOfMonth()])->count() }},
          @endforeach
       ]
    },
    {
      name: "{{ Carbon\Carbon::now()->subMonth()->startOfMonth()->format('Y-m') }}",
      type: 'bar',
      data: [
          @foreach($managers as $manager)
          {{ $manager->checkouts()->whereBetween('created_at', [Carbon\Carbon::now()->subMonth()->startOfMonth(), Carbon\Carbon::now()->subMonth()->endOfMonth()])->count() }},
          @endforeach
        ]
    },
    {
      name: "{{ Carbon\Carbon::now()->subMonths(2)->startOfMonth()->format('Y-m') }}",
      type: 'bar',
      data: [
          @foreach($managers as $manager)
          {{ $manager->checkouts()->whereBetween('created_at', [Carbon\Carbon::now()->subMonths(2)->startOfMonth(), Carbon\Carbon::now()->subMonths(2)->endOfMonth()])->count() }},
          @endforeach
        ]
    }
  ]
};

if (option && typeof option === 'object') {
  myChart.setOption(option);
}

window.addEventListener('resize', myChart.resize);
</script>
<script>
    var dom = document.getElementById('kpi-plans');
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
    data: ['{{ Carbon\Carbon::now()->format('Y') }}', '{{ Carbon\Carbon::now()->subYear()->format('Y') }}', 'KPI']
  },
  xAxis: [
    {
      type: 'category',
      axisTick: {
        alignWithLabel: true
      },
      // prettier-ignore
      data: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
    }
  ],
  yAxis: [
    {
      type: 'value',
      name: '{{ Carbon\Carbon::now()->format('Y') }}',
      position: 'right',
      alignTicks: true,
      axisLine: {
        show: true,
        lineStyle: {
          color: colors[0]
        }
      },
      axisLabel: {
        formatter: '{value}'
      }
    },
    {
      type: 'value',
      name: '{{ Carbon\Carbon::now()->subYear()->format('Y') }}',
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
        formatter: '{value}'
      }
    },
    {
      type: 'value',
      name: 'KPI',
      position: 'left',
      alignTicks: true,
      axisLine: {
        show: true,
        lineStyle: {
          color: colors[2]
        }
      },
      axisLabel: {
        formatter: '{value}'
      }
    }
  ],
  series: [
    {
      name: "{{ Carbon\Carbon::now()->format('Y') }}",
      type: 'bar',
      data: [
        {{ App\Models\CheckoutDetail::whereBetween('created_at', [Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-01-01')->format('Y-m-d'), Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-01-31')->format('Y-m-d')])->where('status', 1)->sum('total_price')}}, 
        {{ App\Models\CheckoutDetail::whereBetween('created_at', [Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-02-01')->format('Y-m-d'), Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-02-31')->format('Y-m-d')])->where('status', 1)->sum('total_price')}}, 
        {{ App\Models\CheckoutDetail::whereBetween('created_at', [Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-03-01')->format('Y-m-d'), Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-03-31')->format('Y-m-d')])->where('status', 1)->sum('total_price')}}, 
        {{ App\Models\CheckoutDetail::whereBetween('created_at', [Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-04-01')->format('Y-m-d'), Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-04-31')->format('Y-m-d')])->where('status', 1)->sum('total_price')}}, 
        {{ App\Models\CheckoutDetail::whereBetween('created_at', [Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-05-01')->format('Y-m-d'), Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-05-31')->format('Y-m-d')])->where('status', 1)->sum('total_price')}}, 
        {{ App\Models\CheckoutDetail::whereBetween('created_at', [Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-06-01')->format('Y-m-d'), Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-06-31')->format('Y-m-d')])->where('status', 1)->sum('total_price')}}, 
        {{ App\Models\CheckoutDetail::whereBetween('created_at', [Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-07-01')->format('Y-m-d'), Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-07-31')->format('Y-m-d')])->where('status', 1)->sum('total_price')}}, 
        {{ App\Models\CheckoutDetail::whereBetween('created_at', [Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-08-01')->format('Y-m-d'), Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-08-31')->format('Y-m-d')])->where('status', 1)->sum('total_price')}}, 
        {{ App\Models\CheckoutDetail::whereBetween('created_at', [Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-09-01')->format('Y-m-d'), Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-09-31')->format('Y-m-d')])->where('status', 1)->sum('total_price')}}, 
        {{ App\Models\CheckoutDetail::whereBetween('created_at', [Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-10-01')->format('Y-m-d'), Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-10-31')->format('Y-m-d')])->where('status', 1)->sum('total_price')}}, 
        {{ App\Models\CheckoutDetail::whereBetween('created_at', [Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-11-01')->format('Y-m-d'), Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-11-31')->format('Y-m-d')])->where('status', 1)->sum('total_price')}},
        {{ App\Models\CheckoutDetail::whereBetween('created_at', [Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-12-01')->format('Y-m-d'), Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-12-31')->format('Y-m-d')])->where('status', 1)->sum('total_price')}}, 
      ]
    },
    {
      name: "{{ Carbon\Carbon::now()->subYear()->format('Y') }}",
      type: 'bar',
      yAxisIndex: 1,
      data: [
        {{ App\Models\CheckoutDetail::whereBetween('created_at', [Carbon\Carbon::parse(Carbon\Carbon::now()->subYear()->format('Y') . '-01-01')->format('Y-m-d'), Carbon\Carbon::parse(Carbon\Carbon::now()->subYear()->format('Y') . '-01-31')->format('Y-m-d')])->where('status', 1)->sum('total_price')}}, 
        {{ App\Models\CheckoutDetail::whereBetween('created_at', [Carbon\Carbon::parse(Carbon\Carbon::now()->subYear()->format('Y') . '-02-01')->format('Y-m-d'), Carbon\Carbon::parse(Carbon\Carbon::now()->subYear()->format('Y') . '-02-31')->format('Y-m-d')])->where('status', 1)->sum('total_price')}}, 
        {{ App\Models\CheckoutDetail::whereBetween('created_at', [Carbon\Carbon::parse(Carbon\Carbon::now()->subYear()->format('Y') . '-03-01')->format('Y-m-d'), Carbon\Carbon::parse(Carbon\Carbon::now()->subYear()->format('Y') . '-03-31')->format('Y-m-d')])->where('status', 1)->sum('total_price')}}, 
        {{ App\Models\CheckoutDetail::whereBetween('created_at', [Carbon\Carbon::parse(Carbon\Carbon::now()->subYear()->format('Y') . '-04-01')->format('Y-m-d'), Carbon\Carbon::parse(Carbon\Carbon::now()->subYear()->format('Y') . '-04-31')->format('Y-m-d')])->where('status', 1)->sum('total_price')}}, 
        {{ App\Models\CheckoutDetail::whereBetween('created_at', [Carbon\Carbon::parse(Carbon\Carbon::now()->subYear()->format('Y') . '-05-01')->format('Y-m-d'), Carbon\Carbon::parse(Carbon\Carbon::now()->subYear()->format('Y') . '-05-31')->format('Y-m-d')])->where('status', 1)->sum('total_price')}}, 
        {{ App\Models\CheckoutDetail::whereBetween('created_at', [Carbon\Carbon::parse(Carbon\Carbon::now()->subYear()->format('Y') . '-06-01')->format('Y-m-d'), Carbon\Carbon::parse(Carbon\Carbon::now()->subYear()->format('Y') . '-06-31')->format('Y-m-d')])->where('status', 1)->sum('total_price')}}, 
        {{ App\Models\CheckoutDetail::whereBetween('created_at', [Carbon\Carbon::parse(Carbon\Carbon::now()->subYear()->format('Y') . '-07-01')->format('Y-m-d'), Carbon\Carbon::parse(Carbon\Carbon::now()->subYear()->format('Y') . '-07-31')->format('Y-m-d')])->where('status', 1)->sum('total_price')}}, 
        {{ App\Models\CheckoutDetail::whereBetween('created_at', [Carbon\Carbon::parse(Carbon\Carbon::now()->subYear()->format('Y') . '-08-01')->format('Y-m-d'), Carbon\Carbon::parse(Carbon\Carbon::now()->subYear()->format('Y') . '-08-31')->format('Y-m-d')])->where('status', 1)->sum('total_price')}}, 
        {{ App\Models\CheckoutDetail::whereBetween('created_at', [Carbon\Carbon::parse(Carbon\Carbon::now()->subYear()->format('Y') . '-09-01')->format('Y-m-d'), Carbon\Carbon::parse(Carbon\Carbon::now()->subYear()->format('Y') . '-09-31')->format('Y-m-d')])->where('status', 1)->sum('total_price')}}, 
        {{ App\Models\CheckoutDetail::whereBetween('created_at', [Carbon\Carbon::parse(Carbon\Carbon::now()->subYear()->format('Y') . '-10-01')->format('Y-m-d'), Carbon\Carbon::parse(Carbon\Carbon::now()->subYear()->format('Y') . '-10-31')->format('Y-m-d')])->where('status', 1)->sum('total_price')}}, 
        {{ App\Models\CheckoutDetail::whereBetween('created_at', [Carbon\Carbon::parse(Carbon\Carbon::now()->subYear()->format('Y') . '-11-01')->format('Y-m-d'), Carbon\Carbon::parse(Carbon\Carbon::now()->subYear()->format('Y') . '-11-31')->format('Y-m-d')])->where('status', 1)->sum('total_price')}},
        {{ App\Models\CheckoutDetail::whereBetween('created_at', [Carbon\Carbon::parse(Carbon\Carbon::now()->subYear()->format('Y') . '-12-01')->format('Y-m-d'), Carbon\Carbon::parse(Carbon\Carbon::now()->subYear()->format('Y') . '-12-31')->format('Y-m-d')])->where('status', 1)->sum('total_price')}}, 
      ]
    },
    {
      name: 'KPI',
      type: 'line',
      yAxisIndex: 2,
      data: [
        {{ $kpi_list->where('date', '=', Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-01-01')->format('Y-m-d'))->count() ? $kpi_list->where('date', '=', Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-01-01')->format('Y-m-d'))->first()->details->sum('plan_sum') : 0 }}, 
        {{ $kpi_list->where('date', '=', Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-02-01')->format('Y-m-d'))->count() ? $kpi_list->where('date', '=', Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-02-01')->format('Y-m-d'))->first()->details->sum('plan_sum') : 0 }}, 
        {{ $kpi_list->where('date', '=', Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-03-01')->format('Y-m-d'))->count() ? $kpi_list->where('date', '=', Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-03-01')->format('Y-m-d'))->first()->details->sum('plan_sum') : 0 }}, 
        {{ $kpi_list->where('date', '=', Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-04-01')->format('Y-m-d'))->count() ? $kpi_list->where('date', '=', Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-04-01')->format('Y-m-d'))->first()->details->sum('plan_sum') : 0 }}, 
        {{ $kpi_list->where('date', '=', Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-05-01')->format('Y-m-d'))->count() ? $kpi_list->where('date', '=', Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-05-01')->format('Y-m-d'))->first()->details->sum('plan_sum') : 0 }}, 
        {{ $kpi_list->where('date', '=', Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-06-01')->format('Y-m-d'))->count() ? $kpi_list->where('date', '=', Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-06-01')->format('Y-m-d'))->first()->details->sum('plan_sum') : 0 }}, 
        {{ $kpi_list->where('date', '=', Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-07-01')->format('Y-m-d'))->count() ? $kpi_list->where('date', '=', Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-07-01')->format('Y-m-d'))->first()->details->sum('plan_sum') : 0 }}, 
        {{ $kpi_list->where('date', '=', Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-08-01')->format('Y-m-d'))->count() ? $kpi_list->where('date', '=', Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-08-01')->format('Y-m-d'))->first()->details->sum('plan_sum') : 0 }}, 
        {{ $kpi_list->where('date', '=', Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-09-01')->format('Y-m-d'))->count() ? $kpi_list->where('date', '=', Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-09-01')->format('Y-m-d'))->first()->details->sum('plan_sum') : 0 }}, 
        {{ $kpi_list->where('date', '=', Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-10-01')->format('Y-m-d'))->count() ? $kpi_list->where('date', '=', Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-10-01')->format('Y-m-d'))->first()->details->sum('plan_sum') : 0 }}, 
        {{ $kpi_list->where('date', '=', Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-11-01')->format('Y-m-d'))->count() ? $kpi_list->where('date', '=', Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-11-01')->format('Y-m-d'))->first()->details->sum('plan_sum') : 0 }},
        {{ $kpi_list->where('date', '=', Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-12-01')->format('Y-m-d'))->count() ? $kpi_list->where('date', '=', Carbon\Carbon::parse(Carbon\Carbon::now()->format('Y') . '-12-01')->format('Y-m-d'))->first()->details->sum('plan_sum') : 0 }}, 
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