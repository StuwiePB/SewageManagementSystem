@extends('r_admin.layout')

@section('title', 'Staff directory')

@section('content')
<div class="header">
    <div>
        <h1>Staff directory</h1>
        <p>Admin and operation accounts that can sign in to console or operations.</p>
    </div>
    @if(auth()->user()?->isSuperAdmin())
        <a href="{{ route('admin.staff.users.create') }}" class="btn btn-primary"><i class="fas fa-user-plus" style="margin-right:0.35rem;"></i>Add user</a>
    @endif
</div>
@if(session('success'))
    <div class="alert-success" style="margin-bottom: 1.5rem;">{{ session('success') }}</div>
@endif

<div class="grid-2col" style="align-items: start;">
    <div class="card">
        <div class="card-header">
            <h2 class="section-head"><i class="fas fa-user-shield" style="color: var(--accent-purple); margin-right: 0.5rem;"></i> Admin users</h2>
            <span class="badge badge-admin">{{ $adminUsers->count() }}</span>
        </div>
        <p class="section-desc">Includes super administrators and administrators.</p>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adminUsers as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td class="cell-muted">{{ $user->username ?? '—' }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if($user->hasRole(\App\Models\User::ROLE_SUPER_ADMIN))
                                    <span class="badge badge-admin">Super admin</span>
                                @else
                                    <span class="badge badge-admin">Admin</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="cell-muted">No admin accounts found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="section-head"><i class="fas fa-user-cog" style="color: var(--accent-green); margin-right: 0.5rem;"></i> Operation users</h2>
            <span class="badge badge-status-teal">{{ $operationUsers->count() }}</span>
        </div>
        <p class="section-desc">Users with access to the operations dashboard.</p>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Crew</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($operationUsers as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td class="cell-muted">{{ $user->username ?? '—' }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->crew?->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="cell-muted">No operation accounts found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
