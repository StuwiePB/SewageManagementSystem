<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crew Management - Admin Control</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
        .header { margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; }
        .header-left h1 { font-size: 2rem; font-weight: 800; color: #fff; margin-bottom: 0.5rem; }
        .header-left p { color: #94a3b8; font-size: 0.9375rem; }
        
        /* Filters */
        .filters { background: #1e1e2f; border: 1px solid rgba(139, 92, 246, 0.2); border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; display: flex; gap: 1rem; flex-wrap: wrap; align-items: center; }
        .filters label { color: #94a3b8; font-size: 0.875rem; font-weight: 500; }
        .filters select, .filters input { background: #0f0f1a; border: 1px solid rgba(139, 92, 246, 0.3); border-radius: 8px; padding: 0.625rem 1rem; color: #e2e8f0; font-size: 0.875rem; outline: none; }
        .filters select:focus, .filters input:focus { border-color: #8b5cf6; }
        .filter-group { display: flex; flex-direction: column; gap: 0.5rem; }
        .btn-reset { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); padding: 0.625rem 1.25rem; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
        .btn-reset:hover { background: rgba(239, 68, 68, 0.25); }
        
        /* Stats Cards */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stat-card-small { background: #1e1e2f; border: 1px solid rgba(139, 92, 246, 0.2); border-radius: 12px; padding: 1rem; text-align: center; }
        .stat-card-small h4 { font-size: 0.75rem; color: #94a3b8; font-weight: 500; margin-bottom: 0.5rem; text-transform: uppercase; }
        .stat-card-small .value { font-size: 1.5rem; font-weight: 700; color: #fff; }
        
        /* Table */
        .table-card { background: #1e1e2f; border: 1px solid rgba(139, 92, 246, 0.2); border-radius: 12px; padding: 1.5rem; }
        .table-card h3 { font-size: 1.125rem; font-weight: 700; color: #fff; margin-bottom: 1.5rem; }
        table { width: 100%; border-collapse: collapse; }
        thead { background: rgba(139, 92, 246, 0.1); }
        th { padding: 1rem; text-align: left; font-size: 0.8125rem; font-weight: 600; color: #a78bfa; text-transform: uppercase; }
        td { padding: 1rem; border-bottom: 1px solid rgba(139, 92, 246, 0.1); color: #e2e8f0; font-size: 0.875rem; }
        tbody tr:hover { background: rgba(139, 92, 246, 0.05); }
        .badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
        .badge-active { background: rgba(16, 185, 129, 0.15); color: #34d399; }
        .badge-leave { background: rgba(251, 191, 36, 0.15); color: #fbbf24; }
        .badge-inactive { background: rgba(239, 68, 68, 0.15); color: #f87171; }
        .no-data { text-align: center; padding: 3rem; color: #94a3b8; }
        
        /* Add New Worker Button */
        .btn-add-worker { background: linear-gradient(135deg, #8b5cf6, #a78bfa); color: #fff; border: none; padding: 0.875rem 2rem; border-radius: 12px; font-weight: 600; font-size: 0.9375rem; cursor: pointer; transition: all 0.3s; display: inline-flex; align-items: center; gap: 0.75rem; text-decoration: none; box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4); }
        .btn-add-worker:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(139, 92, 246, 0.5); }
        .btn-add-worker i { font-size: 1.125rem; }
        
        @media (max-width: 1024px) {
            .sidebar { width: 240px; }
            .filters { flex-direction: column; align-items: stretch; }
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
            <a href="{{ route('admin.dashboard') }}" class="nav-item">
                <i class="fas fa-th-large"></i>
                <span>Overview</span>
            </a>
            <a href="{{ route('admin.crew-management') }}" class="nav-item active">
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
            <div class="header-left">
                <h1>Crew Management</h1>
                <p>Manage sewage maintenance crew members and their roles</p>
            </div>
            <div class="header-right">
                <a href="{{ route('admin.crew.create') }}" class="btn-add-worker">
                    <i class="fas fa-user-plus"></i>
                    <span>Add New Worker</span>
                </a>
            </div>
        </div>

        <!-- Role Statistics -->
        <div class="stats-grid">
            <div class="stat-card-small">
                <h4>Work Leaders</h4>
                <div class="value">{{ $roleCounts['Work Leader'] }}</div>
            </div>
            <div class="stat-card-small">
                <h4>Senior Technicians</h4>
                <div class="value">{{ $roleCounts['Senior Technician'] }}</div>
            </div>
            <div class="stat-card-small">
                <h4>Technicians</h4>
                <div class="value">{{ $roleCounts['Technician'] }}</div>
            </div>
            <div class="stat-card-small">
                <h4>Equipment Operators</h4>
                <div class="value">{{ $roleCounts['Equipment Operator'] }}</div>
            </div>
            <div class="stat-card-small">
                <h4>Safety Officers</h4>
                <div class="value">{{ $roleCounts['Safety Officer'] }}</div>
            </div>
            <div class="stat-card-small">
                <h4>Maintenance Workers</h4>
                <div class="value">{{ $roleCounts['Maintenance Worker'] }}</div>
            </div>
        </div>

        <!-- Filters -->
        <form method="GET" action="{{ route('admin.crew-management') }}" class="filters">
            <div class="filter-group">
                <label>Filter by Role</label>
                <select name="role" onchange="this.form.submit()">
                    <option value="">All Roles</option>
                    <option value="Work Leader" {{ request('role') == 'Work Leader' ? 'selected' : '' }}>Work Leader</option>
                    <option value="Senior Technician" {{ request('role') == 'Senior Technician' ? 'selected' : '' }}>Senior Technician</option>
                    <option value="Technician" {{ request('role') == 'Technician' ? 'selected' : '' }}>Technician</option>
                    <option value="Equipment Operator" {{ request('role') == 'Equipment Operator' ? 'selected' : '' }}>Equipment Operator</option>
                    <option value="Safety Officer" {{ request('role') == 'Safety Officer' ? 'selected' : '' }}>Safety Officer</option>
                    <option value="Maintenance Worker" {{ request('role') == 'Maintenance Worker' ? 'selected' : '' }}>Maintenance Worker</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Filter by Status</label>
                <select name="status" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Active</option>
                    <option value="On Leave" {{ request('status') == 'On Leave' ? 'selected' : '' }}>On Leave</option>
                    <option value="Inactive" {{ request('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Search</label>
                <input type="text" name="search" placeholder="Name or Employee ID" value="{{ request('search') }}">
            </div>

            @if(request()->hasAny(['role', 'status', 'search']))
                <div class="filter-group" style="justify-content: flex-end;">
                    <label>&nbsp;</label>
                    <a href="{{ route('admin.crew-management') }}" class="btn-reset">Reset Filters</a>
                </div>
            @endif
        </form>

        <!-- Crew Table -->
        <div class="table-card">
            <h3>Crew Members ({{ $crews->count() }} total)</h3>
            
            @if($crews->count() > 0)
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Hire Date</th>
                                <th>Specialization</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($crews as $crew)
                                <tr>
                                    <td><strong>{{ $crew->employee_id }}</strong></td>
                                    <td>{{ $crew->name }}</td>
                                    <td>{{ $crew->role }}</td>
                                    <td>{{ $crew->phone ?? '-' }}</td>
                                    <td>{{ $crew->email ?? '-' }}</td>
                                    <td>
                                        @if($crew->status == 'Active')
                                            <span class="badge badge-active">Active</span>
                                        @elseif($crew->status == 'On Leave')
                                            <span class="badge badge-leave">On Leave</span>
                                        @else
                                            <span class="badge badge-inactive">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ $crew->hire_date ? $crew->hire_date->format('M d, Y') : '-' }}</td>
                                    <td>{{ $crew->specialization ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="no-data">
                    <i class="fas fa-users" style="font-size: 3rem; color: #475569; margin-bottom: 1rem;"></i>
                    <p>No crew members found matching your criteria.</p>
                </div>
            @endif
        </div>
    </main>
</body>
</html>
