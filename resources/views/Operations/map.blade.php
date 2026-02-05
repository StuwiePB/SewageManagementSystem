@extends('operations.layouts.app')

@section('content')

<div class="topbar">
    <div>
        <h1>GIS Map</h1>
        <p>Interactive map view of incidents and work orders</p>
    </div>
</div>

<div class="card" style="padding:0; overflow:hidden;">
    <div style="
        height:600px;
        background:#f3f4f6;
        display:flex;
        align-items:center;
        justify-content:center;
        color:#9ca3af;
        position:relative;
    ">
        <div style="text-align:center;">
            <div style="font-size:48px; margin-bottom:16px;">🗺️</div>
            <h3 style="margin:0 0 8px; color:#374151;">GIS Map Placeholder</h3>
            <p style="margin:0; color:#6b7280;">Map integration ready for implementation</p>
            <p style="margin:8px 0 0; font-size:12px; color:#9ca3af;">
                {{ $reports->count() }} active reports • {{ $workOrders->count() }} active work orders
            </p>
        </div>
    </div>
</div>

{{-- Map Legend --}}
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap:20px; margin-top:20px;">
    <div class="card">
        <h3 style="margin:0 0 12px; font-size:16px;">Active Reports</h3>
        <p style="margin:0; color:#6b7280; font-size:14px;">{{ $reports->count() }} reports with coordinates</p>
        @if($reports->count() > 0)
            <ul style="list-style:none; padding:0; margin-top:12px;">
                @foreach($reports->take(5) as $report)
                    <li style="padding:8px 0; border-bottom:1px solid #f3f4f6; font-size:13px;">
                        <strong>{{ $report->report_number }}</strong><br>
                        <span style="color:#6b7280;">{{ $report->location_address }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="card">
        <h3 style="margin:0 0 12px; font-size:16px;">Active Work Orders</h3>
        <p style="margin:0; color:#6b7280; font-size:14px;">{{ $workOrders->count() }} work orders with coordinates</p>
        @if($workOrders->count() > 0)
            <ul style="list-style:none; padding:0; margin-top:12px;">
                @foreach($workOrders->take(5) as $workOrder)
                    <li style="padding:8px 0; border-bottom:1px solid #f3f4f6; font-size:13px;">
                        <strong>{{ $workOrder->work_order_number }}</strong><br>
                        <span style="color:#6b7280;">{{ $workOrder->location_address }}</span>
                        @if($workOrder->crew)
                            <br><span style="color:#0077CC; font-size:12px;">Crew: {{ $workOrder->crew->name }}</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>

@endsection
