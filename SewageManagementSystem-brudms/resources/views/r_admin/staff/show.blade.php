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
<style>
    .alert-error-popup {
        position: fixed;
        top: 1.25rem;
        right: 1.25rem;
        z-index: 1200;
        max-width: min(28rem, calc(100vw - 2.5rem));
        background: rgba(255, 91, 91, 0.18);
        border: 1px solid rgba(255, 91, 91, 0.55);
        color: #ffc0c0;
        padding: 0.8rem 1rem;
        border-radius: 0.625rem;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.28);
        font-size: 0.9375rem;
        font-weight: 600;
    }
</style>
@if(session('success'))
    <div class="alert-success" style="margin-bottom: 1.25rem;">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert-error-popup" role="alert">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="alert-error-popup" role="alert">{{ $errors->first() }}</div>
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
            <p class="info-label">Staffname</p>
            <p class="info-value">{{ $staffUser->staffname ?? '—' }}</p>
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
            @if($canResetStaffPassword ?? false)
                <form method="post" action="{{ route('admin.staff.users.password.reset', $staffUser) }}" style="display: inline-flex; flex-wrap: wrap; gap: 0.5rem; align-items: center;">
                    @csrf
                    @if(request()->filled('q'))
                        <input type="hidden" name="q" value="{{ request('q') }}">
                    @endif
                    @if(request()->filled('role'))
                        <input type="hidden" name="role" value="{{ request('role') }}">
                    @endif
                    <input
                        type="password"
                        name="password"
                        placeholder="New password"
                        required
                        style="min-width: 10rem; padding: 0.45rem 0.6rem; border-radius: 0.5rem; border: 1px solid rgba(160, 174, 208, 0.35); background: rgba(17, 24, 39, 0.4); color: #fff;"
                    >
                    <input
                        type="password"
                        name="password_confirmation"
                        placeholder="Confirm password"
                        required
                        style="min-width: 10rem; padding: 0.45rem 0.6rem; border-radius: 0.5rem; border: 1px solid rgba(160, 174, 208, 0.35); background: rgba(17, 24, 39, 0.4); color: #fff;"
                    >
                    <button type="submit" class="btn btn-primary">Change password</button>
                </form>
            @endif
            @if($canDeleteStaffUser ?? false)
                <form method="post" action="{{ route('admin.staff.users.delete', $staffUser) }}" style="display: inline;" onsubmit="return confirm('Delete this account permanently? This action cannot be undone.');">
                    @csrf
                    @if(request()->filled('q'))
                        <input type="hidden" name="q" value="{{ request('q') }}">
                    @endif
                    @if(request()->filled('role'))
                        <input type="hidden" name="role" value="{{ request('role') }}">
                    @endif
                    <button type="submit" class="btn btn-danger" style="background: rgba(255, 91, 91, 0.15); border-color: rgba(255, 91, 91, 0.4); color: #ff8f8f;">Delete account</button>
                </form>
            @endif
        </div>
    </div>
@endif
@endsection
