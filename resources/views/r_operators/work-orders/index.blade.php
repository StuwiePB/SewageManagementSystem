@extends('r_operators.layouts.app')

@section('content')

<header class="topbar">
    <div>
        <h1>Work Orders</h1>
        <p>Manage and track work orders (created by admin)</p>
    </div>
</header>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom: 1rem;">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert" style="margin-bottom: 1rem; background: rgba(255, 91, 91, 0.15); color: #ffb4b4; border: 1px solid rgba(255, 91, 91, 0.35);">{{ session('error') }}</div>
@endif

<section class="card card-mb" aria-label="Filters">
    <form method="GET" action="{{ route('operations.work-orders.index') }}" class="grid-filters">
        <div class="form-group">
            <label class="form-label" for="status">Status</label>
            <select id="status" name="status" class="form-control">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="assigned" {{ request('status') == 'assigned' ? 'selected' : '' }}>Assigned</option>
                <option value="on_site" {{ request('status') == 'on_site' ? 'selected' : '' }}>On Site</option>
                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Complete</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label" for="priority">Priority</label>
            <select id="priority" name="priority" class="form-control">
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

<section class="card" aria-label="Work orders list">
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
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($workOrders as $workOrder)
                    <tr>
                        <td>
                            <a href="{{ route('operations.work-orders.show', $workOrder) }}" class="link-primary">{{ $workOrder->work_order_number }}</a>
                        </td>
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
                        <td>
                            <div style="display:flex; align-items:center; gap:0.65rem;">
                                <a href="{{ route('operations.work-orders.show', $workOrder) }}" class="link-primary">View</a>
                                <form method="POST" action="{{ route('operations.work-orders.destroy', $workOrder) }}" style="display:inline;" onsubmit="return confirm('Delete this work order and linked customer report? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="link-primary" style="background:none; border:none; color:#ff8f8f; cursor:pointer; padding:0;">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="cell-muted">No work orders found.</td>
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
                    <span class="btn-page disabled"><svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Previous</span>
                @else
                    <a href="{{ $workOrders->previousPageUrl() }}" class="btn-page"><svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Previous</a>
                @endif
                @if($workOrders->hasMorePages())
                    <a href="{{ $workOrders->nextPageUrl() }}" class="btn-page">Next <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg></a>
                @else
                    <span class="btn-page disabled">Next <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg></span>
                @endif
            </div>
        </div>
    @endif
</section>

@endsection
