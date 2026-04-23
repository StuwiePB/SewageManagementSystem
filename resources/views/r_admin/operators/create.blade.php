<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Operator - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #0f0f1a; color: #e2e8f0; display: flex; min-height: 100vh; }
        .sidebar { width: 280px; background: linear-gradient(180deg, #1a1a2e 0%, #16162a 100%); border-right: 1px solid rgba(139, 92, 246, 0.1); display: flex; flex-direction: column; }
        .sidebar-header { padding: 2rem 1.5rem; text-align: center; border-bottom: 1px solid rgba(139, 92, 246, 0.15); }
        .sidebar-header i { font-size: 2.5rem; color: #8b5cf6; margin-bottom: 0.75rem; display: block; }
        .sidebar-header h2 { font-size: 1.25rem; font-weight: 700; color: #fff; }
        .sidebar-header p { font-size: 0.875rem; color: #94a3b8; }
        .sidebar-nav { flex: 1; padding: 1.5rem 0; }
        .nav-item { display: flex; align-items: center; gap: 0.875rem; padding: 0.875rem 1.5rem; color: #e2e8f0; font-weight: 500; font-size: 0.9375rem; transition: all 0.2s; text-decoration: none; border-left: 3px solid transparent; }
        .nav-item:hover { background: rgba(139, 92, 246, 0.1); color: #fff; }
        .nav-item.active { background: #8b5cf6; color: #fff; border-left-color: #a78bfa; }
        .nav-item i { width: 20px; text-align: center; }
        .sidebar-footer { padding: 1.5rem; border-top: 1px solid rgba(139, 92, 246, 0.15); }
        .sidebar-footer .nav-item { padding: 0.75rem 1rem; background: none; border: none; width: 100%; text-align: left; cursor: pointer; font: inherit; }
        .main-content { flex: 1; padding: 2rem; overflow-y: auto; }
        .header { margin-bottom: 2rem; }
        .header h1 { font-size: 2rem; font-weight: 800; color: #fff; margin-bottom: 0.5rem; }
        .header p { color: #94a3b8; font-size: 0.9375rem; }
        .form-card { background: #1e1e2f; border: 1px solid rgba(139, 92, 246, 0.2); border-radius: 12px; padding: 1.5rem; max-width: 480px; }
        .form-card label { display: block; font-size: 0.875rem; font-weight: 500; color: #94a3b8; margin-bottom: 0.5rem; }
        .form-card input { width: 100%; padding: 0.75rem 1rem; background: #0f0f1a; border: 1px solid rgba(139, 92, 246, 0.3); border-radius: 8px; color: #e2e8f0; font-size: 1rem; }
        .form-card input:focus { outline: none; border-color: #8b5cf6; }
        .form-card .form-group { margin-bottom: 1.25rem; }
        .form-card .text-danger { font-size: 0.8125rem; color: #f87171; margin-top: 0.25rem; }
        .btn { display: inline-block; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; font-size: 0.9375rem; cursor: pointer; border: none; text-decoration: none; transition: all 0.2s; }
        .btn-primary { background: #8b5cf6; color: #fff; }
        .btn-primary:hover { background: #7c3aed; }
        .btn-secondary { background: #334155; color: #e2e8f0; }
        .btn-secondary:hover { background: #475569; }
        .alert-success { background: rgba(16, 185, 129, 0.15); color: #34d399; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-shield-halved"></i>
            <h2>Admin Control</h2>
            <p>Drainage Management System</p>
        </div>
        <nav class="sidebar-nav">
            <a href="{{ route('admin.dashboard') }}" class="nav-item">
                <i class="fas fa-th-large"></i>
                <span>Overview</span>
            </a>
            <a href="{{ route('admin.staff.operations.create') }}" class="nav-item active">
                <i class="fas fa-user-plus"></i>
                <span>New operator user</span>
            </a>
            <a href="{{ route('admin.workers.create') }}" class="nav-item">
                <i class="fas fa-hard-hat"></i>
                <span>New Worker</span>
            </a>
            <a href="{{ route('ai.incidents.dashboard') }}" class="nav-item">
                <i class="fas fa-robot"></i>
                <span>AI Incidents</span>
            </a>
            <a href="{{ route('admin.incidents.review') }}" class="nav-item">
                <i class="fas fa-clipboard-check"></i>
                <span>Review Queue</span>
            </a>
            <a href="{{ route('operations.dashboard') }}" class="nav-item">
                <i class="fas fa-map"></i>
                <span>Operations</span>
            </a>
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
        <div class="header">
            <h1>Create operator user</h1>
            <p>Super admin only — use @operation.brudms.jkr.bn email</p>
        </div>
        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif
        <div class="form-card">
            <form method="POST" action="{{ route('admin.staff.users.store') }}">
                @csrf
                <div class="form-group">
                    <label for="staffname">Staffname (unique)</label>
                    <input type="text" name="staffname" id="staffname" value="{{ old('staffname') }}" required pattern="[a-zA-Z0-9._-]+" maxlength="50">
                    @error('staffname') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus>
                    @error('name') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="email">Email (login)</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required placeholder="name@operation.brudms.jkr.bn">
                    @error('email') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required>
                    @error('password') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirm password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required>
                </div>
                <div class="form-group">
                    <label for="crew_id">Crew (optional — crew leader)</label>
                    <select name="crew_id" id="crew_id" style="width:100%; padding:0.75rem 1rem; background:#0f0f1a; border:1px solid rgba(139, 92, 246, 0.3); border-radius:8px; color:#e2e8f0; font-size:1rem;">
                        <option value="">None (operation user only)</option>
                        @foreach($crews as $c)
                        <option value="{{ $c->id }}" {{ old('crew_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    <p style="font-size:0.8125rem; color:#94a3b8; margin-top:0.25rem;">Assigning a crew makes this operator the crew leader; they can use the Workers page to take attendance.</p>
                </div>
                <div style="display: flex; gap: 0.75rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">Create operation user</button>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
