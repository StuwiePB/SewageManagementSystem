@extends('r_admin.layout')

@section('content')

<div class="header">
    <div>
        <h1>{{ $stats['title'] ?? 'Statistics' }}</h1>
        <p>{{ $stats['period_label'] ?? '' }} • Generated {{ $stats['generated_at'] ?? '' }}</p>
    </div>
    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem;">
        @php $params = request()->only(['period', 'type', 'start', 'end']); @endphp
        <a href="{{ route('admin.statistics.export.csv', $params) }}" class="btn btn-primary">Export CSV</a>
        <a href="{{ route('admin.statistics.export.pdf', $params) }}" target="_blank" rel="noopener" class="btn btn-secondary">Print / PDF</a>
        <a href="{{ route('admin.statistics.index') }}" class="btn btn-secondary">Back</a>
    </div>
</div>

@if($type === 'reports')
<div class="card" style="margin-bottom: 1.5rem;">
    <p class="info-label" style="margin: 0;">Total Reports</p>
    <p class="section-head" style="margin: 0.25rem 0 0;">{{ $stats['total'] ?? 0 }}</p>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem;">
    <div class="card">
        <h3 class="chart-card-title">By Status</h3>
        <div style="height: 260px;"><canvas id="chart-reports-status"></canvas></div>
    </div>
    <div class="card">
        <h3 class="chart-card-title">By Issue Type</h3>
        <div style="height: 260px;"><canvas id="chart-reports-issue"></canvas></div>
    </div>
</div>

<div class="card">
    <h3 class="chart-card-title">Daily Trend</h3>
    <div style="height: 280px;"><canvas id="chart-reports-daily"></canvas></div>
</div>
@endif

@if($type === 'work_orders')
<div class="stats-grid-4">
    <div class="card">
        <p>Total Work Orders</p>
        <h3>{{ $stats['total'] ?? 0 }}</h3>
    </div>
    <div class="card">
        <p>Completed</p>
        <h3>{{ $stats['completed'] ?? 0 }}</h3>
    </div>
    <div class="card">
        <p>Completion Rate</p>
        <h3>{{ $stats['completion_rate'] ?? 0 }}%</h3>
    </div>
    <div class="card">
        <p>Pending Backlog</p>
        <h3>{{ $stats['pending_backlog'] ?? 0 }}</h3>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem;">
    <div class="card">
        <h3 class="chart-card-title">By Status</h3>
        <div style="height: 260px;"><canvas id="chart-wo-status"></canvas></div>
    </div>
    <div class="card">
        <h3 class="chart-card-title">By Priority</h3>
        <div style="height: 260px;"><canvas id="chart-wo-priority"></canvas></div>
    </div>
</div>

<div class="card">
    <h3 class="chart-card-title">Daily Trend</h3>
    <div style="height: 280px;"><canvas id="chart-wo-daily"></canvas></div>
</div>
@endif

@if($type === 'geographic')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
<div class="card" style="margin-bottom: 1.5rem; padding: 0; overflow: hidden;">
    <div style="padding: 1rem 1.25rem; border-bottom: 1px solid rgba(106, 150, 255, 0.15); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
        <h3 class="chart-card-title" style="margin: 0;">Hotspot Map – Report / Work Order Intensity</h3>
        <select id="hotspot-metric" class="form-control" style="width: auto; min-width: 160px;">
            <option value="reports">Reports</option>
            <option value="work_orders">Work Orders</option>
        </select>
    </div>
    <div style="position: relative;">
        <div id="hotspot-map" style="height: 500px; width: 100%;"></div>
        <div id="hotspot-legend" style="position: absolute; bottom: 1.5rem; right: 1.5rem; background: var(--bg-secondary); padding: 0.75rem 1rem; border-radius: 8px; border: 1px solid rgba(106, 150, 255, 0.2); box-shadow: 0 2px 12px rgba(0,0,0,0.3); z-index: 1000; font-size: 0.75rem; color: var(--text-primary);">
            <div style="font-weight: 600; margin-bottom: 0.5rem;">Intensity</div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;"><span style="display: inline-block; width: 14px; height: 14px; border-radius: 2px; background: #166534;"></span> Very Low</div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;"><span style="display: inline-block; width: 14px; height: 14px; border-radius: 2px; background: #22c55e;"></span> Low</div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;"><span style="display: inline-block; width: 14px; height: 14px; border-radius: 2px; background: #eab308;"></span> Medium</div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;"><span style="display: inline-block; width: 14px; height: 14px; border-radius: 2px; background: #f97316;"></span> High</div>
            <div style="display: flex; align-items: center; gap: 0.5rem;"><span style="display: inline-block; width: 14px; height: 14px; border-radius: 2px; background: #dc2626;"></span> Very High</div>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom: 1.5rem;">
    <h3 class="chart-card-title">Top Mukims – Reports</h3>
    <div style="height: 320px;"><canvas id="chart-geo-mukim-reports"></canvas></div>
