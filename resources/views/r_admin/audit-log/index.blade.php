@extends('r_admin.layout')

@section('title', 'Audit Log')

@push('styles')
    @include('r_operators.partials.datetime-picker-assets')
    <style>
        .audit-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
        .audit-stat { background: var(--bg-secondary); border: 1px solid rgba(106, 150, 255, 0.12); border-radius: 12px; padding: 1rem 1.15rem; }
        .audit-stat p { margin: 0; font-size: 0.75rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.04em; }
        .audit-stat h3 { margin: 0.35rem 0 0; font-size: 1.5rem; font-weight: 800; }
        .audit-timeline { display: flex; flex-direction: column; gap: 0; }
        .audit-entry {
            display: grid;
            grid-template-columns: 120px 1fr;
            gap: 1.25rem;
            padding: 1.15rem 0;
            border-bottom: 1px solid rgba(106, 150, 255, 0.1);
        }
        .audit-entry:last-child { border-bottom: none; }
        .audit-time { font-size: 0.8125rem; color: var(--text-secondary); line-height: 1.5; }
        .audit-time strong { display: block; color: var(--text-primary); font-size: 0.875rem; font-weight: 600; }
        .audit-body { display: flex; flex-wrap: wrap; align-items: flex-start; gap: 0.65rem; }
        .audit-badge-area {
            display: inline-flex; align-items: center; gap: 0.35rem;
            padding: 0.2rem 0.55rem; border-radius: 6px; font-size: 0.7rem; font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.03em;
        }
        .audit-badge-action {
            padding: 0.2rem 0.55rem; border-radius: 6px; font-size: 0.75rem; font-weight: 500;
            background: rgba(106, 150, 255, 0.12); color: var(--accent-blue);
        }
        .audit-user {
            display: inline-flex; align-items: center; gap: 0.4rem;
            font-size: 0.8125rem; color: var(--text-secondary);
        }
        .audit-user-avatar {
            width: 26px; height: 26px; border-radius: 50%;
            background: rgba(106, 150, 255, 0.2); color: var(--accent-blue);
            display: flex; align-items: center; justify-content: center;
            font-size: 0.65rem; font-weight: 700;
        }
        .audit-desc { flex: 1 1 100%; margin: 0.15rem 0 0; font-size: 0.9375rem; color: var(--text-primary); line-height: 1.45; }
        .audit-meta { flex: 1 1 100%; font-size: 0.75rem; color: var(--text-secondary); margin-top: 0.25rem; }
        .audit-meta code {
            background: rgba(0,0,0,0.2); padding: 0.1rem 0.35rem; border-radius: 4px;
            font-size: 0.7rem; color: #c4d4ff;
        }
        @media (max-width: 640px) {
            .audit-entry { grid-template-columns: 1fr; gap: 0.5rem; }
        }
    </style>
@endpush

@section('content')
<div class="header">
    <div>
        <h1><i class="fas fa-clipboard-list" style="color: var(--accent-blue); margin-right: 0.5rem;"></i> Audit Log</h1>
        <p>Activity across Admin and Operations — who did what and when</p>
    </div>
</div>

<div class="audit-stats">
    <div class="audit-stat">
        <p>Today</p>
        <h3>{{ $stats['today'] }}</h3>
    </div>
    <div class="audit-stat">
        <p>Admin actions</p>
        <h3 style="color: #A86AFF;">{{ $stats['admin'] }}</h3>
    </div>
    <div class="audit-stat">
        <p>Operations actions</p>
        <h3 style="color: #4a90e2;">{{ $stats['operations'] }}</h3>
    </div>
    <div class="audit-stat">
        <p>Total recorded</p>
        <h3>{{ $stats['total'] }}</h3>
    </div>
</div>

