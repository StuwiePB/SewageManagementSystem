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
        }

        .sidebar h2 {
            margin: 0 0 32px;
            font-size: 20px;
            color: #2563eb;
        }

        .nav a {
            display: block;
            padding: 10px 12px;
            margin-bottom: 8px;
            text-decoration: none;
            color: #374151;
            border-radius: 8px;
        }

        .nav a.active,
        .nav a:hover {
            background-color: #eff6ff;
            color: #2563eb;
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
    </style>
</head>
<body>

<div class="layout">
    {{-- Sidebar --}}
    <aside class="sidebar">
        <h2>SewageOps</h2>

        <nav class="nav">
            <a href="{{ route('operations.dashboard') }}" class="active">Dashboard</a>
            <a href="#">GIS Map</a>
            <a href="#">Incidents</a>
            <a href="#">Work Orders</a>
            <a href="#">Maintenance</a>
            <a href="#">Crew Management</a>
        </nav>
    </aside>

    {{-- Main content --}}
    <main class="main">
        @yield('content')
    </main>
</div>

</body>
</html>
