<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $stats['title'] ?? 'Statistics' }} – DMS Ops</title>
    <style>
        body { font-family: system-ui, sans-serif; padding: 24px; color: #1f2937; }
        h1 { margin: 0 0 4px; font-size: 22px; color: #0d9488; }
        .meta { font-size: 13px; color: #6b7280; margin-bottom: 24px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { font-size: 12px; color: #6b7280; font-weight: 600; }
        h2 { font-size: 16px; margin: 24px 0 12px; }
        .kpi { display: inline-block; margin-right: 24px; margin-bottom: 16px; }
        .kpi strong { font-size: 24px; display: block; }
        .kpi span { font-size: 12px; color: #6b7280; }
        @media print {
            body { padding: 16px; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom:20px;">
        <button onclick="window.print()" style="padding:8px 16px; background:#0d9488; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:500;">Print / Save as PDF</button>
    </div>

    <h1>{{ $stats['title'] ?? 'Statistics' }}</h1>
    <p class="meta">Period: {{ $stats['period_label'] ?? '' }} &bull; Generated: {{ $stats['generated_at'] ?? '' }}</p>

    @if($type === 'reports')
        <div class="kpi"><strong>{{ $stats['total'] ?? 0 }}</strong><span>Total Reports</span></div>
        <h2>By Status</h2>
        <table>
            <thead><tr><th>Status</th><th>Count</th></tr></thead>
            <tbody>
                @foreach($stats['by_status'] ?? [] as $status => $count)
                <tr><td>{{ ucfirst(str_replace('_', ' ', $status)) }}</td><td>{{ $count }}</td></tr>
                @endforeach
            </tbody>
        </table>
        <h2>By Issue Type</h2>
        <table>
            <thead><tr><th>Issue Type</th><th>Count</th></tr></thead>
            <tbody>
                @foreach($stats['by_issue_type'] ?? [] as $issue => $count)
                <tr><td>{{ ucfirst(str_replace('_', ' ', $issue)) }}</td><td>{{ $count }}</td></tr>
                @endforeach
            </tbody>
        </table>
        <h2>Daily Trend</h2>
        <table>
            <thead><tr><th>Date</th><th>Count</th></tr></thead>
            <tbody>
                @foreach($stats['by_day'] ?? [] as $row)
                <tr><td>{{ \Carbon\Carbon::parse($row->date ?? $row['date'])->format('M d, Y') }}</td><td>{{ $row->count ?? $row['count'] }}</td></tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($type === 'work_orders')
        <div class="kpi"><strong>{{ $stats['total'] ?? 0 }}</strong><span>Total Work Orders</span></div>
        <div class="kpi"><strong>{{ $stats['completed'] ?? 0 }}</strong><span>Completed</span></div>
        <div class="kpi"><strong>{{ $stats['completion_rate'] ?? 0 }}%</strong><span>Completion Rate</span></div>
        <div class="kpi"><strong>{{ $stats['pending_backlog'] ?? 0 }}</strong><span>Pending Backlog</span></div>
        <h2>By Status</h2>
        <table>
            <thead><tr><th>Status</th><th>Count</th></tr></thead>
            <tbody>
                @foreach($stats['by_status'] ?? [] as $status => $count)
                <tr><td>{{ ucfirst(str_replace('_', ' ', $status)) }}</td><td>{{ $count }}</td></tr>
                @endforeach
            </tbody>
        </table>
        <h2>By Priority</h2>
        <table>
            <thead><tr><th>Priority</th><th>Count</th></tr></thead>
            <tbody>
                @foreach($stats['by_priority'] ?? [] as $priority => $count)
                <tr><td>{{ ucfirst($priority) }}</td><td>{{ $count }}</td></tr>
                @endforeach
            </tbody>
        </table>
        <h2>Daily Trend</h2>
        <table>
            <thead><tr><th>Date</th><th>Count</th></tr></thead>
            <tbody>
                @foreach($stats['by_day'] ?? [] as $row)
                <tr><td>{{ \Carbon\Carbon::parse($row->date ?? $row['date'])->format('M d, Y') }}</td><td>{{ $row->count ?? $row['count'] }}</td></tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($type === 'geographic')
        <h2>Reports by District (Hotspots)</h2>
        <table>
            <thead><tr><th>District</th><th>Count</th></tr></thead>
            <tbody>
                @foreach($stats['reports_by_district'] ?? [] as $row)
                <tr><td>{{ config("brunei.districts.{$row->district}") ?? $row->district }}</td><td>{{ $row->count }}</td></tr>
                @endforeach
            </tbody>
        </table>
        <h2>Work Orders by District (Hotspots)</h2>
        <table>
            <thead><tr><th>District</th><th>Count</th></tr></thead>
            <tbody>
                @foreach($stats['work_orders_by_district'] ?? [] as $row)
                <tr><td>{{ config("brunei.districts.{$row->district}") ?? $row->district }}</td><td>{{ $row->count }}</td></tr>
                @endforeach
            </tbody>
        </table>
        <h2>Top Mukims – Reports</h2>
        <table>
            <thead><tr><th>Mukim</th><th>District</th><th>Count</th></tr></thead>
            <tbody>
                @foreach($stats['reports_by_mukim'] ?? [] as $row)
                <tr><td>{{ config("brunei.mukims.{$row->district}.{$row->mukim}") ?? $row->mukim }}</td><td>{{ config("brunei.districts.{$row->district}") ?? $row->district }}</td><td>{{ $row->count }}</td></tr>
                @endforeach
            </tbody>
        </table>
        <h2>Top Mukims – Work Orders</h2>
        <table>
            <thead><tr><th>Mukim</th><th>District</th><th>Count</th></tr></thead>
            <tbody>
                @foreach($stats['work_orders_by_mukim'] ?? [] as $row)
                <tr><td>{{ config("brunei.mukims.{$row->district}.{$row->mukim}") ?? $row->mukim }}</td><td>{{ config("brunei.districts.{$row->district}") ?? $row->district }}</td><td>{{ $row->count }}</td></tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <p class="meta" style="margin-top:32px;">DMS Ops – Public Works Department &bull; Admin</p>
</body>
</html>
