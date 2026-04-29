@extends('r_admin.layout')

@section('title', 'Statistics')

@push('styles')
<style>
    .stat-intro { display: flex; align-items: flex-start; gap: 1.25rem; flex-wrap: wrap; }
    .stat-intro-icon { width: 56px; height: 56px; border-radius: 12px; background: rgba(106, 150, 255, 0.25); color: var(--accent-blue); display: flex; align-items: center; justify-content: center; font-size: 1.75rem; flex-shrink: 0; }
    .stat-intro h3 { font-size: 1.125rem; font-weight: 700; color: var(--text-primary); margin: 0 0 0.5rem; }
    .stat-intro p { font-size: 0.875rem; color: var(--text-secondary); margin: 0; line-height: 1.5; }
    .stat-period-row { display: flex; flex-wrap: wrap; align-items: center; gap: 1rem; margin-bottom: 1.75rem; }
    .stat-period-row label { font-weight: 500; color: var(--text-secondary); }
    .stat-period-row select { min-width: 200px; padding: 0.5rem 0.75rem; background: var(--bg-primary); border: 1px solid rgba(106, 150, 255, 0.3); border-radius: 8px; color: var(--text-primary); font-size: 0.9375rem; }
    .stat-custom-dates { display: none; flex: 1; min-width: 200px; gap: 0.75rem; flex-wrap: wrap; }
    .stat-custom-dates.show { display: flex; }
    .stat-custom-dates label { display: block; font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 0.25rem; }
    .stat-custom-dates input { padding: 0.5rem 0.75rem; background: var(--bg-primary); border: 1px solid rgba(106, 150, 255, 0.3); border-radius: 8px; color: var(--text-primary); font-size: 0.9375rem; }
    .stat-type-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.25rem; }
    .stat-type-btn { display: flex; flex-direction: column; align-items: flex-start; text-align: left; padding: 1.5rem; border: 1px solid rgba(106, 150, 255, 0.2); border-radius: 12px; background: var(--bg-secondary); color: inherit; cursor: pointer; transition: all 0.2s; font-family: inherit; min-height: 220px; }
    .stat-type-btn:hover { border-color: var(--accent-blue); background: rgba(106, 150, 255, 0.08); box-shadow: 0 4px 12px rgba(106, 150, 255, 0.15); }
    .stat-type-btn .icon { width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1rem; color: white; }
    .stat-type-btn .icon.blue { background: linear-gradient(135deg, var(--accent-blue), #0d9488); }
    .stat-type-btn .icon.green { background: linear-gradient(135deg, #0d9488, #059669); }
    .stat-type-btn .icon.orange { background: linear-gradient(135deg, #d97706, var(--accent-red)); }
    .stat-type-btn strong { font-size: 1.125rem; color: var(--text-primary); margin-bottom: 0.5rem; display: block; }
    .stat-type-btn span.desc { font-size: 0.8125rem; color: var(--text-secondary); line-height: 1.5; margin-bottom: 0.75rem; }
    .stat-type-btn ul { margin: 0 0 0.75rem; padding-left: 1.125rem; font-size: 0.75rem; color: var(--text-secondary); line-height: 1.8; }
    .stat-type-btn .view-link { margin-top: auto; font-size: 0.8125rem; color: var(--accent-blue); font-weight: 500; }
    .stat-export-tip { display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; }
    .stat-export-tip .icon { width: 44px; height: 44px; border-radius: 10px; background: var(--accent-blue); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
    .stat-export-tip strong { color: var(--text-primary); }
    .stat-export-tip p { margin: 0.25rem 0 0; font-size: 0.8125rem; color: var(--text-secondary); }
    .card-title { font-size: 1.125rem; font-weight: 700; color: var(--text-primary); margin: 0 0 0.5rem; }
    .card-subtitle { font-size: 0.8125rem; color: var(--text-secondary); margin: 0 0 1.25rem; }
    .back-link { display: inline-flex; align-items: center; padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; font-weight: 500; font-size: 0.875rem; background: rgba(106, 150, 255, 0.2); color: var(--accent-blue); }
    .back-link:hover { opacity: 0.9; color: var(--accent-blue); }
</style>
@endpush

@section('content')

<div class="header">
    <div>
        <h1>Statistics</h1>
        <p>Generate statistics for meetings and reports</p>
    </div>
</div>

<div class="card" style="margin-bottom: 1.5rem;">
    <div class="stat-intro">
        <div class="stat-intro-icon"><i class="fas fa-chart-line"></i></div>
        <div>
            <h3>Choose a period, then pick a report type</h3>
            <p>Each report shows graphs and can be exported to CSV (Excel) or printed as PDF for meetings.</p>
        </div>
    </div>
</div>

<div class="card" style="max-width: 900px;">
    <h3 class="card-title">Time period</h3>
    <p class="card-subtitle">Select the date range for your statistics.</p>

    @php($selectedPeriod = request('period', 'this_month'))
    <form action="{{ route('admin.statistics.show') }}" method="GET">
        <div class="stat-period-row">
            <label for="period">Period</label>
            <select name="period" id="period">
                <option value="last_7_days" {{ $selectedPeriod === 'last_7_days' ? 'selected' : '' }}>Last 7 days</option>
                <option value="this_month" {{ $selectedPeriod === 'this_month' ? 'selected' : '' }}>This month</option>
                <option value="this_quarter" {{ $selectedPeriod === 'this_quarter' ? 'selected' : '' }}>This quarter</option>
                <option value="custom" {{ $selectedPeriod === 'custom' ? 'selected' : '' }}>Custom date range</option>
            </select>
            <div id="custom-dates" class="stat-custom-dates">
                <div>
                    <label>Start</label>
                    <input type="date" id="start-date" name="start" value="{{ request('start') }}">
                </div>
                <div>
                    <label>End</label>
                    <input type="date" id="end-date" name="end" value="{{ request('end') }}">
                </div>
            </div>
        </div>

        <h3 class="card-title">Report type</h3>
        <p class="card-subtitle">Click a card to view that report for the selected period.</p>

        <div class="stat-type-grid">
            <button type="submit" name="type" value="reports" class="stat-type-btn">
                <div class="icon blue"><i class="fas fa-file-lines"></i></div>
                <strong>Reports</strong>
                <span class="desc">Incidents and public reports in the selected period.</span>
                <ul>
                    <li>By status &amp; issue type</li>
                    <li>Daily trend chart</li>
                    <li>Doughnut &amp; line graphs</li>
                </ul>
                <span class="view-link">View report</span>
            </button>
            <button type="submit" name="type" value="work_orders" class="stat-type-btn">
                <div class="icon green"><i class="fas fa-clipboard-list"></i></div>
                <strong>Work Orders</strong>
                <span class="desc">Operations work orders and completion metrics.</span>
                <ul>
                    <li>Completion rate &amp; backlog</li>
                    <li>By status &amp; priority</li>
                    <li>Daily trend chart</li>
                </ul>
                <span class="view-link">View report</span>
            </button>
            <button type="submit" name="type" value="geographic" class="stat-type-btn">
                <div class="icon orange"><i class="fas fa-map-location-dot"></i></div>
                <strong>Geographic (Hotspots)</strong>
                <span class="desc">Where incidents and work orders are concentrated.</span>
                <ul>
                    <li>Choropleth map by district</li>
                    <li>Reports &amp; work orders toggle</li>
                    <li>Top mukims bar charts</li>
                </ul>
                <span class="view-link">View report</span>
            </button>
        </div>
    </form>
</div>

<div class="card" style="margin-top: 1.5rem;">
    <div class="stat-export-tip">
        <div class="icon"><i class="fas fa-download"></i></div>
        <div>
            <strong>Export options</strong>
            <p>On the report page you can download <strong>CSV (Excel)</strong> or open <strong>Print / Save as PDF</strong> for meetings.</p>
        </div>
    </div>
</div>

<script>
(function() {
    var period = document.getElementById('period');
    var customDates = document.getElementById('custom-dates');
    var startDate = document.getElementById('start-date');
    var endDate = document.getElementById('end-date');
    if (period && customDates) {
        function toggle() {
            var isCustom = period.value === 'custom';
            customDates.classList.toggle('show', isCustom);
            if (startDate && endDate) {
                startDate.disabled = !isCustom;
                endDate.disabled = !isCustom;
                startDate.required = isCustom;
                endDate.required = isCustom;
            }
        }
        period.addEventListener('change', toggle);
        toggle();
    }
})();
</script>

@endsection
