@extends('layouts.backend')

@section('css')
<style>
    .user-actions-column {
        min-width: 230px;
        width: 230px;
    }

    .user-action-buttons {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        flex-wrap: nowrap;
    }

    .user-action-buttons form {
        display: inline-flex;
        margin: 0;
    }

    .user-action-buttons .btn {
        min-width: 96px;
        white-space: nowrap;
    }

    @media (max-width: 767.98px) {
        .user-actions-column {
            min-width: 150px;
            width: 150px;
        }

        .user-action-buttons {
            flex-direction: column;
        }

        .user-action-buttons .btn,
        .user-action-buttons form {
            width: 100%;
        }
    }
</style>
@endsection

@section('content')
<div class="nk-content ">
    <div class="container-fluid">
        <div class="nk-content-inner">
            <div class="nk-content-body">
                <div class="components-preview mx-auto">
                    <div class="nk-block nk-block-lg">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <form method="GET" action="{{ $archived ? route('users_noactive') : route('users_index') }}" class="card card-bordered mb-3">
                            <div class="card-inner">
                                <div class="row g-2 align-items-end">
                                    <div class="col-lg-6 col-md-5">
                                        <label class="form-label">{{ trans('backend.ui.search') }}</label>
                                        <input type="search" class="form-control" name="search" value="{{ $keyword }}" placeholder="{{ trans('backend.ui.user_search_hint') }}">
                                    </div>
                                    <div class="col-lg-3 col-md-3">
                                        <label class="form-label">{{ trans('backend.ui.role') }}</label>
                                        <select class="form-select" name="role">
                                            <option value="">{{ trans('backend.ui.all_roles') }}</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->name }}" {{ $selectedRole === $role->name ? 'selected' : '' }}>{{ $role->name === 'sale' ? trans('backend.ui.agent') : $role->name_full }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-3 col-md-4 d-flex gap-2">
                                        <button type="submit" class="btn btn-warning flex-grow-1">{{ trans('backend.ui.search_action') }}</button>
                                        <a href="{{ $archived ? route('users_noactive') : route('users_index') }}" class="btn btn-light">{{ trans('backend.ui.clear') }}</a>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            <div class="btn-group">
                                <a href="{{ route('users_index') }}" class="btn {{ !$archived ? 'btn-primary' : 'btn-outline-primary' }}">{{ trans('backend.ui.active_users') }}</a>
                                <a href="{{ route('users_noactive') }}" class="btn {{ $archived ? 'btn-danger' : 'btn-outline-danger' }}">{{ trans('backend.ui.archived_users') }}</a>
                            </div>
                            @unless($archived)
                                <a href="{{ route('user_form') }}" class="btn btn-primary">{{ trans('backend.ui.add') }}</a>
                            @endunless
                        </div>

                        <div class="card">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                  <thead>
                                    <tr class="text-center">
                                      <th scope="col">Login</th>
                                      <th scope="col">{{ trans('backend.table.phone') }}</th>
                                      <th>{{ trans('backend.menu.dealers') }}</th>
                                      <th scope="col">{{ trans('backend.table.client_buy') }}</th>
                                      <th scope="col">{{ trans('backend.table.name') }}</th>
                                      <!--<th scope="col">QR Code</th>-->
                                      <th scope="col">{{ trans('backend.ui.role') }}</th>
                                      <th scope="col">{{ trans('backend.table.date') }}</th>
                                      <th class="user-actions-column">{{ trans('backend.ui.actions') }}</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @foreach($data as $item)
                                    <tr class="text-center">
                                      <td>{{ $item->username }}</td>
                                      <td>{{ $item->phone }}</td>
                                      <td>{{ $item->dealerid ? $item->dealerid->name : null}} </td>
                                      <td><a href="{{ route('user_checkouts', ['id' => $item->code]) }}">{{ $item->checkouts()->count() }} {{ trans('backend.table.qty_short_t') }}</a></td>
                                      <td>{{ $item->name }}</td>
                                      <!--<td>{{ $item->code }}</td>-->
                                      <td>@foreach($item->uroles as $userRole) {{ optional($userRole->rolenameid)->name === 'sale' ? trans('backend.ui.agent') : optional($userRole->rolenameid)->name_full }}@if(!$loop->last), @endif @endforeach</td>
                                      <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
                                      <td class="user-actions-column">
                                        <div class="user-action-buttons">
                                            @if(!$archived && $item->username != 'admin')
                                                <a href="{{ route('user_form', ['id' => $item->code])}}" class="btn btn-outline-primary btn-sm">{{ trans('backend.ui.edit') }}</a>
                                            @endif

                                            @if($archived)
                                                <form method="POST" action="{{ route('user_restore', ['id' => $item->code]) }}" onsubmit="return confirm(@json(trans('backend.ui.restore_confirm')))">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm">{{ trans('backend.ui.restore') }}</button>
                                                </form>
                                            @elseif((int) $item->id !== (int) auth()->id() && !$item->hasRole('admin'))
                                                <form method="POST" action="{{ route('user_archive', ['id' => $item->code]) }}" onsubmit="return confirm(@json(trans('backend.ui.archive_confirm')))">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">{{ trans('backend.ui.archive') }}</button>
                                                </form>
                                            @endif
                                        </div>
                                      </td>
                                    </tr>
                                    @endforeach
                                  </tbody>
                                </table>
                            </div>
                        </div>
                        @if($data->isEmpty())
                            <div class="alert alert-light text-center mt-3">{{ trans('backend.ui.no_users_found') }}</div>
                        @endif
                    </div>

                    @include('backend.nav')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
