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
