@extends('layouts.backend')
@section('css')
<style>
.moreText {
    display: none;
}
.text.show-more .moreText {
    display: inline;
}
.text.show-more .dots {
    display: none;
}
.overlay {
    margin-top: -4em;
    height: 4em;
    position: relative;
    background-image: linear-gradient(to bottom, rgba(255, 255, 255, 0) 0%, #ffffff 50%);
}
</style>
@endsection
@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="nk-block">
                    <div class="row g-gs">
                        <div class="col-sm-6 col-lg-4 col-xxl-4">
                            <div class="card card-bordered">
                                <div class="card-inner">
                                    <div class="team">
                                        <div class="user-card user-card-s2">
                                            <h5>Продажа</h5>
                                        </div>
                                        <ul class="team-info">
                                            @php($aktbugun = App\Models\CashReceipt::where('balance_client', 1)->whereDate('date', Carbon\Carbon::today())->sum('price'))
                                            @php($aktmonth = App\Models\CashReceipt::where('balance_client', 1)->whereBetween('date', [Carbon\Carbon::now()->startOfMonth(), Carbon\Carbon::now()->endOfMonth()])->sum('price'))
                                            @php($aktsubmonth = App\Models\CashReceipt::where('balance_client', 1)->whereBetween('date', [Carbon\Carbon::now()->subMonth()->startOfMonth(), Carbon\Carbon::now()->subMonth()->endOfMonth()])->sum('price'))
                                            @php($aktsubyear = App\Models\CashReceipt::where('balance_client', 1)->whereBetween('date', [Carbon\Carbon::now()->subYear()->startOfYear(), Carbon\Carbon::now()->subYear()->endOfYear()])->sum('price'))
                                            @php($aktyear = App\Models\CashReceipt::where('balance_client', 1)->whereBetween('date', [Carbon\Carbon::now()->startOfYear(), Carbon\Carbon::now()->endOfYear()])->sum('price'))
                                            
                                            <li><span>Сегодня ({{ Carbon\Carbon::now()->locale('ru_RU')->dayName }})</span><span><b>{{ number_format(App\Models\CheckoutDetail::join('checkouts', 'checkout_details.checkout_id', '=', 'checkouts.id')->whereDate('checkouts.date', Carbon\Carbon::today())->where('checkouts.checkout_tip_id', 1)->sum(DB::raw('checkout_details.total_price')) + $aktbugun, 0, '.', ' ') }}</b> сум</span></li>
                                            <li><span>Текущий мес. ({{ Str::limit(Carbon\Carbon::now()->locale('ru_RU')->monthName, 3, '') }})</span><span><b>{{ number_format(App\Models\CheckoutDetail::join('checkouts', 'checkout_details.checkout_id', '=', 'checkouts.id')->whereBetween('checkouts.date', [Carbon\Carbon::now()->startOfMonth(), Carbon\Carbon::now()->endOfMonth()])->where('checkouts.checkout_tip_id', 1)->sum(DB::raw('checkout_details.total_price')) + $aktmonth, 0, '.', ' ') }}</b> сум</span></li>
                                            <li><span>Прошлого мес. ({{ Str::limit(Carbon\Carbon::now()->subMonth()->locale('ru_RU')->monthName, 3, '') }})</span><span><b>{{ number_format(App\Models\CheckoutDetail::join('checkouts', 'checkout_details.checkout_id', '=', 'checkouts.id')->whereBetween('checkouts.date', [Carbon\Carbon::now()->subMonth()->startOfMonth(), Carbon\Carbon::now()->subMonth()->endOfMonth()])->where('checkouts.checkout_tip_id', 1)->sum(DB::raw('checkout_details.total_price')) + $aktsubmonth, 0, '.', ' ') }}</b> сум</span></li>
                                            <li><span>Прошлого года ({{ Carbon\Carbon::now()->subYear()->format('Y') }})</span><span><b>{{ number_format(App\Models\CheckoutDetail::join('checkouts', 'checkout_details.checkout_id', '=', 'checkouts.id')->whereBetween('checkouts.date', [Carbon\Carbon::now()->subYear()->startOfYear(), Carbon\Carbon::now()->subYear()->endOfYear()])->where('checkouts.checkout_tip_id', 1)->sum(DB::raw('checkout_details.total_price')) + $aktsubyear, 0, '.', ' ') }}</b> сум</span></li>
                                            <li><span>С начало года ({{ Carbon\Carbon::now()->format('Y') }})</span><span><b>{{ number_format(App\Models\CheckoutDetail::join('checkouts', 'checkout_details.checkout_id', '=', 'checkouts.id')->whereBetween('checkouts.date', [Carbon\Carbon::now()->startOfYear(), Carbon\Carbon::now()->endOfYear()])->where('checkouts.checkout_tip_id', 1)->sum(DB::raw('checkout_details.total_price')) + $aktyear, 0, '.', ' ') }}</b> сум</span></li>
                                        </ul>
                                        <div class="team-view">
                                            <a href="#" class="btn btn-block btn-dim btn-primary"><span>Ойма ой бўйича кўриш</span></a>
                                        </div>
                                    </div><!-- .team -->
                                </div><!-- .card-inner -->
                            </div><!-- .card -->
                        </div><!-- .col -->
                        <div class="col-sm-6 col-lg-4 col-xxl-4">
                            <div class="card card-bordered">
                                <div class="card-inner">
                                    <div class="team">
                                        <div class="user-card user-card-s2">
                                            <h5>По продажам (долги)</h5>
                                        </div>
                                        <ul class="team-info">
                                            <li><span>Сегодня ({{ Carbon\Carbon::now()->locale('ru_RU')->dayName }})</span><span><b>{{ number_format(App\Models\CheckoutDetail::join('checkouts', 'checkout_details.checkout_id', '=', 'checkouts.id')->whereDate('checkouts.date', Carbon\Carbon::today())->where('checkouts.checkout_tip_id', 3)->sum(DB::raw('checkout_details.total_price')), 0, '.', ' ') }}</b> сум</span></li>
                                            <li><span>Текущий мес. ({{ Str::limit(Carbon\Carbon::now()->locale('ru_RU')->monthName, 3, '') }})</span><span><b>{{ number_format(App\Models\CheckoutDetail::join('checkouts', 'checkout_details.checkout_id', '=', 'checkouts.id')->whereBetween('checkouts.date', [Carbon\Carbon::now()->startOfMonth(), Carbon\Carbon::now()->endOfMonth()])->where('checkouts.checkout_tip_id', 3)->sum(DB::raw('checkout_details.total_price')), 0, '.', ' ') }}</b> сум</span></li>
                                            <li><span>Прошлого мес. ({{ Str::limit(Carbon\Carbon::now()->subMonth()->locale('ru_RU')->monthName, 3, '') }})</span><span><b>{{ number_format(App\Models\CheckoutDetail::join('checkouts', 'checkout_details.checkout_id', '=', 'checkouts.id')->whereBetween('checkouts.date', [Carbon\Carbon::now()->subMonth()->startOfMonth(), Carbon\Carbon::now()->subMonth()->endOfMonth()])->where('checkouts.checkout_tip_id', 3)->sum(DB::raw('checkout_details.total_price')), 0, '.', ' ') }}</b> сум</span></li>
                                            <li><span>Прошлого года ({{ Carbon\Carbon::now()->subYear()->format('Y') }})</span><span><b>{{ number_format(App\Models\CheckoutDetail::join('checkouts', 'checkout_details.checkout_id', '=', 'checkouts.id')->whereBetween('checkouts.date', [Carbon\Carbon::now()->subYear()->startOfYear(), Carbon\Carbon::now()->subYear()->endOfYear()])->where('checkouts.checkout_tip_id', 3)->sum(DB::raw('checkout_details.total_price')), 0, '.', ' ') }}</b> сум</span></li>
                                            <li><span>С начало года ({{ Carbon\Carbon::now()->format('Y') }})</span><span><b>{{ number_format(App\Models\CheckoutDetail::join('checkouts', 'checkout_details.checkout_id', '=', 'checkouts.id')->whereBetween('checkouts.date', [Carbon\Carbon::now()->startOfYear(), Carbon\Carbon::now()->endOfYear()])->where('checkouts.checkout_tip_id', 3)->sum(DB::raw('checkout_details.total_price')), 0, '.', ' ') }}</b> сум</span></li>
                                        </ul>
                                        <div class="team-view">
                                            <a href="#" class="btn btn-block btn-dim btn-primary"><span>Ойма ой бўйича кўриш</span></a>
                                        </div>
                                    </div><!-- .team -->
                                </div><!-- .card-inner -->
                            </div><!-- .card -->
                        </div><!-- .col -->
                        <div class="col-sm-6 col-lg-4 col-xxl-4">
                            <div class="card card-bordered">
                                <div class="card-inner">
                                    <div class="team">
                                        <div class="user-card user-card-s2">
                                            <h5>По продажам (Общие)</h5>
                                        </div>
                                        <ul class="team-info">
                                            <li><span>Сегодня ({{ Carbon\Carbon::now()->locale('ru_RU')->dayName }})</span><span><b>{{ number_format(App\Models\CheckoutDetail::join('checkouts', 'checkout_details.checkout_id', '=', 'checkouts.id')->whereDate('checkouts.date', Carbon\Carbon::today())->sum(DB::raw('checkout_details.total_price')) + $aktbugun, 0, '.', ' ') }}</b> сум</span></li>
                                            <li><span>Текущий мес. ({{ Str::limit(Carbon\Carbon::now()->locale('ru_RU')->monthName, 3, '') }})</span><span><b>{{ number_format(App\Models\CheckoutDetail::join('checkouts', 'checkout_details.checkout_id', '=', 'checkouts.id')->whereBetween('checkouts.date', [Carbon\Carbon::now()->startOfMonth(), Carbon\Carbon::now()->endOfMonth()])->sum(DB::raw('checkout_details.total_price')) + $aktmonth, 0, '.', ' ') }}</b> сум</span></li>
                                            <li><span>Прошлого мес. ({{ Str::limit(Carbon\Carbon::now()->subMonth()->locale('ru_RU')->monthName, 3, '') }})</span><span><b>{{ number_format(App\Models\CheckoutDetail::join('checkouts', 'checkout_details.checkout_id', '=', 'checkouts.id')->whereBetween('checkouts.date', [Carbon\Carbon::now()->subMonth()->startOfMonth(), Carbon\Carbon::now()->subMonth()->endOfMonth()])->sum(DB::raw('checkout_details.total_price')) + $aktsubmonth, 0, '.', ' ') }}</b> сум</span></li>
                                            <li><span>Прошлого года ({{ Carbon\Carbon::now()->subYear()->format('Y') }})</span><span><b>{{ number_format(App\Models\CheckoutDetail::join('checkouts', 'checkout_details.checkout_id', '=', 'checkouts.id')->whereBetween('checkouts.date', [Carbon\Carbon::now()->subYear()->startOfYear(), Carbon\Carbon::now()->subYear()->endOfYear()])->sum(DB::raw('checkout_details.total_price')) + $aktsubyear, 0, '.', ' ') }}</b> сум</span></li>
                                            <li><span>С начало года ({{ Carbon\Carbon::now()->format('Y') }})</span><span><b>{{ number_format(App\Models\CheckoutDetail::join('checkouts', 'checkout_details.checkout_id', '=', 'checkouts.id')->whereBetween('checkouts.date', [Carbon\Carbon::now()->startOfYear(), Carbon\Carbon::now()->endOfYear()])->sum(DB::raw('checkout_details.total_price')) + $aktyear, 0, '.', ' ') }}</b> сум</span></li>
                                        </ul>
                                        
                                        <div class="team-view">
                                            <a href="#" class="btn btn-block btn-dim btn-primary"><span>Ойма ой бўйича кўриш</span></a>
                                        </div>
                                    </div><!-- .team -->
                                </div><!-- .card-inner -->
                            </div><!-- .card -->
                        </div><!-- .col -->
                        <div class="col-sm-6 col-lg-4 col-xxl-4">
                            <div class="card card-bordered">
                                <div class="card-inner">
                                    <div class="team">
                                        <div class="user-card user-card-s2">
                                            <h5>По запасным частям</h5>
                                        </div>
                                        <ul class="team-info">
                                            @php($d = DB::table('warehouse_stocks')->select('product_id', DB::raw('count(*) as total'))->groupBy('product_id')->get())
                                            <li><span style="color: #6c6c6c;">Умумий</span><span><b>{{ number_format(App\Models\WarehouseStock::where('stock', '>', 0)->sum('stock'), 0, '.', ' ') }}</b> | {{ number_format($d->count(), 0, '.', ' ') }}</span></li>
                                            @foreach(App\Models\Warehouse::where('status', 1)->whereIn('id', [1,2,3,4])->get() as $ware)
                                            <li><span style="color: #6c6c6c;">{{ $ware->name }}</span><span><b>{{ number_format($ware->stockall()->where('stock', '>', 0)->sum('stock'), 0, '.', ' ') }}</b> | {{ number_format($ware->stockall()->count(), 0, '.', ' ') }}</span> </li>
                                            @endforeach
                                        </ul>
                                        <div class="team-view" style="display: block;">
                                            <div class="row">
                                                @foreach(App\Models\Warehouse::where('status', 1)->whereIn('id', [1,2,3,4])->get() as $ware)
                                                <div style="flex: 0 0 auto; width: 25%; @if($loop->first) padding-right: 5px; @endif @if($loop->last) padding-left: 5px; @else padding: 0 5px; @endif">
                                                    <a href="#" class="btn btn-block btn-dim btn-primary"><span>{{ $ware->num_code }}</span></a>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div><!-- .team -->
                                </div><!-- .card-inner -->
                            </div><!-- .card -->
                        </div><!-- .col -->
                        <div class="col-sm-6 col-lg-4 col-xxl-4">
                            <div class="card card-bordered">
                                <div class="card-inner">
                                    <div class="team">
                                        <div class="user-card user-card-s2">
                                            <h5>Остаток себестоимости</h5>
                                        </div>
                                        <ul class="team-info">
                                            @php($d = DB::table('warehouse_stocks')->select('product_id', DB::raw('count(*) as total'))->groupBy('product_id')->get())
                                            <li><span style="color: #6c6c6c;">Умумий</span><span><b>{{ number_format(App\Models\WarehouseStock::where('stock', '>', 0)->sum('checkin_total_price'), 0, '.', ' ') }}</b> сум | {{ number_format(App\Models\WarehouseStock::where('checkin_price', null)->count() + App\Models\WarehouseStock::where('checkin_price', '0.00')->count(), 0, '.', ' ') }}</span></li>
                                            @foreach(App\Models\Warehouse::where('status', 1)->whereIn('id', [1,2,3,4])->get() as $ware)
                                            <li><span style="color: #6c6c6c;">{{ $ware->name }}</span><span><b>{{ number_format($ware->stockall()->where('stock', '>', 0)->sum('checkin_total_price'), 0, '.', ' ') }}</b> сум | {{ number_format(App\Models\WarehouseStock::where('warehouse_id', $ware->id)->where('checkin_price', null)->count() + App\Models\WarehouseStock::where('warehouse_id', $ware->id)->where('checkin_price', '0.00')->count(), 0, '.', ' ') }}</span> </li>
                                            @endforeach
                                        </ul>
                                        <div class="team-view" style="display: block;">
                                            <div class="row">
                                                @foreach(App\Models\Warehouse::where('status', 1)->whereIn('id', [1,2,3,4])->get() as $ware)
                                                <div style="flex: 0 0 auto; width: 25%; @if($loop->first) padding-right: 5px; @endif @if($loop->last) padding-left: 5px; @else padding: 0 5px; @endif">
                                                    <a href="#" class="btn btn-block btn-dim btn-primary"><span>{{ $ware->num_code }}</span></a>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div><!-- .team -->
                                </div><!-- .card-inner -->
                            </div><!-- .card -->
                        </div><!-- .col -->
                        <div class="col-sm-6 col-lg-4 col-xxl-4">
                            <div class="card card-bordered">
                                <div class="card-inner">
                                    <div class="team">
                                        <div class="user-card user-card-s2">
                                            <h5>Остаток по продажной цене</h5>
                                        </div>
                                        <ul class="team-info">
                                            @php($d = DB::table('warehouse_stocks')->select('product_id', DB::raw('count(*) as total'))->groupBy('product_id')->get())
                                            <li><span style="color: #6c6c6c;">Умумий</span><span><b>{{ number_format(App\Models\WarehouseStock::where('stock', '>', 0)->sum('checkout_total_price'), 0, '.', ' ') }}</b> сум | {{ number_format(App\Models\WarehouseStock::where('checkout_price', null)->count() + App\Models\WarehouseStock::where('checkout_price', '0.00')->count(), 0, '.', ' ') }}</span></li>
                                            @foreach(App\Models\Warehouse::where('status', 1)->whereIn('id', [1,2,3,4])->get() as $ware)
                                            <li><span style="color: #6c6c6c;">{{ $ware->name }}</span><span><b>{{ number_format($ware->stockall()->where('stock', '>', 0)->sum('checkout_total_price'), 0, '.', ' ') }}</b> сум | {{ number_format(App\Models\WarehouseStock::where('warehouse_id', $ware->id)->where('checkout_price', null)->count() + App\Models\WarehouseStock::where('warehouse_id', $ware->id)->where('checkout_price', '0.00')->count(), 0, '.', ' ') }}</span> </li>
                                            @endforeach
                                        </ul>
                                        <div class="team-view" style="display: block;">
                                            <div class="row">
                                                @foreach(App\Models\Warehouse::where('status', 1)->whereIn('id', [1,2,3,4])->get() as $ware)
                                                <div style="flex: 0 0 auto; width: 25%; @if($loop->first) padding-right: 5px; @endif @if($loop->last) padding-left: 5px; @else padding: 0 5px; @endif">
                                                    <a href="#" class="btn btn-block btn-dim btn-primary"><span>{{ $ware->num_code }}</span></a>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div><!-- .team -->
                                </div><!-- .card-inner -->
                            </div><!-- .card -->
                        </div><!-- .col -->
                        <div style="display: none" class="col-sm-6 col-lg-4 col-xxl-4">
                            <div class="card card-bordered">
                                <div class="card-inner">
                                    <div class="team">
                                        <div class="user-card user-card-s2">
                                            <h5>3 ойликда ТОП Менеджер</h5>
                                        </div>
                                        <ul class="team-info text">
                                            @foreach($managers->paginate(5) as $manager)
                                            <li><span>{{ $manager->name }}</span><span>{{ number_format(App\Models\Checkout::where('manager_id', $manager->id)->whereBetween('created_at', [Carbon\Carbon::now()->subMonths(3)->startOfMonth(), Carbon\Carbon::now()->endOfMonth()])->count(), 0, '.', ' ') }}</span></li>
                                            @endforeach
                                            
                                            <span class="moreText"> 
                                                @foreach($managers->skip(5)->take(100)->get() as $manager)
                                                <li><span>{{ $manager->name }}</span><span>{{ number_format(App\Models\Checkout::where('manager_id', $manager->id)->whereBetween('created_at', [Carbon\Carbon::now()->subMonths(3)->startOfMonth(), Carbon\Carbon::now()->endOfMonth()])->count(), 0, '.', ' ') }}</span></li>
                                                @endforeach
                                            </span>
                                        </ul>
                                        <div class="overid overlay"></div>
                                        <div class="team-view">
                                            <button class="btn btn-block btn-dim btn-primary read-more-btn">Барчасини кўриш</button>
                                        </div>
                                    </div><!-- .team -->
                                </div><!-- .card-inner -->
                            </div><!-- .card -->
                        </div><!-- .col -->
                        <div style="display: none" class="col-sm-6 col-lg-4 col-xxl-4">
                            <div class="card card-bordered">
                                <div class="card-inner">
                                    <div class="team">
                                        <div class="user-card user-card-s2">
                                            <h5>Сотилган 3 ойликда ТОП-5</h5>
                                        </div>
                                        <ul class="team-info">
                                            @foreach($products as $product)
                                            <li><span>{{ Str::limit($product->name, 30,'') }}</span><span>{{ $product->id }}</span></li>
                                            @endforeach
                                        </ul>
                                        <div class="team-view">
                                            <a href="#" class="btn btn-block btn-dim btn-primary"><span>Охирги 3 ойликда ТОП-100</span></a>
                                        </div>
                                    </div><!-- .team -->
                                </div><!-- .card-inner -->
                            </div><!-- .card -->
                        </div><!-- .col -->
                        <div style="display: none" class="col-sm-6 col-lg-4 col-xxl-3">
                            <div class="card card-bordered">
                                <div class="card-inner">
                                    <div class="team">
                                        <div class="user-card user-card-s2">
                                            <h5>Сотилмаган 3 ойликда ТОП-5</h5>
                                        </div>
                                        <ul class="team-info">
                                            @foreach($products as $product)
                                            <li><span>{{ Str::limit($product->name, 30,'') }}</span><span>{{ $product->id }}</span></li>
                                            @endforeach
                                        </ul>
                                        <div class="team-view">
                                            <a href="#" class="btn btn-block btn-dim btn-primary"><span>Охирги 3 ойликда ТОП-100</span></a>
                                        </div>
                                    </div><!-- .team -->
                                </div><!-- .card-inner -->
                            </div><!-- .card -->
                        </div><!-- .col -->
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
const readMoreBtn = document.querySelector(".read-more-btn");
const text = document.querySelector(".text");
const over = document.querySelector(".overid");
readMoreBtn.addEventListener("click", (e) => {
  text.classList.toggle("show-more");
  if (readMoreBtn.innerText === "Барчасини кўриш") {
    readMoreBtn.innerText = "Ёпиш";
    over.classList.remove("overlay");
    
  } else {
    readMoreBtn.innerText = "Барчасини кўриш";
    over.classList.toggle("overlay");
  }
});

</script>
@endsection