@extends('r_admin.layout')

@section('title', 'Staff directory')

@section('content')
<div class="header">
    <div>
        <h1>Staff directory</h1>
        <p>Admin and operation accounts that can sign in to console or operations.</p>
    </div>
    @if(auth()->user()?->isSuperAdmin())
        <a href="{{ route('admin.staff.users.create') }}" class="btn btn-primary"><i class="fas fa-user-plus" style="margin-right:0.35rem;"></i>Add user</a>
    @endif
</div>
@if(session('success'))
    <div class="alert-success" style="margin-bottom: 1.5rem;">{{ session('success') }}</div>
@endif

@php
    $hasFilters = ($search !== '' || ($roleFilter ?? 'all') !== 'all');
    $staffListQuery = array_filter([
        'q' => $search !== '' ? $search : null,
        'role' => (($roleFilter ?? 'all') !== 'all') ? $roleFilter : null,
    ], fn ($v) => $v !== null && $v !== '');
@endphp
<div class="card" style="margin-bottom: 1.5rem;">
    <form method="get" action="{{ route('admin.staff.index') }}" class="staff-directory-filters" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 1rem;">
        <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 200px;">
            <label for="staff-search-q" class="form-label" style="margin-bottom: 0.35rem;">Search by name</label>
            <input type="search" id="staff-search-q" name="q" value="{{ old('q', $search ?? '') }}" class="form-control" placeholder="Type a name…" maxlength="255" autocomplete="off">
        </div>
        <div class="form-group" style="margin-bottom: 0; min-width: 180px;">
            <label for="staff-filter-role" class="form-label" style="margin-bottom: 0.35rem;">Show</label>
            <select id="staff-filter-role" name="role" class="form-control">
                <option value="all" @selected(($roleFilter ?? 'all') === 'all')>All staff</option>
                <option value="super_admin" @selected(($roleFilter ?? '') === 'super_admin')>Super Admin</option>
                <option value="admin" @selected(($roleFilter ?? '') === 'admin')>Admin</option>
                <option value="operation" @selected(in_array(($roleFilter ?? ''), ['operation', 'operator'], true))>Operation</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary" style="white-space: nowrap;"><i class="fas fa-filter" style="margin-right: 0.35rem;"></i>Apply</button>
        @if($hasFilters)
            <a href="{{ route('admin.staff.index') }}" class="btn btn-secondary" style="white-space: nowrap;">Clear</a>
        @endif
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="section-head"><i class="fas fa-users" style="color: var(--accent-purple); margin-right: 0.5rem;"></i> All staff</h2>
        <span class="badge badge-admin">{{ $staffUsers->count() }}</span>
    </div>
    <p class="section-desc">Admin and operation accounts in one unified list.</p>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Staffname</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th style="width: 5rem;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($staffUsers as $user)
                    <tr>
                        <td>
                            {{ $user->name }}
                            @if(!($user->is_active ?? true))
                                <span class="badge" style="margin-left: 0.35rem; background: rgba(255, 91, 91, 0.2); color: var(--accent-red); font-size: 0.65rem;">Inactive</span>
                            @endif
                        </td>
                        <td class="cell-muted">{{ $user->staffname ?? '—' }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if(($user->staff_role_label ?? '') === 'Operation')
                                <span class="badge badge-status-teal">{{ $user->staff_role_label }}</span>
                            @else
                                <span class="badge badge-admin">{{ $user->staff_role_label ?? 'Staff' }}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.staff.users.show', array_merge(['user' => $user], $staffListQuery)) }}" class="btn btn-secondary" style="padding: 0.35rem 0.65rem; font-size: 0.8125rem;">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="cell-muted">No staff accounts found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
