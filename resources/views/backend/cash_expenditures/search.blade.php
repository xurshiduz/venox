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
                                            <input type="text" title="От" name="date_ot" value="{{ $ot ? Carbon\Carbon::parse($ot)->format('d.m.Y') : Carbon\Carbon::now()->startOfMonth()->format('d.m.Y') }}" class="form-control date-picker" placeholder="Дата">
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <input type="text" title="До" name="date_do" value="{{ $do ? Carbon\Carbon::parse($do)->format('d.m.Y') : Carbon\Carbon::now()->endOfMonth()->format('d.m.Y') }}" class="form-control date-picker" placeholder="Дата">
                                        </div>
                                        <div class="col-lg-3 col-sm-4">
                                            <select class="form-select js-select2" title="Тип затрата" placeholder="Select Multiple options" name="type" required data-search="on">
                                                <option value="0">Все</option>
                                                @foreach($contracts as $contract)
                                                    <option value="{{ $contract->id }}">{{ $contract->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @hasanyrole('admin')
                                        <div class="col-lg-3 col-sm-4">
                                            <select class="form-select js-select2" title="Тип затрата" placeholder="Select Multiple options" name="store" required data-search="on">
                                                <option {{ $magazin == 0 ? 'selected' : NULL }} value="0">Все</option>
                                                @foreach($stores as $store)
                                                    <option {{ $magazin == $store->id ? 'selected' : NULL }} value="{{ $store->id }}">{{ $store->name }}</option>
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
                                <h4 class="text-center mb-2 mt-2">Итого: {{ number_format($data->sum('price'), 0, '.', ' ') }} сум</h4>
                                <table class="table table-bordered">
                                  <thead>
                                    <tr class="text-center">
                                      <th width="20px"></th>
                                      <th width="19%">Наимнование</th>
                                      <th width="19%">Итого сумма</th>
                                      <th width="19%"></th>
                                      <th width="19%"></th>
                                      <th width="19%"></th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                      
                                    @foreach($contracts as $contract)
                                    <tr class="text-center">
                                      <td>
                                        <button style="padding: 0px;" data-childrow-target="uniqueChild-{{ $contract->id }}" class="btn child-row-toggler">
                                             <em class="icon ni ni-chevron-right"></em>
                                        </button> 
                                      </td>
                                      @hasanyrole('admin')
                                        @php($dets = $magazin ? $contract->details()->whereBetween('date', [$ot, $do])->where('store_id', $magazin)->get() : $contract->details()->whereBetween('date', [$ot, $do])->get())
                                      @endhasanyrole
                                      <td width="19%">{{ $contract->name }}</td>
                                      <td width="19%">@hasanyrole('admin') {{ number_format($dets->sum('price'), 0, '.', ' ') }} @else {{ number_format($contract->details->whereBetween('date', [$ot, $do])->where('store_id', Auth::user()->store_id)->sum('price'), 0, '.', ' ') }} @endhasanyrole сум</td>
                                      <td width="19%"></td>
                                      <td width="19%"></td>
                                      <td width="19%"></td>
                                    </tr>
                                        @hasanyrole('admin') 
                                        
                                            @foreach($dets as $key=> $item)
                                    
                                                @if ($loop->first)
                                                    <tr  class="text-center child-row" data-childrow-label="uniqueChild-{{ $contract->id }}">
                                                        <td></td>
                                                        <th width="19%">Сумма</th>
                                                        <th width="19%">Способ оплаты</th>
                                                        <th width="19%">Комментарии</th>
                                                        <th width="19%">Дата</th>
                                                        <th width="19%">Кто завел</th>
                                                    </tr>
                                                @endif
                                            
                                                <tr class="text-center child-row" data-childrow-label="uniqueChild-{{ $contract->id }}">
                                                    <td></td>
                                                  <td width="19%">{{ number_format($item->price, 0, '.', ' ') }} сум</td>
                                                  <td width="19%">{{ $item->typename ? $item->typename->name : NULL }}</td>
                                                  <td width="19%">{{ $item->comment }}</td>
                                                  <td width="19%">{{ $item->created_at->format('Y-m-d H:i') }}</td>
                                                  <td width="19%">{{ $item->user_id ? $item->uname->name : NULL }}</td>
                                                </tr>
                                            @endforeach
                                        
                                        @else 
                                        
                                            @foreach($contract->details->whereBetween('date', [$ot, $do])->where('store_id', Auth::user()->store_id) as $key=> $item)
                                                @if ($loop->first)
                                                    <tr  class="text-center child-row" data-childrow-label="uniqueChild-{{ $contract->id }}">
                                                        <td></td>
                                                        <th width="19%">Сумма</th>
                                                        <th width="19%">Комментарии</th>
                                                        <th width="19%">Дата</th>
                                                        <th width="19%">Кто завел</th>
                                                    </tr>
                                                @endif
                                            
                                                <tr class="text-center child-row" data-childrow-label="uniqueChild-{{ $contract->id }}">
                                                    <td></td>
                                                  <td width="19%">{{ number_format($item->price, 0, '.', ' ') }} сум</td>
                                                  <td width="19%">{{ $item->comment }}</td>
                                                  <td width="19%">{{ $item->created_at->format('Y-m-d H:i') }}</td>
                                                  <td width="19%">{{ $item->user_id ? $item->uname->name : NULL }}</td>
                                                </tr>
                                            @endforeach
                                        @endhasanyrole
                                    @endforeach
                                  </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection