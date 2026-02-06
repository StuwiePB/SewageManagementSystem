@extends('operations.layouts.app')

@section('content')

<div class="topbar">
    <div>
        <h1>{{ $workOrder->work_order_number }}</h1>
        <p>Work Order Details</p>
    </div>
    <a href="{{ route('operations.work-orders.index') }}" style="color:#6b7280; text-decoration:none;">← Back to Work Orders</a>
</div>

<div style="display:grid; grid-template-columns: 2fr 1fr; gap:20px; margin-bottom:20px;">
    <div class="card">
        <h3 style="margin:0 0 16px;">Work Order Information</h3>
        
        <div style="margin-bottom:16px;">
            <p style="margin:0; font-size:12px; color:#6b7280;">Type</p>
            <p style="margin:4px 0 0; font-size:16px; font-weight:500;">{{ $workOrder->type }}</p>
        </div>

        <div style="margin-bottom:16px;">
            <p style="margin:0; font-size:12px; color:#6b7280;">Priority</p>
            <span style="
                display:inline-block;
                margin-top:4px;
                padding:4px 12px;
                border-radius:4px;
                font-size:12px;
                font-weight:500;
                background:{{ $workOrder->priority == 'critical' ? '#fee2e2' : ($workOrder->priority == 'high' ? '#fed7aa' : ($workOrder->priority == 'medium' ? '#fef3c7' : '#dcfce7')) }};
                color:{{ $workOrder->priority == 'critical' ? '#991b1b' : ($workOrder->priority == 'high' ? '#9a3412' : ($workOrder->priority == 'medium' ? '#854d0e' : '#166534')) }};
            ">
                {{ ucfirst($workOrder->priority) }}
            </span>
        </div>

        <div style="margin-bottom:16px;">
            <p style="margin:0; font-size:12px; color:#6b7280;">Location</p>
            <p style="margin:4px 0 0; font-size:16px;">{{ $workOrder->location_address }}</p>
            @if($workOrder->district_display || $workOrder->mukim_display)
                <p style="margin:4px 0 0; font-size:13px; color:#6b7280;">
                    {{ $workOrder->district_display }}{{ $workOrder->mukim_display ? ' • ' . $workOrder->mukim_display : '' }}
                </p>
            @endif
            @if($workOrder->latitude && $workOrder->longitude)
                <p style="margin:4px 0 0; font-size:12px; color:#9ca3af;">
                    {{ $workOrder->latitude }}, {{ $workOrder->longitude }}
                </p>
            @endif
        </div>

        @if($workOrder->description)
        <div style="margin-bottom:16px;">
            <p style="margin:0; font-size:12px; color:#6b7280;">Description</p>
            <p style="margin:4px 0 0; font-size:14px; color:#374151;">{{ $workOrder->description }}</p>
        </div>
        @endif

        @if($workOrder->notes)
        <div style="margin-bottom:16px;">
            <p style="margin:0; font-size:12px; color:#6b7280;">Notes</p>
            <p style="margin:4px 0 0; font-size:14px; color:#374151;">{{ $workOrder->notes }}</p>
        </div>
        @endif
    </div>

    <div class="card">
        <h3 style="margin:0 0 16px;">Status & Assignment</h3>
        
        <div style="margin-bottom:16px;">
            <p style="margin:0; font-size:12px; color:#6b7280;">Status</p>
            <span style="
                display:inline-block;
                margin-top:4px;
                padding:4px 12px;
                border-radius:4px;
                font-size:12px;
                font-weight:500;
                background:#e0e7ff;
                color:#3730a3;
            ">
                {{ ucfirst(str_replace('_', ' ', $workOrder->status)) }}
            </span>
        </div>

        <div style="margin-bottom:16px;">
            <p style="margin:0; font-size:12px; color:#6b7280;">Assigned Crew</p>
            @if($workOrder->crew)
                <a href="{{ route('operations.crews.show', $workOrder->crew) }}" style="
                    display:inline-block;
                    margin-top:4px;
                    color:#0077CC;
                    text-decoration:none;
                    font-weight:500;
                ">
                    {{ $workOrder->crew->name }}
                </a>
            @else
                <p style="margin:4px 0 0; color:#9ca3af;">Unassigned</p>
            @endif
        </div>

        @if($workOrder->report)
        <div style="margin-bottom:16px;">
            <p style="margin:0; font-size:12px; color:#6b7280;">Linked Report</p>
            <p style="margin:4px 0 0; font-size:14px;">
                <strong>{{ $workOrder->report->report_number }}</strong><br>
                <span style="color:#6b7280; font-size:12px;">{{ $workOrder->report->issue_type }}</span>
            </p>
        </div>
        @endif

        {{-- Update Status Form --}}
        <form action="{{ route('operations.work-orders.update-status', $workOrder) }}" method="POST" style="margin-top:20px; padding-top:20px; border-top:1px solid #e5e7eb;">
            @csrf
            @method('PATCH')
            
            <div style="margin-bottom:12px;">
                <label style="display:block; margin-bottom:6px; font-size:13px; font-weight:500; color:#374151;">Update Status</label>
                <select name="status" style="
                    width:100%;
                    padding:8px 12px;
                    border:1px solid #d1d5db;
                    border-radius:8px;
                    font-size:14px;
                ">
                    <option value="pending" {{ $workOrder->status == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="assigned" {{ $workOrder->status == 'assigned' ? 'selected' : '' }}>Assigned</option>
                    <option value="in_progress" {{ $workOrder->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ $workOrder->status == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ $workOrder->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            @if($workOrder->status != 'completed' && $workOrder->status != 'cancelled')
            <div style="margin-bottom:12px;">
                <label style="display:block; margin-bottom:6px; font-size:13px; font-weight:500; color:#374151;">Assign/Change Crew</label>
                <select name="crew_id" style="
                    width:100%;
                    padding:8px 12px;
                    border:1px solid #d1d5db;
                    border-radius:8px;
                    font-size:14px;
                ">
                    <option value="">Unassigned</option>
                    @foreach(\App\Models\Crew::orderBy('name')->get() as $crew)
                        <option value="{{ $crew->id }}" {{ $workOrder->crew_id == $crew->id ? 'selected' : '' }}>
                            {{ $crew->name }} ({{ ucfirst(str_replace('_', ' ', $crew->status)) }})
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <button type="submit" style="
                background:#0056A6;
                color:white;
                padding:8px 16px;
                border:none;
                border-radius:8px;
                font-weight:500;
                cursor:pointer;
                width:100%;
            ">Update Status</button>
        </form>
    </div>
</div>

{{-- Timestamps --}}
<div class="card">
    <h3 style="margin:0 0 16px;">Timeline</h3>
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">
        <div>
            <p style="margin:0; font-size:12px; color:#6b7280;">Created</p>
            <p style="margin:4px 0 0; font-size:14px;">{{ $workOrder->created_at->format('M d, Y g:i A') }}</p>
        </div>
        @if($workOrder->assigned_at)
        <div>
            <p style="margin:0; font-size:12px; color:#6b7280;">Assigned</p>
            <p style="margin:4px 0 0; font-size:14px;">{{ $workOrder->assigned_at->format('M d, Y g:i A') }}</p>
        </div>
        @endif
        @if($workOrder->started_at)
        <div>
            <p style="margin:0; font-size:12px; color:#6b7280;">Started</p>
            <p style="margin:4px 0 0; font-size:14px;">{{ $workOrder->started_at->format('M d, Y g:i A') }}</p>
        </div>
        @endif
        @if($workOrder->completed_at)
        <div>
            <p style="margin:0; font-size:12px; color:#6b7280;">Completed</p>
            <p style="margin:4px 0 0; font-size:14px;">{{ $workOrder->completed_at->format('M d, Y g:i A') }}</p>
        </div>
        @endif
    </div>
</div>

@endsection
