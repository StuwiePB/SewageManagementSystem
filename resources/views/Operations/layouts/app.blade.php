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
    </style>
</head>
<body>

<div class="layout">
    {{-- Sidebar --}}
    <aside class="sidebar">
        <h2>SewageOps</h2>

        <nav class="nav">
            <a href="{{ route('operations.dashboard') }}" class="{{ request()->routeIs('operations.dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('operations.map') }}" class="{{ request()->routeIs('operations.map') ? 'active' : '' }}">GIS Map</a>
            <a href="{{ route('operations.reports') }}" class="{{ request()->routeIs('operations.reports') ? 'active' : '' }}">Reports</a>
            <a href="{{ route('operations.work-orders.index') }}" class="{{ request()->routeIs('operations.work-orders.*') ? 'active' : '' }}">Work Orders</a>
            <a href="{{ route('operations.crews.index') }}" class="{{ request()->routeIs('operations.crews.*') ? 'active' : '' }}">Crew Management</a>
        </nav>
    </aside>

    {{-- Main content --}}
    <main class="main">
        @if(session('success'))
            <div style="background: #d1fae5; color: #065f46; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #6ee7b7;">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div style="background: #fee2e2; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fca5a5;">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>
</div>

</body>
</html>
