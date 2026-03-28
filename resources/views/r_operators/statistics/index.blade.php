@extends('r_operators.layouts.app')

@section('content')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="topbar">
    <div>
        <h1>Statistics</h1>
        <p>Generate statistics for meetings and reports</p>
    </div>
</div>

{{-- Intro section --}}
<div class="card" style="margin-bottom:24px; background:linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-color:#bae6fd;">
    <div style="display:flex; align-items:flex-start; gap:20px; flex-wrap:wrap;">
        <div style="width:56px; height:56px; border-radius:14px; background:#0056A6; color:white; display:flex; align-items:center; justify-content:center; font-size:28px; flex-shrink:0;">
            <i class="fas fa-chart-line"></i>
        </div>
        <div style="flex:1; min-width:200px;">
            <h3 style="margin:0 0 8px; font-size:18px; color:#0c4a6e;">Choose a period, then pick a report type</h3>
            <p style="margin:0; color:#0369a1; font-size:14px; line-height:1.5;">Each report shows graphs and can be exported to CSV (Excel) or printed as PDF for meetings.</p>
        </div>
    </div>
</div>

<div class="card" style="max-width:900px;">
    <h3 style="margin:0 0 8px;">Time period</h3>
    <p style="margin:0 0 16px; font-size:13px; color:#6b7280;">Select the date range for your statistics.</p>

    <form action="{{ route('operations.statistics.show') }}" method="GET" style="display:flex; flex-direction:column; gap:28px;">
        <div style="display:flex; flex-wrap:wrap; align-items:center; gap:16px;">
            <label style="font-weight:500; color:#374151;">Period</label>
            <select name="period" id="period" style="
                min-width:200px;
                padding:10px 14px;
                border:1px solid #d1d5db;
                border-radius:8px;
                font-size:14px;
            ">
                <option value="last_7_days">Last 7 days</option>
                <option value="this_month" selected>This month</option>
                <option value="this_quarter">This quarter</option>
                <option value="custom">Custom date range</option>
            </select>
            <div id="custom-dates" style="display:none; flex:1; min-width:200px;">
                <div style="display:flex; gap:12px; flex-wrap:wrap;">
                    <div>
                        <label style="display:block; margin-bottom:4px; font-size:12px; color:#6b7280;">Start</label>
                        <input type="date" name="start" value="{{ request('start') }}" style="padding:8px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:14px;">
                    </div>
                    <div>
                        <label style="display:block; margin-bottom:4px; font-size:12px; color:#6b7280;">End</label>
                        <input type="date" name="end" value="{{ request('end') }}" style="padding:8px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:14px;">
                    </div>
                </div>
            </div>
        </div>

        <div>
            <h3 style="margin:0 0 4px;">Report type</h3>
            <p style="margin:0 0 20px; font-size:13px; color:#6b7280;">Click a card to view that report for the selected period.</p>

            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap:20px;">
                <button type="submit" name="type" value="reports" class="stat-type-btn" style="
                    display:flex;
                    flex-direction:column;
                    align-items:flex-start;
                    text-align:left;
                    padding:24px;
                    border:2px solid #e5e7eb;
                    border-radius:16px;
                    background:#fff;
                    cursor:pointer;
                    transition: all 0.2s;
                    font-family:inherit;
                    min-height:220px;
                ">
                    <div style="width:52px; height:52px; border-radius:12px; background:linear-gradient(135deg, #0056A6 0%, #0d9488 100%); color:white; display:flex; align-items:center; justify-content:center; font-size:24px; margin-bottom:16px;">
                        <i class="fas fa-file-lines"></i>
                    </div>
                    <span style="font-weight:700; font-size:18px; color:#1f2937; margin-bottom:8px;">Reports</span>
                    <span style="font-size:13px; color:#6b7280; line-height:1.5; margin-bottom:12px;">Incidents and public reports in the selected period.</span>
                    <ul style="margin:0; padding-left:18px; font-size:12px; color:#6b7280; line-height:1.8;">
                        <li>By status &amp; issue type</li>
                        <li>Daily trend chart</li>
                        <li>Doughnut &amp; line graphs</li>
                    </ul>
                    <span style="margin-top:auto; font-size:13px; color:#0056A6; font-weight:500;">View report</span>
                </button>

                <button type="submit" name="type" value="work_orders" class="stat-type-btn" style="
                    display:flex;
                    flex-direction:column;
                    align-items:flex-start;
                    text-align:left;
                    padding:24px;
                    border:2px solid #e5e7eb;
                    border-radius:16px;
                    background:#fff;
                    cursor:pointer;
                    transition: all 0.2s;
                    font-family:inherit;
                    min-height:220px;
                ">
                    <div style="width:52px; height:52px; border-radius:12px; background:linear-gradient(135deg, #0d9488 0%, #059669 100%); color:white; display:flex; align-items:center; justify-content:center; font-size:24px; margin-bottom:16px;">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <span style="font-weight:700; font-size:18px; color:#1f2937; margin-bottom:8px;">Work Orders</span>
                    <span style="font-size:13px; color:#6b7280; line-height:1.5; margin-bottom:12px;">Operations work orders and completion metrics.</span>
                    <ul style="margin:0; padding-left:18px; font-size:12px; color:#6b7280; line-height:1.8;">
                        <li>Completion rate &amp; backlog</li>
                        <li>By status &amp; priority</li>
                        <li>Daily trend chart</li>
                    </ul>
                    <span style="margin-top:auto; font-size:13px; color:#0056A6; font-weight:500;">View report</span>
                </button>

                <button type="submit" name="type" value="geographic" class="stat-type-btn" style="
                    display:flex;
                    flex-direction:column;
                    align-items:flex-start;
                    text-align:left;
                    padding:24px;
                    border:2px solid #e5e7eb;
                    border-radius:16px;
                    background:#fff;
                    cursor:pointer;
                    transition: all 0.2s;
                    font-family:inherit;
                    min-height:220px;
                ">
                    <div style="width:52px; height:52px; border-radius:12px; background:linear-gradient(135deg, #d97706 0%, #dc2626 100%); color:white; display:flex; align-items:center; justify-content:center; font-size:24px; margin-bottom:16px;">
                        <i class="fas fa-map-location-dot"></i>
                    </div>
                    <span style="font-weight:700; font-size:18px; color:#1f2937; margin-bottom:8px;">Geographic (Hotspots)</span>
                    <span style="font-size:13px; color:#6b7280; line-height:1.5; margin-bottom:12px;">Where incidents and work orders are concentrated.</span>
                    <ul style="margin:0; padding-left:18px; font-size:12px; color:#6b7280; line-height:1.8;">
                        <li>Choropleth map by district</li>
                        <li>Reports &amp; work orders toggle</li>
                        <li>Top mukims bar charts</li>
                    </ul>
                    <span style="margin-top:auto; font-size:13px; color:#0056A6; font-weight:500;">View report</span>
                </button>
            </div>
        </div>
    </form>
</div>

{{-- Export tip --}}
<div class="card" style="margin-top:24px; background:#f8fafc; border-color:#e2e8f0;">
    <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
        <div style="width:44px; height:44px; border-radius:10px; background:#0056A6; color:white; display:flex; align-items:center; justify-content:center; font-size:20px;">
            <i class="fas fa-download"></i>
        </div>
        <div>
            <strong style="color:#1e293b;">Export options</strong>
            <p style="margin:4px 0 0; font-size:13px; color:#64748b;">On the report page you can download <strong>CSV (Excel)</strong> or open <strong>Print / Save as PDF</strong> for meetings.</p>
        </div>
    </div>
</div>

<style>
.stat-type-btn:hover {
    border-color: #0056A6 !important;
    background: rgba(0, 86, 166, 0.04) !important;
    box-shadow: 0 4px 12px rgba(0, 86, 166, 0.12);
}
</style>

<script>
(function() {
    var period = document.getElementById('period');
    var customDates = document.getElementById('custom-dates');
    if (period && customDates) {
        function toggle() {
            customDates.style.display = period.value === 'custom' ? 'flex' : 'none';
        }
        period.addEventListener('change', toggle);
        toggle();
    }
})();
</script>

@endsection
