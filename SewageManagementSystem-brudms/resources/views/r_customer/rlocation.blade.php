<x-layouts::customer :title="__('Report Problem') . ' – BruDMS'" :bare="true">
    @push('styles')
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        @include('r_customer.partials.report-flow-spacing')
        <style>
            body, main { overflow: hidden !important; }
            .press-btn { transition: transform 0.06s ease; }
            .press-btn:active { transform: scale(0.92) !important; }
            #confirm-location-btn:active { transform: translateX(-50%) scale(0.92) !important; }
            #confirm-location-btn:disabled {
                background: rgba(66, 106, 120, 0.35) !important;
                color: rgba(255, 255, 255, 0.65) !important;
                box-shadow: none !important;
                cursor: not-allowed !important;
            }
            #confirm-location-btn:disabled:active {
                transform: translateX(-50%) !important;
            }
            #confirm-location-btn.confirm-location-btn-active {
                background: #04BCFF !important;
                color: #0a1628 !important;
                box-shadow: 0 2px 8px rgba(4, 188, 255, 0.3) !important;
                cursor: pointer !important;
            }
            #locate-btn:active { transform: scale(0.92) !important; }
            .leaflet-control-attribution { display: none !important; }
            .leaflet-control-zoom { display: none !important; }
        </style>
    @endpush

    {{-- Desktop: normal background --}}
    <div class="hidden lg:block fixed inset-0 z-0" style="background-image: url('/images/crdboard.png'); background-size: cover; background-position: center; background-repeat: no-repeat;"></div>

    {{-- Mobile: rotated -90deg background --}}
    <div class="lg:hidden" style="position: fixed; inset: 0; overflow: hidden; z-index: 0;">
        <div style="width: 100vh; height: 100vw; transform: rotate(-90deg); transform-origin: top left; position: absolute; top: 100%; left: 0; background-image: url('/images/crdboard.png'); background-size: cover; background-position: center; background-repeat: no-repeat;"></div>
    </div>

    @php
        $guestReportFlow = filter_var($guestReportFlow ?? false, FILTER_VALIDATE_BOOLEAN);
        $user = auth()->user();
        $reportStep = function (string $step) use ($guestReportFlow, $user) {
            return $guestReportFlow
                ? route('guest.report.'.$step)
                : route('customer.'.$step, ['name' => $user->profileSlug()]);
        };
    @endphp
    <a href="{{ $reportStep('rpicture') }}" class="press-btn delayed-nav" style="position: fixed; top: 4vh; left: 20px; z-index: 20; display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 9999px; text-decoration: none;" aria-label="{{ __('Back') }}">
        <img src="{{ asset('images/Vectors/all_backarrow.svg') }}" alt="" style="width: 21px; height: 21px;" />
    </a>

    <div style="position: fixed; left: 6px; right: 6px; top: 11vh; bottom: -50vh; border-radius: 21px 21px 0 0; background: rgba(217, 217, 217, 0.07); backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px); z-index: 1; pointer-events: none;"></div>

    <img src="{{ asset('images/Vectors/report_rlocation.svg') }}" alt="" style="position: fixed; left: calc(20px + (40px - 21px) / 2); top: calc(11vh + 30px); z-index: 5; width: 30px; height: 31px; pointer-events: none;" />

    {{-- Map: isolated wrapper, explicit size, no overflow tricks --}}
    <div style="position: fixed; left: 50%; top: 42%; transform: translate(-50%, -50%); width: 85%; max-width: 320px; height: 260px; border-radius: 16px; background: rgba(217, 217, 217, 0.06); border: 0.7px solid rgba(255, 255, 255, 0.25); z-index: 2;">
        <div id="map" style="width: 100%; height: 100%; border-radius: 16px;"></div>
        <div id="location-prompt" class="rflow-location-prompt" style="position: absolute; inset: 0; background: rgba(0, 0, 0, 0.85); border-radius: 16px; z-index: 10;">
            <span style="color: white; font-size: 14px; font-weight: 600; font-family: Poppins, sans-serif;">Get your current location</span>
            <span id="location-prompt-hint" style="color: rgba(255, 255, 255, 0.7); font-size: 12px; font-family: Poppins, sans-serif;">Tap below to allow location access</span>
            <button type="button" id="get-location-btn" class="press-btn" style="width: 100%; max-width: 200px; padding: 12px 20px; border-radius: 12px; background: #04BCFF; color: #0a1628; font-family: Poppins, sans-serif; font-weight: 600; font-size: 14px; border: none; cursor: pointer;">📍 Get my location</button>
            <span style="color: rgba(255, 255, 255, 0.5); font-size: 11px; font-family: Poppins, sans-serif;">Or tap map to set manually</span>
        </div>
        <button type="button" id="locate-btn" class="press-btn" style="position: absolute; bottom: 12px; right: 12px; z-index: 11; width: 40px; height: 40px; border-radius: 50%; background: white; border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.2); cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 18px;" title="Locate me">📍</button>
    </div>

    <div id="location-display-box" style="position: fixed; left: 50%; transform: translateX(-50%); top: calc(42% + 130px + 12px); width: 85%; max-width: 320px; min-height: 48px; padding: 12px 16px; border-radius: 16px; background: rgba(217, 217, 217, 0.06); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 0.7px solid rgba(255, 255, 255, 0.25); z-index: 4; display: flex; align-items: center; pointer-events: none;">
        <span id="location-display-text" style="color: rgba(255, 255, 255, 0.7); font-size: 13px; font-family: Poppins, sans-serif; font-weight: 400; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis;">Tap map or 📍 to set location</span>
    </div>

    <div style="position: fixed; left: 6px; right: 6px; bottom: 0; height: 100px; border-radius: 0; background: linear-gradient(to top, rgba(217, 217, 217, 0.02), transparent); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); mask-image: linear-gradient(to top, black 60%, transparent); -webkit-mask-image: linear-gradient(to top, black 60%, transparent); z-index: 6; pointer-events: none;"></div>
    <button type="button" id="confirm-location-btn" class="press-btn" disabled style="position: fixed; left: 50%; transform: translateX(-50%); bottom: 24px; z-index: 7; width: 82%; max-width: 360px; height: 48px; border: none; border-radius: 16px; background: rgba(66, 106, 120, 0.35); color: rgba(255, 255, 255, 0.65); font-family: Poppins, sans-serif; font-weight: 700; font-size: 15px; cursor: not-allowed; display: flex; align-items: center; justify-content: center; box-shadow: none;">Confirm location</button>

    <div class="rflow-main-stack rflow-main-stack--inert">
        <div class="rflow-header-col">
            <span class="rflow-header-line-primary">Where it happens determines how fast we respond</span>
            <span class="rflow-header-line-secondary">Pinpoint your location</span>
        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.querySelectorAll('.delayed-nav').forEach(function(el) {
            el.style.transition = 'transform 0.1s ease';
            el.addEventListener('click', function(e) {
                e.preventDefault();
                var href = el.getAttribute('href');
                el.style.transform = 'scale(0.95)';
                setTimeout(function() {
                    el.style.transform = 'scale(1)';
                    setTimeout(function() { window.location.href = href; }, 100);
                }, 100);
            });
        });

        var defaultCenter = [4.9031, 114.9398];
        var bruneiBounds = L.latLngBounds(
            L.latLng(4.00, 114.00),
            L.latLng(5.12, 115.40)
        );
        var savedLat = null, savedLng = null;
        try {
            var s = sessionStorage.getItem('rproblem_lat');
            var t = sessionStorage.getItem('rproblem_lng');
            if (s && t) { savedLat = parseFloat(s); savedLng = parseFloat(t); }
        } catch (e) {}

        var map = L.map('map', {
            center: savedLat && savedLng ? [savedLat, savedLng] : defaultCenter,
            zoom: 15,
            minZoom: 10,
            maxZoom: 18,
            maxBounds: bruneiBounds,
            maxBoundsViscosity: 1.0,
            zoomControl: false
        });
        L.control.zoom({ position: 'topright' }).addTo(map);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '',
            maxZoom: 19,
            maxNativeZoom: 19
        }).addTo(map);

        map.on('drag', function() {
            map.panInsideBounds(bruneiBounds, { animate: false });
        });
        map.on('zoomend', function() {
            map.panInsideBounds(bruneiBounds, { animate: false });
        });

        var marker = null;
        var isVerifiedBrunei = false;
        var pendingLat = null;
        var pendingLng = null;
        var pendingAddress = '';
        var confirmLocationBtn = document.getElementById('confirm-location-btn');

        function updateConfirmButtonState() {
            if (!confirmLocationBtn) return;
            var canConfirm = !!marker && isVerifiedBrunei;
            confirmLocationBtn.disabled = !canConfirm;
            confirmLocationBtn.classList.toggle('confirm-location-btn-active', canConfirm);
        }

        if (savedLat && savedLng && bruneiBounds.contains(L.latLng(savedLat, savedLng))) {
            marker = L.marker([savedLat, savedLng]).addTo(map);
            document.getElementById('location-prompt').style.display = 'none';
            pendingLat = savedLat;
            pendingLng = savedLng;
            updateDisplay(savedLat, savedLng);
        } else if (savedLat && savedLng) {
            savedLat = null;
            savedLng = null;
            try {
                sessionStorage.removeItem('rproblem_lat');
                sessionStorage.removeItem('rproblem_lng');
                sessionStorage.removeItem('rproblem_address');
            } catch (e) {}
        }

        var lastGeocodeTime = 0;
        function updateDisplay(lat, lng) {
            var el = document.getElementById('location-display-text');
            el.textContent = 'Looking up address…';
            el.style.color = 'rgba(255, 255, 255, 0.8)';
            var now = Date.now();
            var delay = Math.max(0, 1100 - (now - lastGeocodeTime));
            lastGeocodeTime = now + delay;
            setTimeout(function() {
                fetch('https://nominatim.openstreetmap.org/reverse?lat=' + lat + '&lon=' + lng + '&format=json', {
                    headers: { 'Accept': 'application/json', 'User-Agent': 'BruDMS/1.0' }
                }).then(function(r) { return r.json(); }).then(function(data) {
                    var addr = (data && data.display_name) ? data.display_name : (lat.toFixed(6) + ', ' + lng.toFixed(6));
                    var country = (data && data.address && data.address.country) ? String(data.address.country) : '';
                    var looksMalaysia = /malaysia/i.test(country) || /malaysia/i.test(addr);
                    pendingAddress = addr;
                    el.textContent = addr;
                    el.style.color = looksMalaysia ? 'rgba(255, 120, 120, 0.95)' : 'rgba(255, 255, 255, 0.95)';
                    isVerifiedBrunei = !looksMalaysia;
                    updateConfirmButtonState();
                }).catch(function() {
                    var fallback = lat.toFixed(6) + ', ' + lng.toFixed(6);
                    pendingAddress = fallback;
                    el.textContent = fallback;
                    el.style.color = 'rgba(255, 255, 255, 0.95)';
                    isVerifiedBrunei = true;
                    updateConfirmButtonState();
                });
            }, delay);
        }

        function setLocation(lat, lng) {
            var latlng = L.latLng(lat, lng);
            if (!bruneiBounds.contains(latlng)) {
                document.getElementById('location-prompt-hint').textContent = 'Please pick a location within Brunei only.';
                document.getElementById('location-prompt').style.display = 'flex';
                return;
            }
            pendingLat = lat;
            pendingLng = lng;
            pendingAddress = '';
            if (marker) map.removeLayer(marker);
            marker = L.marker([lat, lng]).addTo(map);
            isVerifiedBrunei = false;
            map.setView([lat, lng], map.getZoom());
            document.getElementById('location-prompt').style.display = 'none';
            updateConfirmButtonState();
            updateDisplay(lat, lng);
        }

        map.on('click', function(e) {
            setLocation(e.latlng.lat, e.latlng.lng);
        });

        function tryGetLocation() {
            if (!navigator.geolocation) {
                document.getElementById('location-prompt-hint').textContent = 'Location not supported. Tap map to set manually.';
                document.getElementById('location-prompt').style.display = 'flex';
                return;
            }
            document.getElementById('location-prompt-hint').textContent = 'Getting your location…';
            document.getElementById('get-location-btn').disabled = true;
            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    setLocation(pos.coords.latitude, pos.coords.longitude);
                },
                function(err) {
                    document.getElementById('get-location-btn').disabled = false;
                    var msg = 'Allow location in browser settings, or tap map to set manually.';
                    if (err.code === 1) msg = 'Location denied. Allow in browser settings, or tap map to set manually.';
                    else if (err.code === 3) msg = 'Taking too long. Tap "Get my location" to try again.';
                    document.getElementById('location-prompt-hint').textContent = msg;
                    document.getElementById('location-prompt').style.display = 'flex';
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
            );
        }

        document.getElementById('get-location-btn').addEventListener('click', function(e) {
            e.stopPropagation();
            tryGetLocation();
        });

        document.getElementById('location-prompt').addEventListener('click', function(e) {
            if (e.target.id === 'get-location-btn' || e.target.closest('#get-location-btn')) return;
            this.style.display = 'none';
        });

        document.getElementById('locate-btn').addEventListener('click', function(e) {
            e.stopPropagation();
            tryGetLocation();
        });

        if (!savedLat || !savedLng) {
            tryGetLocation();
        }

        updateConfirmButtonState();

        document.getElementById('confirm-location-btn').addEventListener('click', function() {
            if (!marker || !isVerifiedBrunei) return;
            var latlng = marker.getLatLng();
            var confirmedAddress = pendingAddress || (document.getElementById('location-display-text').textContent || '').trim();
            try {
                sessionStorage.setItem('rproblem_lat', String(latlng.lat));
                sessionStorage.setItem('rproblem_lng', String(latlng.lng));
                if (confirmedAddress) {
                    sessionStorage.setItem('rproblem_address', confirmedAddress);
                } else {
                    sessionStorage.removeItem('rproblem_address');
                }
            } catch (e) {}
            window.location.href = '{{ $reportStep("rdetails") }}';
        });

        setTimeout(function() { map.invalidateSize(); }, 200);
    </script>
    @endpush
</x-layouts::customer>
