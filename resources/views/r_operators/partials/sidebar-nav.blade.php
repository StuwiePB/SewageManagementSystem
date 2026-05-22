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
    <a href="{{ route('operations.work-orders.index') }}" class="{{ request()->routeIs('operations.work-orders.*') && ! request()->routeIs('operations.old-work-orders*') ? 'active' : '' }}">
        <i class="fas fa-clipboard-list"></i>
        <span>Work Orders</span>
    </a>

    <div class="nav-group {{ request()->routeIs('operations.old-reports*') || request()->routeIs('operations.old-work-orders*') ? 'is-open' : '' }}">
        <span class="nav-group-label {{ request()->routeIs('operations.old-reports*') || request()->routeIs('operations.old-work-orders*') ? 'active' : '' }}">
            <i class="fas fa-folder-open"></i>
            <span>Paper Archive</span>
        </span>
        <a href="{{ route('operations.old-reports.index') }}" class="nav-sub {{ request()->routeIs('operations.old-reports*') ? 'active' : '' }}">
            <i class="fas fa-file-lines"></i>
            <span>Old Reports</span>
        </a>
        <a href="{{ route('operations.old-work-orders.index') }}" class="nav-sub {{ request()->routeIs('operations.old-work-orders*') ? 'active' : '' }}">
            <i class="fas fa-archive"></i>
            <span>Old Work Orders</span>
        </a>
    </div>

    <a href="{{ route('operations.statistics.index') }}" class="{{ request()->routeIs('operations.statistics.*') ? 'active' : '' }}">
        <i class="fas fa-chart-line"></i>
        <span>Statistics</span>
    </a>
</nav>
