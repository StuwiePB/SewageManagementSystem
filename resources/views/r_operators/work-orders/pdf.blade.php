<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Work Order {{ $workOrder->work_order_number }} – DMS Ops</title>
    <style>
        body { font-family: system-ui, sans-serif; padding: 24px; color: #1f2937; max-width: 800px; margin: 0 auto; }
        h1 { margin: 0 0 4px; font-size: 22px; color: #6A96FF; }
        .meta { font-size: 13px; color: #6b7280; margin-bottom: 24px; }
        .section { margin-bottom: 24px; }
        .section h2 { font-size: 14px; color: #6b7280; font-weight: 600; margin: 0 0 8px; text-transform: uppercase; letter-spacing: 0.02em; }
        .section p, .section .value { margin: 0 0 8px; font-size: 14px; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: 500; }
        .photos { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-top: 12px; }
        .photos img { width: 100%; height: 140px; object-fit: cover; border: 1px solid #e5e7eb; border-radius: 8px; }
        .photo-caption { font-size: 11px; color: #6b7280; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 13px; }
        th, td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { color: #6b7280; font-weight: 600; width: 140px; }
        .signature-block { margin-top: 40px; padding-top: 24px; border-top: 2px solid #e5e7eb; }
        .signature-block h2 { margin-bottom: 16px; }
        .signature-row { margin-bottom: 20px; }
        .signature-row label { display: block; font-size: 12px; color: #6b7280; font-weight: 600; margin-bottom: 4px; }
        .signature-line { border: none; border-bottom: 1px solid #374151; padding: 6px 0; font-size: 14px; width: 100%; max-width: 280px; background: transparent; }
        .signature-line::placeholder { color: #9ca3af; }
        @media print {
            body { padding: 16px; }
            .no-print { display: none !important; }
            .photos img { max-height: 120px; }
            .signature-block { margin-top: 32px; page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom:20px;">
        <button onclick="window.print()" style="padding:8px 16px; background:#6A96FF; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:500;">Print / Save as PDF</button>
    </div>

    <h1>Work Order {{ $workOrder->work_order_number }}</h1>
    <p class="meta">Generated {{ now()->format('M d, Y g:i A') }}</p>

    <div class="section">
        <h2>Overview</h2>
        <table>
            <tr><th>Type</th><td>{{ $workOrder->type }}</td></tr>
            <tr><th>Priority</th><td><span class="badge" style="background:#fef3c7; color:#854d0e;">{{ ucfirst($workOrder->priority) }}</span></td></tr>
            <tr><th>Status</th><td>{{ ucfirst(str_replace('_', ' ', $workOrder->status)) }}</td></tr>
            <tr><th>Location</th><td>{{ $workOrder->location_address }}</td></tr>
            @if($workOrder->district_display || $workOrder->mukim_display)
            <tr><th>District / Mukim</th><td>{{ $workOrder->district_display }}{{ $workOrder->mukim_display ? ' • ' . $workOrder->mukim_display : '' }}</td></tr>
            @endif
            @if($workOrder->latitude && $workOrder->longitude)
            <tr><th>Coordinates</th><td>{{ $workOrder->latitude }}, {{ $workOrder->longitude }}</td></tr>
            @endif
        </table>
    </div>

    @if($workOrder->description)
    <div class="section">
        <h2>Description / Situation details</h2>
        <p>{{ $workOrder->description }}</p>
    </div>
    @endif

    @if($workOrder->notes)
    <div class="section">
        <h2>Notes</h2>
        <p>{{ $workOrder->notes }}</p>
    </div>
    @endif

    @if($workOrder->report)
    <div class="section">
        <h2>Linked report</h2>
        <p><strong>{{ $workOrder->report->report_number }}</strong> – {{ ucfirst($workOrder->report->issue_type) }}</p>
    </div>
    @endif

    <div class="section">
        <h2>Timeline</h2>
        <table>
            <tr><th>Created</th><td>{{ $workOrder->created_at->format('M d, Y g:i A') }}</td></tr>
            @if($workOrder->assigned_at)<tr><th>Assigned</th><td>{{ $workOrder->assigned_at->format('M d, Y g:i A') }}</td></tr>@endif
            @if($workOrder->started_at)<tr><th>Started</th><td>{{ $workOrder->started_at->format('M d, Y g:i A') }}</td></tr>@endif
            @if($workOrder->completed_at)<tr><th>Completed</th><td>{{ $workOrder->completed_at->format('M d, Y g:i A') }}</td></tr>@endif
        </table>
    </div>

    @if($workOrder->photos->isNotEmpty())
    <div class="section">
        <h2>Site photos</h2>
        <div class="photos">
            @foreach($workOrder->photos as $photo)
            <div>
                <img src="{{ asset('storage/' . $photo->path) }}" alt="Site photo">
                <p class="photo-caption">{{ $photo->original_name ?? 'Photo ' . $loop->iteration }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="section signature-block">
        <h2>Approval (Higher ups – sign after review)</h2>
        <p style="font-size: 12px; color: #6b7280; margin-bottom: 16px;">Print and sign by hand, or use your PDF reader’s Fill &amp; Sign to complete below.</p>
        <div class="signature-row">
            <label for="approved-by">Approved by (name)</label>
            <div style="min-height: 28px; border-bottom: 1px solid #374151; width: 100%; max-width: 320px;">&nbsp;</div>
        </div>
        <div class="signature-row">
            <label>Signature</label>
            <div style="min-height: 48px; border-bottom: 1px solid #374151; width: 100%; max-width: 320px;">&nbsp;</div>
        </div>
        <div class="signature-row">
            <label>Date</label>
            <div style="min-height: 28px; border-bottom: 1px solid #374151; width: 100%; max-width: 160px;">&nbsp;</div>
        </div>
    </div>

    <p class="meta" style="margin-top:32px;">Drainage Management System – Work Order Document</p>
</body>
</html>
