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
                                  @if($errors->any())
                                    <div class="alert alert-danger">
                                        @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                                    </div>
                                  @endif
                                  
                                   {!! Form::open() !!}
                                    
                                    <div class="row gy-3">
                                        
                                        <div class="col-lg-2 col-sm-2">
                                            <div class="form-group">
                                                <label class="form-label" for="inps">{{ trans('savdo.form.select_date') }}</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" name="date" value="{{ old('date', $item && $item->date ? $item->date : Carbon\Carbon::now()->format('d.m.Y')) }}"
                                                    class="form-control date-picker" placeholder="{{ trans('savdo.form.select_date') }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-3 col-sm-3">
                                            <div class="form-group">
                                                <label class="form-label">{{ trans('savdo.form.type_cash_expenditure') }}</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2" id="cash_expenditure_types" onchange="showDiv(this.value)" placeholder="Select Multiple options" name="cash_expenditure_types" required data-search="on">
                                                            <option value="">{{ trans('savdo.form.type_cash_expenditure') }}</option>
                                                        @foreach($contracts as $contract)
                                                            <option
                                                                @if((string) old('cash_expenditure_types', optional($item)->cash_expenditure_types) === (string) $contract->id) selected @endif
                                                                value="{{ $contract->id }}"
                                                                data-bonus-source="{{ in_array((int) $contract->id, $mainExpenditureTypeIds, true) ? 1 : 0 }}"
                                                            >{{ $contract->name }}</option>
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
                                                            <option @if((string) old('cash_receipt_type_id', optional($item)->cash_receipt_type_id) === (string) $type->id) selected @endif value="{{ $type->id }}">{{ $type->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-12" id="bonus_source_fields" style="display:none;">
                                            <div class="row gy-3">
                                                <div class="col-lg-6 col-sm-6">
                                                    <div class="form-group">
                                                        <label class="form-label">Kimning to‘lovidan (bonus)</label>
                                                        <div class="form-control-wrap">
                                                            <select class="form-select js-select2" id="bonus_client_id" name="bonus_client_id" data-search="on">
                                                                <option value="">Mijozni tanlang</option>
                                                                @foreach($clients as $client)
                                                                    <option value="{{ $client->id }}" @if((string) old('bonus_client_id', optional($item)->bonus_client_id) === (string) $client->id) selected @endif>
                                                                        {{ $client->name }}{{ $client->phone ? ' | '.$client->phone : '' }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-6 col-sm-6">
                                                    <div class="form-group">
                                                        <label class="form-label">Qaysi to‘lovidan kamayadi</label>
                                                        <div class="form-control-wrap">
                                                            <select
                                                                class="form-select js-select2"
                                                                id="source_cash_receipt_id"
                                                                name="source_cash_receipt_id"
                                                                data-search="on"
                                                                data-url="{{ route('cash_expenditure_client_payments', ['client' => '__CLIENT__']) }}"
                                                                data-exclude-expense-id="{{ optional($item)->id }}"
                                                            >
                                                                <option value="">To‘lovni tanlang</option>
                                                                @foreach($sourcePayments as $payment)
                                                                    <option
                                                                        value="{{ $payment['id'] }}"
                                                                        data-available="{{ $payment['available'] }}"
                                                                        data-currency="{{ $payment['currency'] }}"
                                                                        @if((string) old('source_cash_receipt_id', optional($item)->source_cash_receipt_id) === (string) $payment['id']) selected @endif
                                                                    >{{ $payment['label'] }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <small class="text-soft">Основной xarajat summasi oylik Excelda “To‘langan”dan ayriladi va bonus ustunida chiqadi. Mijoz qarzi o‘zgarmaydi.</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-3 col-sm-3">
                                            <div class="form-group">
                                                <label class="form-label" for="default-02">{{ trans('savdo.form.summa') }}</label>
                                                <div class="form-control-wrap">
                                                    <input type="number" step="0.01" class="form-control" name="price" id="default-02" required placeholder="Сумма" value="{{ old('price', optional($item)->price) }}">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-2 col-sm-2">
                                            <div class="form-group">
                                                <label class="form-label" for="default-01">{{ trans('savdo.form.comment') }}</label>
                                                <div class="form-control-wrap">
                                                    <input type="text" value="{{ old('comment', optional($item)->comment) }}" class="form-control" name="comment" id="default-01"
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

@section('script')
<script>
    $(document).ready(function () {
        const $client = $('#bonus_client_id');
        const $payment = $('#source_cash_receipt_id');
        const $price = $('input[name="price"]');
        const $expenseType = $('#cash_expenditure_types');
        const $bonusFields = $('#bonus_source_fields');

        function isMainExpenseSelected() {
            return String($expenseType.find('option:selected').data('bonus-source')) === '1';
        }

        function toggleBonusFields() {
            const enabled = isMainExpenseSelected();
            $bonusFields.toggle(enabled);
            $client.prop('disabled', !enabled).prop('required', enabled);

            if (!enabled) {
                $client.val('').trigger('change.select2');
                $payment.empty()
                    .append(new Option('To‘lovni tanlang', ''))
                    .prop('disabled', true)
                    .prop('required', false)
                    .trigger('change.select2');
                $price.removeAttr('max title');
                return;
            }

            if ($client.val()) {
                $payment.prop('disabled', false).prop('required', true);
            } else {
                $payment.prop('disabled', true).prop('required', false);
            }
        }

        function applyPaymentLimit() {
            const option = $payment.find('option:selected');
            const available = option.data('available');
            const currency = option.data('currency');

            if (available !== undefined) {
                $price.attr('max', available);
                $price.attr('title', 'Tanlangan to‘lovdan qolgan summa: ' + available + ' ' + currency);
            } else {
                $price.removeAttr('max title');
            }
        }

        function loadPayments(clientId) {
            $payment.prop('disabled', true).empty().append(new Option('Yuklanmoqda...', ''));
            $payment.trigger('change.select2');

            if (!clientId) {
                $payment.empty()
                    .append(new Option('To‘lovni tanlang', ''))
                    .prop('required', false)
                    .trigger('change.select2');
                $price.removeAttr('max title');
                return;
            }

            let url = $payment.data('url').replace('__CLIENT__', clientId);
            const excludeExpenseId = $payment.data('exclude-expense-id');
            if (excludeExpenseId) {
                url += '?exclude_expense_id=' + encodeURIComponent(excludeExpenseId);
            }

            fetch(url, {headers: {'Accept': 'application/json'}})
                .then(response => {
                    if (!response.ok) throw new Error('To‘lovlarni yuklab bo‘lmadi.');
                    return response.json();
                })
                .then(data => {
                    $payment.empty().append(new Option('To‘lovni tanlang', ''));
                    data.payments.forEach(payment => {
                        const option = new Option(payment.label, payment.id);
                        option.dataset.available = payment.available;
                        option.dataset.currency = payment.currency;
                        $payment.append(option);
                    });
                    $payment.prop('disabled', false).prop('required', true).trigger('change.select2');
                })
                .catch(error => {
                    $payment.empty()
                        .append(new Option(error.message, ''))
                        .prop('disabled', false)
                        .prop('required', true)
                        .trigger('change.select2');
                });
        }

        $client.on('change', function () {
            if (isMainExpenseSelected()) {
                loadPayments(this.value);
            }
        });
        $payment.on('change', applyPaymentLimit);
        $expenseType.on('change', toggleBonusFields);

        toggleBonusFields();
        if (isMainExpenseSelected() && $client.val()) {
            applyPaymentLimit();
        }
    });
</script>
@endsection
