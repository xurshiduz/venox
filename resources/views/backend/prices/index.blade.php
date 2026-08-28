@extends('layouts.backend')

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="components-preview mx-auto">
                    <div class="nk-block nk-block-lg">
                        <div class="row">
                            <div class="col-md-10 mb-3">
                                <form method="POST" action="{{ route('products_search') }}">
                                    @csrf
                                    <input type="text" class="form-control" value="{{ $keyword ? $keyword : NULL }}" name="search" required placeholder="Поиск по штрих-код и наименование">
                                </form>
                            </div>
                            <div class="col-md-2 mb-2">
                               <a href="#" class="btn btn-warning btn-block">Печать прайса</a> 
                            </div>
                        </div>
                        @php($curs = App\Models\CurrencyType::all())
                        <div class="card">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                  <thead>
                                    <tr class="text-center">
                                      <th scope="col">Наименование</th>
                                      <th scope="col">Штрих код</th>
                                      @foreach($curs as $cur)
                                      <th width="150px">Цена ({{ $cur->belgi }})</th>
                                      @endforeach
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @foreach($data as $item)
                                    <tr class="text-center">
                                      <td>{{ $item->name }}</td>
                                      <td>{{ $item->id }}</td>
                                      @foreach($curs as $cur)
                                      <td>
                                        @if($item->price && $item->currency_type)
                                            @if($item->currency_type == $cur->id)
                                                {{ number_format($item->price, 2, '.', ' ') }}
                                            @else
                                                @if($item->currency_type == 1)
                                                    {{ number_format(($item->price / $cur->currencyid->first()->price), 2, '.', ' ') }}
                                                @else
                                                    {{ number_format(($item->price * $item->currencyid->currencyid->first()->price), 2, '.', ' ') }}
                                                @endif
                                            @endif
                                        @endif

                                        </td>
                                      @endforeach
                                    </tr>
                                    @endforeach
                                  </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    @include('backend.nav')
            </div>
        </div>
    </div>
</div>
@endsection