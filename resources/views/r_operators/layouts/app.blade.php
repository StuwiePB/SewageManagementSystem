<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SewageOps - Operations Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- You can later move CSS to a proper file --}}
    <style>
        body {
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f6f8fb;
            color: #1f2937;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background-color: #ffffff;
            border-right: 1px solid #e5e7eb;
            padding: 24px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            box-sizing: border-box;
        }

        .sidebar h2 {
            margin: 0 0 32px;
            font-size: 20px;
            color: #0056A6;
        }

        .nav a {
            display: block;
            padding: 10px 12px;
            margin-bottom: 8px;
            text-decoration: none;
            color: #374151;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .nav a.active,
        .nav a:hover {
            background-color: rgba(0, 86, 166, 0.1);
            color: #0056A6;
        }

        /* Main content */
        .main {
            flex: 1;
            padding: 32px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }

        .topbar h1 {
            margin: 0;
            font-size: 24px;
        }

        .topbar a {
            font-size: 14px;
        }

        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #e5e7eb;
        }

        .card p {
            margin: 0;
            font-size: 14px;
            color: #6b7280;
        }

        .card h3 {
            margin: 8px 0 0;
            font-size: 28px;
        }

        /* Button styles */
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #0056A6;
            color: white;
        }

        .btn-primary:hover {
            background: #004494;
        }

        .btn-secondary {
            background: #0077CC;
            color: white;
        }

        .btn-secondary:hover {
            background: #0066aa;
        }

        /* Form elements */
        input[type="text"],
        input[type="email"],
        input[type="number"],
        input[type="tel"],
        input[type="file"],
        select,
        textarea {
            font-family: inherit;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="number"]:focus,
        input[type="tel"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #0056A6;
            box-shadow: 0 0 0 3px rgba(0, 86, 166, 0.1);
        }

        /* Alerts */
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; border: 1px solid; }
        .alert-success { background: #d1fae5; color: #065f46; border-color: #6ee7b7; }
        .alert-error { background: #fee2e2; color: #991b1b; border-color: #fca5a5; }

        /* Grids */
        .grid-2col { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 32px; }
        .grid-filters { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; align-items: end; }
        .grid-auto { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 16px; }

        /* Card header */
        .card-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; }
        .card-title { margin: 0 0 4px; font-size: 18px; }
        .card-subtitle { color: #6b7280; margin: 0; font-size: 14px; }
        .card-meta { margin-top: 6px; font-size: 12px; color: #6b7280; }
        .card-mb { margin-bottom: 20px; }

        /* Form controls */
        .form-group { margin-bottom: 0; }
        .form-label { display: block; margin-bottom: 6px; font-size: 13px; color: #6b7280; font-weight: 500; }
        .form-control { width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; box-sizing: border-box; }
        .form-control:focus { outline: none; border-color: #0056A6; box-shadow: 0 0 0 3px rgba(0, 86, 166, 0.1); }
        .btn-submit { background: #0056A6; color: white; padding: 8px 20px; border: none; border-radius: 8px; font-weight: 500; cursor: pointer; width: 100%; font-size: 14px; }
        .btn-submit:hover { background: #004494; }

        /* Tables */
        .table-wrap { overflow-x: auto; }
        .ops-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .ops-table th { text-align: left; padding: 12px 8px; font-size: 13px; color: #6b7280; font-weight: 600; border-bottom: 2px solid #e5e7eb; }
        .ops-table td { padding: 12px 8px; border-bottom: 1px solid #f3f4f6; }
        .ops-table tbody tr:last-child td { border-bottom: none; }
        .ops-table .cell-muted { color: #9ca3af; text-align: center; padding: 24px; }

        /* Badges */
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; }
        .badge-critical { background: #fee2e2; color: #991b1b; }
        .badge-high { background: #fed7aa; color: #9a3412; }
        .badge-medium { background: #fef3c7; color: #854d0e; }
        .badge-low { background: #dcfce7; color: #166534; }
        .badge-status { background: #e0e7ff; color: #3730a3; }
        .badge-status-amber { background: #fef9c3; color: #713f12; }
        .badge-status-teal { background: #ccfbf1; color: #0f766e; }
        .badge-status-green { background: #dcfce7; color: #166534; }
        .badge-status-red { background: #fee2e2; color: #991b1b; }
        .badge-report-new { background: #dbeafe; color: #1e40af; }
        .badge-report-progress { background: #fef3c7; color: #854d0e; }
        .badge-report-resolved { background: #dcfce7; color: #166534; }
        .badge-report-closed { background: #e5e7eb; color: #374151; }
        .badge-change { color: #ef4444; margin-top: 6px; font-size: 12px; }

        /* Links */
        .link-primary { color: #0056A6; text-decoration: none; font-weight: 500; }
        .link-primary:hover { text-decoration: underline; }

        /* List */
        .list-unstyled { list-style: none; padding: 0; margin: 0; }
        .list-item { margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid #f3f4f6; }
        .list-item:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
        .list-item strong { display: block; margin-bottom: 4px; }
        .list-item small { color: #6b7280; font-size: 12px; }

        /* Pagination */
        .pagination-bar { margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; }
        .pagination-info { font-size: 13px; color: #6b7280; }
        .pagination-btns { display: flex; gap: 8px; }
        .btn-page { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; font-size: 13px; border-radius: 8px; border: 1px solid #d1d5db; text-decoration: none; font-weight: 500; color: #0056A6; background: #fff; cursor: pointer; }
        .btn-page:hover { background: #f9fafb; }
        .btn-page:disabled, .btn-page.disabled { color: #9ca3af; background: #f3f4f6; border-color: #e5e7eb; cursor: default; pointer-events: none; }
        .btn-page.disabled svg { opacity: 0.6; }

        /* Section title in cards */
        .section-head { margin: 0 0 8px; font-size: 16px; }
        .section-desc { color: #6b7280; margin: 0 0 16px; font-size: 14px; }

        /* Detail rows (show page) */
        .detail-row { margin-bottom: 16px; }
        .detail-row:last-child { margin-bottom: 0; }
        .info-label { margin: 0; font-size: 12px; color: #6b7280; }
        .info-value { margin: 4px 0 0; font-size: 16px; font-weight: 500; }
        .info-value-muted { margin: 4px 0 0; font-size: 14px; color: #374151; }
        .info-value-small { margin: 4px 0 0; font-size: 13px; color: #6b7280; }
        .btn-edit { display: inline-flex; align-items: center; padding: 6px 14px; background: #0056A6; color: white; text-decoration: none; font-size: 13px; font-weight: 500; border-radius: 8px; }
        .btn-edit:hover { background: #004494; color: white; }

        /* Map */
        .card-map { padding: 0; overflow: hidden; }
        .map-legend { padding: 10px 16px; background: #f9fafb; border-bottom: 1px solid #e5e7eb; font-size: 13px; color: #6b7280; }
        .map-legend-item { display: inline-flex; align-items: center; gap: 6px; margin-right: 16px; }
        .map-legend-dot { border-radius: 50%; width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700; color: #fff; }
        .map-legend-dot.report { background: #0056A6; }
        .map-legend-dot.workorder { background: #0d9488; }
        .map-container { height: 600px; width: 100%; }
        .map-list { list-style: none; padding: 0; margin-top: 12px; }
        .map-list li { padding: 8px 0; border-bottom: 1px solid #f3f4f6; font-size: 13px; }
        .map-list li:last-child { border-bottom: none; }
        .map-list .muted { color: #6b7280; }
        .grid-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px; }

        @media (max-width: 768px) {
            .grid-2col { grid-template-columns: 1fr; }
        }

        .sidebar-footer {
            margin-top: auto;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
        }
        .sidebar .layout-inner {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }
        .sidebar .nav {
            flex: 1;
        }
        .btn-logout {
            display: block;
            width: 100%;
            padding: 10px 12px;
            margin-top: 8px;
            border-radius: 8px;
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #b91c1c;
            font-size: 14px;
            font-weight: 500;
            font-family: inherit;
            cursor: pointer;
            text-align: center;
            transition: background 0.2s, border-color 0.2s;
        }
        .btn-logout:hover {
            background: #fee2e2;
            border-color: #f87171;
        }
    </style>
</head>
<body>

<div class="layout">
    {{-- Sidebar --}}
    <aside class="sidebar">
        <div class="layout-inner">
            <h2>SewageOps</h2>

            <nav class="nav">
                <a href="{{ route('operations.dashboard') }}" class="{{ request()->routeIs('operations.dashboard') ? 'active' : '' }}">Dashboard</a>
                <a href="{{ route('operations.map') }}" class="{{ request()->routeIs('operations.map') ? 'active' : '' }}">GIS Map</a>
                <a href="{{ route('operations.reports') }}" class="{{ request()->routeIs('operations.reports') ? 'active' : '' }}">Reports</a>
                <a href="{{ route('operations.work-orders.index') }}" class="{{ request()->routeIs('operations.work-orders.*') ? 'active' : '' }}">Work Orders</a>
                <a href="{{ route('operations.statistics.index') }}" class="{{ request()->routeIs('operations.statistics.*') ? 'active' : '' }}">Statistics</a>
            </nav>

            <div class="sidebar-footer">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-logout">Log out</button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Main content --}}
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

</body>
</html>
