@extends('layouts.backend')

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="components-preview mx-auto">
                    <div class="nk-block nk-block-lg">
                        <div class="row">
                            <div class="col-lg-11 col-md-11 col-sm-11 mb-3">
                                <form method="GET" action="{{ route('products_stock_search') }}">
                                    <input type="text" class="form-control" value="{{ $keyword ? $keyword : NULL }}" name="search" required placeholder="Поиск по штрих-код и наименование">
                                </form>
                            </div>
                            <div class="col-lg-1 col-md-1 col-sm-1 mb-3">
                                <button class="btn btn-dark btn-block" type="submit"><em class="icon ni ni-scan"></em></button>
                            </div>
                        </div>

                        <div class="card">
                            <div class="table-responsive">
                                <table class="table table-bordered text-nowrap">
                                  <thead>
                                    <tr class="text-center">
                                      <th scope="col">Наименование</th>
                                      <th scope="col">Штрих-код товара</th>
                                      <th scope="col">Категория</th>
                                      <th width="150px">Остатка</th>
                                      <th scope="col">Средняя продажа в месяц</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @foreach($data as $item)
                                    <tr>
                                      <td>{{ $item->name }}</td>
                                      <td class="text-center">{{ $item->barcode }}</td>
                                      <td class="text-center">{{ $item->category_id ? $item->catid->name : NULL}}</td>
                                      <td class="text-center">{{ ($item->checkindetails->where('status', 1)->sum('qty') - $item->checkoutdetails->where('status', 1)->sum('qty')) }} {{ $item->unitid ? $item->unitid->name : null}}</td>
                                      <td class="text-center">
                                        @if($item->checkoutdetails->sum('qty') != 0)
                                        @php($counts = $item->checkoutdetails->groupBy(function($val) { return Carbon\Carbon::parse($val->created_at)->format('Y-m'); })->count())
                                            {{ $item->checkoutdetails->sum('qty') / $counts}} {{ $item->unitid ? $item->unitid->name : NULL}}
                                        @endif
                                      </td>
                                    </tr>
                                    @endforeach
                                  </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <?php
                    $link_limit = 8;
                    ?>

                    <nav class="mt-3">
                    @if ($data->lastPage() > 1)
                        <ul class="pagination">
                            <li class="page-item {{ ($data->currentPage() == 1) ? ' disabled' : '' }}">
                                <a class="page-link" href="{{ $data->url(1) }}" tabindex="-1" aria-disabled="true">Пред</a>
                             </li>
                            @for ($i = 1; $i <= $data->lastPage(); $i++)
                                <?php
                                $half_total_links = floor($link_limit / 2);
                                $from = $data->currentPage() - $half_total_links;
                                $to = $data->currentPage() + $half_total_links;
                                if ($data->currentPage() < $half_total_links) {
                                   $to += $half_total_links - $data->currentPage();
                                }
                                if ($data->lastPage() - $data->currentPage() < $half_total_links) {
                                    $from -= $half_total_links - ($data->lastPage() - $data->currentPage()) - 1;
                                }
                                ?>
                                @if ($from < $i && $i < $to)
                                    <li class="{{ ($data->currentPage() == $i) ? ' active' : '' }}" style="font-weight: {{ ($data->currentPage() == $i) ? 'bold' : '' }}">
                                        <a class="page-link" href="{{ $data->url($i) }}">{{ $i }}</a>
                                    </li>
                                @endif
                            @endfor
                            <li class="page-item {{ ($data->currentPage() == $data->lastPage()) ? ' disabled' : '' }}">
                                <a class="page-link" href="{{ $data->url($data->lastPage()) }}">Посл.</a>
                            </li>
                        </ul>
                    @endif
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection