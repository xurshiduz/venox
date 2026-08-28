@extends('layouts.backend')

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="components-preview mx-auto">
                    <div class="nk-block nk-block-lg">
                        <div class="card card-bordered card-preview">
                            <div class="card-inner">
                                <div class="preview-block">
                                  
                                   {!! Form::open() !!}
                                    
                                    <div class="row gy-3">
                                        
                                        <div class="col-lg-2 col-sm-2">
                                            <div class="form-group">
                                                <label class="form-label" for="inps">{{ trans('savdo.form.select_date') }}</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" name="date" value="{{ $item && $item->date ? $item->date : Carbon\Carbon::now()->format('d.m.Y') }}"
                                                    class="form-control date-picker" placeholder="{{ trans('savdo.form.select_date') }}">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-3 col-sm-3">
                                            <div class="form-group">
                                                <label class="form-label">{{ trans('savdo.form.type_cash_expenditure') }}</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2" onchange="showDiv(this.value)" placeholder="Select Multiple options" name="cash_expenditure_types" required data-search="on">
                                                            <option value="">{{ trans('savdo.form.type_cash_expenditure') }}</option>
                                                        @foreach($contracts as $contract)
                                                            <option @if($item && $item->cash_expenditure_types == $contract->id) selected @endif value="{{ $contract->id }}">{{ $contract->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-2 col-sm-2">
                                            <div class="form-group">
                                                <label class="form-label">{{ trans('savdo.form.type_pay') }}</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2" placeholder="Select Multiple options" name="cash_receipt_type_id" required data-search="on">
                                                        @foreach($types as $type)
                                                            <option @if($item && $item->cash_receipt_type_id == $type->id) selected @endif value="{{ $type->id }}">{{ $type->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-3 col-sm-3">
                                            <div class="form-group">
                                                <label class="form-label" for="default-02">{{ trans('savdo.form.summa') }}</label>
                                                <div class="form-control-wrap">
                                                    <input type="number" class="form-control" name="price" id="default-02" required placeholder="Сумма" value="{{ $item ? $item->price : NULL }}">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-2 col-sm-2">
                                            <div class="form-group">
                                                <label class="form-label" for="default-01">{{ trans('savdo.form.comment') }}</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" value="{{ $item && $item->comment ? $item->comment : NULL }}" class="form-control" name="comment" id="default-01"
                                                    placeholder="{{ trans('savdo.form.comment') }}">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        
                                        <div id="hidden_div" {{ ($item && ($item->supplier_id || (int)$item->cash_expenditure_types === 8)) ? '' : 'style=display:none;' }}>
                                            <div class="form-group">
                                                <label class="form-label">{{ trans('savdo.form.select_ship') }}</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2" placeholder="Select Multiple options" id="div_select" name="supplier_id" required data-search="on">
                                                        @foreach($suppliers as $supplier)
                                                            <option @if($item && $item->supplier_id == $supplier->id) selected @endif value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-12 col-sm-12" id="hidden_emp" style="display:none;" >
                                            <div class="form-group">
                                                <label class="form-label">{{ trans('savdo.form.select_empl') }}</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2" placeholder="Select Multiple options" id="emp_select" name="employee_id" data-search="on">
                                                        @foreach($employees as $employee)
                                                            <option @if($item && $item->employee_id == $employee->id) selected @endif value="{{ $employee->id }}">{{ $employee->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        
                                        <script>
                                            function showDiv(value){
                                                if(value == 8){
                                                    document.getElementById('hidden_div').style.display = "block";
                                                    document.getElementById('hidden_emp').style.display = "none";
                                                    document.getElementById('emp_select').setAttribute("disabled","disabled");
                                                    document.getElementById('div_select').removeAttribute('disabled');
                                                } else{
                                                    document.getElementById('hidden_div').style.display = "none";
                                                    document.getElementById('hidden_emp').style.display = "none";
                                                    document.getElementById('div_select').setAttribute("disabled","disabled");
                                                    document.getElementById('emp_select').setAttribute("disabled","disabled");
                                                }
                                            }
                                        
                                            document.addEventListener("DOMContentLoaded", function () {
                                                let select = document.querySelector('select[name="cash_expenditure_types"]');
                                        
                                                if(select){
                                                    showDiv(select.value);
                                        
                                                    // 🔥 select2 uchun
                                                    $(select).on('change', function () {
                                                        showDiv(this.value);
                                                    });
                                                }
                                            });
                                        </script>
                                        
                                        
                                    </div>
                                    
                                    <div class="row gy-3 mt-3">
                                        <div class="col-md-6">
                                            <a href="{{ route('cash_receipts_index') }}" class="btn btn-danger btn-block text-uppercase">{{ trans('savdo.home.cancel_button') }}</a>
                                        </div>
                                        <div class="col-md-6">
                                            <button type="submit" class="btn btn-primary btn-block text-uppercase" onclick="this.disabled=true;this.value='Submitting...'; this.form.submit();">{{ trans('savdo.home.save_button') }}</button>
                                        </div>
                                    </div>
                                    {!! Form::close() !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection