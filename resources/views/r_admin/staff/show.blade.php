@extends('r_admin.layout')

@section('title', 'Staff: '.$staffUser->name)

@php
    $backParams = [];
    if (request()->filled('q')) {
        $backParams['q'] = request('q');
    }
    if (request()->filled('role') && request('role') !== 'all') {
        $backParams['role'] = request('role');
    }
@endphp

@section('content')
@if(session('success'))
    <div class="alert-success" style="margin-bottom: 1.25rem;">{{ session('success') }}</div>
@endif
<div class="header">
    <div>
        <a href="{{ route('admin.staff.index', $backParams) }}" class="back-link" style="margin-bottom: 0.75rem;"><i class="fas fa-arrow-left" style="margin-right: 0.35rem;"></i>Staff directory</a>
        <h1>{{ $staffUser->name }}</h1>
        <p>{{ $staffRoleLabel }} account</p>
    </div>
</div>

<div class="grid-2col" style="align-items: start;">
    <section class="card">
        <h3 class="section-head" style="margin-bottom: 1rem;">Account</h3>
        <div class="detail-row">
            <p class="info-label">Name</p>
            <p class="info-value">{{ $staffUser->name }}</p>
        </div>
        <div class="detail-row">
            <p class="info-label">Username</p>
            <p class="info-value">{{ $staffUser->username ?? '—' }}</p>
        </div>
        <div class="detail-row">
            <p class="info-label">Email</p>
            <p class="info-value">{{ $staffUser->email }}</p>
        </div>
        <div class="detail-row">
            <p class="info-label">Phone</p>
            <p class="info-value">{{ $staffUser->phone ?? '—' }}</p>
        </div>
        <div class="detail-row">
            <p class="info-label">Role</p>
            <p class="info-value">
                @if($staffUser->hasRole(\App\Models\User::ROLE_SUPER_ADMIN))
                    <span class="badge badge-admin">Super admin</span>
                @elseif($staffUser->hasRole(\App\Models\User::ROLE_ADMIN))
                    <span class="badge badge-admin">Admin</span>
                @else
                    <span class="badge badge-status-teal">Operator</span>
                @endif
            </p>
        </div>
        <div class="detail-row">
            <p class="info-label">Sign-in</p>
            <p class="info-value">
                @if($staffUser->is_active ?? true)
                    <span class="badge badge-status-teal">Active</span>
                    <span class="cell-muted" style="font-size: 0.8125rem; margin-left: 0.35rem;">Can log in</span>
                @else
                    <span class="badge" style="background: rgba(255, 91, 91, 0.2); color: var(--accent-red);">Deactivated</span>
                    <span class="cell-muted" style="font-size: 0.8125rem; margin-left: 0.35rem;">Cannot log in</span>
                @endif
            </p>
        </div>
        <div class="detail-row">
            <p class="info-label">Email verified</p>
            <p class="info-value">{{ $staffUser->email_verified_at ? $staffUser->email_verified_at->format('M j, Y g:i A') : '—' }}</p>
        </div>
        <div class="detail-row">
            <p class="info-label">Member since</p>
            <p class="info-value">{{ $staffUser->created_at?->format('M j, Y g:i A') ?? '—' }}</p>
        </div>
    </section>

    @if($staffUser->hasRole(\App\Models\User::ROLE_OPERATOR))
        <section class="card">
            <h3 class="section-head" style="margin-bottom: 1rem;">Operations</h3>
            <div class="detail-row">
                <p class="info-label">Assigned crew</p>
                <p class="info-value">{{ $staffUser->crew?->name ?? '—' }}</p>
            </div>
            @if($staffUser->crew)
                <div class="detail-row">
                    <p class="info-label">Crew phone</p>
                    <p class="info-value">{{ $staffUser->crew->contact_phone ?? '—' }}</p>
                </div>
                <div class="detail-row">
                    <p class="info-label">Crew email</p>
                    <p class="info-value">{{ $staffUser->crew->contact_email ?? '—' }}</p>
                </div>
                <div class="detail-row">
                    <p class="info-label">Crew status</p>
                    <p class="info-value">{{ $staffUser->crew->status ? ucfirst(str_replace('_', ' ', $staffUser->crew->status)) : '—' }}</p>
                </div>
            @endif
        </section>
    @else
        <section class="card">
            <h3 class="section-head" style="margin-bottom: 1rem;">Console access</h3>
            <p class="section-desc" style="margin: 0;">This user signs in to the admin console. Operator-only fields (crew assignment) do not apply.</p>
        </section>
    @endif
</div>

@if($canManageStaffActivation ?? false)
    <div class="card" style="margin-top: 1.5rem;">
        <h3 class="section-head" style="margin-bottom: 0.75rem;">Account access</h3>
        <p class="section-desc" style="margin-bottom: 1rem;">Deactivated users are signed out immediately and cannot authenticate until reactivated.</p>
        <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center;">
            @if($staffUser->is_active ?? true)
                <form method="post" action="{{ route('admin.staff.users.deactivate', $staffUser) }}" style="display: inline;" onsubmit="return confirm('Deactivate this account? They will be signed out and cannot log in until reactivated.');">
                    @csrf
                    @if(request()->filled('q'))
                        <input type="hidden" name="q" value="{{ request('q') }}">
                    @endif
                    @if(request()->filled('role'))
                        <input type="hidden" name="role" value="{{ request('role') }}">
                    @endif
                    <button type="submit" class="btn btn-danger">Deactivate account</button>
                </form>
            @else
                <form method="post" action="{{ route('admin.staff.users.activate', $staffUser) }}" style="display: inline;">
                    @csrf
                    @if(request()->filled('q'))
                        <input type="hidden" name="q" value="{{ request('q') }}">
                    @endif
                    @if(request()->filled('role'))
                        <input type="hidden" name="role" value="{{ request('role') }}">
                    @endif
                    <button type="submit" class="btn btn-primary">Activate account</button>
                </form>
            @endif
        </div>
    </div>
@endif
@endsection
