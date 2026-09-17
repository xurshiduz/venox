@extends('layouts.backend')

@section('css')
<style>
    .user-actions-column {
        min-width: 220px;
        width: 220px;
    }

    .users-filter-card {
        margin-bottom: 18px;
        overflow: visible;
    }

    .users-filter-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .users-list-card {
        overflow: hidden;
    }

    .users-list-toolbar {
        min-height: 64px;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        border-bottom: 1px solid var(--ui-border);
        background: var(--ui-surface);
    }

    .users-list-toolbar .btn-group,
    .users-list-toolbar .btn {
        position: static;
        margin: 0;
    }

    .users-table {
        min-width: 1220px;
    }

    .users-role-column {
        min-width: 320px;
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
        .users-filter-actions,
        .users-list-toolbar {
            align-items: stretch;
            flex-direction: column;
        }

        .users-filter-actions .btn,
        .users-list-toolbar .btn,
        .users-list-toolbar .btn-group {
            width: 100%;
        }

        .users-list-toolbar .btn-group .btn {
            width: 50%;
        }

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

                        <form method="GET" action="{{ $archived ? route('users_noactive') : route('users_index') }}" class="card card-bordered users-filter-card">
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
                                    <div class="col-lg-3 col-md-4 users-filter-actions">
                                        <button type="submit" class="btn btn-warning flex-grow-1">{{ trans('backend.ui.search_action') }}</button>
                                        <a href="{{ $archived ? route('users_noactive') : route('users_index') }}" class="btn btn-light">{{ trans('backend.ui.clear') }}</a>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <div class="card users-list-card">
                            <div class="users-list-toolbar">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('users_index') }}" class="btn {{ !$archived ? 'btn-primary' : 'btn-outline-primary' }}">{{ trans('backend.ui.active_users') }}</a>
                                    <a href="{{ route('users_noactive') }}" class="btn {{ $archived ? 'btn-danger' : 'btn-outline-danger' }}">{{ trans('backend.ui.archived_users') }}</a>
                                </div>
                                @unless($archived)
                                    <a href="{{ route('user_form') }}" class="btn btn-primary">{{ trans('backend.ui.add') }}</a>
                                @endunless
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered users-table">
                                  <thead>
                                    <tr class="text-center">
                                      <th scope="col">Login</th>
                                      <th scope="col">{{ trans('backend.table.phone') }}</th>
                                      <th>{{ trans('backend.menu.dealers') }}</th>
                                      <th scope="col">{{ trans('backend.table.client_buy') }}</th>
                                      <th scope="col">{{ trans('backend.table.name') }}</th>
                                      <!--<th scope="col">QR Code</th>-->
                                      <th scope="col" class="users-role-column">{{ trans('backend.ui.role') }}</th>
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
                                      <td class="users-role-column">@foreach($item->uroles as $userRole) {{ optional($userRole->rolenameid)->name === 'sale' ? trans('backend.ui.agent') : optional($userRole->rolenameid)->name_full }}@if(!$loop->last), @endif @endforeach</td>
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
                                    @if($data->isEmpty())
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">{{ trans('backend.ui.no_users_found') }}</td>
                                    </tr>
                                    @endif
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
</div>
@endsection
