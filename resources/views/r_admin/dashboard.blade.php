@extends('r_admin.layout')

@section('title', 'Overview')

@push('styles')
<style>
    .adm-hero-avatar { display: grid; place-items: center; height: 92px; width: 92px; border-radius: 22px; background: var(--adm-ink-fixed); font-family: var(--font-adm-display); font-size: 1.5rem; font-weight: 800; color: var(--adm-accent); }
    .adm-hero-online { position: absolute; bottom: -6px; right: -6px; border-radius: 999px; background: var(--adm-ok); padding: 0.25rem 0.5rem; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #fff; box-shadow: 0 0 0 4px var(--adm-surface); }
    .adm-hero-chip { border-radius: 999px; background: var(--adm-page); padding: 0.25rem 0.65rem; font-size: 11.5px; font-weight: 600; color: var(--adm-ink); }
    .adm-hero-stat-value { font-family: var(--font-adm-mono); font-variant-numeric: tabular-nums; font-size: 1.6rem; font-weight: 600; line-height: 1; color: var(--adm-ink); }
    .adm-hero-stat-value.alert { color: var(--adm-alert); }
    .adm-hero-stat-label { margin-top: 0.4rem; font-size: 0.75rem; color: var(--adm-muted); }

    .adm-avatar-ring { display: grid; place-items: center; height: 44px; width: 44px; border-radius: 999px; font-size: 12px; font-weight: 700; color: #fff; flex-shrink: 0; }
    .adm-crew-row { display: flex; justify-content: space-between; font-size: 12.5px; margin-bottom: 0.4rem; }
    .adm-crew-track { height: 8px; border-radius: 999px; background: var(--adm-page); overflow: hidden; }
    .adm-crew-fill { height: 100%; border-radius: 999px; background: var(--adm-flow); }

    .adm-queue-item { display: flex; align-items: center; gap: 0.9rem; padding: 0.75rem 0; border-bottom: 1px solid var(--adm-line); text-decoration: none; color: inherit; }
    .adm-queue-item:last-child { border-bottom: none; }
    .adm-queue-icon { display: grid; place-items: center; height: 44px; width: 44px; border-radius: 16px; flex-shrink: 0; }
    .adm-queue-title { font-size: 13.5px; font-weight: 600; color: var(--adm-ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .adm-queue-desc { font-size: 12px; color: var(--adm-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .adm-queue-time { font-size: 11.5px; color: var(--adm-muted); flex-shrink: 0; }

    .adm-sentinel { position: relative; overflow: hidden; border-radius: var(--radius-adm-card); background: linear-gradient(135deg, var(--adm-accent2), var(--adm-accent) 60%, #D98A00); color: var(--adm-ink-fixed); padding: 1.5rem; box-shadow: var(--shadow-adm-hero); }
    .adm-sentinel-glow { position: absolute; top: -4rem; right: -4rem; height: 13rem; width: 13rem; border-radius: 999px; background: rgba(255,255,255,.25); filter: blur(30px); pointer-events: none; }
    .adm-sentinel-ring-value { font-family: var(--font-adm-mono); font-size: 2.6rem; font-weight: 600; line-height: 1; }
    .adm-sentinel-mini { font-family: var(--font-adm-mono); font-size: 1.15rem; font-weight: 600; line-height: 1; }
    .adm-sentinel-mini-label { margin-top: 0.25rem; font-size: 11px; font-weight: 600; color: rgba(16,38,46,.7); }
    .adm-sentinel-btn { display: block; width: 100%; border-radius: 999px; background: var(--adm-ink-fixed); padding: 0.7rem; text-align: center; font-size: 13px; font-weight: 700; color: var(--adm-accent); text-decoration: none; margin-top: 1.1rem; }
    .adm-sentinel-btn:hover { background: var(--adm-ink2-fixed); }

    .charts-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
    .chart-card { border-radius: var(--radius-adm-card); padding: 1.5rem; }
    .chart-card canvas { max-height: 280px; }
    @media (max-width: 1024px) { .charts-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
@php
    $adminInitials = \Illuminate\Support\Str::of(auth()->user()->name ?? 'Admin')->explode(' ')->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('');
    $roleLabel = match (auth()->user()->role ?? '') {
        'super_admin' => 'Super Administrator',
        'admin' => 'Administrator',
        default => ucfirst(auth()->user()->role ?? 'Admin'),
    };
    $crewPalette = ['var(--adm-flow)', 'var(--adm-ink-fixed)', 'var(--adm-flow2)', 'var(--adm-ink2-fixed)', 'var(--adm-accent)'];
@endphp

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

<div class="header" style="margin-bottom:1.25rem;">
    <div>
        <h1>System Overview</h1>
        <p>AI incidents and system operations</p>
    </div>
</div>

{{-- Row 1: profile hero + crews --}}
<div class="grid gap-5 lg:grid-cols-[1.15fr_1fr]" style="margin-bottom:1.25rem;">

    <section class="card" style="margin-bottom:0;">
        <div class="flex items-start gap-4">
            <div class="relative shrink-0">
                <div class="adm-hero-avatar">{{ $adminInitials }}</div>
                <span class="adm-hero-online">Online</span>
            </div>
            <div class="min-w-0 pt-1">
                <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--adm-muted);">{{ $roleLabel }}</p>
                <h2 style="font-family:var(--font-adm-display);font-size:1.3rem;font-weight:800;margin-top:2px;color:var(--adm-ink);">{{ auth()->user()->name }}</h2>
                <p style="font-size:13px;color:var(--adm-muted);">{{ auth()->user()->email }}</p>
                <div class="mt-3 flex flex-wrap gap-1.5">
                    <span class="adm-hero-chip">{{ $crews->count() }} crew{{ $crews->count() === 1 ? '' : 's' }} tracked</span>
                    <span class="adm-hero-chip">{{ $totalReports }} total reports</span>
                </div>
            </div>
        </div>

        <div class="mt-5 grid grid-cols-3 gap-3" style="border-top:1px solid var(--adm-line);padding-top:1rem;">
            <a href="{{ route('admin.work-orders.index') }}" style="text-decoration:none;">
                <p class="adm-hero-stat-value">{{ $workInProgress }}</p>
                <p class="adm-hero-stat-label">Open work orders</p>
            </a>
            <div>
                <p class="adm-hero-stat-value">{{ $resolvedReports }}</p>
                <p class="adm-hero-stat-label">Resolved</p>
            </div>
            <div>
                <p class="adm-hero-stat-value">{{ $medianCloseHours !== null ? $medianCloseHours.'h' : '—' }}</p>
                <p class="adm-hero-stat-label">Median close time</p>
            </div>
        </div>
    </section>

    <section class="card" style="margin-bottom:0;">
        <div class="flex items-center justify-between">
            <h2 class="section-head">Crews</h2>
            <a href="{{ route('admin.work-orders.index') }}" class="link-primary" style="font-size:12.5px;">Assign work</a>
        </div>

        <div class="mt-4 flex items-center gap-2.5" style="flex-wrap:wrap;">
            @forelse($crews as $i => $crew)
                <div class="adm-avatar-ring" style="background:{{ $crewPalette[$i % count($crewPalette)] }};" title="{{ $crew->name }}">
                    {{ \Illuminate\Support\Str::of($crew->name)->explode(' ')->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('') }}
                </div>
            @empty
                <p class="info-value-muted" style="font-size:13px;">No crews on file yet.</p>
            @endforelse
        </div>

        <div class="mt-5" style="display:flex;flex-direction:column;gap:0.9rem;">
            @forelse($crews as $crew)
                @php
                    $total = max($crew->work_orders_count, 1);
                    $pct = min(100, round($crew->active_work_orders_count / $total * 100));
                @endphp
                <div>
                    <div class="adm-crew-row"><span style="font-weight:600;color:var(--adm-ink);">{{ $crew->name }}</span><span class="tnum" style="color:var(--adm-muted);">{{ $crew->active_work_orders_count }} / {{ $crew->work_orders_count }} active</span></div>
                    <div class="adm-crew-track"><div class="adm-crew-fill" style="width:{{ $pct }}%"></div></div>
                </div>
            @empty
            @endforelse
        </div>
    </section>
</div>

{{-- Row 1.5: original KPI tiles, kept intact (mockup has no slot for these) --}}
<div class="stats-grid-4">
    <a href="{{ route('admin.customer-reports.index') }}" class="card" style="text-decoration:none;">
        <p>Total customer reports</p>
        <h3>{{ $totalReports }}</h3>
    </a>
    <div class="card">
        <p>Reports today</p>
        <h3>{{ $reportsToday }}</h3>
    </div>
    <div class="card">
        <p>Cancelled work orders</p>
        <h3>{{ $cancelledWorkOrders }}</h3>
    </div>
    <a href="{{ route('admin.civilians.index') }}" class="card" style="text-decoration:none;">
        <p>Civilian users</p>
        <h3>{{ $civilianUsers }}</h3>
    </a>
    <div class="card">
        <p>Admins</p>
        <h3>{{ $adminUsers }}</h3>
    </div>
    <div class="card">
        <p>Operations</p>
        <h3>{{ $operationsUsers }}</h3>
    </div>
    <div class="card">
        <p>Crew leaders</p>
        <h3>{{ $crewLeaders }}</h3>
    </div>
</div>

{{-- Row 2: charts --}}
<div class="charts-grid">
    <div class="chart-card">
        <h3 class="chart-card-title">Activity Trend</h3>
        <canvas id="activityChart"></canvas>
    </div>
    <div class="chart-card">
        <h3 class="chart-card-title">Status Distribution</h3>
        <canvas id="statusChart"></canvas>
    </div>
</div>

{{-- Row 3: recent activity + AI sentinel --}}
<div class="grid gap-5 lg:grid-cols-[1.35fr_1fr]">

    <section class="card" style="margin-bottom:0;">
        <div class="flex items-center justify-between">
            <h2 class="section-head">Recent activity</h2>
            <span class="info-value-muted" style="font-size:12px;">Last 7 days</span>
        </div>

        <div class="mt-3">
            @forelse(($recentActivities ?? []) as $activity)
                @php
                    $iconBg = match ($activity['type'] ?? '') {
                        'civilian_report' => 'rgba(46, 125, 143, 0.12); color: var(--adm-flow)',
                        'ai_detection' => 'rgba(194, 69, 61, 0.12); color: var(--adm-alert)',
                        'crew_dispatched' => 'rgba(62, 142, 110, 0.12); color: var(--adm-ok)',
                        default => 'rgba(240, 162, 2, 0.12); color: #9A6A00',
                    };
                @endphp
                <a href="{{ $activity['link'] ?? '#' }}" class="adm-queue-item">
                    <span class="adm-queue-icon" style="background: {{ $iconBg }};">
                        @if(($activity['type'] ?? '') === 'civilian_report')
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"><path d="M12 3 3 19h18L12 3Z"/><path d="M12 10v4M12 17h.01"/></svg>
                        @elseif(($activity['type'] ?? '') === 'ai_detection')
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"><rect x="4" y="7" width="16" height="12" rx="3"/><path d="M9 3v4M15 3v4M8 13h.01M16 13h.01M9 17h6"/></svg>
                        @elseif(($activity['type'] ?? '') === 'work_order_created')
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"><rect x="4" y="3" width="16" height="18" rx="3"/><path d="M9 8h6M9 12h6M9 16h3"/></svg>
                        @else
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"><path d="M3 13h2l2-5h10l2 5h2M5 13v5h14v-5M8 18v2M16 18v2"/></svg>
                        @endif
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="adm-queue-title">{{ $activity['title'] }}</p>
                        <p class="adm-queue-desc">{{ $activity['description'] }}</p>
                    </div>
                    <span class="adm-queue-time">{{ \Carbon\Carbon::parse($activity['time'])->diffForHumans() }}</span>
                </a>
            @empty
                <p class="cell-muted">No recent activities</p>
            @endforelse
        </div>
    </section>

    <section class="adm-sentinel">
        <div class="adm-sentinel-glow"></div>
        <div class="relative flex items-center gap-2">
            <span style="display:inline-block;height:8px;width:8px;border-radius:999px;background:var(--adm-ink-fixed);"></span>
            <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;">AI triage &middot; incidents</p>
        </div>

        <div class="relative mt-5 flex items-center gap-6">
            @php
                $ringPct = $incidentsAutoTriagePct ?? 0;
                $circumference = 314;
                $offset = round($circumference * (1 - $ringPct / 100));
            @endphp
            <svg viewBox="0 0 120 120" style="height:124px;width:124px;transform:rotate(-90deg);flex-shrink:0;">
                <circle cx="60" cy="60" r="50" fill="none" stroke="rgba(16,38,46,.16)" stroke-width="13"/>
                <circle cx="60" cy="60" r="50" fill="none" stroke="var(--adm-ink-fixed)" stroke-width="13" stroke-linecap="round" stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $offset }}"/>
            </svg>
            <div>
                <p class="adm-sentinel-ring-value">{{ $incidentsAutoTriagePct ?? '—' }}<span style="font-size:1.3rem;">%</span></p>
                <p style="margin-top:0.5rem;max-width:150px;font-size:13px;font-weight:500;line-height:1.3;">of incidents already AI-analyzed</p>
            </div>
        </div>

        <div class="relative mt-6 grid grid-cols-3 gap-3" style="border-top:1px solid rgba(16,38,46,.15);padding-top:1rem;">
            <div><p class="adm-sentinel-mini">{{ $incidentsTotal }}</p><p class="adm-sentinel-mini-label">Total</p></div>
            <div><p class="adm-sentinel-mini">{{ $incidentsAnalyzed }}</p><p class="adm-sentinel-mini-label">Analyzed</p></div>
            <div><p class="adm-sentinel-mini">{{ $incidentsAiGenerated }}</p><p class="adm-sentinel-mini-label">AI-flagged</p></div>
        </div>

        <a href="{{ route('admin.incidents.review') }}" class="adm-sentinel-btn">Review held reports</a>
    </section>
</div>
@endsection

@push('scripts')
<script>
    window.__ADMIN_CHART_DATA = {
        months: @json($months),
        reportsData: @json($reportsData),
        maintenanceData: @json($maintenanceData),
        resolvedData: @json($resolvedData),
        statusPieCounts: @json($statusPieCounts),
    };
</script>
@vite(['resources/js/admin-dashboard.js'])
@endpush
