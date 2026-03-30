@extends('r_operators.layouts.app')

@section('content')

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">

<header class="topbar">
    <div>
        <h1>GIS Map</h1>
        <p>Interactive map view of incidents and work orders</p>
    </div>
</header>

<section class="card card-map" aria-label="Map">
    <div class="map-legend">
        <span class="map-legend-item"><span class="map-legend-dot report">R</span> Report</span>
        <span class="map-legend-item"><span class="map-legend-dot workorder">W</span> Work Order</span>
    </div>
    <div id="operations-map" class="map-container"></div>
</section>

<section class="grid-cards" aria-label="Map lists">
    <div class="card">
        <h3 class="section-head">Active Reports</h3>
        <p class="section-desc">{{ $reports->count() }} reports with coordinates</p>
        @if($reports->count() > 0)
            <ul class="map-list">
                @foreach($reports->take(5) as $report)
                    <li>
                        <strong>{{ $report->report_number }}</strong><br>
                        <span class="muted">{{ $report->location_address }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="card">
        <h3 class="section-head">Active Work Orders</h3>
        <p class="section-desc">{{ $workOrders->count() }} work orders with coordinates</p>
        @if($workOrders->count() > 0)
            <ul class="map-list">
                @foreach($workOrders->take(5) as $workOrder)
                    <li>
                        <strong>{{ $workOrder->work_order_number }}</strong><br>
                        <span class="muted">{{ $workOrder->location_address }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
(function () {
    var reports = @json($mapReports);
    var workOrders = @json($mapWorkOrders);

    var map = L.map('operations-map').setView([4.5, 114.7], 10);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    reports.forEach(function (r) {
        L.marker([r.lat, r.lng], { icon: L.divIcon({ className: 'report-marker', html: '<span style="background:#6A96FF;color:#fff;border-radius:50%;width:24px;height:24px;display:inline-flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;">R</span>' }) })
            .addTo(map)
            .bindPopup('<strong>' + (r.number || 'Report') + '</strong><br>' + (r.address || ''));
    });
    workOrders.forEach(function (w) {
        L.marker([w.lat, w.lng], { icon: L.divIcon({ className: 'wo-marker', html: '<span style="background:#22d3ee;color:#0f172a;border-radius:50%;width:24px;height:24px;display:inline-flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;">W</span>' }) })
            .addTo(map)
            .bindPopup('<strong>' + (w.number || 'Work Order') + '</strong><br>' + (w.address || ''));
    });

    var all = reports.map(function (r) { return [r.lat, r.lng]; }).concat(workOrders.map(function (w) { return [w.lat, w.lng]; }));
    if (all.length > 0) {
        var bounds = L.latLngBounds(all);
        map.fitBounds(bounds, { padding: [40, 40], maxZoom: 14 });
    }
})();
</script>

@endsection