</div>

<div class="card">
    <h3 class="chart-card-title">Top Mukims – Work Orders</h3>
    <div style="height: 320px;"><canvas id="chart-geo-mukim-wo"></canvas></div>
</div>
@endif

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    {{-- Fixed hex, not var(--brudms-primary) — Canvas 2D's fillStyle/strokeStyle (which is what
         Chart.js hands these strings to) cannot resolve CSS custom properties at all; that only
         works inside real CSS. The browser silently rejects the invalid value and falls back to
         black, so every chart on this page (both Top Mukims bars, the reports/work-orders
         doughnuts, and the daily line chart) was rendering in the wrong colour regardless of
         theme — this wasn't a light/dark contrast issue, var() just never worked here at all. --}}
    const blue = '#2E7D8F';
    const colors = ['#2E7D8F', '#56FF8B', '#059669', '#d97706', '#FF5B5B', '#A86AFF', '#22d3ee', '#65a30d'];
    const textColor = '#B0B0B0';
    const gridColor = 'rgba(106, 150, 255, 0.1)';

    function makeChart(id, type, labels, data, options) {
        const el = document.getElementById(id);
        if (!el) return;
        if (!labels.length && !data.length) {
            el.parentElement.innerHTML = '<p style="color: ' + textColor + '; padding: 2rem; text-align: center;">No data</p>';
            return;
        }
        const defaultOpts = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: type !== 'line', labels: { color: textColor, padding: 12 } } },
            scales: type === 'line' || type === 'bar' ? {
                y: { beginAtZero: true, ticks: { stepSize: 1, color: textColor }, grid: { color: gridColor } },
                x: { ticks: { color: textColor }, grid: { color: gridColor } }
            } : {}
        };
        new Chart(el, {
            type: type,
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: type === 'bar' || type === 'line' ? blue : colors.slice(0, data.length),
                    borderColor: blue,
                    borderWidth: type === 'line' ? 2 : 0,
                    fill: type === 'line',
                    tension: type === 'line' ? 0.4 : 0,
                }]
            },
            options: options ? Object.assign({}, defaultOpts, options) : defaultOpts
        });
    }

    function barOptions() {
        return {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, ticks: { stepSize: 1, color: textColor }, grid: { color: gridColor } },
                y: { ticks: { color: textColor }, grid: { color: gridColor } }
            }
        };
    }

    @if($type === 'reports')
    @php
        $byStatus = $stats['by_status'] ?? [];
        $statusLabels = array_map(fn($k) => ucfirst(str_replace('_', ' ', $k)), array_keys($byStatus));
        $statusData = array_values($byStatus);
        $byIssue = $stats['by_issue_type'] ?? [];
        $issueLabels = array_map(fn($k) => ucfirst(str_replace('_', ' ', $k)), array_keys($byIssue));
        $issueData = array_values($byIssue);
        $byDay = $stats['by_day'] ?? [];
        $dayLabels = [];
        $dayData = [];
        foreach ($byDay as $row) {
            $dayLabels[] = \Carbon\Carbon::parse($row->date ?? $row['date'])->format('M d');
            $dayData[] = $row->count ?? $row['count'];
        }
    @endphp
    makeChart('chart-reports-status', 'doughnut', @json($statusLabels), @json($statusData));
    makeChart('chart-reports-issue', 'doughnut', @json($issueLabels), @json($issueData));
    makeChart('chart-reports-daily', 'line', @json($dayLabels), @json($dayData), { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } });
    @endif

    @if($type === 'work_orders')
    @php
        $byStatus = $stats['by_status'] ?? [];
        $woStatusLabels = array_map(fn($k) => ucfirst(str_replace('_', ' ', $k)), array_keys($byStatus));
        $woStatusData = array_values($byStatus);
        $byPriority = $stats['by_priority'] ?? [];
        $woPriorityLabels = array_map(fn($k) => ucfirst($k), array_keys($byPriority));
        $woPriorityData = array_values($byPriority);
        $woByDay = $stats['by_day'] ?? [];
        $woDayLabels = [];
        $woDayData = [];
        foreach ($woByDay as $row) {
            $woDayLabels[] = \Carbon\Carbon::parse($row->date ?? $row['date'])->format('M d');
            $woDayData[] = $row->count ?? $row['count'];
        }
    @endphp
    makeChart('chart-wo-status', 'doughnut', @json($woStatusLabels), @json($woStatusData));
    makeChart('chart-wo-priority', 'doughnut', @json($woPriorityLabels), @json($woPriorityData));
    makeChart('chart-wo-daily', 'line', @json($woDayLabels), @json($woDayData), { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } });
    @endif

    @if($type === 'geographic')
    @php
        $reportsDist = $stats['reports_by_district'] ?? collect();
        $reportsByDistrictObj = $reportsDist->pluck('count', 'district')->toArray();
        $woDist = $stats['work_orders_by_district'] ?? collect();
        $workOrdersByDistrictObj = $woDist->pluck('count', 'district')->toArray();
        $reportsDistLabels = $reportsDist->map(fn($r) => config("brunei.districts.{$r->district}") ?? $r->district)->values()->toArray();
        $reportsDistData = $reportsDist->pluck('count')->values()->toArray();
        $woDistLabels = $woDist->map(fn($r) => config("brunei.districts.{$r->district}") ?? $r->district)->values()->toArray();
        $woDistData = $woDist->pluck('count')->values()->toArray();
        $mukimReports = $stats['reports_by_mukim'] ?? collect();
        $mukimReportsLabels = $mukimReports->map(fn($r) => (config("brunei.mukims.{$r->district}.{$r->mukim}") ?? $r->mukim) . ' (' . (config("brunei.districts.{$r->district}") ?? $r->district) . ')')->values()->toArray();
        $mukimReportsData = $mukimReports->pluck('count')->values()->toArray();
        $mukimWo = $stats['work_orders_by_mukim'] ?? collect();
        $mukimWoLabels = $mukimWo->map(fn($r) => (config("brunei.mukims.{$r->district}.{$r->mukim}") ?? $r->mukim) . ' (' . (config("brunei.districts.{$r->district}") ?? $r->district) . ')')->values()->toArray();
        $mukimWoData = $mukimWo->pluck('count')->values()->toArray();
    @endphp
    makeChart('chart-geo-mukim-reports', 'bar', @json($mukimReportsLabels), @json($mukimReportsData), barOptions());
    makeChart('chart-geo-mukim-wo', 'bar', @json($mukimWoLabels), @json($mukimWoData), barOptions());
    @endif
})();
</script>

