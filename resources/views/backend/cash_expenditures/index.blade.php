@extends('layouts.backend')

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="components-preview mx-auto">
                    <div class="nk-block nk-block-lg">
                            <div class="row">
                                <div class="col-md-10">
                                    <form method="POST" action="{{ route('cash_expenditures_search') }}">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-2 mb-3">
                                            <input type="text" title="От" name="date_ot" value="{{ Carbon\Carbon::now()->startOfMonth()->format('d.m.Y') }}" class="form-control date-picker" placeholder="Дата">
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <input type="text" title="До" name="date_do" value="{{ Carbon\Carbon::now()->endOfMonth()->format('d.m.Y') }}" class="form-control date-picker" placeholder="Дата">
                                        </div>
                                        <div class="col-lg-3 col-sm-4">
                                            <select class="form-select js-select2" title="Тип затрата" placeholder="Select Multiple options" name="type" required data-search="on">
                                                <option value="0">Все</option>
                                                @foreach($contracts as $contract)
                                                    <option value="{{ $contract->id }}">{{ $contract->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @hasanyrole('admin|report')
                                        <div class="col-lg-3 col-sm-4">
                                            <select class="form-select js-select2" title="Тип затрата" placeholder="Select Multiple options" name="store" required data-search="on">
                                                <option value="0">Все</option>
                                                @foreach($stores as $store)
                                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @endhasanyrole
                                        <div class="col-md-2 mb-2">
                                           <button type="submit" class="btn btn-warning btn-block">Поиск</button> 
                                        </div>
                                    </div>
                                    </form>
                                </div>
                                <div class="col-md-2 mb-2">
                               <a href="{{ route('cash_expenditure_form') }}" class="btn btn-primary btn-block">Добавить</a> 
                            </div>
                            </div>
                        
                        
                        <div class="card">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                  <thead>
                                    <tr class="text-center">
                                      <th scope="col">Наимнование</th>
                                      <th scope="col">Итого сумма</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @php($s = 0)
                                    @foreach($contracts as $contract)
                                    <tr class="text-center">
                                        <td width="180px">{{ $contract->name }}</td>
                                        <td width="180px;">
                                          @hasanyrole('admin|report') 
                                          @php($s += $contract->details->sum('price'))
                                          <a href="{{ route('cash_category_select', ['id' => $contract->code]) }}">
                                          {{ number_format($contract->details->sum('price'), 0, '.', ' ') }} 
                                          @else 
                                          @php($s += $contract->details->where('user_id', Auth::id())->sum('price'))
                                          {{ number_format($contract->details->where('user_id', Auth::id())->sum('price'), 0, '.', ' ') }} 
                                          </a> 
                                          @endhasanyrole сум
                                          
                                        </td>
                                    </tr>
                                    @endforeach
                                    <tr class="text-center">
                                        <td width="180px"><b>Итого</b></td>
                                        <td width="180px;"><b>{{ number_format($s, 0, '.', ' ') }} сум</b></td>
                                    </tr>
                                  </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="card">
                            <h4 class="text-center mb-2 mt-2">История</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                  <thead>
                                    <tr class="text-center">
                                      <th scope="col">Тип затрата</th>
                                      <th scope="col">Сумма</th>
                                      <th scope="col">Способ</th>
                                      <th scope="col">Комментарии</th>
                                      <th scope="col">Дата</th>
                                      <th scope="col">Кто завел</th>
                                      <th scope="col">Удалить</th>
                                      <th scope="col">Действия</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @foreach($data as $item)
                                    <tr class="text-center">
                                      <td>{{ $item->cename ? $item->cename->name : NULL }}</td>
                                      <td>{{ number_format($item->price, 0, '.', ' ') }} сум</td>
                                      <td>{{ $item->typename ? $item->typename->name : NULL }}</td>
                                      <td>{{ $item->comment }}</td>
                                      <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
                                      <td>{{ $item->user_id ? $item->uname->name : NULL }}</td>
                                      <td><a href="{{ route('cash_exp_delete', ['id' => $item->code])}}" style="text-decoration:underline;">Удалить</a></td>
                                      <td><a href="{{ route('cash_expenditure_form', ['id' => $item->code])}}" style="text-decoration:underline;">Редактировать</a></td>
                                    </tr>
                                    @endforeach
                                  </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    @include('layouts.nav')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection