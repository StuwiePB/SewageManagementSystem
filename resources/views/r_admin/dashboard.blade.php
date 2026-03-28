@extends('r_admin.layout')

@section('title', 'Overview')

@push('styles')
<style>
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
    .stat-card { background: var(--bg-secondary); border: 1px solid rgba(106, 150, 255, 0.15); border-radius: 12px; padding: 1.5rem; display: flex; align-items: center; justify-content: space-between; transition: transform 0.2s; }
    .stat-card:hover { transform: translateY(-2px); }
    a.stat-card { text-decoration: none; color: inherit; cursor: pointer; }
    a.stat-card:focus-visible { outline: 2px solid var(--accent-blue); outline-offset: 3px; }
    .stat-content h3 { font-size: 0.875rem; color: var(--text-secondary); font-weight: 500; margin-bottom: 0.5rem; }
    .stat-content .value { font-size: 2rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.25rem; }
    .stat-content .change { font-size: 0.8125rem; font-weight: 500; }
    .change.positive { color: var(--accent-green); }
    .change.negative { color: var(--accent-red); }
    .stat-icon { width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; }
    .stat-icon.blue { background: rgba(106, 150, 255, 0.2); color: var(--accent-blue); }
    .stat-icon.green { background: rgba(86, 255, 139, 0.2); color: var(--accent-green); }
    .stat-icon.purple { background: rgba(168, 106, 255, 0.2); color: var(--accent-purple); }
    .stat-icon.red { background: rgba(255, 91, 91, 0.2); color: var(--accent-red); }
    .charts-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 2rem; }
    .chart-card { background: var(--bg-secondary); border: 1px solid rgba(106, 150, 255, 0.15); border-radius: 12px; padding: 1.5rem; }
    .chart-card h3 { font-size: 1.125rem; font-weight: 700; color: var(--text-primary); margin-bottom: 1.5rem; }
    .chart-card canvas { max-height: 300px; }
    .recent-activities-card { background: var(--bg-secondary); border: 1px solid rgba(106, 150, 255, 0.15); border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; }
    .recent-activities-card h3 { font-size: 1.125rem; font-weight: 700; color: var(--text-primary); margin-bottom: 1rem; }
    .activity-list { list-style: none; padding: 0; margin: 0; }
    .activity-item { display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.75rem 0; border-bottom: 1px solid rgba(106, 150, 255, 0.1); transition: background 0.2s; }
    .activity-item:last-child { border-bottom: none; }
    .activity-item:hover { background: rgba(106, 150, 255, 0.05); }
    .activity-icon { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.875rem; }
    .activity-icon.report { background: rgba(106, 150, 255, 0.2); color: var(--accent-blue); }
    .activity-icon.ai { background: rgba(255, 91, 91, 0.2); color: var(--accent-red); }
    .activity-icon.crew { background: rgba(86, 255, 139, 0.2); color: var(--accent-green); }
    .activity-content { flex: 1; min-width: 0; }
    .activity-content strong { display: block; color: var(--text-primary); font-size: 0.9375rem; margin-bottom: 0.25rem; }
    .activity-content span { color: var(--text-secondary); font-size: 0.8125rem; }
    .activity-time { color: var(--text-secondary); font-size: 0.75rem; flex-shrink: 0; }
    .activity-item a { text-decoration: none; color: inherit; display: flex; align-items: flex-start; gap: 0.75rem; width: 100%; }
    @media (max-width: 1024px) { .charts-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div class="header">
            <h1>System Overview</h1>
            <p>AI incidents and system operations</p>
        </div>
        @if(session('success'))
            <div style="background: rgba(86, 255, 139, 0.15); color: #56FF8B; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.5rem;">{{ session('success') }}</div>
        @endif
        <div class="stats-grid">
            <a href="{{ route('admin.customer-reports.index') }}" class="stat-card" title="View all customer reports">
                <div class="stat-content">
                    <h3>Total Customer Reports</h3>
                    <div class="value">{{ $totalReports }}</div>
                    <div class="change positive">Complaints filed by customers</div>
                </div>
                <div class="stat-icon blue"><i class="fas fa-file-alt"></i></div>
            </a>
            <div class="stat-card">
                <div class="stat-content">
                    <h3>Reports Today</h3>
                    <div class="value">{{ $reportsToday }}</div>
                    <div class="change {{ $reportsToday > 0 ? 'positive' : '' }}">Work orders created today</div>
                </div>
                <div class="stat-icon green"><i class="fas fa-calendar-day"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-content">
                    <h3>Resolved</h3>
                    <div class="value">{{ $resolvedReports }}</div>
                    <div class="change positive">Completed work orders</div>
                </div>
                <div class="stat-icon purple"><i class="fas fa-check-circle"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-content">
                    <h3>Work in Progress</h3>
                    <div class="value">{{ $workInProgress }}</div>
                    <div class="change {{ $workInProgress > 0 ? 'negative' : 'positive' }}">Active work orders</div>
                </div>
                <div class="stat-icon red"><i class="fas fa-spinner"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-content">
                    <h3>Cancelled Work Order</h3>
                    <div class="value">{{ $cancelledWorkOrders ?? 0 }}</div>
                    <div class="change {{ ($cancelledWorkOrders ?? 0) > 0 ? 'negative' : 'positive' }}">Cancelled work orders</div>
                </div>
                <div class="stat-icon red"><i class="fas fa-ban"></i></div>
            </div>
        </div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-content">
                    <h3>Civilian Users</h3>
                    <div class="value">{{ $civilianUsers ?? 0 }}</div>
                    <div class="change positive">Customer accounts</div>
                </div>
                <div class="stat-icon blue"><i class="fas fa-users"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-content">
                    <h3>Admin</h3>
                    <div class="value">{{ $adminUsers ?? 0 }}</div>
                    <div class="change positive">Administrators</div>
                </div>
                <div class="stat-icon purple"><i class="fas fa-user-shield"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-content">
                    <h3>Operations</h3>
                    <div class="value">{{ $operationsUsers ?? 0 }}</div>
                    <div class="change positive">Operators</div>
                </div>
                <div class="stat-icon green"><i class="fas fa-user-cog"></i></div>
            </div>
            <div class="stat-card">
                <div class="stat-content">
                    <h3>Crew Leaders</h3>
                    <div class="value">{{ $crewLeaders ?? 0 }}</div>
                    <div class="change positive">Assigned to crews</div>
                </div>
                <div class="stat-icon red"><i class="fas fa-user-tie"></i></div>
            </div>
        </div>
        <div class="charts-grid">
            <div class="chart-card">
                <h3>Activity Trend</h3>
                <canvas id="activityChart"></canvas>
            </div>
            <div class="chart-card">
                <h3>Status Distribution</h3>
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <div class="recent-activities-card">
            <h3>Recent Activities</h3>
            <ul class="activity-list">
                @forelse(($recentActivities ?? []) as $activity)
                    <li class="activity-item">
                        <a href="{{ $activity['link'] ?? '#' }}">
                            @php
                                $iconClass = match($activity['type'] ?? '') {
                                    'civilian_report' => 'report',
                                    'ai_detection' => 'ai',
                                    'crew_dispatched' => 'crew',
                                    'work_order_created' => 'report',
                                    default => 'crew',
                                };
                            @endphp
                            <span class="activity-icon {{ $iconClass }}">
                                @if(($activity['type'] ?? '') === 'civilian_report')
                                    <i class="fas fa-image"></i>
                                @elseif(($activity['type'] ?? '') === 'ai_detection')
                                    <i class="fas fa-robot"></i>
                                @elseif(($activity['type'] ?? '') === 'work_order_created')
                                    <i class="fas fa-clipboard-list"></i>
                                @else
                                    <i class="fas fa-truck"></i>
                                @endif
                            </span>
                            <div class="activity-content">
                                <strong>{{ $activity['title'] }}</strong>
                                <span>{{ $activity['description'] }}</span>
                            </div>
                            <span class="activity-time">{{ \Carbon\Carbon::parse($activity['time'])->diffForHumans() }}</span>
                        </a>
                    </li>
                @empty
                    <li class="activity-item" style="border: none;">
                        <span style="color: var(--text-secondary); font-size: 0.9375rem;">No recent activities</span>
                    </li>
                @endforelse
            </ul>
        </div>
@endsection

@push('scripts')
<script>
    window.__ADMIN_CHART_DATA = {
        months: @json($months),
        reportsData: @json($reportsData),
        maintenanceData: @json($maintenanceData),
        resolvedData: @json($resolvedData),
        resolvedPercentage: {{ $resolvedPercentage }},
        inProgressPercentage: {{ $inProgressPercentage }},
        pendingPercentage: {{ $pendingPercentage }}
    };
</script>
@vite(['resources/js/admin-dashboard.js'])
@endpush



