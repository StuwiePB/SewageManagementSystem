@extends('r_operators.layouts.app')

@section('content')

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 1rem;">{{ session('success') }}</div>
@endif

<header class="topbar">
    <div>
        <h1>Old Reports</h1>
        <p>Digitized OM/DDS paper forms (historical records)</p>
    </div>
    <a href="{{ route('operations.old-reports.create') }}" class="btn btn-primary">
        <i class="fas fa-upload"></i> Upload Report
    </a>
</header>

<section class="card card-mb" aria-label="Filters">
    <form method="GET" action="{{ route('operations.old-reports.index') }}" class="grid-filters">
        <div class="form-group">
            <label class="form-label" for="search">Search</label>
            <input type="text" id="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Archive #, DDS ref, ticket, contact...">
        </div>
        <div class="form-group">
            <label class="form-label" for="date_on">Filter dates by</label>
            <select id="date_on" name="date_on" class="form-control">
                <option value="incident" {{ request('date_on', 'incident') === 'incident' ? 'selected' : '' }}>Incident date &amp; time</option>
                <option value="investigated" {{ request('date_on') === 'investigated' ? 'selected' : '' }}>Investigated date &amp; time</option>
                <option value="digitized" {{ request('date_on') === 'digitized' ? 'selected' : '' }}>Date digitized</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label" for="datetime_from">From</label>
            <input type="text" id="datetime_from" name="datetime_from" class="form-control dms-datetime-picker" value="{{ str_replace('T', ' ', request('datetime_from', '')) }}" placeholder="Select date & time" autocomplete="off">
        </div>
        <div class="form-group">
            <label class="form-label" for="datetime_to">To</label>
            <input type="text" id="datetime_to" name="datetime_to" class="form-control dms-datetime-picker" value="{{ str_replace('T', ' ', request('datetime_to', '')) }}" placeholder="Select date & time" autocomplete="off">
        </div>
        <div class="form-group">
            <button type="submit" class="btn-submit">Filter</button>
        </div>
    </form>
</section>

<section class="card" aria-label="Old reports list">
    <div class="table-wrap">
        <table class="ops-table">
            <thead>
                <tr>
                    <th>Archive #</th>
                    <th>DDS Reference</th>
                    <th>Ticket</th>
                    <th>Contact</th>
                    <th>Incident Date &amp; Time</th>
                    <th>Investigated</th>
                    <th>Source</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($paperReports as $paper)
                    <tr>
                        <td><strong>{{ $paper->archive_number }}</strong></td>
                        <td>{{ $paper->dds_file_reference ?? '—' }}</td>
                        <td>{{ $paper->service_request_reference ?? '—' }}</td>
                        <td>{{ $paper->contact_name ?? '—' }}</td>
                        <td>{{ $paper->incident_at?->format('d M Y, H:i') ?? '—' }}</td>
                        <td>{{ $paper->investigated_at?->format('d M Y, H:i') ?? '—' }}</td>
                        <td><span class="badge badge-status">{{ strtoupper($paper->source) }}</span></td>
                        <td>
                            <a href="{{ route('operations.old-work-orders.create', ['report' => $paper->id]) }}" class="link-primary">Add Old Work Order</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="cell-muted">No old reports match your filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($paperReports->hasPages())
        <div class="pagination-bar">
            <span class="pagination-info">Showing {{ $paperReports->firstItem() }}–{{ $paperReports->lastItem() }} of {{ $paperReports->total() }}</span>
            <div class="pagination-btns">
                @if($paperReports->onFirstPage())
                    <span class="btn-page disabled">Previous</span>
                @else
                    <a href="{{ $paperReports->previousPageUrl() }}" class="btn-page">Previous</a>
                @endif
                @if($paperReports->hasMorePages())
                    <a href="{{ $paperReports->nextPageUrl() }}" class="btn-page">Next</a>
                @else
                    <span class="btn-page disabled">Next</span>
                @endif
            </div>
        </div>
    @endif
</section>

@endsection
