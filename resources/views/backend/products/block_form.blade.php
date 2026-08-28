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
                                    {!! Form::open(['id' => 'appointment_form']) !!}
                                    <div class="row gy-3">
                                        <div class="col-lg-2 col-sm-2">
                                            <div class="form-group">
                                                <label class="form-label">{{ trans('backend.menu.warehouses_index') }}</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2 wareid" placeholder="{{ trans('backend.menu.manufacturer_product') }}" required data-search="on">
                                                        <option value="">Выберите склада</option>
                                                        @foreach($warehouses as $warehouse)
                                                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-2 col-sm-2">
                                            <div class="form-group">
                                                <label class="form-label">{{ trans('backend.table.block') }}</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2 all_shirina" placeholder="{{ trans('backend.menu.manufacturer_product') }}" required data-search="on">
                                                        <option value="">Выберите склада</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-2 col-sm-2">
                                            <div class="form-group">
                                                <label class="form-label">{{ trans('backend.table.cell') }}</label>
                                                <div class="form-control-wrap">
                                                    <select class="form-select js-select2 all_cell" placeholder="{{ trans('backend.menu.manufacturer_product') }}" name="warehouse_cell_id" required data-search="on">
                                                        <option value="">Выберите склада</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-1">
                                            <label class="form-label" style="color: white;">.</label>
                                            <button id="register" type="submit" class="btn btn-primary btn-block">OK</button>
                                        </div>
                                    
                                    </div>
                                    {!! Form::close() !!}    
                                    <hr style="margin-top: 10px;">
                                    @if($item)
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>{{ trans('backend.input.warehouse') }}</th>
                                                    <th>{{ trans('backend.table.block') }}</th>
                                                    <th><em class="icon ni ni-trash"></em></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($details as $detail)
                                                <tr>
                                                    <td width="50px">{{$loop->iteration}}</td>
                                                    <td>{{ $detail->wareid->name }}</td>
                                                    <td>{{ $detail->wareblockid ? $detail->wareblockid->row : null}}-блок {{ $detail->wareblockcellid ? $detail->wareblockcellid->cell . '-' . $detail->wareblockcellid->cell_number  : null}}</td>
                                                    <td width="50px"><a href="{{ route('product_block_delete', ['id' => $detail->code]) }}"><em class="icon ni ni-trash"></em></a></td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @endif
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
    $('#appointment_form').on('submit', function () {
       $('#register').attr('disabled', 'true'); 
    });
    
    $(".wareid").on("keyup change", function(e) {
        
        var model = $('.wareid').val();
        
        option = " ",
        parent = $('.all_shirina').parent().parent().parent();  
        
        $.ajax({
            type: 'POST',
            url: '{{ route("warehouse_blocks_api") }}', 
            data: {'model': model},
            success:function(data) {                          
                $.each(data, function(index, item) {
                    option += '<option value="' + item.id +'">'+ item.row + '</option>'; 
                });                 

                parent.find('.all_shirina').html(" ");
                parent.find('.all_shirina').append('<option value="0" disabled selected>Выберите блок</option>');
                parent.find('.all_shirina').append(option);         
            },
                error:function(data){
                    console.log('error');
            }      
        });  
    });
    
    $(".all_shirina").on("keyup change", function(e) {
        
        var model = $('.all_shirina').val();
        
        option = " ",
        parent = $('.all_cell').parent().parent().parent();  
        
        $.ajax({
            type: 'POST',
            url: '{{ route("warehouse_block_cells_api") }}', 
            data: {'model': model},
            success:function(data) {                          
                $.each(data, function(index, item) {
                    option += '<option value="' + item.id +'">'+ item.cell + '-' + item.cell_number + '</option>'; 
                });                 

                parent.find('.all_cell').html(" ");
                parent.find('.all_cell').append('<option value="0" disabled selected>Выберите ячейка</option>');
                parent.find('.all_cell').append(option);         
            },
                error:function(data){
                    console.log('error');
            }      
        });  
    });
</script>

@endsection