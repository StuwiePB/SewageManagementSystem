@extends('r_admin.layout')

@section('title', 'Civilian: '.$civilianUser->name)

@php
    $backParams = [];
    if (request()->filled('q')) {
        $backParams['q'] = request('q');
    }
@endphp

@section('content')
@if(session('success'))
    <div class="alert-success" style="margin-bottom: 1.25rem;">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert-danger" style="margin-bottom: 1.25rem;">{{ session('error') }}</div>
@endif
<div class="header">
    <div>
        <a href="{{ route('admin.civilians.index', $backParams) }}" class="back-link" style="margin-bottom: 0.75rem;"><i class="fas fa-arrow-left" style="margin-right: 0.35rem;"></i>Civilian users</a>
        <h1>{{ $civilianUser->name }}</h1>
        <p>Civilian account</p>
    </div>
</div>

<div class="grid-2col" style="align-items: start;">
    <section class="card">
        <h3 class="section-head" style="margin-bottom: 1rem;">Account</h3>
        <div class="detail-row">
            <p class="info-label">Name</p>
            <p class="info-value">{{ $civilianUser->name }}</p>
        </div>
        <div class="detail-row">
            <p class="info-label">Email</p>
            <p class="info-value">{{ $civilianUser->email }}</p>
        </div>
        <div class="detail-row">
            <p class="info-label">Phone</p>
            <p class="info-value">{{ $civilianUser->phone ?? '—' }}</p>
        </div>
        <div class="detail-row">
            <p class="info-label">Role</p>
            <p class="info-value"><span class="badge badge-status">Civilian</span></p>
        </div>
        <div class="detail-row">
            <p class="info-label">Sign-in</p>
            <p class="info-value">
                @if($civilianUser->is_active ?? true)
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
            <p class="info-value">{{ $civilianUser->email_verified_at ? $civilianUser->email_verified_at->format('M j, Y g:i A') : '—' }}</p>
        </div>
        <div class="detail-row">
            <p class="info-label">Member since</p>
            <p class="info-value">{{ $civilianUser->created_at?->format('M j, Y g:i A') ?? '—' }}</p>
        </div>
    </section>

</div>

@if($canManageCivilianAccount ?? false)
    <div class="card" style="margin-top: 1.5rem;">
        <h3 class="section-head" style="margin-bottom: 0.75rem;">Account access</h3>
        <p class="section-desc" style="margin-bottom: 1rem;">Use these actions to manage civilian account access.</p>
        <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center;">
            @if($civilianUser->is_active ?? true)
                <form method="post" action="{{ route('admin.civilians.deactivate', $civilianUser) }}" style="display: inline;" onsubmit="return confirm('Deactivate this account? They will be signed out and cannot log in until reactivated.');">
                    @csrf
                    <input type="hidden" name="return_to" value="show">
                    @if(request()->filled('q'))
                        <input type="hidden" name="q" value="{{ request('q') }}">
                    @endif
                    <button type="submit" class="btn btn-danger">Deactivate account</button>
                </form>
            @else
                <form method="post" action="{{ route('admin.civilians.activate', $civilianUser) }}" style="display: inline;">
                    @csrf
                    <input type="hidden" name="return_to" value="show">
                    @if(request()->filled('q'))
                        <input type="hidden" name="q" value="{{ request('q') }}">
                    @endif
                    <button type="submit" class="btn btn-primary">Activate account</button>
                </form>
            @endif

            <form method="post" action="{{ route('admin.civilians.delete', $civilianUser) }}" style="display: inline;" onsubmit="return confirm('Delete this account permanently? This action cannot be undone.');">
                @csrf
                @if(request()->filled('q'))
                    <input type="hidden" name="q" value="{{ request('q') }}">
                @endif
                <button type="submit" class="btn btn-danger" style="background: rgba(255, 91, 91, 0.15); border-color: rgba(255, 91, 91, 0.4); color: #ff8f8f;">Delete account</button>
            </form>
        </div>
    </div>
@endif
@endsection
