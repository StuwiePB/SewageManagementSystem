@extends('operations.layouts.app')

@section('content')

<div class="topbar">
    <div>
        <h1>Work Orders</h1>
        <p>Manage and track work orders</p>
    </div>
    <a href="{{ route('operations.work-orders.create') }}" style="
        background:#0056A6;
        color:white;
        padding:10px 20px;
        border-radius:8px;
        text-decoration:none;
        font-weight:500;
        display:inline-block;
    ">+ Create Work Order</a>
</div>

{{-- Filters --}}
<div class="card" style="margin-bottom:20px;">
    <form method="GET" action="{{ route('operations.work-orders.index') }}" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:16px; align-items:end;">
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
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="assigned" {{ request('status') == 'assigned' ? 'selected' : '' }}>Assigned</option>
                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
        <div>
            <label style="display:block; margin-bottom:6px; font-size:13px; color:#6b7280; font-weight:500;">Priority</label>
            <select name="priority" style="
                width:100%;
                padding:8px 12px;
                border:1px solid #d1d5db;
                border-radius:8px;
                font-size:14px;
            ">
                <option value="">All Priorities</option>
                <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                <option value="critical" {{ request('priority') == 'critical' ? 'selected' : '' }}>Critical</option>
            </select>
        </div>
        <div>
            <label style="display:block; margin-bottom:6px; font-size:13px; color:#6b7280; font-weight:500;">Crew</label>
            <select name="crew_id" style="
                width:100%;
                padding:8px 12px;
                border:1px solid #d1d5db;
                border-radius:8px;
                font-size:14px;
            ">
                <option value="">All Crews</option>
                @foreach($crews as $crew)
                    <option value="{{ $crew->id }}" {{ request('crew_id') == $crew->id ? 'selected' : '' }}>{{ $crew->name }}</option>
                @endforeach
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

{{-- Work Orders Table --}}
<div class="card">
    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="text-align:left; border-bottom:2px solid #e5e7eb;">
                <th style="padding:12px 8px; font-size:13px; color:#6b7280; font-weight:600;">Work Order #</th>
                <th style="padding:12px 8px; font-size:13px; color:#6b7280; font-weight:600;">Location</th>
                <th style="padding:12px 8px; font-size:13px; color:#6b7280; font-weight:600;">Type</th>
                <th style="padding:12px 8px; font-size:13px; color:#6b7280; font-weight:600;">Priority</th>
                <th style="padding:12px 8px; font-size:13px; color:#6b7280; font-weight:600;">Crew</th>
                <th style="padding:12px 8px; font-size:13px; color:#6b7280; font-weight:600;">Status</th>
                <th style="padding:12px 8px; font-size:13px; color:#6b7280; font-weight:600;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($workOrders as $workOrder)
                <tr style="border-bottom:1px solid #f3f4f6;">
                    <td style="padding:12px 8px; font-size:14px;">
                        <a href="{{ route('operations.work-orders.show', $workOrder) }}" style="color:#0056A6; text-decoration:none; font-weight:500;">
                            {{ $workOrder->work_order_number }}
                        </a>
                    </td>
                    <td style="padding:12px 8px; font-size:14px;">{{ $workOrder->location_address }}</td>
                    <td style="padding:12px 8px; font-size:14px;">{{ $workOrder->type }}</td>
                    <td style="padding:12px 8px;">
                        <span style="
                            padding:4px 8px;
                            border-radius:4px;
                            font-size:12px;
                            font-weight:500;
                            background:{{ $workOrder->priority == 'critical' ? '#fee2e2' : ($workOrder->priority == 'high' ? '#fed7aa' : ($workOrder->priority == 'medium' ? '#fef3c7' : '#dcfce7')) }};
                            color:{{ $workOrder->priority == 'critical' ? '#991b1b' : ($workOrder->priority == 'high' ? '#9a3412' : ($workOrder->priority == 'medium' ? '#854d0e' : '#166534')) }};
                        ">
                            {{ ucfirst($workOrder->priority) }}
                        </span>
                    </td>
                    <td style="padding:12px 8px; font-size:14px;">
                        @if($workOrder->crew)
                            <a href="{{ route('operations.crews.show', $workOrder->crew) }}" style="color:#0077CC; text-decoration:none;">
                                {{ $workOrder->crew->name }}
                            </a>
                        @else
                            <span style="color:#9ca3af;">Unassigned</span>
                        @endif
                    </td>
                    <td style="padding:12px 8px;">
                        <span style="
                            padding:4px 8px;
                            border-radius:4px;
                            font-size:12px;
                            font-weight:500;
                            background:#e0e7ff;
                            color:#3730a3;
                        ">
                            {{ ucfirst(str_replace('_', ' ', $workOrder->status)) }}
                        </span>
                    </td>
                    <td style="padding:12px 8px;">
                        <a href="{{ route('operations.work-orders.show', $workOrder) }}" style="
                            color:#0056A6;
                            text-decoration:none;
                            font-size:13px;
                        ">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="padding:24px; text-align:center; color:#9ca3af;">
                        No work orders found. <a href="{{ route('operations.work-orders.create') }}" style="color:#0056A6;">Create your first work order</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination --}}
    @if($workOrders->hasPages())
        <div style="margin-top:20px; padding-top:20px; border-top:1px solid #e5e7eb;">
            {{ $workOrders->links() }}
        </div>
    @endif
</div>

@endsection
