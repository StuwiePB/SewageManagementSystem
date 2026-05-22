@extends('operations.layouts.app')

@section('content')

<div class="topbar">
    <div>
        <h1>Operations Dashboard</h1>
        <p>Public Works Department • Real-time monitoring and management</p>
    </div>
</div>

{{-- Summary Cards --}}
<div class="card-grid">
    <div class="card">
        <p>Active Incidents</p>
        <h3>{{ $activeIncidents ?? '—' }}</h3>
        <p style="color:#ef4444; margin-top:6px;">
            {{ $incidentChange ?? '' }}
        </p>
    </div>

    <div class="card">
        <p>Crews Available</p>
        <h3>{{ $crewsAvailable ?? '—' }}</h3>
        <p style="margin-top:6px;">
            {{ $crewsStatus ?? '' }}
        </p>
    </div>
</div>

{{-- Middle Section --}}
<div style="display:grid; grid-template-columns: 2fr 1fr; gap:20px; margin-bottom:32px;">

    {{-- GIS Overview --}}
    <div class="card">
        <h3>Sewage Network Overview</h3>
        <p style="color:#6b7280;">Interactive GIS map</p>

        <div style="
            margin-top:16px;
            height:220px;
            background:#f3f4f6;
            border-radius:10px;
            display:flex;
            align-items:center;
            justify-content:center;
            color:#9ca3af;
        ">
            GIS Map Placeholder
        </div>
    </div>

    {{-- Recent Incidents --}}
    <div class="card">
        <h3>Recent Incidents</h3>

        <ul style="list-style:none; padding:0; margin-top:16px;">
            @forelse ($recentIncidents ?? [] as $incident)
                <li style="margin-bottom:16px;">
                    <strong>{{ $incident['title'] }}</strong><br>
                    <small style="color:#6b7280;">
                        {{ $incident['status'] }} • {{ $incident['time'] }}
                    </small>
                </li>
            @empty
                <p style="color:#9ca3af;">No incidents available</p>
            @endforelse
        </ul>
    </div>

</div>

{{-- Active Work Orders --}}
<div class="card">
    <h3>Active Work Orders</h3>

    <table style="width:100%; border-collapse:collapse; margin-top:16px;">
        <thead>
            <tr style="text-align:left; border-bottom:1px solid #e5e7eb;">
                <th>Work Order ID</th>
                <th>Location</th>
                <th>Type</th>
                <th>Priority</th>
                <th>Assigned Crew</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($workOrders ?? [] as $order)
                <tr style="border-bottom:1px solid #f3f4f6;">
                    <td>{{ $order['id'] }}</td>
                    <td>{{ $order['location'] }}</td>
                    <td>{{ $order['type'] }}</td>
                    <td>{{ $order['priority'] }}</td>
                    <td>{{ $order['crew'] }}</td>
                    <td>{{ $order['status'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="padding:16px; color:#9ca3af;">
                        No active work orders
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
