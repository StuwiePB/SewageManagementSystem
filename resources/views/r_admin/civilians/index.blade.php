@extends('r_admin.layout')

@section('title', 'Civilian users')

@section('content')
@if(session('success'))
    <div class="alert-success" style="margin-bottom: 1.25rem;">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert-danger" style="margin-bottom: 1.25rem;">{{ session('error') }}</div>
@endif
<div class="header">
    <div>
        <a href="{{ route('admin.dashboard') }}" class="back-link" style="margin-bottom: 0.75rem;"><i class="fas fa-arrow-left" style="margin-right: 0.35rem;"></i>Overview</a>
        <h1>Civilian users</h1>
        <p>Customer accounts registered in the system.</p>
    </div>
</div>

@php
    $hasFilters = ($search ?? '') !== '';
@endphp
<div class="card" style="margin-bottom: 1.5rem;">
    <form method="get" action="{{ route('admin.civilians.index') }}" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 1rem;">
        <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 240px;">
            <label for="civilian-search-q" class="form-label" style="margin-bottom: 0.35rem;">Search by name</label>
            <input type="search" id="civilian-search-q" name="q" value="{{ old('q', $search ?? '') }}" class="form-control" placeholder="Type a name..." maxlength="255" autocomplete="off">
        </div>
        <button type="submit" class="btn btn-primary" style="white-space: nowrap;"><i class="fas fa-filter" style="margin-right: 0.35rem;"></i>Apply</button>
        @if($hasFilters)
            <a href="{{ route('admin.civilians.index') }}" class="btn btn-secondary" style="white-space: nowrap;">Clear</a>
        @endif
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="section-head"><i class="fas fa-users" style="color: var(--accent-blue); margin-right: 0.5rem;"></i> Civilian accounts</h2>
        <span class="badge badge-status">{{ $civilianUsers->count() }}</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Staffname</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th style="width: 6rem;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($civilianUsers as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td class="cell-muted">{{ $user->staffname ?? '—' }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($user->is_active ?? true)
                                <span class="badge badge-status-teal">Active</span>
                            @else
                                <span class="badge" style="background: rgba(255, 91, 91, 0.2); color: var(--accent-red);">Deactivated</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.civilians.show', array_filter(['user' => $user, 'q' => request('q')])) }}" class="btn btn-secondary" style="padding: 0.35rem 0.65rem; font-size: 0.8125rem;">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="cell-muted">No civilian accounts found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
