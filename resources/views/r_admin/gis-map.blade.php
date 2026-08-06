@extends('r_admin.layout')

@section('title', 'GIS Map')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<style>
    #gis-map { height: 70vh; min-height: 500px; width: 100%; border-radius: 12px; }
    #gis-map-customer { height: 70vh; min-height: 500px; width: 100%; border-radius: 12px; }
    .layer-toggles { display: flex; gap: 1.5rem; flex-wrap: wrap; margin-bottom: 1rem; }
    .layer-toggle { display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer; padding: 0.5rem 1rem; background: var(--bg-secondary); border: 1px solid rgba(106, 150, 255, 0.2); border-radius: 8px; color: var(--text-secondary); font-size: 0.9375rem; user-select: none; }
    .layer-toggle:hover { color: var(--text-primary); border-color: rgba(106, 150, 255, 0.4); }
    .layer-toggle.active { color: var(--accent-blue); border-color: var(--accent-blue); }
    .layer-toggle .dot { width: 18px; height: 18px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700; }
    .layer-toggle.report .dot { background: #2E7D8F; color: #fff; }
    .layer-toggle.workorder .dot { background: #15803D; color: #fff; }
    .info-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.5rem; margin-top: 1.5rem; }
    .customer-gis-stats {
        margin-bottom: 1rem;
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        background: rgba(97, 107, 110, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.21);
        border-radius: 10px;
        overflow: hidden;
    }
    .customer-gis-stat {
        padding: 0.7rem 0.75rem;
        text-align: center;
    }
    .customer-gis-stat + .customer-gis-stat {
        border-left: 1px solid rgba(255, 255, 255, 0.35);
    }
    .customer-gis-stat-label {
        font-size: 0.78rem;
        color: rgba(255, 255, 255, 0.9);
        font-weight: 600;
        display: block;
        margin-bottom: 0.2rem;
    }
    .customer-gis-stat-value {
        font-size: 1.55rem;
        color: var(--text-primary);
        font-weight: 700;
        line-height: 1;
    }
    .customer-gis-legend {
        margin-bottom: 0.9rem;
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .customer-gis-legend-item {
        border: 1px solid rgba(255, 255, 255, 0.22);
        background: rgba(97, 107, 110, 0.18);
        color: var(--text-primary);
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 700;
        padding: 0.35rem 0.62rem;
        display: inline-flex;
        align-items: center;
        gap: 0.42rem;
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.16s ease;
    }
    .customer-gis-legend-item:hover {
        border-color: rgba(106, 150, 255, 0.68);
        transform: translateY(-1px);
    }
    .customer-gis-legend-item.is-active {
        border-color: rgba(106, 150, 255, 0.9);
        box-shadow: 0 0 0 1px rgba(106, 150, 255, 0.35) inset;
    }
    .customer-gis-legend-dot {
        width: 0.62rem;
        height: 0.62rem;
        border-radius: 999px;
        flex-shrink: 0;
    }
    .customer-gis-legend-count {
        background: rgba(255, 255, 255, 0.18);
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.05rem 0.42rem;
        line-height: 1.35;
    }
    .district-tooltip {
        background: rgba(26, 29, 43, 0.95);
        color: #fff;
        border: 1px solid rgba(106, 150, 255, 0.45);
        border-radius: 6px;
        box-shadow: none;
        font-size: 0.78rem;
        font-weight: 600;
        padding: 0.25rem 0.45rem;
    }
    .district-tooltip:before {
        display: none;
    }
    .leaflet-container .leaflet-interactive:focus {
        outline: none !important;
    }
    .gis-layer-toggles { display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 0.85rem; align-items: center; }
    .gis-layer-toggle {
        display: inline-flex; align-items: center; gap: 0.45rem; cursor: pointer;
        padding: 0.45rem 0.85rem; background: var(--bg-secondary);
        border: 1px solid rgba(106, 150, 255, 0.25); border-radius: 8px;
        color: var(--text-secondary); font-size: 0.875rem; user-select: none;
    }
    .gis-layer-toggle.active { color: var(--accent-blue); border-color: var(--accent-blue); }
</style>
@endpush

@section('content')
@php
    $customerMapRows = collect($mapCustomerReports ?? []);
    $customerCountResolved = $customerMapRows->where('status', 'resolved')->count();
    $customerCountInProgress = $customerMapRows->filter(fn ($r) => in_array(($r['status'] ?? null), ['in_progress', 'under_review'], true))->count();
    $customerCountActive = $customerMapRows->filter(fn ($r) => !in_array(($r['status'] ?? null), ['resolved', 'in_progress', 'under_review'], true))->count();
@endphp
<div class="header">
    <h1>GIS Map</h1>
    <p>Interactive map view of incidents and work orders</p>
</div>

<div class="card" id="admin-ops-map-section" style="padding: 1.5rem; overflow: visible;">
    <h3 class="section-head" style="margin: 0 0 1rem;">Operations map</h3>
    <p class="section-desc" style="margin: 0 0 0.75rem;">Terrain view + hex drainage risk grid, scored hourly from live rainfall forecasts (yellow/orange/red = elevated). Click a hexagon for its factor breakdown.</p>
    <div class="gis-layer-toggles">
        <label class="gis-layer-toggle" id="toggle-terrain"><span>Terrain map</span></label>
        <label class="gis-layer-toggle active" id="toggle-risk-grid"><span>Risk grid</span></label>
    </div>
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
    <div id="gis-map-wrapper" style="position: relative; overflow: visible;">
        <div id="gis-map"></div>
        <div id="risk-grid-panel-admin-ops" class="risk-grid-panel"></div>
    </div>
</div>

<div class="card" id="customer-map-section" style="padding: 1.5rem; overflow: hidden; margin-top: 1.5rem; display: none;">
    <h3 class="section-head" style="margin: 0 0 0.5rem;">Customer-style report map</h3>
    <p class="section-desc" style="margin-top: 0; margin-bottom: 1rem;">Satellite-style view focused on report points, adapted from the customer live map experience.</p>
    <div class="customer-gis-stats">
        <div class="customer-gis-stat">
            <span class="customer-gis-stat-label">Active incidents</span>
            <span class="customer-gis-stat-value">{{ $customerCountActive }}</span>
        </div>
        <div class="customer-gis-stat">
            <span class="customer-gis-stat-label">Case in progress</span>
            <span class="customer-gis-stat-value">{{ $customerCountInProgress }}</span>
        </div>
        <div class="customer-gis-stat">
            <span class="customer-gis-stat-label">Resolved</span>
            <span class="customer-gis-stat-value">{{ $customerCountResolved }}</span>
        </div>
    </div>
    <p class="section-desc" style="margin: 0 0 0.4rem; font-size: 0.8rem;">Filter by issue type — click a pill to isolate reports, click again (or "All issues") to reset.</p>
    <div id="customer-gis-legend" class="customer-gis-legend" aria-label="Filter reports by issue type"></div>
    <p class="section-desc" style="margin: 0 0 0.4rem; font-size: 0.8rem;">Filter by district</p>
    <div id="customer-gis-district-legend" class="customer-gis-legend" aria-label="Filter reports by district"></div>
    <div id="gis-map-customer-wrapper" style="position: relative; overflow: visible;">
        <div id="gis-map-customer"></div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
@include('partials.gis-risk-grid')
@include('partials.gis-weather-widget')
<script>
(function () {
    var reports = @json($mapReports);
    var workOrders = @json($mapWorkOrders);
    var customerReports = @json($mapCustomerReports ?? []);
    var currentView = @json($mapView ?? 'admin_ops');
    var bruneiBounds = L.latLngBounds(
        L.latLng(3.70, 113.75),
        L.latLng(5.60, 115.85)
    );
    var districtBorderStyle = {
        color: 'rgba(106, 150, 255, 0.9)',
        weight: 2.5,
        opacity: 0.95,
        fill: true,
        fillColor: 'var(--brudms-primary)',
        fillOpacity: 0.02
    };
    var districtHoverStyle = {
        color: '#8ab0ff',
        weight: 3.2,
        opacity: 1,
        fillOpacity: 0.05
    };

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function formatStatusLabel(status) {
        var key = String(status || '').trim().toLowerCase();
        if (key === 'in_progress') return 'In Progress';
        if (key === 'under_review') return 'Under Review';
        if (key === 'on_site') return 'On Site';
        if (key === 'on_the_way') return 'On The Way';
        if (key === 'pending_approval') return 'Pending Approval';
        if (key === 'resolved') return 'Resolved';
        if (key === 'completed') return 'Completed';
        if (key === 'assigned') return 'Assigned';
        if (key === 'pending') return 'Pending';
        if (!key) return 'Pending';
        return key.replace(/_/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); });
    }

    function markerColorsForStatus(status) {
        var key = String(status || 'pending').toLowerCase();
        if (key === 'resolved') return { fill: '#00FF26', stroke: '#00b31a' };
        if (key === 'in_progress' || key === 'under_review') return { fill: '#FFAE00', stroke: '#cc8800' };
        return { fill: '#ff0000', stroke: '#b30000' };
    }

    /** Higher = more urgent — used to sort the issue legend and to pick a group's dot colour. */
    function statusSeverityRank(status) {
        var key = String(status || 'pending').toLowerCase();
        if (key === 'resolved') return 0;
        if (key === 'in_progress' || key === 'under_review') return 1;
        return 2;
    }

    function formatIssueLabel(raw) {
        var text = (raw && String(raw).trim()) ? String(raw).trim() : 'Not specified';
        return text.replace(/_/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); });
    }

    function popupLine(label, value) {
        return '<div style="margin-top:2px;"><strong style="color:#6f8fdf;">' + escapeHtml(label) + ':</strong> '
            + '<strong style="color:#1f2937;">' + escapeHtml(value) + '</strong></div>';
    }

    function districtName(feature) {
        var raw = feature && feature.properties ? (feature.properties.NAME_1 || '') : '';
        if (raw === 'BruneiandMuara') return 'Brunei-Muara';
        return raw || 'District';
    }

    /** Standard ray-casting point-in-polygon test; ring is a list of [lng, lat] pairs (GeoJSON order). */
    function pointInRing(lng, lat, ring) {
        var inside = false;
        for (var i = 0, j = ring.length - 1; i < ring.length; j = i++) {
            var xi = ring[i][0], yi = ring[i][1];
            var xj = ring[j][0], yj = ring[j][1];
            var intersect = ((yi > lat) !== (yj > lat)) && (lng < (xj - xi) * (lat - yi) / (yj - yi) + xi);
            if (intersect) inside = !inside;
        }
        return inside;
    }

    function pointInFeatureGeometry(lng, lat, geometry) {
        if (!geometry) return false;
        var polygons = geometry.type === 'MultiPolygon' ? geometry.coordinates : [geometry.coordinates];
        for (var p = 0; p < polygons.length; p++) {
            var rings = polygons[p];
            if (!rings.length || !pointInRing(lng, lat, rings[0])) continue;
            var inHole = false;
            for (var h = 1; h < rings.length; h++) {
                if (pointInRing(lng, lat, rings[h])) { inHole = true; break; }
            }
            if (!inHole) return true;
        }
        return false;
    }

    /** Same point-in-polygon method scripts/generate-cells.js uses server-side for risk cells — reports have no stored district column, so this is derived client-side from the same district boundary GeoJSON already used to draw the district layer. */
    function districtForPoint(lat, lng, geojson) {
        if (!geojson || !geojson.features) return null;
        for (var i = 0; i < geojson.features.length; i++) {
            var feature = geojson.features[i];
            if (pointInFeatureGeometry(lng, lat, feature.geometry)) {
                return districtName(feature);
            }
        }
        return null;
    }

    function addDistrictLayer(targetMap, styleOverride) {
        fetch('{{ asset("geojson/brunei-districts.json") }}')
            .then(function (response) { return response.json(); })
            .then(function (geojson) {
                L.geoJSON(geojson, {
                    interactive: true,
                    style: function () {
                        return Object.assign({}, districtBorderStyle, styleOverride || {});
                    },
                    onEachFeature: function (feature, layer) {
                        layer.bindTooltip(districtName(feature), {
                            sticky: true,
                            direction: 'top',
                            className: 'district-tooltip'
                        });
                        layer.on('mouseover', function () {
                            this.setStyle(districtHoverStyle);
                        });
                        layer.on('mouseout', function () {
                            this.setStyle(Object.assign({}, districtBorderStyle, styleOverride || {}));
                        });
                    }
                }).addTo(targetMap);
            })
            .catch(function () {});
    }

    var overviewPadding = [20, 20];
    var adminOpsOverviewMaxZoom = 10;
    var customerOverviewMaxZoom = 10;

    var map = L.map('gis-map', {
        center: [4.9031, 114.9398],
        zoom: 13,
        minZoom: 9,
        maxZoom: 18,
        maxBounds: bruneiBounds,
        maxBoundsViscosity: 1.0,
        inertia: false,
        bounceAtZoomLimits: false,
        zoomAnimation: true,
        fadeAnimation: true,
        markerZoomAnimation: true,
        zoomControl: true,
    });
    var satelliteImagery = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Tiles &copy; Esri',
        maxZoom: 18,
        maxNativeZoom: 18,
        updateWhenZooming: false,
        updateWhenIdle: true,
        keepBuffer: 10
    });
    var satelliteLabels = L.tileLayer('https://services.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Labels &copy; Esri',
        opacity: 0.9,
        maxZoom: 18,
        maxNativeZoom: 18,
        updateWhenZooming: false,
        updateWhenIdle: true,
        keepBuffer: 10
    });
    var satelliteGroup = L.layerGroup([satelliteImagery, satelliteLabels]).addTo(map);
    map.setMaxBounds(bruneiBounds);
    var minOpsZoom = map.getBoundsZoom(bruneiBounds, true);
    if (minOpsZoom > map.getMinZoom()) {
        map.setMinZoom(minOpsZoom);
    }
    map.on('drag', function () {
        map.panInsideBounds(bruneiBounds, { animate: false });
    });
    map.on('zoomend', function () {
        map.panInsideBounds(bruneiBounds, { animate: false });
    });
    addDistrictLayer(map);

    var riskGrid = window.RiskGrid.init(map, { satelliteLayer: satelliteGroup, instanceKey: 'admin-ops', panelHostId: 'risk-grid-panel-admin-ops' });
    if (window.BruneiGisWeather) {
        window.BruneiGisWeather.init(map, { hostId: 'gis-map-wrapper' });
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
        document.getElementById('risk-grid-panel-admin-ops').style.display = on ? '' : 'none';
    });

    var reportLayer = L.layerGroup();
    var workOrderLayer = L.layerGroup();

    {{-- Fixed, theme-independent marker colours — these used to reference --brudms-primary /
         --text-primary, which flip between light/dark admin themes. Since the marker's own
         circle colour didn't flip with them, that produced near-invisible combinations (e.g.
         white-on-white "R" in light mode, ~1.2:1 "W" in dark mode). Leaflet popups also always
         render on a plain white background regardless of page theme, so popup text needs a
         colour that works there specifically, not a page-theme variable. --}}
    reports.forEach(function (r) {
        L.marker([r.lat, r.lng], {
            icon: L.divIcon({
                className: 'report-marker',
                html: '<span style="background:#2E7D8F;color:#fff;border-radius:50%;width:24px;height:24px;display:inline-flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;">R</span>'
            })
        })
            .addTo(reportLayer)
            .bindPopup('<strong>' + (r.number || 'Report') + '</strong><br>' + (r.address || ''));
    });
    workOrders.forEach(function (w) {
        L.marker([w.lat, w.lng], {
            icon: L.divIcon({
                className: 'wo-marker',
                html: '<span style="background:#15803D;color:#fff;border-radius:50%;width:24px;height:24px;display:inline-flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;">W</span>'
            })
        })
            .addTo(workOrderLayer)
            .bindPopup('<strong>' + (w.number || 'Work Order') + '</strong><br>' + (w.address || '') + (w.crew ? '<br><span style="color:#15803D;">Crew: ' + w.crew + '</span>' : ''));
    });

    reportLayer.addTo(map);
    workOrderLayer.addTo(map);

    var all = reports.map(function (r) { return [r.lat, r.lng]; }).concat(workOrders.map(function (w) { return [w.lat, w.lng]; }));
    if (all.length > 0) {
        map.fitBounds(L.latLngBounds(all), { padding: overviewPadding, maxZoom: adminOpsOverviewMaxZoom });
    } else {
        map.fitBounds(bruneiBounds, { padding: overviewPadding, maxZoom: adminOpsOverviewMaxZoom });
    }

    document.getElementById('toggle-reports').addEventListener('click', function () {
        this.classList.toggle('active');
        if (map.hasLayer(reportLayer)) map.removeLayer(reportLayer); else map.addLayer(reportLayer);
    });
    document.getElementById('toggle-workorders').addEventListener('click', function () {
        this.classList.toggle('active');
        if (map.hasLayer(workOrderLayer)) map.removeLayer(workOrderLayer); else map.addLayer(workOrderLayer);
    });

    var customerMap = L.map('gis-map-customer', {
        center: [4.9031, 114.9398],
        zoom: 13,
        minZoom: 9,
        maxZoom: 18,
        maxBounds: bruneiBounds,
        maxBoundsViscosity: 1.0,
        inertia: false,
        bounceAtZoomLimits: false,
        zoomAnimation: true,
        fadeAnimation: true,
        markerZoomAnimation: true,
        zoomControl: true,
    });
    customerMap.setMaxBounds(bruneiBounds);
    var minCustomerZoom = customerMap.getBoundsZoom(bruneiBounds, true);
    if (minCustomerZoom > customerMap.getMinZoom()) {
        customerMap.setMinZoom(minCustomerZoom);
    }
    customerMap.on('drag', function () {
        customerMap.panInsideBounds(bruneiBounds, { animate: false });
    });
    customerMap.on('zoomend', function () {
        customerMap.panInsideBounds(bruneiBounds, { animate: false });
    });

    var customerSatellite = L.layerGroup();
    // Deliberately no RiskGrid.init() here — the hex risk grid is an internal JKR
    // decision-support overlay, not something this customer-preview pane should surface.
    if (window.BruneiGisWeather) {
        window.BruneiGisWeather.init(customerMap, { hostId: 'gis-map-customer-wrapper' });
    }

    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Tiles &copy; Esri',
        maxZoom: 18,
        maxNativeZoom: 18,
        updateWhenZooming: false,
        updateWhenIdle: true,
        keepBuffer: 10
    }).addTo(customerMap);
    L.tileLayer('https://services.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Labels &copy; Esri',
        opacity: 0.9,
        maxZoom: 18,
        maxNativeZoom: 18,
        updateWhenZooming: false,
        updateWhenIdle: true,
        keepBuffer: 10
    }).addTo(customerMap);

    addDistrictLayer(customerMap);

    var customerBounds = [];
    var customerLegend = document.getElementById('customer-gis-legend');
    var customerMarkerRefs = [];
    var issueGroups = {}; // key -> { label, markers: [], severityRank }

    customerReports.forEach(function (r) {
        if (r.lat == null || r.lng == null) return;
        var status = r.status || 'pending';
        var markerColors = markerColorsForStatus(status);
        var issueRaw = (r.problem_type && String(r.problem_type).trim()) ? String(r.problem_type).trim() : 'Not specified';
        var issueKey = issueRaw.toLowerCase();
        var issueText = formatIssueLabel(issueRaw);
        var addressText = (r.address && String(r.address).trim()) ? String(r.address).trim() : 'No address provided';
        var statusText = formatStatusLabel(r.status);
        var popupHtml = '<div style="min-width:230px; max-width:270px;">';
        if (r.photo_url) {
            popupHtml += '<img src="' + escapeHtml(r.photo_url) + '" alt="Report photo" '
                + 'style="width:100%; max-height:120px; object-fit:cover; border-radius:8px; margin-bottom:6px; border:1px solid rgba(0,0,0,0.12);" />';
        }
        popupHtml += '<div><strong style="font-size:14px; color:#111827;">' + escapeHtml(r.number || 'Customer Report') + '</strong></div>'
            + popupLine('Issue', issueText)
            + popupLine('Address', addressText)
            + popupLine('Status', statusText);
        if (r.description && String(r.description).trim()) {
            popupHtml += popupLine('Details', String(r.description).trim());
        }
        popupHtml += '</div>';

        var marker = L.circleMarker([r.lat, r.lng], {
            radius: 7,
            fillColor: markerColors.fill,
            color: markerColors.stroke,
            weight: 2,
            fillOpacity: 0.9
        });
        marker._issueKey = issueKey;

        marker
            .addTo(customerMap)
            .bindPopup(popupHtml, { maxWidth: 320 });
        marker.on('click', function () {
            customerMarkerRefs.forEach(function (x) { x.marker.setStyle({ radius: 7, weight: 2 }); });
            marker.setStyle({ radius: 9, weight: 3 });
            highlightGroup(customerLegend, marker._issueKey);
            highlightGroup(customerDistrictLegend, marker._districtKey);
            marker.openPopup();
        });

        customerMarkerRefs.push({ marker: marker, report: r });
        customerBounds.push([r.lat, r.lng]);

        if (!issueGroups[issueKey]) {
            issueGroups[issueKey] = { label: issueText, markers: [], severityRank: -1 };
        }
        issueGroups[issueKey].markers.push(marker);
        issueGroups[issueKey].severityRank = Math.max(issueGroups[issueKey].severityRank, statusSeverityRank(status));
    });

    var customerDistrictLegend = document.getElementById('customer-gis-district-legend');
    var activeIssueFilters = {};
    var activeDistrictFilters = {};

    function highlightGroup(container, key) {
        if (!container || key == null) return;
        container.querySelectorAll('.customer-gis-legend-item').forEach(function (el) {
            el.classList.toggle('is-active', el.dataset.filterKey === key);
        });
    }

    function anyActive(filters) {
        return Object.keys(filters).some(function (k) { return filters[k]; });
    }

    /** Issue and district filters combine with AND — e.g. "Overflow" + "Tutong" shows only overflow reports in Tutong. */
    function applyFilters() {
        var issueKeys = Object.keys(activeIssueFilters).filter(function (k) { return activeIssueFilters[k]; });
        var districtKeys = Object.keys(activeDistrictFilters).filter(function (k) { return activeDistrictFilters[k]; });
        customerMarkerRefs.forEach(function (ref) {
            var matchesIssue = issueKeys.length === 0 || activeIssueFilters[ref.marker._issueKey];
            var matchesDistrict = districtKeys.length === 0 || activeDistrictFilters[ref.marker._districtKey];
            var visible = matchesIssue && matchesDistrict;
            var onMap = customerMap.hasLayer(ref.marker);
            if (visible && !onMap) {
                ref.marker.addTo(customerMap);
            } else if (!visible && onMap) {
                customerMap.removeLayer(ref.marker);
            }
        });
    }

    /**
     * Renders one filter legend row (issue types or districts) from grouped marker data.
     * `activeFilters` and `resetLabel` differ per row but the group/count/toggle/reset
     * mechanics are identical, so both rows share this instead of duplicating the wiring.
     */
    function renderFilterLegend(container, groups, activeFilters, resetLabel, getDotColor) {
        if (!container) return;

        var allBtn = document.createElement('button');
        allBtn.type = 'button';
        allBtn.className = 'customer-gis-legend-item is-active';
        allBtn.dataset.filterKey = '';
        allBtn.innerHTML = '<span>' + escapeHtml(resetLabel) + '</span><span class="customer-gis-legend-count">' + customerMarkerRefs.length + '</span>';
        allBtn.addEventListener('click', function () {
            Object.keys(activeFilters).forEach(function (k) { delete activeFilters[k]; });
            applyFilters();
            container.querySelectorAll('.customer-gis-legend-item').forEach(function (el) {
                el.classList.toggle('is-active', el === allBtn);
            });
        });
        container.appendChild(allBtn);

        groups.forEach(function (group) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'customer-gis-legend-item';
            btn.dataset.filterKey = group.key;
            var dot = getDotColor ? '<span class="customer-gis-legend-dot" style="background:' + getDotColor(group) + ';"></span>' : '';
            btn.innerHTML = dot
                + '<span>' + escapeHtml(group.label) + '</span>'
                + '<span class="customer-gis-legend-count">' + group.markers.length + '</span>';
            btn.addEventListener('click', function () {
                activeFilters[group.key] = !activeFilters[group.key];
                applyFilters();
                allBtn.classList.toggle('is-active', !anyActive(activeFilters));
                btn.classList.toggle('is-active', !!activeFilters[group.key]);
                if (activeFilters[group.key] && group.markers.length) {
                    customerMap.flyToBounds(L.latLngBounds(group.markers.map(function (m) { return m.getLatLng(); })), { padding: [40, 40], maxZoom: 15, duration: 0.45 });
                }
            });
            container.appendChild(btn);
        });
    }

    /** Groups a list of {key, label, markers, severityRank} by key, then sorts: most-urgent-first, then largest, then alphabetical — same priority ordering for both the issue and district rows. */
    function sortGroups(groupsByKey) {
        return Object.keys(groupsByKey)
            .map(function (key) { return Object.assign({ key: key }, groupsByKey[key]); })
            .sort(function (a, b) {
                if (b.severityRank !== a.severityRank) return b.severityRank - a.severityRank;
                if (b.markers.length !== a.markers.length) return b.markers.length - a.markers.length;
                return a.label.localeCompare(b.label);
            });
    }

    var severityDotColor = { 2: '#ff0000', 1: '#FFAE00', 0: '#00b31a' };

    if (customerLegend) {
        // Most urgent issue types (still-active/red reports) first, then by how many reports
        // share that issue, then alphabetically — surfaces what needs attention first instead
        // of an arbitrary per-report order that could repeat the same issue label many times.
        renderFilterLegend(customerLegend, sortGroups(issueGroups), activeIssueFilters, 'All issues', function (g) { return severityDotColor[g.severityRank]; });
    }

    // Reports have no stored district column — derive it client-side from the same district
    // boundary GeoJSON the map already renders, then build the district filter row once that
    // point-in-polygon pass finishes (marker creation above doesn't wait on it).
    if (customerDistrictLegend) {
        fetch('{{ asset("geojson/brunei-districts.json") }}')
            .then(function (response) { return response.json(); })
            .then(function (geojson) {
                var districtGroups = {};
                customerMarkerRefs.forEach(function (ref) {
                    var latlng = ref.marker.getLatLng();
                    var name = districtForPoint(latlng.lat, latlng.lng, geojson) || 'Unknown';
                    var key = name.toLowerCase();
                    ref.marker._districtKey = key;
                    if (!districtGroups[key]) {
                        districtGroups[key] = { label: name, markers: [], severityRank: -1 };
                    }
                    districtGroups[key].markers.push(ref.marker);
                    districtGroups[key].severityRank = Math.max(districtGroups[key].severityRank, statusSeverityRank(ref.report.status));
                });
                renderFilterLegend(customerDistrictLegend, sortGroups(districtGroups), activeDistrictFilters, 'All districts');
            })
            .catch(function () {
                customerDistrictLegend.innerHTML = '<span style="font-size:0.75rem;color:var(--text-secondary);">Could not load district boundaries.</span>';
            });
    }

    if (customerBounds.length > 0) {
        customerMap.fitBounds(L.latLngBounds(customerBounds), { padding: overviewPadding, maxZoom: customerOverviewMaxZoom });
    } else {
        customerMap.fitBounds(bruneiBounds, { padding: overviewPadding, maxZoom: customerOverviewMaxZoom });
    }

    var adminOpsSection = document.getElementById('admin-ops-map-section');
    var customerSection = document.getElementById('customer-map-section');

    function applyMapSelection(value) {
        var showCustomer = value === 'customer';
        adminOpsSection.style.display = showCustomer ? 'none' : 'block';
        customerSection.style.display = showCustomer ? 'block' : 'none';
        setTimeout(function () {
            if (showCustomer) {
                customerMap.invalidateSize();
            } else {
                map.invalidateSize();
            }
        }, 120);
    }
    applyMapSelection(currentView);
})();
</script>
@endsection