<section class="card card-mb">
    <form method="GET" action="{{ route('admin.audit-log.index') }}" class="grid-filters">
        <div class="form-group flex-grow">
            <label class="form-label" for="search">Search</label>
            <input type="text" id="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Description, user, action...">
        </div>
        <div class="form-group">
            <label class="form-label" for="area">Area</label>
            <select id="area" name="area" class="form-control">
                <option value="">All areas</option>
                <option value="admin" {{ request('area') === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="operations" {{ request('area') === 'operations' ? 'selected' : '' }}>Operations</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label" for="action">Action type</label>
            <select id="action" name="action" class="form-control">
                <option value="">All actions</option>
                @foreach($actionOptions as $code => $label)
                    <option value="{{ $code }}" {{ request('action') === $code ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label" for="user_id">User</label>
            <select id="user_id" name="user_id" class="form-control">
                <option value="">All users</option>
                @foreach($staffUsers as $u)
                    <option value="{{ $u->id }}" {{ (string) request('user_id') === (string) $u->id ? 'selected' : '' }}>{{ $u->name ?? $u->staffname }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label" for="datetime_from">From</label>
            <input type="text" id="datetime_from" name="datetime_from" class="form-control dms-datetime-picker" value="{{ str_replace('T', ' ', request('datetime_from', '')) }}" autocomplete="off">
        </div>
        <div class="form-group">
            <label class="form-label" for="datetime_to">To</label>
            <input type="text" id="datetime_to" name="datetime_to" class="form-control dms-datetime-picker" value="{{ str_replace('T', ' ', request('datetime_to', '')) }}" autocomplete="off">
        </div>
        <div class="form-group">
            <button type="submit" class="btn-submit">Filter</button>
        </div>
    </form>
</section>

<section class="card">
    @if($logs->isEmpty())
        <p class="cell-muted" style="padding: 2rem 1rem;">No audit entries match your filters.</p>
    @else
        <div class="audit-timeline">
            @foreach($logs as $log)
                @php
                    $areaMeta = $log->area_meta;
                    $initials = collect(explode(' ', $log->user_name ?? '?'))->map(fn ($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('');
                @endphp
                <article class="audit-entry">
                    <div class="audit-time">
                        <strong>{{ $log->created_at->format('d M Y') }}</strong>
                        {{ $log->created_at->format('H:i') }}
                    </div>
                    <div class="audit-body">
                        <span class="audit-badge-area" style="background: {{ $areaMeta['color'] }}22; color: {{ $areaMeta['color'] }};">
                            <i class="fas {{ $areaMeta['icon'] ?? 'fa-circle' }}"></i>
                            {{ $areaMeta['label'] ?? ucfirst($log->area) }}
                        </span>
                        <span class="audit-badge-action">{{ $log->action_label }}</span>
                        <span class="audit-user">
                            <span class="audit-user-avatar">{{ $initials }}</span>
                            {{ $log->user_name ?? 'System' }}
                            @if($log->user_role)
                                <span style="opacity: 0.7;">· {{ str_replace('_', ' ', $log->user_role) }}</span>
                            @endif
                        </span>
                        <p class="audit-desc">{{ $log->description }}</p>
                        @if(!empty($log->properties))
                            <p class="audit-meta">
                                @foreach($log->properties as $key => $val)
                                    @if(is_scalar($val) && $val !== '')
                                        <code>{{ $key }}: {{ $val }}</code>
                                    @endif
                                @endforeach
                            </p>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @endif

    @if($logs->hasPages())
        <div class="pagination-bar">
            <span class="pagination-info">{{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }}</span>
            <div class="pagination-btns">
                @if($logs->onFirstPage())<span class="btn-page disabled">Previous</span>@else<a href="{{ $logs->previousPageUrl() }}" class="btn-page">Previous</a>@endif
                @if($logs->hasMorePages())<a href="{{ $logs->nextPageUrl() }}" class="btn-page">Next</a>@else<span class="btn-page disabled">Next</span>@endif
            </div>
        </div>
    @endif
</section>
@endsection

@push('scripts')
    @include('r_operators.partials.datetime-picker-init')
@endpush
