@extends('layouts.backend')

@section('content')
<div class="nk-content">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="components-preview">
                    <div class="nk-block-head nk-block-head-sm">
                        <div class="nk-block nk-block-lg">
                            <div class="nk-block-head">
                                <div class="nk-block-head-content">
                                    <h6 class="nk-block-title">Ползователи</h6>
                                </div>
                            </div>
                            <div class="card card-preview">
                                <div class="card-inner">
                                    @include('layouts.message.success')
                                    @include('layouts.message.error')
                                    <a href="{{ route('admin_create_user') }}" style="float: right;" class="btn btn-white btn-sm btn-outline-primary"><em class="icon ni ni-plus"></em><span>Добавить</span></a>

                                    <table class="datatable-init nk-tb-list nk-tb-ulist" data-auto-responsive="false">
                                        <thead>
                                            <tr class="nk-tb-item nk-tb-head">
                                                <th class="nk-tb-col tb-col-mb"><span class="sub-text">ID</span></th>
                                                <th class="nk-tb-col tb-col-md"><span class="sub-text">Наисенование</span></th>
                                                <th class="nk-tb-col tb-col-md"><span class="sub-text">Дата</span></th>
                                                <th class="nk-tb-col nk-tb-col-tools text-right">
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($data as $item)                        
                                            <tr class="nk-tb-item">
                                                <td class="nk-tb-col tb-col-mb">
                                                    {{ $item->id }}
                                                </td>
                                                
                                                <td class="nk-tb-col tb-col-md">
                                                    <span>{{ $item->name }}</span>
                                                </td>

                                                <td class="nk-tb-col tb-col-md">
                                                    <span>{{ $item->updated_at }}</span>
                                                </td>
                                                <td class="nk-tb-col nk-tb-col-tools">
                                                    <ul class="nk-tb-actions gx-1">
                                                        <li class="nk-tb-action-hidden">
                                                            <a href="{{ route('admin_create_user', ['id' => $item->id]) }}" class="btn btn-trigger btn-icon" data-toggle="tooltip" data-placement="top" title="Изменить">
                                                                <em class="icon ni ni-edit"></em>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </td>
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
</div>
@endsection

