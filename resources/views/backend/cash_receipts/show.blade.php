@extends('layouts.backend')

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        
          <nav style="padding:0px 0 20px 0;">
            <ul class="breadcrumb breadcrumb-arrow">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Главная</a></li>
                <li class="breadcrumb-item"><a href="{{ route('cash_receipts_index') }}">Все клиенты</a> </li>
            </ul>
        </nav>
        
        
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="components-preview mx-auto">
                    <div class="nk-block nk-block-lg">
                        <div class="card">
                            <div class="row">
                                <div class="col-md-9 mb-1">
                                    <form method="POST" action="#">
                                        @csrf
                                        <input id="searchText" type="text" class="form-control" value="{{ $keyword ? $keyword : NULL }}" name="search" required placeholder="Поиск по № договора">
                                    </form>
                                </div>
                                
                                <div class="col-md-3 mb-1"> 
                                   <a href="{{ route('cash_receipt_form',['client'=>$id])  }}" class="btn btn-success text-uppercase btn-block">Добавить</a>  
                                </div>
                                
                            </div>
                            <div class="table-responsive text-nowrap">
                                  <table class="table table-bordered" >
                                      <thead>
                                        <tr class="text-center">
                                          <th scope="col">Номер контракта</th>
                                          <th scope="col">Сумма поступление</th>
                                          <th scope="col">Дата поступление</th>
                                        </tr>
                                      </thead>
                                      
                                      <tbody id="searchTable">
                                        @foreach($cashes as $item)
                                        <tr class="text-center">
                                          <td width="180px">{{ $item->contracktname->number }}</td>
                                          <td width="180px">{{ number_format($item->price,2,',',' ') }}</td>
                                          <td width="180px">{{ Carbon\Carbon::parse($item->date)->format('d/m/Y'); }}</td>
                                         
                                        </tr>
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
