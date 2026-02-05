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
        <h3>{{ $activeIncidents ?? 0 }}</h3>
        @if(isset($incidentChange) && $incidentChange)
            <p style="color:#ef4444; margin-top:6px; font-size:12px;">
                {{ $incidentChange }}
            </p>
        @endif
    </div>

    <div class="card">
        <p>Crews Available</p>
        <h3>{{ $crewsAvailable ?? 0 }}</h3>
        <p style="margin-top:6px; font-size:12px; color:#6b7280;">
            {{ $crewsStatus ?? '0 on-site' }}
        </p>
    </div>

    <div class="card">
        <p>Pending Work Orders</p>
        <h3>{{ count($workOrders ?? []) }}</h3>
        <p style="margin-top:6px; font-size:12px; color:#6b7280;">
            Active assignments
        </p>
    </div>

    <div class="card">
        <p>Reports Today</p>
        <h3>{{ \App\Models\Report::whereDate('created_at', today())->count() }}</h3>
        <p style="margin-top:6px; font-size:12px; color:#6b7280;">
            New submissions
        </p>
    </div>
</div>

{{-- Middle Section --}}
<div style="display:grid; grid-template-columns: 2fr 1fr; gap:20px; margin-bottom:32px;">

    {{-- Stats Graph --}}
    <div class="card">
        <h3 style="margin:0 0 8px;">Incidents Trend (Last 7 Days)</h3>
        <p style="color:#6b7280; margin:0 0 16px; font-size:14px;">Daily incident reports</p>
        <canvas id="incidentsChart" style="max-height:300px;"></canvas>
    </div>

    {{-- Recent Incidents --}}
    <div class="card">
        <h3 style="margin:0 0 8px;">Recent Incidents</h3>
        <p style="color:#6b7280; margin:0 0 16px; font-size:14px;">Latest reports</p>

        <ul style="list-style:none; padding:0; margin:0;">
            @forelse ($recentIncidents ?? [] as $incident)
                <li style="margin-bottom:16px; padding-bottom:16px; border-bottom:1px solid #f3f4f6;">
                    <strong style="display:block; margin-bottom:4px;">{{ $incident['title'] }}</strong>
                    <small style="color:#6b7280; font-size:12px;">
                        {{ $incident['status'] }} • {{ $incident['time'] }}
                    </small>
                </li>
            @empty
                <p style="color:#9ca3af; margin:0;">No incidents available</p>
            @endforelse
        </ul>
    </div>

</div>

{{-- Pending Work Orders --}}
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <div>
            <h3 style="margin:0 0 4px;">Pending Work Orders</h3>
            <p style="color:#6b7280; margin:0; font-size:14px;">Active assignments requiring attention</p>
        </div>
        <a href="{{ route('operations.work-orders.index') }}" style="color:#0056A6; text-decoration:none; font-weight:500;">View All →</a>
    </div>

    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="text-align:left; border-bottom:2px solid #e5e7eb;">
                    <th style="padding:12px 8px; font-size:13px; color:#6b7280; font-weight:600;">Work Order ID</th>
                    <th style="padding:12px 8px; font-size:13px; color:#6b7280; font-weight:600;">Location</th>
                    <th style="padding:12px 8px; font-size:13px; color:#6b7280; font-weight:600;">Type</th>
                    <th style="padding:12px 8px; font-size:13px; color:#6b7280; font-weight:600;">Priority</th>
                    <th style="padding:12px 8px; font-size:13px; color:#6b7280; font-weight:600;">Assigned Crew</th>
                    <th style="padding:12px 8px; font-size:13px; color:#6b7280; font-weight:600;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($workOrders ?? [] as $order)
                    <tr style="border-bottom:1px solid #f3f4f6;">
                        <td style="padding:12px 8px; font-size:14px;">
                            <a href="{{ route('operations.work-orders.index') }}" style="color:#0056A6; text-decoration:none;">{{ $order['id'] }}</a>
                        </td>
                        <td style="padding:12px 8px; font-size:14px;">{{ $order['location'] }}</td>
                        <td style="padding:12px 8px; font-size:14px;">{{ $order['type'] }}</td>
                        <td style="padding:12px 8px;">
                            <span style="
                                padding:4px 8px;
                                border-radius:4px;
                                font-size:12px;
                                font-weight:500;
                                background:{{ $order['priority'] == 'Critical' ? '#fee2e2' : ($order['priority'] == 'High' ? '#fed7aa' : ($order['priority'] == 'Medium' ? '#fef3c7' : '#dcfce7')) }};
                                color:{{ $order['priority'] == 'Critical' ? '#991b1b' : ($order['priority'] == 'High' ? '#9a3412' : ($order['priority'] == 'Medium' ? '#854d0e' : '#166534')) }};
                            ">
                                {{ $order['priority'] }}
                            </span>
                        </td>
                        <td style="padding:12px 8px; font-size:14px;">{{ $order['crew'] }}</td>
                        <td style="padding:12px 8px;">
                            <span style="
                                padding:4px 8px;
                                border-radius:4px;
                                font-size:12px;
                                font-weight:500;
                                background:#e0e7ff;
                                color:#3730a3;
                            ">
                                {{ $order['status'] }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:24px; text-align:center; color:#9ca3af;">
                            No active work orders
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
    // Chart.js configuration - Simple and easy to explain
    const ctx = document.getElementById('incidentsChart');
    
    // Chart data from Laravel backend
    const chartLabels = @json($chartLabels ?? []);
    const chartData = @json($chartData ?? []);

    // Create the chart
    new Chart(ctx, {
        type: 'line',  // Line chart type
        data: {
            labels: chartLabels,  // X-axis: dates
            datasets: [{
                label: 'Incidents',
                data: chartData,  // Y-axis: incident counts
                borderColor: '#0056A6',  // Primary color for the line
                backgroundColor: 'rgba(0, 86, 166, 0.1)',  // Light fill under line
                borderWidth: 2,
                fill: true,  // Fill area under the line
                tension: 0.4  // Smooth curve
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false  // Hide legend for simplicity
                }
            },
            scales: {
                y: {
                    beginAtZero: true,  // Start Y-axis at 0
                    ticks: {
                        stepSize: 1  // Show whole numbers only
                    }
                }
            }
        }
    });
</script>

@endsection