@if($type === 'geographic')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
(function() {
    var nameToSlug = { 'Belait': 'belait', 'BruneiandMuara': 'brunei-muara', 'Temburong': 'temburong', 'Tutong': 'tutong' };
    var intensityColors = ['#166534', '#22c55e', '#eab308', '#f97316', '#dc2626'];
    function getColor(pct) {
        if (pct <= 20) return intensityColors[0];
        if (pct <= 40) return intensityColors[1];
        if (pct <= 60) return intensityColors[2];
        if (pct <= 80) return intensityColors[3];
        return intensityColors[4];
    }
    function escapeHtml(s) {
        if (s === null || s === undefined) return '';
        var d = document.createElement('div');
        d.textContent = String(s);
        return d.innerHTML;
    }
    var reportsByDistrict = @json($reportsByDistrictObj ?? []);
    var workOrdersByDistrict = @json($workOrdersByDistrictObj ?? []);
    var reportMapPoints = @json($stats['report_map_points'] ?? []);
    var workOrderMapPoints = @json($stats['work_order_map_points'] ?? []);

    var BRUNEI_BOUNDS = L.latLngBounds([[3.8, 113.8], [5.3, 115.6]]);
    var map = L.map('hotspot-map', {
        minZoom: 9,
        maxBounds: BRUNEI_BOUNDS,
        maxBoundsViscosity: 1.0
    }).setView([4.5, 114.7], 9);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);

    // Pane above district fill so point markers stay clickable (polygons would cover them otherwise)
    var HOTSPOT_MARKER_PANE = 'hotspotPointMarkers';
    if (!map.getPane(HOTSPOT_MARKER_PANE)) {
        map.createPane(HOTSPOT_MARKER_PANE);
    }
    map.getPane(HOTSPOT_MARKER_PANE).style.zIndex = 650;
    map.getPane(HOTSPOT_MARKER_PANE).style.pointerEvents = 'auto';

    var currentMetric = 'reports';
    var layer = null;
    var markersLayer = L.layerGroup().addTo(map);

    function getCounts() { return currentMetric === 'reports' ? reportsByDistrict : workOrdersByDistrict; }

    function style(feature) {
        var name = feature.properties.NAME_1;
        var slug = nameToSlug[name] || name.toLowerCase().replace(/\s+/g, '-');
        var counts = getCounts();
        var count = counts[slug] || 0;
        var values = Object.values(counts);
        var max = Math.max.apply(null, values.length ? values : [1]);
        var pct = max > 0 ? (count / max) * 100 : 0;
        return {
            fillColor: getColor(pct),
            weight: 1.5,
            opacity: 1,
            color: '#1f2937',
            fillOpacity: 0.85
        };
    }

    function onEachFeature(feature, layer) {
        var name = feature.properties.NAME_1;
        var slug = nameToSlug[name] || name.toLowerCase().replace(/\s+/g, '-');
        var counts = getCounts();
        var count = counts[slug] || 0;
        var displayName = name === 'BruneiandMuara' ? 'Brunei-Muara' : name;
        layer.bindPopup('<strong>' + displayName + '</strong><br>Count: ' + count);
    }

    function fitMapToPointLayer() {
        var pts = currentMetric === 'reports' ? reportMapPoints : workOrderMapPoints;
        var coords = [];
        pts.forEach(function (p) {
            var lat = Number(p.lat);
            var lng = Number(p.lng);
            if (Number.isFinite(lat) && Number.isFinite(lng)) coords.push([lat, lng]);
        });
        if (!coords.length) return;
        var b = L.latLngBounds(coords);
        if (b.isValid()) map.fitBounds(b, { padding: [40, 40], maxZoom: 11 });
    }

    function renderPointMarkers() {
        markersLayer.clearLayers();
        var isReports = currentMetric === 'reports';
        var pts = isReports ? reportMapPoints : workOrderMapPoints;
        {{-- Fixed colours, not var(--brudms-primary) — that variable is literally white in
             light mode and a pale cyan in dark mode, which made the "reports" marker either
             invisible (white fill + white border) or washed out depending on theme. Same blue
             used for the "R" marker on the GIS map, for consistency across the two maps. --}}
        var fill = isReports ? '#2E7D8F' : '#f97316';
        var label = isReports ? 'R' : 'WO';
        pts.forEach(function (p) {
            var lat = Number(p.lat);
            var lng = Number(p.lng);
            if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;
            var popup = '<strong>' + escapeHtml(p.label) + '</strong>';
            if (p.detail) popup += '<br>' + escapeHtml(p.detail);
            if (p.status) popup += '<br><span style="opacity:0.9">' + escapeHtml(p.status) + '</span>';
            if (p.address) popup += '<br><small>' + escapeHtml(p.address) + '</small>';
            L.marker([lat, lng], {
                pane: HOTSPOT_MARKER_PANE,
                icon: L.divIcon({
                    className: 'hotspot-point-marker',
                    // The badge's width varies with label length ("R" vs "WO"), so it can't use
                    // a fixed iconSize/iconAnchor — instead a 0x0 icon anchored at the point,
                    // with the visible span self-centering via a CSS transform, keeps the badge
                    // centered on the actual lat/lng regardless of how wide it renders.
                    html: '<span style="position:absolute;top:0;left:0;transform:translate(-50%,-50%);'
                        + 'background:' + fill + ';color:#fff;border:2px solid #fff;border-radius:999px;'
                        + 'min-width:22px;height:22px;padding:0 4px;box-sizing:border-box;display:inline-flex;'
                        + 'align-items:center;justify-content:center;font-size:10px;font-weight:700;white-space:nowrap;'
                        + 'box-shadow:0 1px 4px rgba(0,0,0,0.35);">' + label + '</span>',
                    iconSize: [0, 0]
                })
            }).bindPopup(popup).addTo(markersLayer);
        });
    }

    function refreshDistrictStyles() {
        if (!layer) return;
        layer.eachLayer(function (lyr) {
            if (!lyr.feature) return;
            lyr.setStyle(style(lyr.feature));
            var name = lyr.feature.properties.NAME_1;
            var slug = nameToSlug[name] || name.toLowerCase().replace(/\s+/g, '-');
            var counts = getCounts();
            var count = counts[slug] || 0;
            var displayName = name === 'BruneiandMuara' ? 'Brunei-Muara' : name;
            lyr.bindPopup('<strong>' + displayName + '</strong><br>Count: ' + count);
        });
    }

    function updateChoropleth() {
        if (layer) {
            refreshDistrictStyles();
            renderPointMarkers();
            return;
        }
        fetch('{{ asset("geojson/brunei-districts.json") }}', { credentials: 'same-origin' })
            .then(function (r) {
                if (!r.ok) throw new Error('GeoJSON ' + r.status);
                return r.json();
            })
            .then(function (geojson) {
                layer = L.geoJSON(geojson, { style: style, onEachFeature: onEachFeature }).addTo(map);
                if (typeof layer.bringToBack === 'function') {
                    layer.bringToBack();
                }
                map.fitBounds(layer.getBounds(), { padding: [30, 30], maxZoom: 10 });
                renderPointMarkers();
                fitMapToPointLayer();
            })
            .catch(function (err) {
                console.error('Hotspot map: district GeoJSON failed (file must exist at public/geojson/brunei-districts.json)', err);
                renderPointMarkers();
                fitMapToPointLayer();
                if (!reportMapPoints.length && !workOrderMapPoints.length) {
                    map.setView([4.5, 114.7], 9);
                }
            });
    }

    document.getElementById('hotspot-metric').addEventListener('change', function() {
        currentMetric = this.value;
        updateChoropleth();
    });

    updateChoropleth();
})();
</script>
@endif

@endsection
