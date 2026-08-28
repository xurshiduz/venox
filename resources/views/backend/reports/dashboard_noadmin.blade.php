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
                                            <h5>Касса</h5>
                                        </div>
                                        <ul class="team-info">
                                            @php($aktbugun = App\Models\CashReceipt::whereDate('date', Carbon\Carbon::today())->sum('price'))
                                            @php($aktmonth = App\Models\CashReceipt::whereBetween('date', [Carbon\Carbon::now()->startOfMonth(), Carbon\Carbon::now()->endOfMonth()])->sum('price'))
                                            
                                            <li><span>Сегодня ({{ Carbon\Carbon::now()->locale('ru_RU')->dayName }})</span><span><b>{{ number_format($aktbugun, 0, '.', ' ') }}</b> сум</span></li>
                                            <li><span>Текущий мес. ({{ Str::limit(Carbon\Carbon::now()->locale('ru_RU')->monthName, 3, '') }})</span><span><b>{{ number_format($aktmonth, 0, '.', ' ') }}</b> сум</span></li>
                                        </ul>
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
                                            @php($dolgbugun = App\Models\Checkout::where('checkout_tip_id', 1)->whereDate('date', Carbon\Carbon::today())->sum('total_price_debt'))
                                            @php($dolgmonth = App\Models\Checkout::where('checkout_tip_id', 1)->whereBetween('date', [Carbon\Carbon::now()->startOfMonth(), Carbon\Carbon::now()->endOfMonth()])->sum('total_price_debt'))
                                            
                                            <li><span>Сегодня ({{ Carbon\Carbon::now()->locale('ru_RU')->dayName }})</span><span><b>{{ number_format($dolgbugun, 0, '.', ' ') }}</b> сум</span></li>
                                            <li><span>Текущий мес. ({{ Str::limit(Carbon\Carbon::now()->locale('ru_RU')->monthName, 3, '') }})</span><span><b>{{ number_format($dolgmonth, 0, '.', ' ') }}</b> сум</span></li>
                                        </ul>
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
                                            <li><span>Сегодня ({{ Carbon\Carbon::now()->locale('ru_RU')->dayName }})</span><span><b>{{ number_format($aktbugun + $dolgbugun, 0, '.', ' ') }}</b> сум</span></li>
                                            <li><span>Текущий мес. ({{ Str::limit(Carbon\Carbon::now()->locale('ru_RU')->monthName, 3, '') }})</span><span><b>{{ number_format($aktmonth + $dolgmonth, 0, '.', ' ') }}</b> сум</span></li>
                                        </ul>
                                        
                                    </div><!-- .team -->
                                </div><!-- .card-inner -->
                            </div><!-- .card -->
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