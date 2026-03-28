@extends('r_operators.layouts.app')

@section('content')

<header class="topbar">
    <div>
        <h1>Reports</h1>
        <p>View and manage incident reports</p>
    </div>
</header>

<section class="card card-mb" aria-label="Filters">
    <form method="GET" action="{{ route('operations.reports') }}" class="grid-filters">
        <div class="form-group">
            <label class="form-label" for="search">Search</label>
            <input type="text" id="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Report #, location...">
        </div>
        <div class="form-group">
            <label class="form-label" for="status">Status</label>
            <select id="status" name="status" class="form-control">
                <option value="">All Statuses</option>
                <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>New</option>
                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label" for="severity">Severity</label>
            <select id="severity" name="severity" class="form-control">
                <option value="">All Severities</option>
                <option value="low" {{ request('severity') == 'low' ? 'selected' : '' }}>Low</option>
                <option value="medium" {{ request('severity') == 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="high" {{ request('severity') == 'high' ? 'selected' : '' }}>High</option>
                <option value="critical" {{ request('severity') == 'critical' ? 'selected' : '' }}>Critical</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label" for="district">District</label>
            <select id="district" name="district" class="form-control">
                <option value="">All Districts</option>
                @foreach(config('brunei.districts', []) as $slug => $name)
                    <option value="{{ $slug }}" {{ request('district') == $slug ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label" for="issue_type">Issue Type</label>
            <select id="issue_type" name="issue_type" class="form-control">
                <option value="">All Types</option>
                <option value="blockage" {{ request('issue_type') == 'blockage' ? 'selected' : '' }}>Blockage</option>
                <option value="overflow" {{ request('issue_type') == 'overflow' ? 'selected' : '' }}>Overflow</option>
                <option value="odor" {{ request('issue_type') == 'odor' ? 'selected' : '' }}>Odor</option>
                <option value="maintenance" {{ request('issue_type') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                <option value="other" {{ request('issue_type') == 'other' ? 'selected' : '' }}>Other</option>
            </select>
        </div>
        <div class="form-group">
            <button type="submit" class="btn-submit">Filter</button>
        </div>
    </form>
</section>

<section class="card" aria-label="Reports list">
    <div class="table-wrap">
        <table class="ops-table">
            <thead>
                <tr>
                    <th>Report #</th>
                    <th>Type</th>
                    <th>District</th>
                    <th>Mukim</th>
                    <th>Location</th>
                    <th>Severity</th>
                    <th>Status</th>
                    <th>Work Order</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reports as $report)
                    <tr>
                        <td><strong>{{ $report->report_number }}</strong></td>
                        <td>{{ ucfirst($report->issue_type) }}</td>
                        <td>{{ $report->district_display ?? '—' }}</td>
                        <td>{{ $report->mukim_display ?? '—' }}</td>
                        <td>{{ $report->location_address }}</td>
                        <td>
                            <span class="badge badge-{{ $report->severity === 'critical' ? 'critical' : ($report->severity === 'high' ? 'high' : ($report->severity === 'medium' ? 'medium' : 'low')) }}">
                                {{ ucfirst($report->severity) }}
                            </span>
                        </td>
                        <td>
                            @php
                                $reportStatusClass = match($report->status) {
                                    'new' => 'badge-report-new',
                                    'in_progress' => 'badge-report-progress',
                                    'resolved' => 'badge-report-resolved',
                                    'closed' => 'badge-report-closed',
                                    default => 'badge-status'
                                };
                            @endphp
                            <span class="badge {{ $reportStatusClass }}">{{ ucfirst(str_replace('_', ' ', $report->status)) }}</span>
                        </td>
                        <td>
                            @if($report->workOrder)
                                <a href="{{ route('operations.work-orders.show', $report->workOrder) }}" class="link-primary">{{ $report->workOrder->work_order_number }}</a>
                            @else
                                <span style="color:#9ca3af;">—</span>
                            @endif
                        </td>
                        <td>{{ $report->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="cell-muted">No reports found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($reports->hasPages())
        <div class="pagination-bar">
            <span class="pagination-info">Showing {{ $reports->firstItem() }}–{{ $reports->lastItem() }} of {{ $reports->total() }}</span>
            <div class="pagination-btns">
                @if($reports->onFirstPage())
                    <span class="btn-page disabled"><svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Previous</span>
                @else
                    <a href="{{ $reports->previousPageUrl() }}" class="btn-page"> <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Previous</a>
                @endif
                @if($reports->hasMorePages())
                    <a href="{{ $reports->nextPageUrl() }}" class="btn-page">Next <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg></a>
                @else
                    <span class="btn-page disabled">Next <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg></span>
                @endif
            </div>
        </div>
    @endif
</section>

@endsection
