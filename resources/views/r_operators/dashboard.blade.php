@extends('r_operators.layouts.app')

@section('content')

<header class="topbar">
    <div>
        <h1>Operations Dashboard</h1>
        <p>Public Works Department • Real-time monitoring and management</p>
    </div>
</header>

<section class="card-grid" aria-label="Summary statistics">
    <div class="card">
        <p>Active Incidents</p>
        <h3>{{ $activeIncidents ?? 0 }}</h3>
        @if(isset($incidentChange) && $incidentChange)
            <p class="card-meta badge-change">{{ $incidentChange }}</p>
        @endif
    </div>

    <div class="card">
        <p>Pending Work Orders</p>
        <h3>{{ count($workOrders ?? []) }}</h3>
        <p class="card-meta">Active assignments</p>
    </div>

    <div class="card">
        <p>Reports Today</p>
        <h3>{{ $reportsSentToday ?? 0 }}</h3>
        <p class="card-meta">Sent from admin today</p>
    </div>
</section>

<section class="grid-2col" aria-label="Trend and recent activity">
    <div class="card">
        <h3 class="section-head">Incidents Trend (Last 7 Days)</h3>
        <p class="section-desc">Daily incident reports</p>
        <canvas id="incidentsChart" style="max-height:300px;"></canvas>
    </div>

    <div class="card">
        <h3 class="section-head">Recent Incidents</h3>
        <p class="section-desc">Latest reports</p>
        <ul class="list-unstyled">
            @forelse ($recentIncidents ?? [] as $incident)
                <li class="list-item">
                    <strong>{{ $incident['title'] }}</strong>
                    <small>{{ $incident['status'] }} • {{ $incident['time'] }}</small>
                </li>
            @empty
                <li class="list-item"><span class="cell-muted">No incidents available</span></li>
            @endforelse
        </ul>
    </div>
</section>

<section class="card" aria-label="Pending work orders">
    <div class="card-header">
        <div>
            <h3 class="card-title">Pending Work Orders</h3>
            <p class="card-subtitle">Active assignments requiring attention</p>
        </div>
        <a href="{{ route('operations.work-orders.index') }}" class="link-primary">View All</a>
    </div>

    <div class="table-wrap">
        <table class="ops-table">
            <thead>
                <tr>
                    <th>Work Order ID</th>
                    <th>Location</th>
                    <th>Type</th>
                    <th>Priority</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($workOrders ?? [] as $order)
                    <tr>
                        <td>
                            <a href="{{ route('operations.work-orders.index') }}" class="link-primary">{{ $order['id'] }}</a>
                        </td>
                        <td>{{ $order['location'] }}</td>
                        <td>{{ $order['type'] }}</td>
                        <td>
                            <span class="badge badge-{{ strtolower($order['priority']) === 'critical' ? 'critical' : (strtolower($order['priority']) === 'high' ? 'high' : (strtolower($order['priority']) === 'medium' ? 'medium' : 'low')) }}">
                                {{ $order['priority'] }}
                            </span>
                        </td>
                        <td><span class="badge badge-status">{{ $order['status'] }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="cell-muted">No active work orders</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    const ctx = document.getElementById('incidentsChart');
    const chartLabels = @json($chartLabels ?? []);
    const chartData = @json($chartData ?? []);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'Incidents',
                data: chartData,
                borderColor: '#6A96FF',
                backgroundColor: 'rgba(106, 150, 255, 0.12)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: {
                x: {
                    ticks: { color: '#B0B0B0' },
                    grid: { color: 'rgba(106, 150, 255, 0.08)' }
                },
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, color: '#B0B0B0' },
                    grid: { color: 'rgba(106, 150, 255, 0.08)' }
                }
            }
        }
    });
</script>

@endsection
