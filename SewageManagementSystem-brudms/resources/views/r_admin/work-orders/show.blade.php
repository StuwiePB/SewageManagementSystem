@extends('r_admin.layout')

@section('content')

<header class="header">
    <div>
        <h1>{{ $workOrder->work_order_number }}</h1>
        <p>Work Order Details</p>
    </div>
    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem;">
        @if(auth()->user()?->isSuperAdmin())
            <form method="POST" action="{{ route('admin.work-orders.destroy', $workOrder) }}" onsubmit="return confirm('Delete this work order? It will be archived and can be restored later.');" style="margin: 0;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
        @endif
        <a href="{{ route('admin.work-orders.pdf', $workOrder) }}" target="_blank" rel="noopener" class="btn btn-secondary">View / Print PDF</a>
        <a href="{{ route('admin.work-orders.index') }}" class="btn btn-secondary">Back to Work Orders</a>
    </div>
</header>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="grid-2col">
    <div class="card">
        <div class="card-header">
            <h3 class="section-head" style="margin:0;">Work Order Information</h3>
            <a href="{{ route('admin.work-orders.edit', $workOrder) }}" class="btn-edit">Edit</a>
        </div>

        <div class="detail-row">
            <p class="info-label">Type</p>
            <p class="info-value">{{ $workOrder->type }}</p>
        </div>

        <div class="detail-row">
            <p class="info-label">Priority</p>
            <span class="badge badge-{{ $workOrder->priority === 'critical' ? 'critical' : ($workOrder->priority === 'high' ? 'high' : ($workOrder->priority === 'medium' ? 'medium' : 'low')) }}">{{ ucfirst($workOrder->priority) }}</span>
        </div>

        <div class="detail-row">
            <p class="info-label">Location</p>
            <p class="info-value">{{ $workOrder->location_address }}</p>
            @if($workOrder->district_display || $workOrder->mukim_display)
                <p class="info-value-small">{{ $workOrder->district_display }}{{ $workOrder->mukim_display ? ' • ' . $workOrder->mukim_display : '' }}</p>
            @endif
            @if($workOrder->latitude && $workOrder->longitude)
                <p class="info-value-small">{{ $workOrder->latitude }}, {{ $workOrder->longitude }}</p>
            @endif
        </div>

        @if($workOrder->description)
        <div class="detail-row">
            <p class="info-label">Description</p>
            <p class="info-value-muted">{{ $workOrder->description }}</p>
        </div>
        @endif

        @if($workOrder->notes)
        <div class="detail-row">
            <p class="info-label">Notes</p>
            <p class="info-value-muted">{{ $workOrder->notes }}</p>
        </div>
        @endif
    </div>

    <div class="card">
        <h3 class="section-head">Status &amp; Assignment</h3>

        <div class="detail-row">
            <p class="info-label">Status</p>
            @php
                $statusClass = match($workOrder->status) {
                    'on_the_way', 'pending_approval' => 'badge-status-amber',
                    'on_site' => 'badge-status-teal',
                    'completed' => 'badge-status-green',
                    'cancelled' => 'badge-status-red',
                    default => 'badge-status'
                };
            @endphp
            <span class="badge {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $workOrder->status)) }}</span>
        </div>

        @if($workOrder->report)
        <div class="detail-row">
            <p class="info-label">Linked Report</p>
            <p class="info-value-muted">
                <strong>{{ $workOrder->report->report_number }}</strong><br>
                <span class="info-value-small">{{ $workOrder->report->issue_type }}</span>
            </p>
        </div>
        @endif

        <form action="{{ route('admin.work-orders.update-status', $workOrder) }}" method="POST" style="margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid rgba(106, 150, 255, 0.15);">
            @csrf
            @method('PATCH')
            <div class="form-group" style="margin-bottom:12px;">
                <label class="form-label" for="status">Update Status</label>
                <select id="status" name="status" class="form-control">
                    <option value="pending" {{ $workOrder->status == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="assigned" {{ $workOrder->status == 'assigned' ? 'selected' : '' }}>Assigned</option>
                    <option value="in_progress" {{ $workOrder->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="on_the_way" {{ $workOrder->status == 'on_the_way' ? 'selected' : '' }}>On the Way</option>
                    <option value="on_site" {{ $workOrder->status == 'on_site' ? 'selected' : '' }}>On Site</option>
                    <option value="pending_approval" {{ $workOrder->status == 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                    <option value="completed" {{ $workOrder->status == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ $workOrder->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <button type="submit" class="btn-submit">Update Status</button>
        </form>
            @if(!in_array($workOrder->status, ['pending_approval', 'completed', 'cancelled']))
                <p class="info-label" style="margin: 1rem 0 0.5rem;">Or send to higher ups:</p>
                <form action="{{ route('admin.work-orders.submit-approval', $workOrder) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn" style="width: 100%; background: rgba(255, 165, 0, 0.25); color: #FFA500;">Submit for approval</button>
                </form>
            @endif
    </div>
</div>

<section class="card" aria-label="Report picture">
    <h3 class="section-head">Report picture</h3>
    <p class="section-desc">Original image attached to the linked report.</p>

    @if($workOrder->report && $workOrder->report->customerReport && $workOrder->report->customerReport->photo_path)
        <a href="{{ \Illuminate\Support\Facades\Storage::url($workOrder->report->customerReport->photo_path) }}" target="_blank" rel="noopener" style="display:inline-block;">
            <img
                src="{{ \Illuminate\Support\Facades\Storage::url($workOrder->report->customerReport->photo_path) }}"
                alt="Report picture"
                style="max-width:100%; width:auto; max-height:360px; border-radius:10px; border:1px solid rgba(106, 150, 255, 0.2);"
            >
        </a>
    @else
        <p style="margin:0; font-size:14px; color:var(--text-secondary);">No report picture available for this linked report.</p>
    @endif
</section>

<section class="card" aria-label="Site photos">
    <h3 class="section-head">Site photos</h3>
    <p class="section-desc">Add 2 or 3 photos per upload for stronger evidence and so the constructor can better understand the situation.</p>

    <form action="{{ route('admin.work-orders.photos.store', $workOrder) }}" method="POST" enctype="multipart/form-data" style="margin-bottom: 1.25rem;">
        @csrf
        <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <input type="file" name="photos[]" accept="image/*" multiple class="form-control" id="site-photos-input" style="width: auto; max-width: 280px;">
            <button type="submit" class="btn-submit" style="width:auto;">Upload 2–3 photos</button>
        </div>
        <p class="info-value-small" style="margin: 0.25rem 0 0;">Select 2 or 3 images at once. Each max 10MB.</p>
        @error('photos')
            <p style="color: var(--accent-red); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>
        @enderror
        @error('photos.*')
            <p style="color: var(--accent-red); font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</p>
        @enderror
    </form>

    @if($workOrder->photos->isEmpty())
        <p class="info-value-muted" style="margin: 0;">No photos yet.</p>
    @else
        <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap:16px;">
            @foreach($workOrder->photos as $photo)
                <div style="border: 1px solid rgba(106, 150, 255, 0.2); border-radius: 8px; overflow: hidden;">
                    <a href="{{ \Illuminate\Support\Facades\Storage::url($photo->path) }}" target="_blank" rel="noopener" style="display: block;">
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($photo->path) }}" alt="Site photo" style="width: 100%; height: 120px; object-fit: cover;">
                    </a>
                    <div style="padding: 0.5rem; display: flex; justify-content: space-between; align-items: center;">
                        <span class="info-value-small" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $photo->original_name ?? 'Photo' }}</span>
                        <form action="{{ route('admin.work-orders.photos.destroy', [$workOrder, $photo]) }}" method="POST" onsubmit="return confirm('Remove this photo?');" style="margin: 0;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">Remove</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>

<section class="card" aria-label="Timeline">
    <h3 class="section-head">Timeline</h3>
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">
        <div class="detail-row">
            <p class="info-label">Created</p>
            <p class="info-value-muted">{{ $workOrder->created_at->format('M d, Y g:i A') }}</p>
        </div>
        @if($workOrder->assigned_at)
        <div class="detail-row">
            <p class="info-label">Assigned</p>
            <p class="info-value-muted">{{ $workOrder->assigned_at->format('M d, Y g:i A') }}</p>
        </div>
        @endif
        @if($workOrder->started_at)
        <div class="detail-row">
            <p class="info-label">Started</p>
            <p class="info-value-muted">{{ $workOrder->started_at->format('M d, Y g:i A') }}</p>
        </div>
        @endif
        @if($workOrder->completed_at)
        <div class="detail-row">
            <p class="info-label">Completed</p>
            <p class="info-value-muted">{{ $workOrder->completed_at->format('M d, Y g:i A') }}</p>
        </div>
        @endif
    </div>
</section>

@endsection
