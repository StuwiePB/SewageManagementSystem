@extends('r_admin.layout')

@section('title', 'Old Reports')

@section('content')
<div class="header">
    <div>
        <h1>Old Reports</h1>
        <p>View digitized OM/DDS paper forms (read-only)</p>
    </div>
</div>

@include('r_admin.partials.dms-archive-view-only-notice')

<section class="card card-mb">
    <form method="GET" action="{{ route('admin.old-reports.index') }}" class="grid-filters">
        <div class="form-group flex-grow">
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

<section class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Archive #</th>
                    <th>DDS Reference</th>
                    <th>Ticket</th>
                    <th>Contact</th>
                    <th>Incident</th>
                    <th>Source</th>
                    <th></th>
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
                        <td><span class="badge badge-status">{{ strtoupper($paper->source) }}</span></td>
                        <td><a href="{{ route('admin.old-reports.show', $paper) }}" class="link-primary">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="cell-muted">No old reports match your filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($paperReports->hasPages())
        <div class="pagination-bar">
            <span class="pagination-info">Showing {{ $paperReports->firstItem() }}–{{ $paperReports->lastItem() }} of {{ $paperReports->total() }}</span>
            <div class="pagination-btns">
                @if($paperReports->onFirstPage())<span class="btn-page disabled">Previous</span>@else<a href="{{ $paperReports->previousPageUrl() }}" class="btn-page">Previous</a>@endif
                @if($paperReports->hasMorePages())<a href="{{ $paperReports->nextPageUrl() }}" class="btn-page">Next</a>@else<span class="btn-page disabled">Next</span>@endif
            </div>
        </div>
    @endif
</section>
@endsection

@push('styles')
    @include('r_operators.partials.datetime-picker-assets')
@endpush
@push('scripts')
    @include('r_operators.partials.datetime-picker-init')
@endpush
