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
</style>
<script>
window.GisSafeRoute = {
    _instances: {},

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

        if (!options.livemap) {
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
                    setStatus('Route displayed. Yellow = higher risk; green = lower.' + woNote + (heavy ? ' (' + heavy + ')' : ''));
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
