@extends('r_admin.layout')

@section('title', 'GIS Map')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<style>
    #gis-map { height: 70vh; min-height: 500px; width: 100%; border-radius: 12px; }
    .layer-toggles { display: flex; gap: 1.5rem; flex-wrap: wrap; margin-bottom: 1rem; }
    .layer-toggle { display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer; padding: 0.5rem 1rem; background: var(--bg-secondary); border: 1px solid rgba(106, 150, 255, 0.2); border-radius: 8px; color: var(--text-secondary); font-size: 0.9375rem; user-select: none; }
    .layer-toggle:hover { color: var(--text-primary); border-color: rgba(106, 150, 255, 0.4); }
    .layer-toggle.active { color: var(--accent-blue); border-color: var(--accent-blue); }
    .layer-toggle .dot { width: 18px; height: 18px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700; }
    .layer-toggle.report .dot { background: #6A96FF; color: #fff; }
    .layer-toggle.workorder .dot { background: var(--accent-green); color: var(--bg-primary); }
    .info-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.5rem; margin-top: 1.5rem; }
</style>
@endpush

@section('content')
<div class="header">
    <h1>GIS Map</h1>
    <p>Interactive map view of incidents and work orders</p>
</div>

<div class="card" style="padding: 1.5rem; overflow: hidden;">
    <div class="layer-toggles">
        <label class="layer-toggle report active" id="toggle-reports">
            <span class="dot">R</span>
            <span>Report</span>
        </label>
        <label class="layer-toggle workorder active" id="toggle-workorders">
            <span class="dot">W</span>
            <span>Work Order</span>
        </label>
    </div>
    <div id="gis-map"></div>
</div>

<div class="info-cards">
    <div class="card">
        <h3 style="margin: 0 0 0.5rem; font-size: 1rem;">Active Reports</h3>
        <p style="margin: 0; color: var(--text-secondary); font-size: 0.875rem;">{{ $reports->count() }} reports with coordinates</p>
    </div>
    <div class="card">
        <h3 style="margin: 0 0 0.5rem; font-size: 1rem;">Active Work Orders</h3>
        <p style="margin: 0; color: var(--text-secondary); font-size: 0.875rem;">{{ $workOrders->count() }} work orders with coordinates</p>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
(function () {
    var reports = @json($mapReports);
    var workOrders = @json($mapWorkOrders);

    var map = L.map('gis-map').setView([4.5, 114.7], 10);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    var reportLayer = L.layerGroup();
    var workOrderLayer = L.layerGroup();

    reports.forEach(function (r) {
        L.marker([r.lat, r.lng], {
            icon: L.divIcon({
                className: 'report-marker',
                html: '<span style="background:#6A96FF;color:#fff;border-radius:50%;width:24px;height:24px;display:inline-flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;">R</span>'
            })
        })
            .addTo(reportLayer)
            .bindPopup('<strong>' + (r.number || 'Report') + '</strong><br>' + (r.address || ''));
    });
    workOrders.forEach(function (w) {
        L.marker([w.lat, w.lng], {
            icon: L.divIcon({
                className: 'wo-marker',
                html: '<span style="background:#56FF8B;color:#1A1D2B;border-radius:50%;width:24px;height:24px;display:inline-flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;">W</span>'
            })
        })
            .addTo(workOrderLayer)
            .bindPopup('<strong>' + (w.number || 'Work Order') + '</strong><br>' + (w.address || '') + (w.crew ? '<br><span style="color:#56FF8B;">Crew: ' + w.crew + '</span>' : ''));
    });

    reportLayer.addTo(map);
    workOrderLayer.addTo(map);

    var all = reports.map(function (r) { return [r.lat, r.lng]; }).concat(workOrders.map(function (w) { return [w.lat, w.lng]; }));
    if (all.length > 0) {
        map.fitBounds(L.latLngBounds(all), { padding: [40, 40], maxZoom: 14 });
    }

    document.getElementById('toggle-reports').addEventListener('click', function () {
        this.classList.toggle('active');
        if (map.hasLayer(reportLayer)) map.removeLayer(reportLayer); else map.addLayer(reportLayer);
    });
    document.getElementById('toggle-workorders').addEventListener('click', function () {
        this.classList.toggle('active');
        if (map.hasLayer(workOrderLayer)) map.removeLayer(workOrderLayer); else map.addLayer(workOrderLayer);
    });
})();
</script>
@endsection
