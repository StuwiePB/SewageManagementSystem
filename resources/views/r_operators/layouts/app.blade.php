<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DMS Ops - Operations Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-primary: #1A1D2B;
            --bg-secondary: #272B3C;
            --text-primary: #FFFFFF;
            --text-secondary: #B0B0B0;
            --accent-blue: #6A96FF;
            --accent-red: #FF5B5B;
            --accent-green: #56FF8B;
            --accent-purple: #A86AFF;
            --border-subtle: rgba(106, 150, 255, 0.15);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
        }

        .layout { display: flex; min-height: 100vh; }

        .sidebar {
            width: 280px;
            background: var(--bg-secondary);
            border-right: 1px solid var(--border-subtle);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .sidebar-header {
            padding: 2rem 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(106, 150, 255, 0.2);
        }
        .sidebar-header i { font-size: 2.5rem; color: var(--accent-blue); margin-bottom: 0.75rem; display: block; }
        .sidebar-header h2 { font-size: 1.25rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.25rem; }
        .sidebar-header p { font-size: 0.875rem; color: var(--text-secondary); }

        .sidebar .layout-inner {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }
        .sidebar .nav { flex: 1; padding: 1.5rem 0; }
        .nav a {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            padding: 0.875rem 1.5rem;
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.9375rem;
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        .nav a:hover {
            background: rgba(106, 150, 255, 0.1);
            color: var(--text-primary);
        }
        .nav a.active {
            background: rgba(106, 150, 255, 0.2);
            color: var(--accent-blue);
            border-left-color: var(--accent-blue);
        }
        .nav a i { width: 20px; text-align: center; }

        .sidebar-footer {
            padding: 1.5rem;
            border-top: 1px solid var(--border-subtle);
        }
        .sidebar-footer form { width: 100%; }
        .sidebar-footer .btn-logout {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            width: 100%;
            padding: 0.875rem 1.5rem;
            background: none;
            border: none;
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.9375rem;
            font-family: inherit;
            cursor: pointer;
            text-align: left;
            border-radius: 0;
            border-left: 3px solid transparent;
            transition: all 0.2s;
        }
        .sidebar-footer .btn-logout:hover {
            background: rgba(255, 91, 91, 0.12);
            color: var(--accent-red);
        }

        .main {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
            background: var(--bg-primary);
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .topbar h1 {
            margin: 0;
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-primary);
        }
        .topbar > div > p {
            margin: 0.5rem 0 0;
            font-size: 0.9375rem;
            color: var(--text-secondary);
        }
        .topbar a { font-size: 0.875rem; }

        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }
        .card {
            background: var(--bg-secondary);
            border: 1px solid var(--border-subtle);
            border-radius: 12px;
            padding: 1.25rem;
        }
        .card p {
            margin: 0;
            font-size: 0.8125rem;
            color: var(--text-secondary);
        }
        .card h3 {
            margin: 8px 0 0;
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: opacity 0.2s;
            font-size: 0.875rem;
        }
        .btn-primary { background: var(--accent-blue); color: white; }
        .btn-primary:hover { opacity: 0.9; }
        .btn-secondary { background: rgba(106, 150, 255, 0.2); color: var(--accent-blue); }
        .btn-secondary:hover { opacity: 0.9; }

        input[type="text"],
        input[type="email"],
        input[type="number"],
        input[type="tel"],
        input[type="file"],
        input[type="date"],
        input[type="datetime-local"],
        select,
        textarea {
            font-family: inherit;
            background: var(--bg-primary);
            border: 1px solid rgba(106, 150, 255, 0.3);
            color: var(--text-primary);
        }
        input::placeholder, textarea::placeholder { color: var(--text-secondary); opacity: 0.6; }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="number"]:focus,
        input[type="tel"]:focus,
        input[type="date"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(106, 150, 255, 0.2);
        }

        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9375rem; }
        .alert-success { background: rgba(86, 255, 139, 0.15); color: var(--accent-green); border: 1px solid rgba(86, 255, 139, 0.35); }
        .alert-error { background: rgba(255, 91, 91, 0.15); color: var(--accent-red); border: 1px solid rgba(255, 91, 91, 0.35); }

        .grid-2col { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 32px; }
        .grid-filters { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; align-items: end; }
        .grid-auto { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 16px; }

        .card-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; }
        .card-title { margin: 0 0 4px; font-size: 18px; font-weight: 700; color: var(--text-primary); }
        .card-subtitle { color: var(--text-secondary); margin: 0; font-size: 14px; }
        .card-meta { margin-top: 6px; font-size: 12px; color: var(--text-secondary); }
        .card-mb { margin-bottom: 20px; }

        .form-group { margin-bottom: 0; }
        .form-label { display: block; margin-bottom: 6px; font-size: 13px; color: var(--text-secondary); font-weight: 500; }
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(106, 150, 255, 0.2);
        }
        .btn-submit {
            background: var(--accent-blue);
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            width: 100%;
            font-size: 14px;
        }
        .btn-submit:hover { opacity: 0.9; }

        .table-wrap { overflow-x: auto; }
        .ops-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .ops-table th {
            text-align: left;
            padding: 12px 8px;
            font-size: 13px;
            color: var(--text-secondary);
            font-weight: 600;
            border-bottom: 1px solid rgba(106, 150, 255, 0.15);
        }
        .ops-table td {
            padding: 12px 8px;
            border-bottom: 1px solid rgba(106, 150, 255, 0.08);
            color: var(--text-primary);
        }
        .ops-table tbody tr:last-child td { border-bottom: none; }
        .ops-table .cell-muted { color: var(--text-secondary); text-align: center; padding: 24px; }

        .badge { display: inline-block; padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 500; }
        .badge-critical { background: rgba(255, 91, 91, 0.25); color: #ff8888; }
        .badge-high { background: rgba(255, 165, 0, 0.25); color: #FFA500; }
        .badge-medium { background: rgba(255, 165, 0, 0.2); color: #FFA500; }
        .badge-low { background: rgba(106, 150, 255, 0.2); color: var(--accent-blue); }
        .badge-status { background: rgba(106, 150, 255, 0.2); color: var(--accent-blue); }
        .badge-status-amber { background: rgba(255, 165, 0, 0.25); color: #FFA500; }
        .badge-status-teal { background: rgba(34, 211, 238, 0.25); color: #22d3ee; }
        .badge-status-green { background: rgba(86, 255, 139, 0.2); color: var(--accent-green); }
        .badge-status-red { background: rgba(255, 91, 91, 0.25); color: var(--accent-red); }
        .badge-report-new { background: rgba(106, 150, 255, 0.2); color: var(--accent-blue); }
        .badge-report-progress { background: rgba(255, 165, 0, 0.2); color: #FFA500; }
        .badge-report-resolved { background: rgba(86, 255, 139, 0.2); color: var(--accent-green); }
        .badge-report-closed { background: rgba(106, 150, 255, 0.1); color: var(--text-secondary); }
        .badge-change { color: var(--accent-red); margin-top: 6px; font-size: 12px; }

        .link-primary { color: var(--accent-blue); text-decoration: none; font-weight: 500; }
        .link-primary:hover { text-decoration: underline; }

        .list-unstyled { list-style: none; padding: 0; margin: 0; }
        .list-item { margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid rgba(106, 150, 255, 0.1); }
        .list-item:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
        .list-item strong { display: block; margin-bottom: 4px; color: var(--text-primary); }
        .list-item small { color: var(--text-secondary); font-size: 12px; }

        .pagination-bar {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(106, 150, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }
        .pagination-info { font-size: 13px; color: var(--text-secondary); }
        .pagination-btns { display: flex; gap: 8px; }
        .btn-page {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            font-size: 13px;
            border-radius: 8px;
            border: 1px solid rgba(106, 150, 255, 0.25);
            text-decoration: none;
            font-weight: 500;
            color: var(--accent-blue);
            background: rgba(106, 150, 255, 0.1);
            cursor: pointer;
        }
        .btn-page:hover:not(.disabled) { background: rgba(106, 150, 255, 0.2); }
        .btn-page:disabled, .btn-page.disabled {
            color: var(--text-secondary);
            opacity: 0.5;
            cursor: default;
            pointer-events: none;
        }
        .btn-page.disabled svg { opacity: 0.6; }

        .section-head { margin: 0 0 8px; font-size: 16px; font-weight: 700; color: var(--text-primary); }
        .section-desc { color: var(--text-secondary); margin: 0 0 16px; font-size: 14px; }

        .detail-row { margin-bottom: 16px; }
        .detail-row:last-child { margin-bottom: 0; }
        .info-label { margin: 0; font-size: 12px; color: var(--text-secondary); }
        .info-value { margin: 4px 0 0; font-size: 16px; font-weight: 500; color: var(--text-primary); }
        .info-value-muted { margin: 4px 0 0; font-size: 14px; color: var(--text-secondary); }
        .info-value-small { margin: 4px 0 0; font-size: 13px; color: var(--text-secondary); }
        .btn-edit {
            display: inline-flex;
            align-items: center;
            padding: 6px 14px;
            background: rgba(106, 150, 255, 0.2);
            color: var(--accent-blue);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            border-radius: 8px;
        }
        .btn-edit:hover { background: rgba(106, 150, 255, 0.3); color: var(--accent-blue); }

        .card-map { padding: 0; overflow: hidden; background: var(--bg-secondary); border: 1px solid var(--border-subtle); border-radius: 12px; }
        .map-legend {
            padding: 10px 16px;
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border-subtle);
            font-size: 13px;
            color: var(--text-secondary);
        }
        .map-legend-item { display: inline-flex; align-items: center; gap: 6px; margin-right: 16px; }
        .map-legend-dot {
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 700;
            color: #fff;
        }
        .map-legend-dot.report { background: var(--accent-blue); }
        .map-legend-dot.workorder { background: #22d3ee; }
        .map-container { height: 600px; width: 100%; }
        .map-list { list-style: none; padding: 0; margin-top: 12px; }
        .map-list li { padding: 8px 0; border-bottom: 1px solid rgba(106, 150, 255, 0.1); font-size: 13px; color: var(--text-primary); }
        .map-list li:last-child { border-bottom: none; }
        .map-list .muted { color: var(--text-secondary); }
        .grid-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px; }

        .ops-border-subtle { border-color: rgba(106, 150, 255, 0.15) !important; }

        /* Inline form fields in operator views (create/edit) */
        .main input[type="text"]:not([class*="btn"]),
        .main input[type="number"],
        .main input[type="tel"],
        .main input[type="email"],
        .main input[type="date"],
        .main select,
        .main textarea {
            background: var(--bg-primary) !important;
            border: 1px solid rgba(106, 150, 255, 0.3) !important;
            color: var(--text-primary) !important;
        }

        @media (max-width: 768px) {
            .grid-2col { grid-template-columns: 1fr; }
            .sidebar { width: 240px; }
        }
        @media (max-width: 640px) {
            .layout { flex-direction: column; }
            .sidebar { width: 100%; min-height: auto; border-right: none; border-bottom: 1px solid var(--border-subtle); }
        }
    </style>
    @stack('styles')
</head>
<body>

<div class="layout">
    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-hard-hat"></i>
            <h2>DMS Ops</h2>
            <p>Drainage Management System</p>
        </div>
        <div class="layout-inner">
            <nav class="nav">
                <a href="{{ route('operations.dashboard') }}" class="{{ request()->routeIs('operations.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('operations.map') }}" class="{{ request()->routeIs('operations.map') ? 'active' : '' }}">
                    <i class="fas fa-map-location-dot"></i>
                    <span>GIS Map</span>
                </a>
                <a href="{{ route('operations.reports') }}" class="{{ request()->routeIs('operations.reports') ? 'active' : '' }}">
                    <i class="fas fa-file-lines"></i>
                    <span>Reports</span>
                </a>
                <a href="{{ route('operations.work-orders.index') }}" class="{{ request()->routeIs('operations.work-orders.*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Work Orders</span>
                </a>
                <a href="{{ route('operations.statistics.index') }}" class="{{ request()->routeIs('operations.statistics.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i>
                    <span>Statistics</span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-logout">
                        <i class="fas fa-arrow-right-from-bracket"></i>
                        <span>Sign out</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <main class="main">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>
</div>

@stack('scripts')
</body>
</html>
