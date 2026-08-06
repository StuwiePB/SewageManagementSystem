@php
    $safeRouteAnalyzeUrl = $safeRouteAnalyzeUrl ?? route('safe-route.analyze');
@endphp
<style>
    .gis-safe-route-panel {
        position: absolute;
        z-index: 900;
        left: 12px;
        bottom: 12px;
        width: min(320px, calc(100% - 24px));
        background: var(--brudms-secondary);
        border: 1px solid var(--border-subtle);
        border-radius: 10px;
        padding: 0.65rem 0.75rem;
        color: var(--text-primary);
        font-size: 0.8rem;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
        pointer-events: auto;
    }
    .gis-safe-route-panel.is-livemap {
        position: fixed;
        left: 12px;
        bottom: calc(12px + env(safe-area-inset-bottom, 0px));
        z-index: 1000;
    }
    .gis-safe-route-panel h4 {
        margin: 0 0 0.35rem;
        font-size: 0.88rem;
        font-weight: 700;
        color: var(--text-primary);
    }
    .gis-safe-route-panel p {
        margin: 0 0 0.5rem;
        line-height: 1.35;
        color: var(--text-secondary);
    }
    .gis-safe-route-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        margin-bottom: 0.45rem;
    }
    .gis-safe-route-btn {
        border: 1px solid var(--border-subtle);
        background: var(--brudms-tertiary);
        color: var(--text-primary);
        border-radius: 6px;
        padding: 0.35rem 0.55rem;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        touch-action: manipulation;
    }
    .gis-safe-route-btn:hover { color: var(--brudms-primary-dark); border-color: var(--brudms-primary); }
    .gis-safe-route-btn.is-active {
        background: var(--brudms-tertiary);
        color: var(--text-primary);
        border-color: var(--brudms-primary);
    }
    .gis-safe-route-btn.primary {
        background: var(--accent-blue);
        color: #ffffff;
        border-color: var(--accent-blue);
    }
    .gis-safe-route-status {
        min-height: 1.1rem;
        margin-bottom: 0.35rem;
        color: #cbd5e1;
    }
    .gis-safe-route-summary {
        margin-top: 0.35rem;
        padding-top: 0.35rem;
        border-top: 1px solid rgba(255, 255, 255, 0.12);
        color: var(--text-primary);
        line-height: 1.4;
    }
    .gis-safe-route-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        margin-top: 0.35rem;
    }
    .gis-safe-route-legend-item {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.72rem;
    }
    .gis-safe-route-legend-swatch {
        width: 0.55rem;
        height: 0.55rem;
        border-radius: 50%;
    }
    .gis-safe-route-marker {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.35);
    }
    .gis-safe-route-marker.start { background: #22c55e; }
    .gis-safe-route-marker.end { background: #3b82f6; }
    .gis-safe-route-legend-swatch.work-order {
        border-radius: 2px;
        width: 0.65rem;
        height: 0.65rem;
    }

    /* Customer live map: draggable + minimizable widget */
    .gis-safe-route-panel.is-livemap.is-livemap-widget {
        left: 12px;
        bottom: max(200px, calc(24vh + env(safe-area-inset-bottom, 0px)));
        width: min(300px, calc(100vw - 24px));
        max-height: min(62vh, 480px);
        padding: 0;
        border-radius: 14px;
        background: rgba(97, 107, 110, 0.22);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 0.7px solid rgba(255, 255, 255, 0.21);
        box-shadow: 0 10px 28px rgba(0, 0, 0, 0.35);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        touch-action: none;
        user-select: none;
        -webkit-user-select: none;
    }
    .gis-safe-route-panel.is-livemap.is-livemap-widget.is-dragging {
        transition: none;
        cursor: grabbing;
    }
    .gis-safe-route-widget-header {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.5rem 0.55rem;
        border-bottom: 0.7px solid rgba(255, 255, 255, 0.12);
        flex-shrink: 0;
        cursor: grab;
        touch-action: none;
    }
    .gis-safe-route-widget-grip {
        width: 14px;
        height: 18px;
        border: none;
        padding: 0;
        background: transparent;
        cursor: grab;
        flex-shrink: 0;
        opacity: 0.55;
        background-image: radial-gradient(circle, rgba(255,255,255,0.85) 1.5px, transparent 1.6px);
        background-size: 7px 7px;
        background-position: center;
    }
    .gis-safe-route-widget-title {
        flex: 1;
        min-width: 0;
        font-family: Poppins, sans-serif;
        font-size: 0.88rem;
        font-weight: 700;
        color: rgba(255, 255, 255, 0.95);
        line-height: 1.2;
    }
    .gis-safe-route-widget-toggle {
        width: 28px;
        height: 28px;
        border: 0.7px solid rgba(255, 255, 255, 0.22);
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.08);
        color: rgba(255, 255, 255, 0.9);
        font-size: 1rem;
        line-height: 1;
        cursor: pointer;
        flex-shrink: 0;
        touch-action: manipulation;
    }
    .gis-safe-route-widget-body {
        padding: 0.55rem 0.65rem 0.65rem;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }
    .gis-safe-route-panel.is-livemap.is-livemap-widget.is-minimized {
        width: 52px;
        height: 52px;
        max-height: 52px;
        border-radius: 9999px;
        padding: 0;
        cursor: grab;
    }
    .gis-safe-route-panel.is-livemap.is-livemap-widget.is-minimized .gis-safe-route-widget-body,
    .gis-safe-route-panel.is-livemap.is-livemap-widget.is-minimized .gis-safe-route-widget-grip,
    .gis-safe-route-panel.is-livemap.is-livemap-widget.is-minimized .gis-safe-route-widget-title,
    .gis-safe-route-panel.is-livemap.is-livemap-widget.is-minimized .gis-safe-route-widget-toggle {
        display: none;
    }
    .gis-safe-route-panel.is-livemap.is-livemap-widget.is-minimized .gis-safe-route-widget-header {
        border-bottom: none;
        padding: 0;
        width: 100%;
        height: 100%;
        justify-content: center;
        align-items: center;
    }
    .gis-safe-route-widget-chip-icon {
        display: none;
        width: 22px;
        height: 22px;
        background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M3 17l4-4 4 4 4-8 4 4'/%3E%3C/svg%3E") center/contain no-repeat;
    }
    .gis-safe-route-panel.is-livemap.is-livemap-widget.is-minimized .gis-safe-route-widget-chip-icon {
        display: block;
    }
    .gis-safe-route-panel.is-livemap.is-livemap-widget .gis-safe-route-widget-body h4 {
        display: none;
    }
    .gis-safe-route-panel.is-livemap.is-livemap-widget .gis-safe-route-widget-body p {
        color: rgba(255, 255, 255, 0.72);
        font-family: Poppins, sans-serif;
        font-size: 0.72rem;
    }
    .gis-safe-route-panel.is-livemap.is-livemap-widget .gis-safe-route-btn {
        font-family: Poppins, sans-serif;
        background: rgba(66, 106, 120, 0.35);
        border-color: rgba(255, 255, 255, 0.18);
        color: rgba(255, 255, 255, 0.92);
    }
    .gis-safe-route-panel.is-livemap.is-livemap-widget .gis-safe-route-btn.primary {
        background: #04BCFF;
        border-color: #04BCFF;
        color: #0a1628;
    }
    .gis-safe-route-panel.is-livemap.is-livemap-widget .gis-safe-route-status {
        color: rgba(255, 255, 255, 0.58);
        font-family: Poppins, sans-serif;
        font-size: 0.68rem;
    }
</style>
<script>
window.GisSafeRoute = {
    _instances: {},

    initLivemapWidget: function (panel) {
        var body = document.createElement('div');
        body.className = 'gis-safe-route-widget-body';
        while (panel.firstChild) {
            body.appendChild(panel.firstChild);
        }
        var header = document.createElement('div');
        header.className = 'gis-safe-route-widget-header';
        header.innerHTML = ''
            + '<button type="button" class="gis-safe-route-widget-grip" aria-hidden="true" tabindex="-1"></button>'
            + '<span class="gis-safe-route-widget-title">Safe route</span>'
            + '<button type="button" class="gis-safe-route-widget-toggle" data-action="widget-toggle" aria-label="Minimize safe route">−</button>'
            + '<span class="gis-safe-route-widget-chip-icon" aria-hidden="true"></span>';
        panel.appendChild(header);
        panel.appendChild(body);
        panel.classList.add('is-livemap-widget');

        var toggleBtn = panel.querySelector('[data-action="widget-toggle"]');
        var storageKey = 'brudms_livemap_safe_route_widget';
        var dragThreshold = 8;
        var dragging = false;
        var moved = false;
        var startX = 0;
        var startY = 0;
        var startLeft = 0;
        var startTop = 0;

        function clampPosition(left, top) {
            var w = panel.offsetWidth;
            var h = panel.offsetHeight;
            return {
                left: Math.max(8, Math.min(window.innerWidth - w - 8, left)),
                top: Math.max(8, Math.min(window.innerHeight - h - 8, top))
            };
        }

        function applyPosition(left, top) {
            var pos = clampPosition(left, top);
            panel.style.left = pos.left + 'px';
            panel.style.top = pos.top + 'px';
            panel.style.right = 'auto';
            panel.style.bottom = 'auto';
            panel.dataset.widgetPositioned = '1';
            return pos;
        }

        function readPosition() {
            var rect = panel.getBoundingClientRect();
            return { left: rect.left, top: rect.top };
        }

        function saveWidgetState() {
            try {
                var pos = readPosition();
                sessionStorage.setItem(storageKey, JSON.stringify({
                    left: pos.left,
                    top: pos.top,
                    minimized: panel.classList.contains('is-minimized')
                }));
            } catch (e) {}
        }

        function restoreWidgetState() {
            try {
                var raw = sessionStorage.getItem(storageKey);
                if (!raw) {
                    return;
                }
                var data = JSON.parse(raw);
                if (data && typeof data.left === 'number' && typeof data.top === 'number') {
                    applyPosition(data.left, data.top);
                }
                if (data && data.minimized) {
                    setMinimized(true, true);
                }
            } catch (e) {}
        }

        function setMinimized(minimized, skipSave) {
            panel.classList.toggle('is-minimized', !!minimized);
            if (toggleBtn) {
                toggleBtn.textContent = minimized ? '+' : '−';
                toggleBtn.setAttribute('aria-label', minimized ? 'Expand safe route' : 'Minimize safe route');
            }
            if (!minimized && panel.dataset.widgetPositioned === '1') {
                var pos = readPosition();
                applyPosition(pos.left, pos.top);
            }
            if (!skipSave) {
                saveWidgetState();
            }
        }

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function (ev) {
                ev.preventDefault();
                ev.stopPropagation();
                setMinimized(!panel.classList.contains('is-minimized'));
            });
        }

        function onPointerDown(ev) {
            if (ev.button !== undefined && ev.button !== 0) {
                return;
            }
            if (ev.target.closest('[data-action="widget-toggle"]') || ev.target.closest('.gis-safe-route-btn')) {
                return;
            }
            dragging = true;
            moved = false;
            panel.classList.add('is-dragging');
            var rect = panel.getBoundingClientRect();
            if (panel.dataset.widgetPositioned !== '1') {
                applyPosition(rect.left, rect.top);
            } else {
                startLeft = rect.left;
                startTop = rect.top;
            }
            startLeft = parseFloat(panel.style.left) || rect.left;
            startTop = parseFloat(panel.style.top) || rect.top;
            startX = ev.clientX;
            startY = ev.clientY;
            if (panel.setPointerCapture && ev.pointerId !== undefined) {
                try { panel.setPointerCapture(ev.pointerId); } catch (err) {}
            }
            ev.preventDefault();
        }

        function onPointerMove(ev) {
            if (!dragging) {
                return;
            }
            var dx = ev.clientX - startX;
            var dy = ev.clientY - startY;
            if (!moved && (Math.abs(dx) > dragThreshold || Math.abs(dy) > dragThreshold)) {
                moved = true;
            }
            if (moved) {
                applyPosition(startLeft + dx, startTop + dy);
            }
        }

        function onPointerUp(ev) {
            if (!dragging) {
                return;
            }
            dragging = false;
            panel.classList.remove('is-dragging');
            if (panel.releasePointerCapture && ev.pointerId !== undefined) {
                try { panel.releasePointerCapture(ev.pointerId); } catch (err) {}
            }
            if (!moved && panel.classList.contains('is-minimized')) {
                setMinimized(false);
            } else if (moved) {
                saveWidgetState();
            }
        }

        panel.addEventListener('pointerdown', onPointerDown);
        panel.addEventListener('pointermove', onPointerMove);
        panel.addEventListener('pointerup', onPointerUp);
        panel.addEventListener('pointercancel', onPointerUp);

        restoreWidgetState();
    },

    init: function (map, options) {
        if (!map || !options || !options.containerId) {
            return null;
        }
        var key = options.containerId;
        if (this._instances[key]) {
            return this._instances[key];
        }

        var analyzeUrl = options.analyzeUrl || @json($safeRouteAnalyzeUrl);
        var csrf = options.csrf || (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
        var container = document.getElementById(options.containerId);
        if (!container) {
            return null;
        }

        var showWorkOrderZones = !!options.showWorkOrderZones;
        var workOrders = options.workOrders || [];
        var workOrderRadiusM = options.workOrderRadiusM || 100;
        var routeIntro = options.routeIntro
            || (options.livemap
                ? 'Pick start and end on the map. Route highlights drainage risk (yellow = higher, green = lower).'
                : 'Pick start and end on the map. Route uses live Brunei rain + drainage risk (yellow = higher, green = lower).');
        var panel = document.createElement('div');
        panel.className = 'gis-safe-route-panel' + (options.livemap ? ' is-livemap' : '');
        panel.innerHTML = ''
            + '<h4>Safe route</h4>'
            + '<p>' + routeIntro + '</p>'
            + '<div class="gis-safe-route-actions">'
            + '<button type="button" class="gis-safe-route-btn" data-action="pick-start">Set start</button>'
            + '<button type="button" class="gis-safe-route-btn" data-action="pick-end">Set end</button>'
            + '<button type="button" class="gis-safe-route-btn" data-action="my-location">My location</button>'
            + '<button type="button" class="gis-safe-route-btn primary" data-action="analyze">Get route</button>'
            + '<button type="button" class="gis-safe-route-btn" data-action="clear">Clear</button>'
            + '</div>'
            + '<div class="gis-safe-route-status" data-role="status">Tap “Set start”, then click the map.</div>'
            + '<div class="gis-safe-route-summary" data-role="summary" hidden></div>'
            + '<div class="gis-safe-route-legend" data-role="legend" hidden></div>';
        container.appendChild(panel);

        if (options.livemap) {
            this.initLivemapWidget(panel);
        } else {
            var pos = window.getComputedStyle(container).position;
            if (pos === 'static') {
                container.style.position = 'relative';
            }
        }

        panel.addEventListener('mousedown', function (ev) { ev.stopPropagation(); });
        panel.addEventListener('touchstart', function (ev) { ev.stopPropagation(); }, { passive: true });
        panel.addEventListener('click', function (ev) { ev.stopPropagation(); });

        var state = {
            pickMode: null,
            start: null,
            end: null,
            routeLayer: L.layerGroup().addTo(map),
            workOrderLayer: showWorkOrderZones ? L.layerGroup() : null,
            startMarker: null,
            endMarker: null,
            busy: false
        };

        function escapeHtml(v) {
            return String(v || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        }

        function clearWorkOrderZones() {
            if (!state.workOrderLayer) {
                return;
            }
            state.workOrderLayer.clearLayers();
            if (map.hasLayer(state.workOrderLayer)) {
                map.removeLayer(state.workOrderLayer);
            }
        }

        function renderWorkOrderZones() {
            if (!state.workOrderLayer || !workOrders.length) {
                return;
            }
            clearWorkOrderZones();
            workOrders.forEach(function (wo) {
                if (wo.lat == null || wo.lng == null) {
                    return;
                }
                L.circle([wo.lat, wo.lng], {
                    radius: workOrderRadiusM,
                    color: '#b91c1c',
                    fillColor: '#ef4444',
                    fillOpacity: 0.24,
                    weight: 2.5,
                    opacity: 0.95
                })
                    .bindPopup(
                        '<div><strong style="color:#ef4444;">Work order area (100 m)</strong><br>'
                        + escapeHtml(wo.number || 'Work order')
                        + (wo.address ? '<br>' + escapeHtml(wo.address) : '')
                        + (wo.status ? '<br><span style="opacity:0.85;">Status: ' + escapeHtml(wo.status) + '</span>' : '')
                        + '</div>'
                    )
                    .addTo(state.workOrderLayer);
            });
            state.workOrderLayer.addTo(map);
        }

        function setStatus(text) {
            var el = panel.querySelector('[data-role="status"]');
            if (el) {
                el.textContent = text;
            }
        }

        function setPickMode(mode) {
            state.pickMode = mode;
            window.GisSafeRoutePicking = !!mode;
            panel.querySelectorAll('[data-action="pick-start"], [data-action="pick-end"]').forEach(function (btn) {
                btn.classList.remove('is-active');
            });
            if (mode === 'start') {
                panel.querySelector('[data-action="pick-start"]').classList.add('is-active');
                setStatus('Click the map for route start.');
            } else if (mode === 'end') {
                panel.querySelector('[data-action="pick-end"]').classList.add('is-active');
                setStatus('Click the map for route end.');
            }
        }

        function placeMarker(kind, latlng) {
            var html = '<span class="gis-safe-route-marker ' + kind + '"></span>';
            var icon = L.divIcon({ className: '', html: html, iconSize: [14, 14], iconAnchor: [7, 7] });
            if (kind === 'start') {
                if (state.startMarker) {
                    map.removeLayer(state.startMarker);
                }
                state.startMarker = L.marker(latlng, { icon: icon }).addTo(map);
            } else {
                if (state.endMarker) {
                    map.removeLayer(state.endMarker);
                }
                state.endMarker = L.marker(latlng, { icon: icon }).addTo(map);
            }
        }

        function clearRoute() {
            state.routeLayer.clearLayers();
            panel.querySelector('[data-role="summary"]').hidden = true;
            panel.querySelector('[data-role="legend"]').hidden = true;
        }

        function clearAll() {
            state.start = null;
            state.end = null;
            state.pickMode = null;
            window.GisSafeRoutePicking = false;
            clearRoute();
            clearWorkOrderZones();
            if (state.startMarker) {
                map.removeLayer(state.startMarker);
                state.startMarker = null;
            }
            if (state.endMarker) {
                map.removeLayer(state.endMarker);
                state.endMarker = null;
            }
            panel.querySelectorAll('.gis-safe-route-btn.is-active').forEach(function (b) {
                b.classList.remove('is-active');
            });
            setStatus('Tap “Set start”, then click the map.');
        }

        function renderSegments(segments) {
            clearRoute();
            segments.forEach(function (seg) {
                var from = seg.from || {};
                var to = seg.to || {};
                L.polyline(
                    [[from.lat, from.lng], [to.lat, to.lng]],
                    {
                        color: seg.stroke || '#22c55e',
                        weight: seg.weight || 5,
                        opacity: 0.9,
                        lineCap: 'round',
                        lineJoin: 'round'
                    }
                ).bindPopup((seg.reasons || []).join('<br>') || 'Lower risk segment')
                    .addTo(state.routeLayer);
            });
        }

        function renderSummary(data) {
            var summaryEl = panel.querySelector('[data-role="summary"]');
            var legendEl = panel.querySelector('[data-role="legend"]');
            var msg = (data.summary && data.summary.message) ? data.summary.message : 'Route ready.';
            var dist = data.distance_km != null ? data.distance_km + ' km' : '';
            var dur = data.duration_min != null ? data.duration_min + ' min' : '';
            summaryEl.innerHTML = '<strong>' + msg + '</strong>'
                + (dist || dur ? '<div style="margin-top:0.25rem;color:#94a3b8;">' + [dist, dur].filter(Boolean).join(' · ') + '</div>' : '');
            summaryEl.hidden = false;

            var legend = data.legend || [];
            var legendHtml = legend.map(function (item) {
                var colors = { yellow: '#eab308', green: '#22c55e' };
                var c = colors[item.color] || '#22c55e';
                return '<span class="gis-safe-route-legend-item"><span class="gis-safe-route-legend-swatch" style="background:' + c + ';"></span>'
                    + (item.label || item.color) + '</span>';
            }).join('');
            if (showWorkOrderZones && workOrders.length && map.hasLayer(state.workOrderLayer)) {
                legendHtml += '<span class="gis-safe-route-legend-item"><span class="gis-safe-route-legend-swatch work-order" style="background:#ef4444;"></span>'
                    + 'Work order zone (100 m)</span>';
            }
            legendEl.innerHTML = legendHtml;
            legendEl.hidden = legendEl.innerHTML.length === 0;
        }

        function analyzeRoute() {
            if (!state.start || !state.end) {
                setStatus('Set both start and end before analyzing.');
                return;
            }
            if (state.busy) {
                return;
            }
            state.busy = true;
            setStatus('Fetching route and risk scores…');
            fetch(analyzeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    start_lat: state.start.lat,
                    start_lng: state.start.lng,
                    end_lat: state.end.lat,
                    end_lng: state.end.lng,
                    for_customer: !!options.livemap
                })
            })
                .then(function (res) {
                    return res.json().then(function (body) {
                        return { ok: res.ok, body: body };
                    });
                })
                .then(function (result) {
                    if (!result.ok) {
                        throw new Error((result.body && result.body.message) || 'Route analysis failed.');
                    }
                    renderSegments(result.body.segments || []);
                    if (showWorkOrderZones) {
                        renderWorkOrderZones();
                    }
                    renderSummary(result.body);
                    var w = result.body.weather || {};
                    var heavy = w.heavy_rain_pct != null ? w.heavy_rain_pct + '% heavy rain' : '';
                    var woNote = (showWorkOrderZones && workOrders.length)
                        ? ' Red circles = active work orders (100 m).'
                        : '';
                    setStatus(options.livemap
                        ? 'Route displayed on the map.' + woNote
                        : 'Route displayed. Yellow = higher risk; green = lower.' + woNote + (heavy ? ' (' + heavy + ')' : ''));
                    var bounds = [];
                    (result.body.segments || []).forEach(function (seg) {
                        if (seg.from) {
                            bounds.push([seg.from.lat, seg.from.lng]);
                        }
                        if (seg.to) {
                            bounds.push([seg.to.lat, seg.to.lng]);
                        }
                    });
                    if (bounds.length > 0) {
                        map.fitBounds(L.latLngBounds(bounds), { padding: [40, 40], maxZoom: 15 });
                    }
                })
                .catch(function (err) {
                    setStatus(err.message || 'Could not analyze route.');
                })
                .finally(function () {
                    state.busy = false;
                });
        }

        map.on('click', function (e) {
            if (!state.pickMode) {
                return;
            }
            if (typeof L !== 'undefined' && L.DomEvent) {
                L.DomEvent.stopPropagation(e);
            }
            var lat = e.latlng.lat;
            var lng = e.latlng.lng;
            if (state.pickMode === 'start') {
                state.start = { lat: lat, lng: lng };
                placeMarker('start', e.latlng);
                setPickMode(null);
                setStatus('Start set. Tap “Set end” or click the map for destination.');
                setPickMode('end');
            } else if (state.pickMode === 'end') {
                state.end = { lat: lat, lng: lng };
                placeMarker('end', e.latlng);
                setPickMode(null);
                setStatus('End set. Press “Get route” to analyze.');
            }
        });

        function bindAction(selector, handler) {
            var el = panel.querySelector(selector);
            if (!el) {
                return;
            }
            el.addEventListener('click', function (ev) {
                ev.preventDefault();
                ev.stopPropagation();
                handler();
            });
        }

        bindAction('[data-action="pick-start"]', function () { setPickMode('start'); });
        bindAction('[data-action="pick-end"]', function () { setPickMode('end'); });
        bindAction('[data-action="clear"]', function () { clearAll(); });
        bindAction('[data-action="analyze"]', function () { analyzeRoute(); });
        bindAction('[data-action="my-location"]', function () {
            if (!navigator.geolocation) {
                setStatus('Geolocation is not available in this browser.');
                return;
            }
            setStatus('Getting your location…');
            navigator.geolocation.getCurrentPosition(function (pos) {
                var latlng = L.latLng(pos.coords.latitude, pos.coords.longitude);
                state.start = { lat: latlng.lat, lng: latlng.lng };
                placeMarker('start', latlng);
                setPickMode('end');
                setStatus('Start set from GPS. Click map for end point.');
                map.panTo(latlng);
            }, function () {
                setStatus('Could not read GPS location.');
            }, { enableHighAccuracy: true, timeout: 12000 });
        });

        var api = { clear: clearAll, map: map, panel: panel };
        window.GisSafeRoute._instances[key] = api;
        return api;
    }
};
</script>
