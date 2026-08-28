@extends('layouts.backend')

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="nk-block">
                    @foreach($item->warehouseall()->get()->groupBy('block') as $ware)
                    <div class="row g-gs mb-3">
                        <div class="col-sm-6 col-lg-4 col-xxl-3">
                            <div class="card card-bordered">
                                <div class="card-inner">
                                    <div class="team">
                                        <div class="user-card user-card-s2">
                                            <div class="user-avatar lg bg-primary">
                                                <span>{{ $ware->first()->block }}</span>
                                            </div>
                                            <div class="user-info">
                                                <h6>Хранение на стеллажах</h6>
                                            </div>
                                        </div>
                                        <ul class="team-info">
                                            <li><span>Рядов</span><span>{{ $ware->first()->rowall->whereNotNull('row')->count() }}</span></li>
                                            <li><span>Столбцов</span><span>{{ $ware->first()->rowall->whereNotNull('column')->count() }}</span></li>
                                            <li><span>Этажов</span><span>{{ $ware->first()->rowall->whereNotNull('floor')->count() }}</span></li>
                                        </ul>
                                        <div class="team-view">
                                            <a href="html/user-details-regular.html" class="btn btn-block btn-dim btn-primary"><span>Посмотреть</span></a>
                                        </div>
                                    </div>
                                </div><!-- .card-inner -->
                            </div><!-- .card -->
                        </div>
                    </div>
                    @endforeach
                    @foreach($item->warehouseall()->whereNull('row')->get()->groupBy('block') as $ware)
                    <div class="row g-gs">
                        <div class="col-sm-6 col-lg-4 col-xxl-3">
                            <div class="card card-bordered">
                                <div class="card-inner">
                                    <div class="team">
                                        <div class="user-card user-card-s2">
                                            <div class="user-avatar lg bg-warning">
                                                <span>{{ $ware->first()->block }}</span>
                                            </div>
                                            <div class="user-info">
                                                <h6>Хранение на полу</h6>
                                            </div>
                                        </div>
                                        <ul class="team-info">
                                            <li><span>Примичание</span><span>{{ $ware->first()->comment }}</span></li>
                                        </ul>
                                        <div class="team-view">
                                            <a href="html/user-details-regular.html" class="btn btn-block btn-dim btn-primary"><span>Посмотреть</span></a>
                                        </div>
                                    </div>
                                </div><!-- .card-inner -->
                            </div><!-- .card -->
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection