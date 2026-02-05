@extends('operations.layouts.app')

@section('content')

<div class="topbar">
    <div>
        <h1>{{ $crew->name }}</h1>
        <p>Crew Details</p>
    </div>
    <div style="display:flex; gap:12px;">
        <a href="{{ route('operations.crews.edit', $crew) }}" style="
            background:#0077CC;
            color:white;
            padding:10px 20px;
            border-radius:8px;
            text-decoration:none;
            font-weight:500;
        ">Edit</a>
        <a href="{{ route('operations.crews.index') }}" style="color:#6b7280; text-decoration:none; padding:10px 0;">← Back</a>
    </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:20px;">
    <div class="card">
        <h3 style="margin:0 0 16px;">Crew Information</h3>
        <div style="margin-bottom:12px;">
            <p style="margin:0; font-size:12px; color:#6b7280;">Name</p>
            <p style="margin:4px 0 0; font-size:16px; font-weight:500;">{{ $crew->name }}</p>
        </div>
        <div style="margin-bottom:12px;">
            <p style="margin:0; font-size:12px; color:#6b7280;">Status</p>
            <span style="
                display:inline-block;
                margin-top:4px;
                padding:4px 12px;
                border-radius:20px;
                font-size:12px;
                font-weight:500;
                background:{{ $crew->status == 'available' ? '#dcfce7' : ($crew->status == 'on_site' ? '#fef3c7' : '#e5e7eb') }};
                color:{{ $crew->status == 'available' ? '#166534' : ($crew->status == 'on_site' ? '#854d0e' : '#374151') }};
            ">
                {{ ucfirst(str_replace('_', ' ', $crew->status)) }}
            </span>
        </div>
        <div style="margin-bottom:12px;">
            <p style="margin:0; font-size:12px; color:#6b7280;">Specialization</p>
            <p style="margin:4px 0 0; font-size:16px;">{{ $crew->specialization ?? '—' }}</p>
        </div>
    </div>

    <div class="card">
        <h3 style="margin:0 0 16px;">Contact Information</h3>
        <div style="margin-bottom:12px;">
            <p style="margin:0; font-size:12px; color:#6b7280;">Phone</p>
            <p style="margin:4px 0 0; font-size:16px;">{{ $crew->contact_phone ?? '—' }}</p>
        </div>
        <div style="margin-bottom:12px;">
            <p style="margin:0; font-size:12px; color:#6b7280;">Email</p>
            <p style="margin:4px 0 0; font-size:16px;">{{ $crew->contact_email ?? '—' }}</p>
        </div>
        @if($crew->notes)
        <div style="margin-bottom:12px;">
            <p style="margin:0; font-size:12px; color:#6b7280;">Notes</p>
            <p style="margin:4px 0 0; font-size:14px; color:#6b7280;">{{ $crew->notes }}</p>
        </div>
        @endif
    </div>
</div>

