<x-layouts::customer :title="__('Live Map') . ' – BruDMS'" :bare="true">
    @push('styles')
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <style>
            body, main { overflow: hidden !important; margin: 0; padding: 0; }
            #livemap { position: fixed; inset: 0; width: 100%; height: 100%; z-index: 1; }
            .leaflet-control-attribution { display: none !important; }
            @keyframes marker-spin { to { transform: rotate(360deg); } }
            .marker-spinner-ring { animation: marker-spin 1.5s linear infinite; transform-origin: center; transform-box: fill-box; }
            #report-detail-panel { transition: transform 0.3s ease; }
            #report-detail-panel.report-panel-hidden { transform: translateY(calc(100% + 40px)); }
            .report-nav-btn:hover img { opacity: 1; }
.report-nav-btn img { opacity: 0.8; }
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
            <span style="font-family: Poppins, sans-serif; font-weight: 700; font-size: 20px; color: rgba(255, 255, 255, 0.9); margin-top: -2px;">{{ $count_active ?? 0 }}</span>
        </div>
        <div style="width: 1.3px; height: 28px; background: rgba(255, 255, 255, 0.5); flex-shrink: 0;"></div>
        <div style="flex: 1; display: flex; flex-direction: column; align-items: center; padding-top: 12px;">
            <span style="font-family: Poppins, sans-serif; font-weight: 600; font-size: 10px; color: rgba(255, 255, 255, 0.9); transform: translateY(-7px);">Case in progress</span>
            <span style="font-family: Poppins, sans-serif; font-weight: 700; font-size: 20px; color: rgba(255, 255, 255, 0.9); margin-top: -2px;">{{ $count_in_progress ?? 0 }}</span>
        </div>
        <div style="width: 1.3px; height: 28px; background: rgba(255, 255, 255, 0.5); flex-shrink: 0;"></div>
        <div style="flex: 1; display: flex; flex-direction: column; align-items: center; padding-top: 12px;">
            <span style="font-family: Poppins, sans-serif; font-weight: 600; font-size: 10px; color: rgba(255, 255, 255, 0.9); transform: translateY(-7px);">Resolved</span>
            <span style="font-family: Poppins, sans-serif; font-weight: 700; font-size: 20px; color: rgba(255, 255, 255, 0.9); margin-top: -2px;">{{ $count_resolved ?? 0 }}</span>
        </div>
    </div>

    <div id="report-detail-panel" class="report-panel-hidden" style="position: fixed; left: 20px; right: 20px; bottom: 20px; z-index: 10; min-height: 200px; height: auto; max-height: 48vh; background: rgba(97, 107, 110, 0.15); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 0.7px solid rgba(255, 255, 255, 0.21); border-radius: 14px; display: flex; align-items: center; justify-content: space-between; padding: 10px 0; gap: 12px;">
        <button type="button" id="report-prev-btn" class="report-nav-btn" style="width: 24px; height: 24px; border: none; background: transparent; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; visibility: hidden;" aria-label="Previous">
            <img src="{{ asset('images/Vectors/livemap_scrollerreport.svg') }}" alt="" style="width: 12px; height: 12px; transform: scaleX(-1);" />
        </button>
        <div id="report-detail-content" style="display: flex; align-items: flex-start; gap: 12px; margin-right: auto; margin-left: -10px; flex: 1; min-width: 0;">
            <div id="report-detail-photo" style="flex: 0 0 auto; width: 118px; height: 140px; border-radius: 4px; overflow: hidden; background: rgba(0,0,0,0.3); display: none;">
                <img id="report-detail-img" src="" alt="" style="width: 100%; height: 100%; object-fit: cover;" />
            </div>
            <div style="display: flex; flex-direction: column; gap: 0px; min-width: 0; flex: 1;">
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
                <div id="report-detail-box" style="display: none; margin-top: 8px; width: 100%; max-width: 100%; height: 52px; flex-shrink: 0; border-radius: 8px; background: rgba(66, 106, 120, 0.25); border: 0.7px solid rgba(255, 255, 255, 0.18); box-sizing: border-box; padding: 6px 8px; overflow-y: auto; overflow-x: hidden;">
                    <p id="report-detail-description" style="margin: 0; color: rgba(255, 255, 255, 0.75); font-family: Poppins, sans-serif; font-weight: 300; font-size: 9px; line-height: 1.35; word-wrap: break-word;"></p>
                </div>
            </div>
        </div>
        <button type="button" id="report-next-btn" class="report-nav-btn" style="width: 24px; height: 24px; border: none; background: transparent; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; visibility: hidden;" aria-label="Next">
            <img src="{{ asset('images/Vectors/livemap_scrollerreport.svg') }}" alt="" style="width: 12px; height: 12px;" />
        </button>
    </div>

    <div id="livemap"></div>

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        var defaultCenter = [4.5353, 114.7277];
        var reports = @json($reports ?? []);

        var map = L.map('livemap', {
            center: defaultCenter,
            zoom: 13,
            zoomControl: false
        });
        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles © Esri'
        }).addTo(map);

        var panel = document.getElementById('report-detail-panel');
        map.on('click', function() {
            if (panel) panel.classList.add('report-panel-hidden');
            if (spinnerRing) { map.removeLayer(spinnerRing); spinnerRing = null; }
        });

        var bounds = [];
        var spinnerRing = null;
        var reportMarkers = [];
        var currentReportIndex = -1;

        function showReportAt(index) {
            if (index < 0 || index >= reportMarkers.length) return;
            currentReportIndex = index;
            var item = reportMarkers[index];
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
                var statusText = (st === 'resolved') ? 'Resolved' : (['in_progress','under_review'].indexOf(st) >= 0 ? 'In progress' : 'Active');
                var statusColor = (st === 'resolved') ? '#00ff73' : (['in_progress','under_review'].indexOf(st) >= 0 ? '#ffae00' : '#e00808');
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
                var statusColors = { pending: '#ff0000', in_progress: '#FFAE00', under_review: '#FFAE00', resolved: '#00FF26' };
                var strokeColors = { pending: '#b30000', in_progress: '#cc8800', under_review: '#cc8800', resolved: '#00b31a' };
                var color = statusColors[r.status] || '#ff0000';
                var stroke = strokeColors[r.status] || '#b30000';
                var marker = L.circleMarker([r.latitude, r.longitude], {
                    radius: 5,
                    fillColor: color,
                    color: stroke,
                    weight: 2,
                    fillOpacity: 0.9
                }).addTo(map);
                var latlng = L.latLng(r.latitude, r.longitude);
                var markerIdx = reportMarkers.length;
                reportMarkers.push({ marker: marker, report: r, latlng: latlng });
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
        } else if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(pos) {
                map.setView([pos.coords.latitude, pos.coords.longitude], 14);
            });
        }

        setTimeout(function() { map.invalidateSize(); }, 200);
    </script>
    @endpush
</x-layouts::customer>
