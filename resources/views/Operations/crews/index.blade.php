@extends('operations.layouts.app')

@section('content')

<div class="topbar">
    <div>
        <h1>Crew Management</h1>
        <p>Manage field crews and assignments</p>
    </div>
    <a href="{{ route('operations.crews.create') }}" style="
        background:#0056A6;
        color:white;
        padding:10px 20px;
        border-radius:8px;
        text-decoration:none;
        font-weight:500;
        display:inline-block;
    ">+ Add New Crew</a>
</div>

{{-- Unassigned Workers Tab --}}
@if($unassignedCount > 0)
<a href="{{ route('operations.crews.unassigned') }}" style="
    display:block;
    text-decoration:none;
    margin-bottom:20px;
">
    <div class="card" style="
        background:linear-gradient(135deg, #0056A6 0%, #0077CC 100%);
        color:white;
        cursor:pointer;
        transition:transform 0.2s, box-shadow 0.2s;
        border:none;
    " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,86,166,0.3)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <div>
                <p style="margin:0; font-size:14px; opacity:0.9;">Unassigned Personnel</p>
                <h3 style="margin:8px 0 0; font-size:32px; font-weight:600;">{{ $unassignedCount }}</h3>
                <p style="margin:4px 0 0; font-size:12px; opacity:0.8;">Click to view and assign →</p>
            </div>
            <div style="font-size:48px; opacity:0.3;">👥</div>
        </div>
    </div>
</a>
@endif

{{-- Attendance section --}}
<div class="card" style="margin-bottom: 24px;">
    <h2 style="margin: 0 0 12px; font-size: 1.125rem; font-weight: 700; color: #1f2937;">Attendance</h2>
    <p style="margin: 0 0 16px; font-size: 0.875rem; color: #6b7280; line-height: 1.5;">
        Crew leaders take attendance on the standalone <strong>Workers</strong> page at <a href="{{ url('/workers') }}" style="color:#0056A6;">/workers</a> (Crew Management tab → Take Attendance). Mark each worker Present, Late, or Absent. Once the attendance feature is connected to the database, recent attendance will appear here.
    </p>
    <a href="{{ url('/workers') }}" style="
        display: inline-block;
        padding: 10px 18px;
        border-radius: 8px;
        background: #0056A6;
        color: white;
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 600;
    ">Open Workers → Take Attendance</a>
</div>

<div class="card">
    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="text-align:left; border-bottom:2px solid #e5e7eb;">
                <th style="padding:12px 8px; font-size:13px; color:#6b7280; font-weight:600;">Name</th>
                <th style="padding:12px 8px; font-size:13px; color:#6b7280; font-weight:600;">Contact</th>
                <th style="padding:12px 8px; font-size:13px; color:#6b7280; font-weight:600;">Status</th>
                <th style="padding:12px 8px; font-size:13px; color:#6b7280; font-weight:600;">Specialization</th>
                <th style="padding:12px 8px; font-size:13px; color:#6b7280; font-weight:600;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($crews as $crew)
                <tr style="border-bottom:1px solid #f3f4f6;">
                    <td style="padding:12px 8px; font-size:14px; font-weight:500;">{{ $crew->name }}</td>
                    <td style="padding:12px 8px; font-size:14px;">
                        @if($crew->contact_phone)
                            <div style="margin-bottom:4px;">{{ $crew->contact_phone }}</div>
                        @endif
                        @if($crew->contact_email)
                            <div style="color:#6b7280; font-size:12px;">{{ $crew->contact_email }}</div>
                        @endif
                    </td>
                    <td style="padding:12px 8px;">
                        <span style="
                            padding:4px 12px;
                            border-radius:20px;
                            font-size:12px;
                            font-weight:500;
                            background:{{ $crew->status == 'available' ? '#dcfce7' : ($crew->status == 'on_site' ? '#fef3c7' : '#e5e7eb') }};
                            color:{{ $crew->status == 'available' ? '#166534' : ($crew->status == 'on_site' ? '#854d0e' : '#374151') }};
                        ">
                            {{ ucfirst(str_replace('_', ' ', $crew->status)) }}
                        </span>
                    </td>
                    <td style="padding:12px 8px; font-size:14px; color:#6b7280;">
                        {{ $crew->specialization ?? '—' }}
                    </td>
                    <td style="padding:12px 8px;">
                        <div style="display:flex; gap:8px;">
                            <a href="{{ route('operations.crews.show', $crew) }}" style="
                                color:#0056A6;
                                text-decoration:none;
                                font-size:13px;
                                padding:4px 8px;
                            ">View</a>
                            <a href="{{ route('operations.crews.edit', $crew) }}" style="
                                color:#0077CC;
                                text-decoration:none;
                                font-size:13px;
                                padding:4px 8px;
                            ">Edit</a>
                            <form action="{{ route('operations.crews.destroy', $crew) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this crew?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="
                                    background:none;
                                    border:none;
                                    color:#ef4444;
                                    cursor:pointer;
                                    font-size:13px;
                                    padding:4px 8px;
                                ">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="padding:24px; text-align:center; color:#9ca3af;">
                        No crews found. <a href="{{ route('operations.crews.create') }}" style="color:#0056A6;">Create your first crew</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