<div class="card" style="margin-bottom:20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
        <div>
            <h3 style="margin:0 0 4px;">Crew Members</h3>
            <p style="margin:0; color:#6b7280; font-size:14px;">Workers assigned to this crew</p>
        </div>
        <a href="{{ route('operations.crews.unassigned') }}" style="
            color:#0056A6;
            text-decoration:none;
            font-size:13px;
            font-weight:500;
        ">Assign Workers →</a>
    </div>

    @if($crew->workers->count() > 0)
        <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap:20px;">
            @foreach($crew->workers as $worker)
                <div style="
                    border:1px solid #e5e7eb;
                    border-radius:12px;
                    padding:20px;
                    background:white;
                    transition:transform 0.2s, box-shadow 0.2s;
                " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.1)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                    {{-- Photo and Name --}}
                    <div style="text-align:center; margin-bottom:16px;">
                        <img src="{{ $worker->photo ?? 'https://ui-avatars.com/api/?name=' . urlencode($worker->name) . '&background=0056A6&color=fff&size=128' }}" 
                             alt="{{ $worker->name }}"
                             style="
                                width:120px;
                                height:120px;
                                border-radius:50%;
                                object-fit:cover;
                                border:3px solid #e5e7eb;
                                margin-bottom:12px;
                             ">
                        <h4 style="margin:0 0 4px; font-size:18px; font-weight:600; color:#1f2937;">{{ $worker->name }}</h4>
                        <p style="margin:0; font-size:13px; color:#6b7280;">{{ $worker->role ?? 'Worker' }}</p>
                        @if($worker->employee_id)
                            <p style="margin:4px 0 0; font-size:11px; color:#9ca3af;">ID: {{ $worker->employee_id }}</p>
                        @endif
                    </div>

                    {{-- Status Badge --}}
                    <div style="text-align:center; margin-bottom:16px;">
                        <span style="
                            padding:6px 16px;
                            border-radius:20px;
                            font-size:12px;
                            font-weight:500;
                            background:{{ $worker->status == 'available' ? '#dcfce7' : ($worker->status == 'on_site' ? '#fef3c7' : '#e5e7eb') }};
                            color:{{ $worker->status == 'available' ? '#166534' : ($worker->status == 'on_site' ? '#854d0e' : '#374151') }};
                        ">
                            {{ ucfirst(str_replace('_', ' ', $worker->status)) }}
                        </span>
                    </div>

                    {{-- Personal Details --}}
                    <div style="border-top:1px solid #f3f4f6; padding-top:16px;">
                        @if($worker->date_of_birth)
                        <div style="margin-bottom:10px;">
                            <p style="margin:0; font-size:11px; color:#6b7280; text-transform:uppercase; letter-spacing:0.5px;">Date of Birth</p>
                            <p style="margin:4px 0 0; font-size:14px; color:#374151;">{{ $worker->date_of_birth->format('M d, Y') }}</p>
                            <p style="margin:2px 0 0; font-size:12px; color:#9ca3af;">Age: {{ $worker->date_of_birth->diffInYears(now()) }} years</p>
                        </div>
                        @endif

                        @if($worker->contact_phone)
                        <div style="margin-bottom:10px;">
                            <p style="margin:0; font-size:11px; color:#6b7280; text-transform:uppercase; letter-spacing:0.5px;">Phone</p>
                            <p style="margin:4px 0 0; font-size:14px; color:#374151;">{{ $worker->contact_phone }}</p>
                        </div>
                        @endif

                        @if($worker->contact_email)
                        <div style="margin-bottom:10px;">
                            <p style="margin:0; font-size:11px; color:#6b7280; text-transform:uppercase; letter-spacing:0.5px;">Email</p>
                            <p style="margin:4px 0 0; font-size:14px; color:#374151; word-break:break-word;">{{ $worker->contact_email }}</p>
                        </div>
                        @endif

                        @if($worker->address)
                        <div style="margin-bottom:10px;">
                            <p style="margin:0; font-size:11px; color:#6b7280; text-transform:uppercase; letter-spacing:0.5px;">Address</p>
                            <p style="margin:4px 0 0; font-size:14px; color:#374151;">{{ $worker->address }}</p>
                        </div>
                        @endif

                        @if($worker->skills)
                        <div style="margin-bottom:10px;">
                            <p style="margin:0; font-size:11px; color:#6b7280; text-transform:uppercase; letter-spacing:0.5px;">Skills</p>
                            <p style="margin:4px 0 0; font-size:13px; color:#374151;">{{ $worker->skills }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div style="text-align:center; padding:48px;">
            <p style="margin:0; color:#9ca3af;">No workers assigned to this crew.</p>
            <a href="{{ route('operations.crews.unassigned') }}" style="
                display:inline-block;
                margin-top:12px;
                color:#0056A6;
                text-decoration:none;
                font-weight:500;
            ">Assign Workers →</a>
        </div>
    @endif
</div>

@if($crew->activeWorkOrders->count() > 0)
<div class="card">
    <h3 style="margin:0 0 16px;">Active Work Orders</h3>
    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="text-align:left; border-bottom:2px solid #e5e7eb;">
                <th style="padding:12px 8px; font-size:13px; color:#6b7280; font-weight:600;">Work Order</th>
                <th style="padding:12px 8px; font-size:13px; color:#6b7280; font-weight:600;">Location</th>
                <th style="padding:12px 8px; font-size:13px; color:#6b7280; font-weight:600;">Priority</th>
                <th style="padding:12px 8px; font-size:13px; color:#6b7280; font-weight:600;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($crew->activeWorkOrders as $workOrder)
                <tr style="border-bottom:1px solid #f3f4f6;">
                    <td style="padding:12px 8px;">
                        <a href="{{ route('operations.work-orders.show', $workOrder) }}" style="color:#0056A6; text-decoration:none;">
                            {{ $workOrder->work_order_number }}
                        </a>
                    </td>
                    <td style="padding:12px 8px; font-size:14px;">{{ $workOrder->location_address }}</td>
                    <td style="padding:12px 8px;">
                        <span style="
                            padding:4px 8px;
                            border-radius:4px;
                            font-size:12px;
                            font-weight:500;
                            background:{{ $workOrder->priority == 'critical' ? '#fee2e2' : ($workOrder->priority == 'high' ? '#fed7aa' : ($workOrder->priority == 'medium' ? '#fef3c7' : '#dcfce7')) }};
                            color:{{ $workOrder->priority == 'critical' ? '#991b1b' : ($workOrder->priority == 'high' ? '#9a3412' : ($workOrder->priority == 'medium' ? '#854d0e' : '#166534')) }};
                        ">
                            {{ ucfirst($workOrder->priority) }}
                        </span>
                    </td>
                    <td style="padding:12px 8px;">
                        <span style="
                            padding:4px 8px;
                            border-radius:4px;
                            font-size:12px;
                            font-weight:500;
                            background:#e0e7ff;
                            color:#3730a3;
                        ">
                            {{ ucfirst(str_replace('_', ' ', $workOrder->status)) }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@endsection
