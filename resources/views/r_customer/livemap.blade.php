<x-layouts::customer :title="__('Live Map') . ' – BruDMS'" :bare="true">
    @push('styles')
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <style>
            body, main { overflow: hidden !important; margin: 0; padding: 0; }
            #livemap { position: fixed; inset: 0; width: 100%; height: 100%; z-index: 1; }
            .leaflet-container { background: var(--brudms-tertiary) !important; }
            html[data-theme='dark'] .leaflet-container,
            html.dark .leaflet-container { background: #0b1628 !important; }
            .leaflet-control-attribution { display: none !important; }
            @keyframes marker-spin { to { transform: rotate(360deg); } }
            .marker-spinner-ring { animation: marker-spin 1.5s linear infinite; transform-origin: center; transform-box: fill-box; }
            #report-detail-panel { transition: transform 0.3s ease; }
            #report-detail-panel.report-panel-hidden { transform: translateY(calc(100% + 40px)); }
            .report-nav-btn:hover img { opacity: 1; }
.report-nav-btn img { opacity: 0.8; }
            .livemap-filter-box {
                position: fixed;
                right: 20px;
                top: calc(4vh + 134px);
                z-index: 10;
                width: 32px;
                height: 32px;
                border-radius: 8px;
                background: rgba(97, 107, 110, 0.15);
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                border: 0.7px solid rgba(255, 255, 255, 0.21);
                box-sizing: border-box;
                overflow: hidden;
                transition: width 0.2s ease, height 0.2s ease, border-radius 0.2s ease;
            }
            .livemap-filter-box.is-open {
                width: 168px;
                height: 124px;
                border-radius: 10px;
                background: rgba(97, 107, 110, 0.15);
                z-index: 11;
            }
            .livemap-filter-btn {
                width: 32px;
                height: 32px;
                border: none;
                background: transparent;
                color: rgba(255, 255, 255, 0.92);
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                padding: 0;
            }
            .livemap-filter-box.is-open .livemap-filter-btn {
                position: absolute;
                top: 2px;
                right: 2px;
            }
            .livemap-filter-close {
                display: none;
                font-family: Poppins, sans-serif;
                font-size: 14px;
                font-weight: 700;
                line-height: 1;
            }
            .livemap-filter-box.is-open .livemap-filter-icon { display: none; }
            .livemap-filter-box.is-open .livemap-filter-close { display: inline; }
            .livemap-filter-options {
                display: none;
                padding: 30px 8px 8px;
            }
            .livemap-filter-box.is-open .livemap-filter-options { display: block; }
            .livemap-filter-option {
                width: 100%;
                border: none;
                background: transparent;
                color: rgba(255, 255, 255, 0.88);
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 6px 4px;
                border-radius: 7px;
                font-family: Poppins, sans-serif;
                font-size: 10px;
                font-weight: 600;
                text-align: left;
                cursor: pointer;
            }
            .livemap-filter-option:hover {
                background: rgba(66, 106, 120, 0.22);
            }
            .livemap-filter-dot {
                width: 12px;
                height: 12px;
                border-radius: 9999px;
                border: 1.4px solid rgba(255, 255, 255, 0.72);
                background: transparent;
                flex-shrink: 0;
                box-sizing: border-box;
                position: relative;
            }
            .livemap-filter-option.is-selected .livemap-filter-dot {
                border-color: rgba(255, 255, 255, 0.72);
            }
            .livemap-filter-option.is-selected .livemap-filter-dot::after {
                content: '';
                position: absolute;
                left: 50%;
                top: 50%;
                width: 5px;
                height: 5px;
                border-radius: 9999px;
                background: currentColor;
                transform: translate(-50%, -50%);
            }
            .livemap-filter-dot.status-pending { color: #FF0000; }
            .livemap-filter-dot.status-progress { color: #FFAE00; }
            .livemap-filter-dot.status-resolved { color: #00FF26; }
            #livemap-safe-route-host .gis-safe-route-panel.is-livemap {
                bottom: max(200px, calc(24vh + env(safe-area-inset-bottom, 0px)));
                left: 12px;
                width: min(300px, calc(100vw - 24px));
            }
            .user-location-marker {
                position: relative;
                width: 20px;
                height: 20px;
            }
            .user-location-dot {
                position: absolute;
                left: 50%;
                top: 50%;
                width: 12px;
                height: 12px;
                border-radius: 9999px;
                background: #2f8cff;
                border: 2px solid #ffffff;
                box-shadow: 0 0 0 1px rgba(9, 30, 66, 0.22);
                transform: translate(-50%, -50%);
                z-index: 2;
            }
            .user-location-aura {
                position: absolute;
                left: 50%;
                top: 50%;
                width: 12px;
                height: 12px;
                border-radius: 9999px;
                border: 2px solid rgba(47, 140, 255, 0.42);
                transform: translate(-50%, -50%);
                animation: user-location-pulse 2.1s ease-out infinite;
                z-index: 1;
            }
            .user-location-aura.aura-delay {
                animation-delay: 0.95s;
            }
            @keyframes user-location-pulse {
                0% {
                    transform: translate(-50%, -50%) scale(1);
                    opacity: 0.7;
                }
                100% {
                    transform: translate(-50%, -50%) scale(2.9);
                    opacity: 0;
                }
            }
        </style>
    @endpush

    @php $user = auth()->user(); @endphp
    <div style="position: fixed; top: 4vh; left: 12px; z-index: 10; display: flex; align-items: center; gap: 6px;">
        <a href="{{ route('customer.dashboard', ['name' => $user->profileSlug()]) }}" class="press-btn delayed-nav" style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 9999px; text-decoration: none; flex-shrink: 0;" aria-label="{{ __('Back') }}">
            <img src="{{ asset('images/Vectors/all_backarrow.svg') }}" alt="" style="width: 21px; height: 21px;" />
        </a>
        <span style="font-family: Poppins, sans-serif; font-weight: 600; font-size: 20px; color: rgba(255, 255, 255, 0.5);">Reports near you</span>
    </div>

    <div style="position: fixed; left: 20px; right: 20px; top: calc(4vh + 56px); z-index: 10; height: 70px; background: rgba(97, 107, 110, 0.15); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 0.7px solid rgba(255, 255, 255, 0.21); border-radius: 9px; display: flex; align-items: center;">
        <div style="flex: 1; display: flex; flex-direction: column; align-items: center; padding-top: 12px;">
            <span style="font-family: Poppins, sans-serif; font-weight: 600; font-size: 10px; color: rgba(255, 255, 255, 0.9); transform: translateY(-7px);">Active incidents</span>
            <span id="count-active" style="font-family: Poppins, sans-serif; font-weight: 700; font-size: 20px; color: rgba(255, 255, 255, 0.9); margin-top: -2px;">{{ $count_active ?? 0 }}</span>
        </div>
        <div style="width: 1.3px; height: 28px; background: rgba(255, 255, 255, 0.5); flex-shrink: 0;"></div>
        <div style="flex: 1; display: flex; flex-direction: column; align-items: center; padding-top: 12px;">
            <span style="font-family: Poppins, sans-serif; font-weight: 600; font-size: 10px; color: rgba(255, 255, 255, 0.9); transform: translateY(-7px);">Case in progress</span>
            <span id="count-progress" style="font-family: Poppins, sans-serif; font-weight: 700; font-size: 20px; color: rgba(255, 255, 255, 0.9); margin-top: -2px;">{{ $count_in_progress ?? 0 }}</span>
        </div>
        <div style="width: 1.3px; height: 28px; background: rgba(255, 255, 255, 0.5); flex-shrink: 0;"></div>
        <div style="flex: 1; display: flex; flex-direction: column; align-items: center; padding-top: 12px;">
            <span style="font-family: Poppins, sans-serif; font-weight: 600; font-size: 10px; color: rgba(255, 255, 255, 0.9); transform: translateY(-7px);">Resolved</span>
            <span id="count-resolved" style="font-family: Poppins, sans-serif; font-weight: 700; font-size: 20px; color: rgba(255, 255, 255, 0.9); margin-top: -2px;">{{ $count_resolved ?? 0 }}</span>
        </div>
    </div>

    <div id="livemap-filter-box" class="livemap-filter-box">
        <button id="livemap-filter-btn" type="button" class="livemap-filter-btn" aria-label="Filter statuses" aria-expanded="false">
            <img class="livemap-filter-icon" src="{{ asset('images/Vectors/livemap_filter.svg') }}" alt="" style="width: 14px; height: 14px; object-fit: contain;" />
            <span class="livemap-filter-close">X</span>
        </button>
        <div id="livemap-filter-options" class="livemap-filter-options" role="menu" aria-label="Map status filters">
            <button type="button" class="livemap-filter-option is-selected" data-filter-status="pending" role="menuitemcheckbox" aria-checked="true">
                <span class="livemap-filter-dot status-pending"></span>
                <span>Active incidents</span>
            </button>
            <button type="button" class="livemap-filter-option is-selected" data-filter-status="in_progress" role="menuitemcheckbox" aria-checked="true">
                <span class="livemap-filter-dot status-progress"></span>
                <span>Case in progress</span>
            </button>
            <button type="button" class="livemap-filter-option is-selected" data-filter-status="resolved" role="menuitemcheckbox" aria-checked="true">
                <span class="livemap-filter-dot status-resolved"></span>
                <span>Resolved</span>
            </button>
        </div>
    </div>

    <div id="report-detail-panel" class="report-panel-hidden" style="position: fixed; left: 20px; right: 20px; bottom: 20px; z-index: 10; min-height: 172px; height: auto; max-height: 42vh; background: rgba(97, 107, 110, 0.15); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 0.7px solid rgba(255, 255, 255, 0.21); border-radius: 14px; display: flex; align-items: center; justify-content: space-between; padding: 10px 0; gap: 12px;">
        <button type="button" id="report-prev-btn" class="report-nav-btn" style="width: 24px; height: 24px; border: none; background: transparent; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; visibility: hidden;" aria-label="Previous">
            <img src="{{ asset('images/Vectors/livemap_scrollerreport.svg') }}" alt="" style="width: 12px; height: 12px; transform: scaleX(-1);" />
        </button>
        <div id="report-detail-content" style="display: flex; align-items: flex-start; gap: 12px; margin-right: auto; margin-left: -10px; flex: 1; min-width: 0;">
            <div id="report-detail-photo" style="flex: 0 0 auto; width: 118px; height: 140px; border-radius: 4px; overflow: hidden; background: rgba(0,0,0,0.3); display: none;">
                <img id="report-detail-img" src="" alt="" style="width: 100%; height: 100%; object-fit: cover;" />
            </div>
            <div style="display: flex; flex-direction: column; justify-content: space-between; gap: 0px; min-width: 0; flex: 1; height: 140px;">
                <span id="report-detail-title" style="font-family: Poppins, sans-serif; font-weight: 700; font-size: 14px; color: rgba(255, 255, 255, 0.4); display: none; white-space: nowrap;"> </span>
                <div id="report-detail-info" style="display: none; flex-direction: column; gap: 1px; margin-top: 2px;">
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <img src="{{ asset('images/Vectors/all_reportstatus.svg') }}" alt="" style="width: 12px; height: 12px; object-fit: contain; opacity: 0.6;" />
                        <span id="report-detail-status" style="font-family: Poppins, sans-serif; font-weight: 600; font-size: 10px;"></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <img src="{{ asset('images/Vectors/all_calempty.svg') }}" alt="" style="width: 12px; height: 12px; object-fit: contain; opacity: 0.6;" />
                        <span id="report-detail-date" style="color: white; font-family: Poppins, sans-serif; font-weight: 400; font-size: 10px;"></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <img src="{{ asset('images/Vectors/all_calcomplete.svg') }}" alt="" style="width: 12px; height: 12px; object-fit: contain; opacity: 0.6;" />
                        <span id="report-detail-resolved" style="font-family: Poppins, sans-serif; font-weight: 400; font-size: 10px;"></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px; min-width: 0;">
                        <img src="{{ asset('images/Vectors/all_location.svg') }}" alt="" style="width: 12px; height: 12px; object-fit: contain; opacity: 0.6; flex-shrink: 0;" />
                        <span id="report-detail-address" style="color: white; font-family: Poppins, sans-serif; font-weight: 400; font-size: 10px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"></span>
                    </div>
                </div>
                <div id="report-detail-box" style="display: none; margin-top: 0; width: calc(100% + 6px); max-width: none; margin-right: -6px; height: 44px; flex-shrink: 0; border-radius: 8px; background: rgba(66, 106, 120, 0.25); border: 0.7px solid rgba(255, 255, 255, 0.18); box-sizing: border-box; padding: 6px 8px; overflow-y: auto; overflow-x: hidden;">
                    <p id="report-detail-description" style="margin: 0; color: rgba(255, 255, 255, 0.75); font-family: Poppins, sans-serif; font-weight: 300; font-size: 9px; line-height: 1.35; word-wrap: break-word;"></p>
                </div>
            </div>
        </div>
        <button type="button" id="report-next-btn" class="report-nav-btn" style="width: 24px; height: 24px; border: none; background: transparent; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; visibility: hidden;" aria-label="Next">
            <img src="{{ asset('images/Vectors/livemap_scrollerreport.svg') }}" alt="" style="width: 12px; height: 12px;" />
        </button>
    </div>

    <div id="livemap-weather-host" style="position: fixed; inset: 0; pointer-events: none; z-index: 1002;"></div>
    <div id="livemap-safe-route-host" style="position: fixed; inset: 0; pointer-events: none; z-index: 1000;"></div>
    <div id="livemap"></div>

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @include('partials.gis-brunei-layers')
    @include('partials.gis-weather-widget')
    @include('partials.gis-safe-route')
    <script>
        var defaultCenter = [4.9031, 114.9398];
        var bruneiBounds = L.latLngBounds(
            L.latLng(3.70, 113.75),
            L.latLng(5.60, 115.85)
        );
        var reports = @json($reports ?? []);

        var map = L.map('livemap', {
            center: defaultCenter,
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
            zoomControl: false
        });
        var minPictureZoom = map.getBoundsZoom(bruneiBounds, true);
        if (minPictureZoom > map.getMinZoom()) {
            map.setMinZoom(minPictureZoom);
        }
        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles © Esri',
            maxZoom: 18,
            maxNativeZoom: 18,
            updateWhenZooming: false,
            updateWhenIdle: true,
            keepBuffer: 10
        }).addTo(map);
        L.tileLayer('https://services.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Labels © Esri',
            opacity: 0.90,
            maxZoom: 18,
            maxNativeZoom: 18,
            updateWhenZooming: false,
            updateWhenIdle: true,
            keepBuffer: 10
        }).addTo(map);

        var satelliteGroup = L.layerGroup();
        if (window.BruneiGisLayers) {
            window.BruneiGisLayers.init(map, { satelliteLayer: satelliteGroup, instanceKey: 'livemap' });
        }
        if (window.BruneiGisWeather) {
            window.BruneiGisWeather.init(map, { hostId: 'livemap-weather-host', livemap: true });
        }

        fetch('{{ asset("geojson/brunei-districts.json") }}')
            .then(function(response) { return response.json(); })
            .then(function(geojson) {
                L.geoJSON(geojson, {
                    interactive: false,
                    style: function() {
                        return {
                            color: 'rgba(255, 255, 255, 0.72)',
                            weight: 1,
                            opacity: 0.72,
                            fill: false
                        };
                    }
                }).addTo(map);
            })
            .catch(function() {});

        map.on('drag', function() {
            map.panInsideBounds(bruneiBounds, { animate: false });
        });
        map.on('zoomend', function() {
            map.panInsideBounds(bruneiBounds, { animate: false });
        });

        var panel = document.getElementById('report-detail-panel');
        map.on('click', function() {
            if (window.GisSafeRoutePicking) {
                return;
            }
            if (panel) panel.classList.add('report-panel-hidden');
            if (spinnerRing) { map.removeLayer(spinnerRing); spinnerRing = null; }
        });

        var bounds = [];
        var spinnerRing = null;
        var reportMarkers = [];
        var currentReportIndex = -1;
        var selectedStatuses = {
            pending: true,
            in_progress: true,
            resolved: true
        };
        var userLocationMarker = null;
        var userAuraOne = null;
        var userAuraTwo = null;
        var userAuraAnimFrame = null;

        var userLocationIcon = L.divIcon({
            className: 'user-location-pin',
            html: '<div class="user-location-marker"><span class="user-location-aura"></span><span class="user-location-aura aura-delay"></span><span class="user-location-dot"></span></div>',
            iconSize: [20, 20],
            iconAnchor: [10, 10]
        });

        function upsertUserLocationMarker(lat, lng) {
            var latlng = [lat, lng];
            if (userLocationMarker) {
                userLocationMarker.setLatLng(latlng);
                upsertUserAura(latlng);
                return;
            }
            userLocationMarker = L.marker(latlng, {
                icon: userLocationIcon,
                interactive: false,
                keyboard: false,
                zIndexOffset: 1200
            }).addTo(map);
            upsertUserAura(latlng);
        }

        function upsertUserAura(latlng) {
            if (!userAuraOne) {
                userAuraOne = L.circleMarker(latlng, {
                    radius: 8,
                    color: 'rgba(47, 140, 255, 0.50)',
                    weight: 2,
                    fill: false,
                    interactive: false
                }).addTo(map);
            } else {
                userAuraOne.setLatLng(latlng);
            }

            if (!userAuraTwo) {
                userAuraTwo = L.circleMarker(latlng, {
                    radius: 8,
                    color: 'rgba(47, 140, 255, 0.36)',
                    weight: 2,
                    fill: false,
                    interactive: false
                }).addTo(map);
            } else {
                userAuraTwo.setLatLng(latlng);
            }

            startUserAuraAnimation();
        }

        function startUserAuraAnimation() {
            if (userAuraAnimFrame) {
                return;
            }

            var cycleMs = 2100;
            var pulse = function(layer, phaseOffsetMs, nowMs) {
                if (!layer) return;
                var progress = ((nowMs + phaseOffsetMs) % cycleMs) / cycleMs;
                var radius = 8 + (progress * 20);
                var opacity = 0.55 * (1 - progress);
                layer.setRadius(radius);
                layer.setStyle({
                    color: 'rgba(47, 140, 255, ' + opacity.toFixed(3) + ')'
                });
            };

            var tick = function(nowMs) {
                pulse(userAuraOne, 0, nowMs);
                pulse(userAuraTwo, 950, nowMs);
                userAuraAnimFrame = window.requestAnimationFrame(tick);
            };

            userAuraAnimFrame = window.requestAnimationFrame(tick);
        }

        function showReportAt(index) {
            if (index < 0 || index >= reportMarkers.length) return;
            currentReportIndex = index;
            var item = reportMarkers[index];
            if (!selectedStatuses[item.normalizedStatus]) {
                return;
            }
            if (spinnerRing) map.removeLayer(spinnerRing);
            spinnerRing = L.circleMarker(item.latlng, {
                radius: 12,
                color: '#fff',
                weight: 2.5,
                fillColor: 'transparent',
                fillOpacity: 0,
                dashArray: '4, 8'
            }).addTo(map);
            setTimeout(function() {
                if (spinnerRing && spinnerRing._path) {
                    spinnerRing._path.classList.add('marker-spinner-ring');
                }
            }, 0);
            var mapBounds = map.getBounds();
            if (!mapBounds.contains(item.latlng)) {
                var center = map.getCenter();
                var nearCenter = L.latLng(
                    center.lat + 0.88 * (item.latlng.lat - center.lat),
                    center.lng + 0.88 * (item.latlng.lng - center.lng)
                );
                map.panTo(nearCenter, { animate: true, duration: 0.6 });
            }
            document.getElementById('report-prev-btn').style.visibility = index > 0 ? 'visible' : 'hidden';
            document.getElementById('report-next-btn').style.visibility = index < reportMarkers.length - 1 ? 'visible' : 'hidden';
            var photoEl = document.getElementById('report-detail-photo');
            var imgEl = document.getElementById('report-detail-img');
            var titleEl = document.getElementById('report-detail-title');
            if (item.report.photo_url && photoEl && imgEl) {
                imgEl.src = item.report.photo_url;
                photoEl.style.display = 'block';
            } else if (photoEl) {
                photoEl.style.display = 'none';
            }
            if (titleEl) {
                titleEl.textContent = item.report.problem_type || 'Report';
                titleEl.style.display = 'block';
            }
            var infoEl = document.getElementById('report-detail-info');
            if (infoEl) {
                infoEl.style.display = 'flex';
                var st = item.report.status || 'pending';
                var isResolved = st === 'resolved';
                var isInProgress = ['in_progress', 'under_review'].indexOf(st) >= 0;
                var statusText = isResolved ? 'Resolved' : (isInProgress ? 'In progress' : 'Pending');
                var statusColor = isResolved ? '#00FF26' : (isInProgress ? '#FFAE00' : '#FF0000');
                var statusEl = document.getElementById('report-detail-status');
                statusEl.textContent = statusText;
                statusEl.style.color = statusColor;
                document.getElementById('report-detail-date').textContent = item.report.created_at || '—';
                var resolvedEl = document.getElementById('report-detail-resolved');
                if (st === 'resolved') {
                    resolvedEl.textContent = item.report.updated_at || '—';
                    resolvedEl.style.color = 'white';
                } else {
                    resolvedEl.innerHTML = '<span style="color: rgba(255,255,255,0.45);">In Progress<span class="in-progress-dots">...</span></span>';
                }
                document.getElementById('report-detail-address').textContent = item.report.address || '—';
                var boxEl = document.getElementById('report-detail-box');
                var descEl = document.getElementById('report-detail-description');
                var desc = (item.report.description && String(item.report.description).trim()) ? String(item.report.description).trim() : '';
                if (boxEl && descEl) {
                    descEl.textContent = desc;
                    boxEl.style.display = 'block';
                }
            }
        }

        reports.forEach(function(r, idx) {
            if (r.latitude != null && r.longitude != null) {
                var st = r.status || 'pending';
                var isResolved = st === 'resolved';
                var isInProgress = ['in_progress', 'under_review'].indexOf(st) >= 0;
                var color = isResolved ? '#00FF26' : (isInProgress ? '#FFAE00' : '#FF0000');
                var stroke = isResolved ? '#00B31A' : (isInProgress ? '#CC8800' : '#B30000');
                var marker = L.circleMarker([r.latitude, r.longitude], {
                    radius: 5,
                    fillColor: color,
                    color: stroke,
                    weight: 2,
                    fillOpacity: 0.9
                }).addTo(map);
                var latlng = L.latLng(r.latitude, r.longitude);
                var normalizedStatus = isResolved ? 'resolved' : (isInProgress ? 'in_progress' : 'pending');
                var markerIdx = reportMarkers.length;
                reportMarkers.push({ marker: marker, report: r, latlng: latlng, normalizedStatus: normalizedStatus });
                marker.on('click', function(e) {
                    L.DomEvent.stopPropagation(e);
                    if (panel) panel.classList.remove('report-panel-hidden');
                    showReportAt(markerIdx);
                });
                bounds.push([r.latitude, r.longitude]);
            }
        });

        document.getElementById('report-prev-btn').addEventListener('click', function() {
            if (currentReportIndex > 0) showReportAt(currentReportIndex - 1);
        });
        document.getElementById('report-next-btn').addEventListener('click', function() {
            if (currentReportIndex >= 0 && currentReportIndex < reportMarkers.length - 1) showReportAt(currentReportIndex + 1);
        });

        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [40, 40], maxZoom: 15 });
        }

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(pos) {
                var lat = pos.coords.latitude;
                var lng = pos.coords.longitude;
                upsertUserLocationMarker(lat, lng);
                if (bounds.length === 0) {
                    map.setView([lat, lng], 14);
                }
            });
        }

        function applyStatusFilter() {
            if (spinnerRing) {
                map.removeLayer(spinnerRing);
                spinnerRing = null;
            }
            if (panel) panel.classList.add('report-panel-hidden');

            reportMarkers.forEach(function(item) {
                if (selectedStatuses[item.normalizedStatus]) {
                    if (!map.hasLayer(item.marker)) {
                        item.marker.addTo(map);
                    }
                } else if (map.hasLayer(item.marker)) {
                    map.removeLayer(item.marker);
                }
            });
        }

        var filterBox = document.getElementById('livemap-filter-box');
        var filterBtn = document.getElementById('livemap-filter-btn');
        var filterOptions = document.getElementById('livemap-filter-options');
        if (filterBtn && filterBox && filterOptions) {
            var optionNodes = Array.prototype.slice.call(filterOptions.querySelectorAll('.livemap-filter-option'));

            filterBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                var isOpen = filterBox.classList.toggle('is-open');
                filterBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });

            optionNodes.forEach(function(optionEl) {
                optionEl.addEventListener('click', function(e) {
                    e.stopPropagation();
                    var key = optionEl.getAttribute('data-filter-status');
                    if (!key || !(key in selectedStatuses)) return;

                    selectedStatuses[key] = !selectedStatuses[key];
                    optionEl.classList.toggle('is-selected', selectedStatuses[key]);
                    optionEl.setAttribute('aria-checked', selectedStatuses[key] ? 'true' : 'false');
                    applyStatusFilter();
                });
            });

            document.addEventListener('click', function() {
                filterBox.classList.remove('is-open');
                filterBtn.setAttribute('aria-expanded', 'false');
            });
        }

        applyStatusFilter();

        if (window.GisSafeRoute) {
            window.GisSafeRoute.init(map, {
                containerId: 'livemap-safe-route-host',
                analyzeUrl: @json(route('safe-route.analyze')),
                csrf: @json(csrf_token()),
                livemap: true
            });
        }

        setTimeout(function() { map.invalidateSize(); }, 200);
    </script>
    @endpush
</x-layouts::customer>
