@extends('operations.layouts.app')

@section('content')

<div class="topbar">
    <div>
        <h1>Reports</h1>
        <p>View and manage incident reports</p>
    </div>
</div>

{{-- Filters --}}
<div class="card" style="margin-bottom:20px;">
    <form method="GET" action="{{ route('operations.reports') }}" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:16px; align-items:end;">
        <div>
            <label style="display:block; margin-bottom:6px; font-size:13px; color:#6b7280; font-weight:500;">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Report #, location..." style="
                width:100%;
                padding:8px 12px;
                border:1px solid #d1d5db;
                border-radius:8px;
                font-size:14px;
            ">
        </div>
        <div>
            <label style="display:block; margin-bottom:6px; font-size:13px; color:#6b7280; font-weight:500;">Status</label>
            <select name="status" style="
                width:100%;
                padding:8px 12px;
                border:1px solid #d1d5db;
                border-radius:8px;
                font-size:14px;
            ">
                <option value="">All Statuses</option>
                <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>New</option>
                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
            </select>
        </div>
        <div>
            <label style="display:block; margin-bottom:6px; font-size:13px; color:#6b7280; font-weight:500;">Severity</label>
            <select name="severity" style="
                width:100%;
                padding:8px 12px;
                border:1px solid #d1d5db;
                border-radius:8px;
                font-size:14px;
            ">
                <option value="">All Severities</option>
                <option value="low" {{ request('severity') == 'low' ? 'selected' : '' }}>Low</option>
                <option value="medium" {{ request('severity') == 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="high" {{ request('severity') == 'high' ? 'selected' : '' }}>High</option>
                <option value="critical" {{ request('severity') == 'critical' ? 'selected' : '' }}>Critical</option>
            </select>
        </div>
        <div>
            <label style="display:block; margin-bottom:6px; font-size:13px; color:#6b7280; font-weight:500;">District</label>
            <select name="district" style="
                width:100%;
                padding:8px 12px;
                border:1px solid #d1d5db;
                border-radius:8px;
                font-size:14px;
            ">
                <option value="">All Districts</option>
                @foreach(config('brunei.districts', []) as $slug => $name)
                    <option value="{{ $slug }}" {{ request('district') == $slug ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label style="display:block; margin-bottom:6px; font-size:13px; color:#6b7280; font-weight:500;">Issue Type</label>
            <select name="issue_type" style="
                width:100%;
                padding:8px 12px;
                border:1px solid #d1d5db;
                border-radius:8px;
                font-size:14px;
            ">
                <option value="">All Types</option>
                <option value="blockage" {{ request('issue_type') == 'blockage' ? 'selected' : '' }}>Blockage</option>
                <option value="overflow" {{ request('issue_type') == 'overflow' ? 'selected' : '' }}>Overflow</option>
                <option value="odor" {{ request('issue_type') == 'odor' ? 'selected' : '' }}>Odor</option>
                <option value="maintenance" {{ request('issue_type') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                <option value="other" {{ request('issue_type') == 'other' ? 'selected' : '' }}>Other</option>
            </select>
        </div>
        <div>
            <button type="submit" style="
                background:#0056A6;
                color:white;
                padding:8px 20px;
                border:none;
                border-radius:8px;
                font-weight:500;
                cursor:pointer;
                width:100%;
            ">Filter</button>
        </div>
    </form>
</div>

{{-- Reports Table --}}
<div class="card">
    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="text-align:left; border-bottom:2px solid #e5e7eb;">
                <th style="padding:12px 8px; font-size:13px; color:#6b7280; font-weight:600;">Report #</th>
                <th style="padding:12px 8px; font-size:13px; color:#6b7280; font-weight:600;">Type</th>
                <th style="padding:12px 8px; font-size:13px; color:#6b7280; font-weight:600;">District</th>
                <th style="padding:12px 8px; font-size:13px; color:#6b7280; font-weight:600;">Mukim</th>
                <th style="padding:12px 8px; font-size:13px; color:#6b7280; font-weight:600;">Location</th>
                <th style="padding:12px 8px; font-size:13px; color:#6b7280; font-weight:600;">Severity</th>
                <th style="padding:12px 8px; font-size:13px; color:#6b7280; font-weight:600;">Status</th>
                <th style="padding:12px 8px; font-size:13px; color:#6b7280; font-weight:600;">Work Order</th>
                <th style="padding:12px 8px; font-size:13px; color:#6b7280; font-weight:600;">Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reports as $report)
                <tr style="border-bottom:1px solid #f3f4f6;">
                    <td style="padding:12px 8px; font-size:14px; font-weight:500;">{{ $report->report_number }}</td>
                    <td style="padding:12px 8px; font-size:14px;">{{ ucfirst($report->issue_type) }}</td>
                    <td style="padding:12px 8px; font-size:14px;">{{ $report->district_display ?? '—' }}</td>
                    <td style="padding:12px 8px; font-size:14px;">{{ $report->mukim_display ?? '—' }}</td>
                    <td style="padding:12px 8px; font-size:14px;">{{ $report->location_address }}</td>
                    <td style="padding:12px 8px;">
                        <span style="
                            padding:4px 8px;
                            border-radius:4px;
                            font-size:12px;
                            font-weight:500;
                            background:{{ $report->severity == 'critical' ? '#fee2e2' : ($report->severity == 'high' ? '#fed7aa' : ($report->severity == 'medium' ? '#fef3c7' : '#dcfce7')) }};
                            color:{{ $report->severity == 'critical' ? '#991b1b' : ($report->severity == 'high' ? '#9a3412' : ($report->severity == 'medium' ? '#854d0e' : '#166534')) }};
                        ">
                            {{ ucfirst($report->severity) }}
                        </span>
                    </td>
                    <td style="padding:12px 8px;">
                        <span style="
                            padding:4px 8px;
                            border-radius:4px;
                            font-size:12px;
                            font-weight:500;
                            background:{{ $report->status == 'new' ? '#dbeafe' : ($report->status == 'in_progress' ? '#fef3c7' : ($report->status == 'resolved' ? '#dcfce7' : '#e5e7eb')) }};
                            color:{{ $report->status == 'new' ? '#1e40af' : ($report->status == 'in_progress' ? '#854d0e' : ($report->status == 'resolved' ? '#166534' : '#374151')) }};
                        ">
                            {{ ucfirst(str_replace('_', ' ', $report->status)) }}
                        </span>
                    </td>
                    <td style="padding:12px 8px; font-size:14px;">
                        @if($report->workOrder)
                            <a href="{{ route('operations.work-orders.show', $report->workOrder) }}" style="color:#0056A6; text-decoration:none;">
                                {{ $report->workOrder->work_order_number }}
                            </a>
                        @else
                            <span style="color:#9ca3af;">—</span>
                        @endif
                    </td>
                    <td style="padding:12px 8px; font-size:13px; color:#6b7280;">
                        {{ $report->created_at->format('M d, Y') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="padding:24px; text-align:center; color:#9ca3af;">
                        No reports found
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination --}}
    @if($reports->hasPages())
        <div style="margin-top:20px; padding-top:20px; border-top:1px solid #e5e7eb;">
            {{ $reports->links() }}
        </div>
    @endif
</div>

@endsection
