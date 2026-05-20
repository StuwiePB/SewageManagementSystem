@extends('r_admin.layout')

@section('content')

<header class="header">
    <div>
        <h1>Archived Work Orders</h1>
        <p>Super Admin archive and restore center</p>
    </div>
    <a href="{{ route('admin.work-orders.index') }}" class="btn btn-secondary">Back to Active Work Orders</a>
</header>

<section class="card card-mb" aria-label="Filters">
    <form method="GET" action="{{ route('admin.work-orders.archived') }}" class="grid-filters">
        <div class="form-group flex-grow" style="min-width: 160px;">
            <label class="form-label" for="status">Status</label>
            <select id="status" name="status" class="form-control" style="min-width: 160px;">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="assigned" {{ request('status') == 'assigned' ? 'selected' : '' }}>Assigned</option>
                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="on_the_way" {{ request('status') == 'on_the_way' ? 'selected' : '' }}>On the Way</option>
                <option value="on_site" {{ request('status') == 'on_site' ? 'selected' : '' }}>On Site</option>
                <option value="pending_approval" {{ request('status') == 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
        <div class="form-group flex-grow" style="min-width: 140px;">
            <label class="form-label" for="priority">Priority</label>
            <select id="priority" name="priority" class="form-control" style="min-width: 140px;">
                <option value="">All Priorities</option>
                <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                <option value="critical" {{ request('priority') == 'critical' ? 'selected' : '' }}>Critical</option>
            </select>
        </div>
        <div class="form-group">
            <button type="submit" class="btn-submit">Filter</button>
        </div>
    </form>
</section>

<section class="card" aria-label="Archived work orders list">
    <div class="table-wrap">
        <table class="ops-table">
            <thead>
                <tr>
                    <th>Work Order #</th>
                    <th>District</th>
                    <th>Location</th>
                    <th>Type</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Archived At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($workOrders as $workOrder)
                    <tr>
                        <td>{{ $workOrder->work_order_number }}</td>
                        <td>{{ $workOrder->district_display ?? '—' }}</td>
                        <td>{{ $workOrder->location_address }}</td>
                        <td>{{ $workOrder->type }}</td>
                        <td>
                            <span class="badge badge-{{ $workOrder->priority === 'critical' ? 'critical' : ($workOrder->priority === 'high' ? 'high' : ($workOrder->priority === 'medium' ? 'medium' : 'low')) }}">
                                {{ ucfirst($workOrder->priority) }}
                            </span>
                        </td>
                        <td>
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
                        </td>
                        <td>{{ optional($workOrder->deleted_at)->format('M d, Y g:i A') ?? '—' }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.work-orders.restore', $workOrder->id) }}" style="display:inline;" onsubmit="return confirm('Restore this work order back to active list?');">
                                @csrf
                                <button type="submit" class="link-primary" style="background:none; border:none; color:#56FF8B; cursor:pointer; padding:0;">Restore</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="cell-muted">No archived work orders found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($workOrders->hasPages())
        <div class="pagination-bar">
            <span class="pagination-info">Showing {{ $workOrders->firstItem() }}–{{ $workOrders->lastItem() }} of {{ $workOrders->total() }}</span>
            <div class="pagination-btns">
                @if($workOrders->onFirstPage())
                    <span class="btn-page disabled">Previous</span>
                @else
                    <a href="{{ $workOrders->previousPageUrl() }}" class="btn-page">Previous</a>
                @endif
                @if($workOrders->hasMorePages())
                    <a href="{{ $workOrders->nextPageUrl() }}" class="btn-page">Next</a>
                @else
                    <span class="btn-page disabled">Next</span>
                @endif
            </div>
        </div>
    @endif
</section>

@endsection
