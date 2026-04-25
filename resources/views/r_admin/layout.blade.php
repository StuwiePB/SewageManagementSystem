<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Console - @yield('title', 'Dashboard')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --bg-primary: #1A1D2B; --bg-secondary: #272B3C; --text-primary: #FFFFFF; --text-secondary: #B0B0B0; --accent-blue: #6A96FF; --accent-red: #FF5B5B; --accent-green: #56FF8B; --accent-purple: #A86AFF; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-primary); color: var(--text-primary); display: flex; min-height: 100vh; }
        .sidebar { width: 280px; background: var(--bg-secondary); border-right: 1px solid rgba(106, 150, 255, 0.15); display: flex; flex-direction: column; }
        .sidebar-header { padding: 2rem 1.5rem; text-align: center; border-bottom: 1px solid rgba(106, 150, 255, 0.2); }
        .sidebar-header i { font-size: 2.5rem; color: var(--accent-blue); margin-bottom: 0.75rem; display: block; }
        .sidebar-header h2 { font-size: 1.25rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.25rem; }
        .sidebar-header p { font-size: 0.875rem; color: var(--text-secondary); }
        .sidebar-nav { flex: 1; padding: 1.5rem 0; }
        .nav-item { display: flex; align-items: center; gap: 0.875rem; padding: 0.875rem 1.5rem; color: var(--text-secondary); font-weight: 500; font-size: 0.9375rem; transition: all 0.2s; cursor: pointer; border-left: 3px solid transparent; text-decoration: none; }
        .nav-item:hover { background: rgba(106, 150, 255, 0.1); color: var(--text-primary); }
        .nav-item.active { background: rgba(106, 150, 255, 0.2); color: var(--accent-blue); border-left-color: var(--accent-blue); }
        .nav-item i { width: 20px; text-align: center; }
        .nav-item.disabled { cursor: default; opacity: 0.7; }
        .sidebar-footer { padding: 1.5rem; border-top: 1px solid rgba(106, 150, 255, 0.15); }
        .sidebar-footer .nav-item { padding: 0.75rem 1rem; background: none; border: none; width: 100%; text-align: left; cursor: pointer; font: inherit; }
        .main-content { flex: 1; padding: 2rem; overflow-y: auto; background: var(--bg-primary); }
        .header { margin-bottom: 2rem; }
        .header h1 { font-size: 2rem; font-weight: 800; color: var(--text-primary); margin-bottom: 0.5rem; }
        .header p { color: var(--text-secondary); font-size: 0.9375rem; }
        .card { background: var(--bg-secondary); border: 1px solid rgba(106, 150, 255, 0.15); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; }
        .btn { display: inline-block; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 500; text-decoration: none; cursor: pointer; border: none; font-size: 0.875rem; }
        .btn-primary { background: var(--accent-blue); color: white; }
        .btn-primary:hover { opacity: 0.9; }
        .btn-secondary { background: rgba(106, 150, 255, 0.2); color: var(--accent-blue); }
        .btn-secondary:hover { opacity: 0.9; }
        .back-link { display: inline-flex; align-items: center; padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; font-weight: 500; font-size: 0.875rem; background: rgba(106, 150, 255, 0.2); color: var(--accent-blue); border: none; cursor: pointer; }
        .back-link:hover { opacity: 0.9; color: var(--accent-blue); }
        .btn-danger { background: rgba(255, 91, 91, 0.2); color: var(--accent-red); }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; color: var(--text-secondary); font-size: 0.875rem; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.5rem 0.75rem; background: var(--bg-primary); border: 1px solid rgba(106, 150, 255, 0.3); border-radius: 8px; color: var(--text-primary); font-size: 0.9375rem; }
        .form-group input::placeholder, .form-group textarea::placeholder { color: var(--text-secondary); opacity: 0.6; }
        .search-filter-bar { display: flex; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap; }
        .search-filter-bar input { flex: 1; min-width: 200px; padding: 0.5rem 1rem; background: var(--bg-primary); border: 1px solid rgba(106, 150, 255, 0.3); border-radius: 8px; color: var(--text-primary); }
        .search-filter-bar select { padding: 0.5rem 1rem; background: var(--bg-primary); border: 1px solid rgba(106, 150, 255, 0.3); border-radius: 8px; color: var(--text-primary); }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid rgba(106, 150, 255, 0.1); }
        th { color: var(--text-secondary); font-size: 0.8125rem; font-weight: 600; }
        td { font-size: 0.9375rem; }
        .badge { padding: 0.25rem 0.5rem; border-radius: 6px; font-size: 0.75rem; font-weight: 500; }
        .badge-admin { background: rgba(168, 106, 255, 0.2); color: var(--accent-purple); }
        .badge-operator { background: rgba(106, 150, 255, 0.2); color: var(--accent-blue); }
        .badge-crew_leader { background: rgba(86, 255, 139, 0.2); color: var(--accent-green); }
        .badge-maintenance { background: rgba(255, 165, 0, 0.2); color: #FFA500; }
        .badge-first_aider { background: rgba(255, 91, 91, 0.2); color: var(--accent-red); }
        .tab-nav { display: flex; gap: 0.5rem; margin-bottom: 1.5rem; border-bottom: 1px solid rgba(106, 150, 255, 0.15); }
        .tab-nav a { padding: 0.75rem 1rem; color: var(--text-secondary); text-decoration: none; font-weight: 500; border-bottom: 2px solid transparent; margin-bottom: -1px; }
        .tab-nav a:hover, .tab-nav a.active { color: var(--accent-blue); border-bottom-color: var(--accent-blue); }
        .nav-dropdown { position: relative; }
        .nav-dropdown-toggle { width: 100%; justify-content: flex-start; background: none; border: none; color: inherit; cursor: pointer; font-family: inherit; text-align: left; }
        .nav-dropdown-toggle i.fa-chevron-down { margin-left: auto; transition: transform 0.2s; }
        .nav-dropdown.open .nav-dropdown-toggle i.fa-chevron-down { transform: rotate(180deg); }
        .nav-dropdown-menu { display: none; padding-left: 2.75rem; padding-top: 0.25rem; padding-bottom: 0.5rem; }
        .nav-dropdown.open .nav-dropdown-menu { display: block; }
        .nav-dropdown-menu a { display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; color: var(--text-secondary); text-decoration: none; font-size: 0.875rem; border-radius: 6px; }
        .nav-dropdown-menu a:hover { background: rgba(106, 150, 255, 0.1); color: var(--text-primary); }
        .nav-dropdown-menu a.active { color: var(--accent-blue); }
        /* Statistics & Work Orders */
        .header { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; }
        .header > div:first-child { flex: 1; min-width: 0; }
        .link-primary { color: var(--accent-blue); text-decoration: none; font-weight: 500; }
        .link-primary:hover { text-decoration: underline; }
        .btn-submit { padding: 0.5rem 1.25rem; background: var(--accent-blue); color: white; border: none; border-radius: 8px; font-weight: 500; cursor: pointer; font-size: 0.875rem; }
        .btn-submit:hover { opacity: 0.9; }
        .form-control { width: 100%; padding: 0.5rem 0.75rem; background: var(--bg-primary); border: 1px solid rgba(106, 150, 255, 0.3); border-radius: 8px; color: var(--text-primary); font-size: 0.9375rem; }
        .form-label { display: block; margin-bottom: 0.5rem; color: var(--text-secondary); font-size: 0.875rem; font-weight: 500; }
        .grid-filters { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 1rem; width: 100%; }
        .grid-filters .form-group { margin-bottom: 0; }
        .grid-filters .form-group.flex-grow { flex: 1; min-width: 0; }
        .badge-low { background: rgba(106, 150, 255, 0.2); color: var(--accent-blue); }
        .badge-medium { background: rgba(255, 165, 0, 0.2); color: #FFA500; }
        .badge-high { background: rgba(255, 91, 91, 0.25); color: var(--accent-red); }
        .badge-critical { background: rgba(255, 91, 91, 0.4); color: #ff8888; }
        .badge-status { background: rgba(106, 150, 255, 0.2); color: var(--accent-blue); }
        .badge-status-amber { background: rgba(255, 165, 0, 0.25); color: #FFA500; }
        .badge-status-teal { background: rgba(34, 211, 238, 0.25); color: #22d3ee; }
        .badge-status-green { background: rgba(86, 255, 139, 0.2); color: var(--accent-green); }
        .badge-status-red { background: rgba(255, 91, 91, 0.25); color: var(--accent-red); }
        .card-mb { margin-bottom: 1.5rem; }
        .cell-muted { color: var(--text-secondary); font-size: 0.9375rem; padding: 1.5rem !important; text-align: center; }
        .pagination-bar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(106, 150, 255, 0.1); }
        .pagination-info { color: var(--text-secondary); font-size: 0.875rem; }
        .pagination-btns { display: flex; gap: 0.5rem; }
        .btn-page { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.75rem; border-radius: 8px; font-size: 0.875rem; color: var(--accent-blue); text-decoration: none; background: rgba(106, 150, 255, 0.15); }
        .btn-page:hover:not(.disabled) { background: rgba(106, 150, 255, 0.25); }
        .btn-page.disabled { color: var(--text-secondary); opacity: 0.6; cursor: default; }
        .grid-2col { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem; }
        .card-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1rem; }
        .section-head { font-size: 1.125rem; font-weight: 700; color: var(--text-primary); }
        .btn-edit { padding: 0.4rem 0.9rem; background: rgba(106, 150, 255, 0.2); color: var(--accent-blue); border-radius: 8px; text-decoration: none; font-size: 0.875rem; font-weight: 500; }
        .btn-edit:hover { background: rgba(106, 150, 255, 0.3); }
        .detail-row { margin-bottom: 1rem; }
        .detail-row:last-child { margin-bottom: 0; }
        .info-label { font-size: 0.8125rem; color: var(--text-secondary); margin-bottom: 0.25rem; }
        .info-value, .info-value-muted { font-size: 0.9375rem; color: var(--text-primary); margin: 0; }
        .info-value-muted { color: var(--text-secondary); }
        .info-value-small { font-size: 0.8125rem; color: var(--text-secondary); margin: 0.25rem 0 0; }
        .alert-success { background: rgba(86, 255, 139, 0.15); color: var(--accent-green); padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.9375rem; }
        .section-desc { color: var(--text-secondary); font-size: 0.875rem; margin-bottom: 1rem; }
        .stats-grid-4 { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
        .stats-grid-4 .card { padding: 1rem; }
        .stats-grid-4 .card p { font-size: 0.8125rem; color: var(--text-secondary); margin: 0 0 0.25rem; }
        .stats-grid-4 .card h3 { font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin: 0; }
        .chart-card-title { font-size: 1.125rem; font-weight: 700; color: var(--text-primary); margin: 0 0 1rem; }
        @media (max-width: 1024px) { .sidebar { width: 240px; } }
    </style>
    @stack('styles')
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-shield-halved"></i>
            <h2>Admin Console</h2>
            <p>Drainage Management System</p>
        </div>
        <nav class="sidebar-nav">
            <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-th-large"></i>
                <span>Overview</span>
            </a>
            <div class="nav-dropdown {{ (request()->routeIs('ai.incidents.*') || request()->routeIs('admin.incidents.*')) ? 'open' : '' }}">
                <button type="button" class="nav-item nav-dropdown-toggle" onclick="this.closest('.nav-dropdown').classList.toggle('open')">
                    <i class="fas fa-robot"></i>
                    <span>AI</span>
                    <i class="fas fa-chevron-down" style="font-size: 0.75rem;"></i>
                </button>
                <div class="nav-dropdown-menu">
                    <a href="{{ route('ai.incidents.dashboard') }}" class="{{ request()->routeIs('ai.incidents.*') ? 'active' : '' }}"><i class="fas fa-exclamation-triangle"></i> AI Incidents</a>
                    <a href="{{ route('admin.incidents.review') }}" class="{{ request()->routeIs('admin.incidents.review') ? 'active' : '' }}"><i class="fas fa-clipboard-check"></i> Review Queue</a>
                </div>
            </div>
            <div class="nav-dropdown {{ request()->routeIs('admin.gis-map') ? 'open' : '' }}">
                <button type="button" class="nav-item nav-dropdown-toggle {{ request()->routeIs('admin.gis-map') ? 'active' : '' }}" onclick="this.closest('.nav-dropdown').classList.toggle('open')">
                    <i class="fas fa-map-location-dot"></i>
                    <span>GIS Map</span>
                    <i class="fas fa-chevron-down" style="font-size: 0.75rem;"></i>
                </button>
                <div class="nav-dropdown-menu">
                    <a href="{{ route('admin.gis-map', ['view' => 'admin_ops']) }}" class="{{ request()->routeIs('admin.gis-map') && request()->query('view', 'admin_ops') === 'admin_ops' ? 'active' : '' }}">
                        <i class="fas fa-layer-group"></i> Admin/Ops GIS map
                    </a>
                    <a href="{{ route('admin.gis-map', ['view' => 'customer']) }}" class="{{ request()->routeIs('admin.gis-map') && request()->query('view') === 'customer' ? 'active' : '' }}">
                        <i class="fas fa-map-pin"></i> Customer GIS map
                    </a>
                </div>
            </div>
            <a href="{{ route('admin.statistics.index') }}" class="nav-item {{ request()->routeIs('admin.statistics.*') ? 'active' : '' }}">
                <i class="fas fa-chart-line"></i>
                <span>Statistics</span>
            </a>
            <a href="{{ route('admin.work-orders.index') }}" class="nav-item {{ request()->routeIs('admin.work-orders.*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list"></i>
                <span>Work Orders</span>
            </a>
            <a href="{{ route('admin.support.chat') }}" class="nav-item {{ request()->routeIs('admin.support.chat') ? 'active' : '' }}">
                <i class="fas fa-comments"></i>
                <span>Customer chat</span>
            </a>
            @if(auth()->user()?->hasRole(\App\Models\User::ROLE_ADMIN) || auth()->user()?->hasRole(\App\Models\User::ROLE_SUPER_ADMIN))
                <div class="nav-dropdown {{ request()->routeIs('admin.staff.*') ? 'open' : '' }}">
                    <button type="button" class="nav-item nav-dropdown-toggle" onclick="this.closest('.nav-dropdown').classList.toggle('open')">
                        <i class="fas fa-user-shield"></i>
                        <span>Staff accounts</span>
                        <i class="fas fa-chevron-down" style="font-size: 0.75rem;"></i>
                    </button>
                    <div class="nav-dropdown-menu">
                        <a href="{{ route('admin.staff.index') }}" class="{{ request()->routeIs('admin.staff.index') ? 'active' : '' }}"><i class="fas fa-users"></i> View admins &amp; operations</a>
                        @if(auth()->user()?->isSuperAdmin())
                            <a href="{{ route('admin.staff.users.create') }}" class="{{ request()->routeIs('admin.staff.users.*') ? 'active' : '' }}"><i class="fas fa-user-plus"></i> Add user</a>
                        @endif
                    </div>
                </div>
            @endif
        </nav>
        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button type="submit" class="nav-item">
                    <i class="fas fa-arrow-right-from-bracket"></i>
                    <span>Sign Out</span>
                </button>
            </form>
        </div>
    </aside>
    <main class="main-content">
        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>
