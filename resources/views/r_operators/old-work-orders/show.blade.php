@extends('r_operators.layouts.app')

@section('content')

<header class="topbar">
    <div>
        <a href="{{ route('operations.old-work-orders.index') }}" class="link-primary" style="display: inline-block; margin-bottom: 0.5rem;">← Old Work Orders</a>
        <h1>{{ $archiveWorkOrder->work_order_number }}</h1>
        <p>{{ ucfirst($archiveWorkOrder->work_type) }} — {{ $archiveWorkOrder->location_address }}</p>
    </div>
</header>

<div class="grid-2col" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem;">
    <section class="card">
        <h3 class="section-head" style="margin-bottom: 1rem;">Work Order</h3>
        <div class="detail-row"><p class="info-label">Work Order ID</p><p class="info-value">{{ $archiveWorkOrder->work_order_number }}</p></div>
        <div class="detail-row"><p class="info-label">Priority</p><p class="info-value">{{ ucfirst($archiveWorkOrder->priority) }}</p></div>
        <div class="detail-row"><p class="info-label">Assigned Crew / Technician</p><p class="info-value">{{ $archiveWorkOrder->assigned_crew ?? '—' }}</p></div>
        <div class="detail-row"><p class="info-label">Work Type</p><p class="info-value">{{ ucfirst($archiveWorkOrder->work_type) }}</p></div>
        <div class="detail-row"><p class="info-label">Status</p><p class="info-value">{{ ucfirst(str_replace('_', ' ', $archiveWorkOrder->status)) }}</p></div>
        <div class="detail-row"><p class="info-label">Problem Category</p><p class="info-value">{{ $archiveWorkOrder->problem_category ?? '—' }}</p></div>
        <div class="detail-row"><p class="info-label">Description</p><p class="info-value">{{ $archiveWorkOrder->description ?? '—' }}</p></div>
        <div class="detail-row"><p class="info-label">Site Notes</p><p class="info-value">{{ $archiveWorkOrder->site_notes ?? '—' }}</p></div>
        <div class="detail-row"><p class="info-label">Digitized by</p><p class="info-value">{{ $archiveWorkOrder->digitizedBy?->name ?? '—' }}</p></div>
    </section>

    <section class="card">
        <h3 class="section-head" style="margin-bottom: 1rem;">Location &amp; Dates</h3>
        <div class="detail-row"><p class="info-label">Location / Address</p><p class="info-value">{{ $archiveWorkOrder->location_address }}</p></div>
        <div class="detail-row"><p class="info-label">Mukim</p><p class="info-value">{{ $archiveWorkOrder->mukim ?? '—' }}</p></div>
        <div class="detail-row"><p class="info-label">Date &amp; Time Created</p><p class="info-value">{{ $archiveWorkOrder->record_created_at?->format('d M Y, H:i') ?? '—' }}</p></div>
        <div class="detail-row"><p class="info-label">Date &amp; Time Started</p><p class="info-value">{{ $archiveWorkOrder->record_started_at?->format('d M Y, H:i') ?? '—' }}</p></div>
        <div class="detail-row"><p class="info-label">Date &amp; Time Completed</p><p class="info-value">{{ $archiveWorkOrder->record_completed_at?->format('d M Y, H:i') ?? '—' }}</p></div>
        <div class="detail-row"><p class="info-label">Estimated Completion</p><p class="info-value">{{ $archiveWorkOrder->estimated_completion_date?->format('d M Y, H:i') ?? '—' }}</p></div>
        <div class="detail-row">
            <p class="info-label">Related Old Report</p>
            <p class="info-value">
                @if($archiveWorkOrder->paperReport)
                    <a href="{{ route('operations.old-reports.index') }}?search={{ urlencode($archiveWorkOrder->paperReport->archive_number) }}" class="link-primary">{{ $archiveWorkOrder->paperReport->archive_number }}</a>
                @else
                    —
                @endif
            </p>
        </div>
    </section>
</div>

@if($archiveWorkOrder->photos->isNotEmpty())
    <section class="card" style="margin-top: 1rem;">
        <h3 class="section-head" style="margin-bottom: 1rem;">Photos</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem;">
            @foreach($archiveWorkOrder->photos as $photo)
                <div>
                    <p class="info-label" style="margin-bottom: 0.5rem;">{{ ucfirst($photo->photo_type) }}</p>
                    <a href="{{ Storage::disk('public')->url($photo->path) }}" target="_blank" rel="noopener">
                        <img src="{{ Storage::disk('public')->url($photo->path) }}" alt="{{ $photo->original_name }}" style="width: 100%; border-radius: 8px; border: 1px solid rgba(106, 150, 255, 0.2);">
                    </a>
                </div>
            @endforeach
        </div>
    </section>
@endif

@endsection
