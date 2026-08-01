@extends('r_operators.layouts.app')

@section('content')

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<style>
    .gis-layer-toggles { display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 0.85rem; }
    .gis-layer-toggle {
        display: inline-flex; align-items: center; gap: 0.45rem; cursor: pointer;
        padding: 0.45rem 0.85rem; background: rgba(26,29,43,0.85);
        border: 1px solid rgba(106, 150, 255, 0.25); border-radius: 8px;
        color: #cbd5e1; font-size: 0.875rem;
    }
    .gis-layer-toggle.active { color: #8ab0ff; border-color: var(--brudms-primary); }
</style>

<header class="topbar">
    <div>
        <h1>GIS Map</h1>
        <p>Brunei terrain + hex drainage risk grid, scored hourly from live rainfall forecasts. Click a hexagon for its factor breakdown.</p>
    </div>
</header>

<section class="card card-map" aria-label="Map">
    <div class="gis-layer-toggles">
        <label class="gis-layer-toggle" id="toggle-terrain"><span>Terrain map</span></label>
        <label class="gis-layer-toggle active" id="toggle-risk-grid"><span>Risk grid</span></label>
    </div>
    <div class="map-legend">
        <span class="map-legend-item"><span class="map-legend-dot report">R</span> Report</span>
        <span class="map-legend-item"><span class="map-legend-dot workorder">W</span> Work Order</span>
    </div>
    <div id="operations-map-wrapper" style="position: relative; overflow: visible;">
        <div id="operations-map" class="map-container"></div>
        <div id="risk-grid-panel-ops" class="risk-grid-panel"></div>
    </div>
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
@include('partials.gis-risk-grid')
@include('partials.gis-weather-widget')
@include('partials.gis-safe-route')
<script>
(function () {
    var reports = @json($mapReports);
    var workOrders = @json($mapWorkOrders);
    var bruneiBounds = L.latLngBounds(L.latLng(3.70, 113.75), L.latLng(5.60, 115.85));

    var map = L.map('operations-map', {
        center: [4.9031, 114.9398],
        zoom: 10,
        minZoom: 9,
        maxZoom: 17,
        maxBounds: bruneiBounds,
        maxBoundsViscosity: 1.0
    });

    var osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    });
    var baseGroup = L.layerGroup([osmLayer]).addTo(map);

    var riskGrid = window.RiskGrid.init(map, { satelliteLayer: baseGroup, instanceKey: 'ops', panelHostId: 'risk-grid-panel-ops' });
    if (window.BruneiGisWeather) {
        window.BruneiGisWeather.init(map, { hostId: 'operations-map-wrapper' });
    }

    document.getElementById('toggle-terrain').addEventListener('click', function () {
        var on = !this.classList.contains('active');
        this.classList.toggle('active', on);
        riskGrid.setBaseTerrain(on);
    });
    document.getElementById('toggle-risk-grid').addEventListener('click', function () {
        var on = !this.classList.contains('active');
        this.classList.toggle('active', on);
        riskGrid.toggleGrid(on);
        document.getElementById('risk-grid-panel-ops').style.display = on ? '' : 'none';
    });

    reports.forEach(function (r) {
        L.marker([r.lat, r.lng], { icon: L.divIcon({ className: 'report-marker', html: '<span style="background:var(--brudms-primary);color:#fff;border-radius:50%;width:24px;height:24px;display:inline-flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;">R</span>' }) })
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
        map.fitBounds(L.latLngBounds(all), { padding: [40, 40], maxZoom: 14 });
    } else {
        map.fitBounds(bruneiBounds, { padding: [20, 20] });
    }

    if (window.GisSafeRoute) {
        window.GisSafeRoute.init(map, {
            containerId: 'operations-map-wrapper',
            analyzeUrl: @json(route('safe-route.analyze')),
            csrf: @json(csrf_token())
        });
    }
})();
</script>

@endsection
