@extends('operations.layouts.app')

@section('content')

<div class="topbar">
    <div>
        <h1>Unassigned Personnel</h1>
        <p>Assign workers to crews</p>
    </div>
    <a href="{{ route('operations.crews.index') }}" style="color:#6b7280; text-decoration:none;">← Back to Crew Management</a>
</div>

@if($unassignedWorkers->count() > 0)
<div class="card">
    <p style="margin:0 0 16px; color:#6b7280; font-size:14px;">
        {{ $unassignedWorkers->count() }} unassigned worker(s) available for assignment
    </p>

    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="text-align:left; border-bottom:2px solid #e5e7eb;">
                <th style="padding:12px 8px; font-size:13px; color:#6b7280; font-weight:600;">Name</th>
                <th style="padding:12px 8px; font-size:13px; color:#6b7280; font-weight:600;">Contact</th>
                <th style="padding:12px 8px; font-size:13px; color:#6b7280; font-weight:600;">Role</th>
                <th style="padding:12px 8px; font-size:13px; color:#6b7280; font-weight:600;">Skills</th>
                <th style="padding:12px 8px; font-size:13px; color:#6b7280; font-weight:600;">Assign to Crew</th>
            </tr>
        </thead>
        <tbody>
            @foreach($unassignedWorkers as $worker)
                <tr style="border-bottom:1px solid #f3f4f6;">
                    <td style="padding:12px 8px; font-size:14px; font-weight:500;">{{ $worker->name }}</td>
                    <td style="padding:12px 8px; font-size:14px;">
                        @if($worker->contact_phone)
                            <div style="margin-bottom:4px;">{{ $worker->contact_phone }}</div>
                        @endif
                        @if($worker->contact_email)
                            <div style="color:#6b7280; font-size:12px;">{{ $worker->contact_email }}</div>
                        @endif
                    </td>
                    <td style="padding:12px 8px; font-size:14px; color:#6b7280;">
                        {{ $worker->role ?? '—' }}
                    </td>
                    <td style="padding:12px 8px; font-size:14px; color:#6b7280;">
                        {{ $worker->skills ?? '—' }}
                    </td>
                    <td style="padding:12px 8px;">
                        <form action="{{ route('operations.crews.assign-worker') }}" method="POST" style="display:flex; gap:8px; align-items:center;">
                            @csrf
                            <input type="hidden" name="worker_id" value="{{ $worker->id }}">
                            <select name="crew_id" required style="
                                padding:6px 10px;
                                border:1px solid #d1d5db;
                                border-radius:6px;
                                font-size:13px;
                                min-width:150px;
                            ">
                                <option value="">Select Crew...</option>
                                @foreach($crews as $crew)
                                    <option value="{{ $crew->id }}">{{ $crew->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" style="
                                background:#0056A6;
                                color:white;
                                padding:6px 16px;
                                border:none;
                                border-radius:6px;
                                font-size:13px;
                                font-weight:500;
                                cursor:pointer;
                            ">Assign</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
<div class="card" style="text-align:center; padding:48px;">
    <div style="font-size:48px; margin-bottom:16px;">✅</div>
    <h3 style="margin:0 0 8px; color:#374151;">All Workers Assigned</h3>
    <p style="margin:0; color:#6b7280;">There are no unassigned workers at this time.</p>
    <a href="{{ route('operations.crews.index') }}" style="
        display:inline-block;
        margin-top:16px;
        color:#0056A6;
        text-decoration:none;
        font-weight:500;
    ">← Back to Crew Management</a>
</div>
@endif

@endsection
