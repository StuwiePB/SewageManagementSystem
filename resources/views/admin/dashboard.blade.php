<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Control - Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #0f0f1a; color: #e2e8f0; display: flex; min-height: 100vh; }
        
        /* Sidebar */
        .sidebar { width: 280px; background: linear-gradient(180deg, #1a1a2e 0%, #16162a 100%); border-right: 1px solid rgba(139, 92, 246, 0.1); display: flex; flex-direction: column; }
        .sidebar-header { padding: 2rem 1.5rem; text-align: center; border-bottom: 1px solid rgba(139, 92, 246, 0.15); }
        .sidebar-header i { font-size: 2.5rem; color: #8b5cf6; margin-bottom: 0.75rem; display: block; }
        .sidebar-header h2 { font-size: 1.25rem; font-weight: 700; color: #fff; margin-bottom: 0.25rem; }
        .sidebar-header p { font-size: 0.875rem; color: #94a3b8; }
        .sidebar-nav { flex: 1; padding: 1.5rem 0; }
        .nav-item { display: flex; align-items: center; gap: 0.875rem; padding: 0.875rem 1.5rem; color: #e2e8f0; font-weight: 500; font-size: 0.9375rem; transition: all 0.2s; cursor: pointer; border-left: 3px solid transparent; text-decoration: none; }
        .nav-item:hover { background: rgba(139, 92, 246, 0.1); color: #fff; }
        .nav-item.active { background: #8b5cf6; color: #fff; border-left-color: #a78bfa; }
        .nav-item i { width: 20px; text-align: center; }
        .sidebar-footer { padding: 1.5rem; border-top: 1px solid rgba(139, 92, 246, 0.15); }
        .sidebar-footer .nav-item { padding: 0.75rem 1rem; }
        
        /* Main Content */
        .main-content { flex: 1; padding: 2rem; overflow-y: auto; }
        .header { margin-bottom: 2rem; }
        .header h1 { font-size: 2rem; font-weight: 800; color: #fff; margin-bottom: 0.5rem; }
        .header p { color: #94a3b8; font-size: 0.9375rem; }
        
        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: #1e1e2f; border: 1px solid rgba(139, 92, 246, 0.2); border-radius: 12px; padding: 1.5rem; display: flex; align-items: center; justify-content: space-between; transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-2px); }
        .stat-content h3 { font-size: 0.875rem; color: #94a3b8; font-weight: 500; margin-bottom: 0.5rem; }
        .stat-content .value { font-size: 2rem; font-weight: 700; color: #fff; margin-bottom: 0.25rem; }
        .stat-content .change { font-size: 0.8125rem; font-weight: 500; }
        .change.positive { color: #10b981; }
        .change.negative { color: #ef4444; }
        .stat-icon { width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; }
        .stat-icon.blue { background: rgba(59, 130, 246, 0.15); color: #60a5fa; }
        .stat-icon.green { background: rgba(16, 185, 129, 0.15); color: #34d399; }
        .stat-icon.purple { background: rgba(139, 92, 246, 0.15); color: #a78bfa; }
        .stat-icon.red { background: rgba(239, 68, 68, 0.15); color: #f87171; }
        
        /* Charts */
        .charts-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 2rem; }
        .chart-card { background: #1e1e2f; border: 1px solid rgba(139, 92, 246, 0.2); border-radius: 12px; padding: 1.5rem; }
        .chart-card h3 { font-size: 1.125rem; font-weight: 700; color: #fff; margin-bottom: 1.5rem; }
        canvas { max-height: 300px; }
        
        @media (max-width: 1024px) {
            .charts-grid { grid-template-columns: 1fr; }
            .sidebar { width: 240px; }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-shield-halved"></i>
            <h2>Admin Control</h2>
            <p>Super Administrator</p>
        </div>
        <nav class="sidebar-nav">
            <a href="{{ route('admin.dashboard') }}" class="nav-item active">
                <i class="fas fa-th-large"></i>
                <span>Overview</span>
            </a>
            <a href="{{ route('admin.crew-management') }}" class="nav-item">
                <i class="fas fa-users-cog"></i>
                <span>Crew Management</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="{{ route('login') }}" class="nav-item">
                <i class="fas fa-arrow-right-from-bracket"></i>
                <span>Sign Out</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="header">
            <h1>System Overview</h1>
            <p>Complete control and monitoring of all system operations</p>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-content">
                    <h3>Total Reports</h3>
                    <div class="value">{{ $totalReports }}</div>
                    <div class="change positive">All customer reports</div>
                </div>
                <div class="stat-icon blue">
                    <i class="fas fa-file-alt"></i>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-content">
                    <h3>Reports Today</h3>
                    <div class="value">{{ $reportsToday }}</div>
                    <div class="change {{ $reportsToday > 0 ? 'positive' : '' }}">Submitted today</div>
                </div>
                <div class="stat-icon green">
                    <i class="fas fa-calendar-day"></i>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-content">
                    <h3>Resolved</h3>
                    <div class="value">{{ $resolvedReports }}</div>
                    <div class="change positive">Completed cases</div>
                </div>
                <div class="stat-icon purple">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-content">
                    <h3>Work in Progress</h3>
                    <div class="value">{{ $workInProgress }}</div>
                    <div class="change {{ $workInProgress > 0 ? 'negative' : 'positive' }}">Pending review</div>
                </div>
                <div class="stat-icon red">
                    <i class="fas fa-spinner"></i>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="charts-grid">
            <div class="chart-card">
                <h3>Activity Trend</h3>
                <canvas id="activityChart"></canvas>
            </div>
            <div class="chart-card">
                <h3>Status Distribution</h3>
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </main>

    <script>
        // Activity Trend Chart
        const activityCtx = document.getElementById('activityChart').getContext('2d');
        new Chart(activityCtx, {
            type: 'line',
            data: {
                labels: @json($months),
                datasets: [
                    {
                        label: 'reports',
                        data: @json($reportsData),
                        borderColor: '#8b5cf6',
                        backgroundColor: 'rgba(139, 92, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'maintenance',
                        data: @json($maintenanceData),
                        borderColor: '#ec4899',
                        backgroundColor: 'rgba(236, 72, 153, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'resolved',
                        data: @json($resolvedData),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { 
                        display: true, 
                        position: 'bottom',
                        labels: { color: '#94a3b8', padding: 15, font: { size: 12 } }
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { color: 'rgba(139, 92, 246, 0.1)' },
                        ticks: { color: '#94a3b8' }
                    },
                    x: { 
                        grid: { display: false },
                        ticks: { color: '#94a3b8' }
                    }
                }
            }
        });

        // Status Distribution Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: ['Resolved', 'In Progress', 'Pending'],
                datasets: [{
                    data: [{{ $resolvedPercentage }}, {{ $inProgressPercentage }}, {{ $pendingPercentage }}],
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { 
                        display: true, 
                        position: 'bottom',
                        labels: { color: '#94a3b8', padding: 12, font: { size: 12 } }
                    }
                }
            }
        });
    </script>
</body>
</html>
