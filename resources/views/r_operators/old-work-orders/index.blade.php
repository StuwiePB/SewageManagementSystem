@extends('r_operators.layouts.app')

@section('content')

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 1rem;">{{ session('success') }}</div>
@endif

<header class="topbar">
    <div>
        <h1>Old Work Orders</h1>
        <p>Digitized historical paper work order records</p>
    </div>
    <a href="{{ route('operations.old-work-orders.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Work Order
    </a>
</header>

<section class="card card-mb" aria-label="Filters">
    <form method="GET" action="{{ route('operations.old-work-orders.index') }}" class="grid-filters">
        <div class="form-group">
            <label class="form-label" for="search">Search</label>
            <input type="text" id="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Work order #, location, crew...">
        </div>
        <div class="form-group">
            <label class="form-label" for="status">Status</label>
            <select id="status" name="status" class="form-control">
                <option value="">All</option>
                @foreach(config('dms_forms.archive_work_order_statuses') as $val => $label)
                    <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label" for="priority">Priority</label>
            <select id="priority" name="priority" class="form-control">
                <option value="">All</option>
                @foreach(config('dms_forms.priorities') as $val => $label)
                    <option value="{{ $val }}" {{ request('priority') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label" for="date_on">Filter dates by</label>
            <select id="date_on" name="date_on" class="form-control">
                <option value="created" {{ request('date_on', 'created') === 'created' ? 'selected' : '' }}>Created date &amp; time</option>
                <option value="started" {{ request('date_on') === 'started' ? 'selected' : '' }}>Started date &amp; time</option>
                <option value="completed" {{ request('date_on') === 'completed' ? 'selected' : '' }}>Completed date &amp; time</option>
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

<section class="card" aria-label="Old work orders list">
    <div class="table-wrap">
        <table class="ops-table">
            <thead>
                <tr>
                    <th>Work Order #</th>
                    <th>Priority</th>
                    <th>Type</th>
                    <th>Location</th>
                    <th>Created</th>
                    <th>Started</th>
                    <th>Completed</th>
                    <th>Status</th>
                    <th>Old Report</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($archiveWorkOrders as $archive)
                    <tr>
                        <td><strong>{{ $archive->work_order_number }}</strong></td>
                        <td>
                            <span class="badge badge-{{ $archive->priority === 'critical' ? 'critical' : ($archive->priority === 'high' ? 'high' : 'medium') }}">
                                {{ ucfirst($archive->priority) }}
                            </span>
                        </td>
                        <td>{{ ucfirst($archive->work_type) }}</td>
                        <td>{{ $archive->location_address }}</td>
                        <td>{{ $archive->record_created_at?->format('d M Y, H:i') ?? '—' }}</td>
                        <td>{{ $archive->record_started_at?->format('d M Y, H:i') ?? '—' }}</td>
                        <td>{{ $archive->record_completed_at?->format('d M Y, H:i') ?? '—' }}</td>
                        <td><span class="badge badge-status">{{ ucfirst(str_replace('_', ' ', $archive->status)) }}</span></td>
                        <td>
                            @if($archive->paperReport)
                                <a href="{{ route('operations.old-reports.index') }}?search={{ urlencode($archive->paperReport->archive_number) }}" class="link-primary">{{ $archive->paperReport->archive_number }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td><a href="{{ route('operations.old-work-orders.show', $archive) }}" class="link-primary">View</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="cell-muted">No old work orders match your filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($archiveWorkOrders->hasPages())
        <div class="pagination-bar">
            <span class="pagination-info">Showing {{ $archiveWorkOrders->firstItem() }}–{{ $archiveWorkOrders->lastItem() }} of {{ $archiveWorkOrders->total() }}</span>
            <div class="pagination-btns">
                @if($archiveWorkOrders->onFirstPage())
                    <span class="btn-page disabled">Previous</span>
                @else
                    <a href="{{ $archiveWorkOrders->previousPageUrl() }}" class="btn-page">Previous</a>
                @endif
                @if($archiveWorkOrders->hasMorePages())
                    <a href="{{ $archiveWorkOrders->nextPageUrl() }}" class="btn-page">Next</a>
                @else
                    <span class="btn-page disabled">Next</span>
                @endif
            </div>
        </div>
    @endif
</section>

@endsection
