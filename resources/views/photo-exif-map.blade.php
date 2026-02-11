<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Photo EXIF Location - Brunei Darussalam</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: linear-gradient(135deg, #1a472a 0%, #2d5a3d 50%, #1a472a 100%); min-height: 100vh; color: #f0f7f4; }
        .app-card { background: rgba(255, 255, 255, 0.95); border-radius: 16px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3); overflow: hidden; color: #1a2e1f; }
        .upload-zone { border: 3px dashed #2d5a3d; border-radius: 12px; padding: 2rem; text-align: center; cursor: pointer; transition: all 0.3s; background: rgba(45, 90, 61, 0.05); }
        .upload-zone:hover, .upload-zone.dragover { border-color: #fddb27; background: rgba(253, 219, 39, 0.1); }
        .upload-zone i { font-size: 3rem; color: #2d5a3d; }
        #map { height: 400px; border-radius: 12px; }
        .exif-table td:first-child { font-weight: 600; color: #2d5a3d; }
        .no-gps-alert { background: #fff3cd; color: #856404; }
        .device-location-alert { background: #cce5ff; color: #004085; }
        .extracting { opacity: 0.7; pointer-events: none; }
    </style>
</head>
<body class="py-4">
    <div class="container">
        <div class="text-center mb-4">
            <h1 class="text-white mb-1"><i class="fas fa-camera-retro me-2"></i>Photo EXIF Location Finder</h1>
            <p class="text-white-50">Upload or capture a photo to extract EXIF metadata and pinpoint its location in Brunei Darussalam</p>
            <p class="text-white-50 small">Compatible with iOS (HEIC) and Android (JPEG)</p>
        </div>

        <div class="app-card p-4 mb-4">
            <div class="row g-4">
                <div class="col-lg-5">
                    <h5 class="mb-3"><i class="fas fa-image me-2"></i>Photo</h5>
                    <div class="upload-zone" id="uploadZone">
                        <i class="fas fa-cloud-upload-alt d-block mb-2"></i>
                        <p class="mb-1">Click to upload or drag & drop</p>
                        <small class="text-muted">JPG, PNG, HEIC — iOS & Android</small>
                        <input type="file" id="photoInput" accept="image/*,image/heic,image/heif,.heic,.heif" capture="environment" class="d-none">
                    </div>
                    <div class="mt-3 text-center d-none" id="previewWrap">
                        <img id="previewImg" src="" alt="Preview" class="img-fluid rounded" style="max-height: 220px;">
                        <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="clearBtn">Clear</button>
                    </div>
                </div>
                <div class="col-lg-7">
                    <h5 class="mb-3"><i class="fas fa-info-circle me-2"></i>EXIF Metadata</h5>
                    <div id="exifPlaceholder" class="text-muted small">Upload a photo to see EXIF data</div>
                    <div id="exifContent" class="d-none">
                        <table class="table table-sm exif-table"><tbody id="exifTable"></tbody></table>
                    </div>
                    <div id="noGpsAlert" class="alert no-gps-alert d-none">
                        <i class="fas fa-map-marker-alt-slash me-2"></i><span id="noGpsText">No GPS data in this photo.</span>
                    </div>
                    <div id="deviceLocationAlert" class="alert device-location-alert d-none">
                        <i class="fas fa-location-crosshairs me-2"></i>Using your device location (photo had no GPS).
                    </div>
                </div>
            </div>
        </div>

        <div class="app-card p-4">
            <h5 class="mb-3"><i class="fas fa-map-marked-alt me-2"></i>Location in Brunei Darussalam</h5>
            <div id="map"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://cdn.jsdelivr.net/npm/exifr@7.1.3/dist/full.umd.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/exif-heic-js@1.0.2/exif-heic.js"></script>
    <script>
        const BRUNEI_CENTER = [4.8903, 114.9422];
        const BRUNEI_BOUNDS = [[4.0, 114.0], [5.2, 115.5]];
        let map, marker;

        map = L.map('map', { center: BRUNEI_CENTER, zoom: 10, maxBounds: BRUNEI_BOUNDS, maxBoundsViscosity: 0.9 });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);

        function toDecimal(coord, ref) {
            if (!coord || !Array.isArray(coord)) return null;
            const sign = (ref === 'S' || ref === 'W') ? -1 : 1;
            const toNum = v => typeof v === 'object' && v?.denominator ? v.numerator / v.denominator : Number(v);
            return sign * (toNum(coord[0]) + toNum(coord[1]) / 60 + toNum(coord[2]) / 3600);
        }

        function hasGps(data) {
            if (!data) return false;
            if (typeof data.latitude === 'number' && typeof data.longitude === 'number') return true;
            return !!(data.GPSLatitude && data.GPSLongitude && data.GPSLatitudeRef && data.GPSLongitudeRef);
        }

        function getGpsCoords(data) {
            if (typeof data.latitude === 'number' && typeof data.longitude === 'number')
                return [data.latitude, data.longitude];
            if (data.GPSLatitude && data.GPSLongitude && data.GPSLatitudeRef && data.GPSLongitudeRef)
                return [toDecimal(data.GPSLatitude, data.GPSLatitudeRef), toDecimal(data.GPSLongitude, data.GPSLongitudeRef)];
            return [null, null];
        }

        function mergeData(target, source) {
            if (!source || typeof source !== 'object') return target;
            for (const k of Object.keys(source)) if (target[k] === undefined) target[k] = source[k];
            return target;
        }

        async function extractWithExifr(file, opts = {}) {
            try {
                return await exifr.parse(file, { gps: true, tiff: true, xmp: true, ...opts }) || {};
            } catch (e) { return {}; }
        }

        async function extractWithExifHeic(buffer) {
            try {
                if (typeof findEXIFinHEIC === 'function')
                    return findEXIFinHEIC(buffer) || {};
            } catch (e) {}
            return {};
        }

        async function readAsArrayBuffer(file) {
            return new Promise((resolve, reject) => {
                const r = new FileReader();
                r.onload = () => resolve(r.result);
                r.onerror = reject;
                r.readAsArrayBuffer(file);
            });
        }

        async function handleFile(file) {
            if (!file) return;
            const isImage = file.type?.startsWith('image/') || /\.(heic|heif|jpg|jpeg|png|gif|webp)$/i.test(file.name || '');
            if (!isImage) return;

            document.getElementById('uploadZone').classList.add('d-none');
            document.getElementById('previewWrap').classList.remove('d-none');
            const card = document.querySelector('.app-card');
            card?.classList.add('extracting');

            const img = document.getElementById('previewImg');
            img.parentElement.querySelectorAll('.heic-fallback').forEach(el => el.remove());
            const url = URL.createObjectURL(file);
            img.style.display = '';
            img.onerror = () => {
                URL.revokeObjectURL(url);
                img.style.display = 'none';
                const p = document.createElement('p');
                p.className = 'text-muted small heic-fallback';
                p.textContent = 'Preview not available for HEIC. EXIF extracted.';
                img.parentElement.insertBefore(p, img.nextSibling);
            };
            img.onload = () => URL.revokeObjectURL(url);
            img.src = url;

            const isHeic = /heic|heif/i.test(file.type || file.name || '');
            let data = {};

            data = await extractWithExifr(file);

            if (!hasGps(data) && isHeic) {
                const fullRead = await extractWithExifr(file, { chunked: false });
                data = mergeData(data, fullRead);
            }

            if (!hasGps(data)) {
                try {
                    const gpsOnly = await exifr.gps(file);
                    if (gpsOnly?.latitude != null && gpsOnly?.longitude != null)
                        data = { ...data, latitude: gpsOnly.latitude, longitude: gpsOnly.longitude };
                } catch (e) {}
            }

            if (!hasGps(data) && isHeic) {
                const buf = await readAsArrayBuffer(file);
                const heicData = await extractWithExifHeic(buf);
                data = mergeData(data, heicData);
            }

            if (!hasGps(data)) {
                const buf = await readAsArrayBuffer(file);
                try {
                    if (typeof findEXIFinJPEG === 'function') {
                        const jpegData = findEXIFinJPEG(buf);
                        if (jpegData) data = mergeData(data, jpegData);
                    }
                } catch (e) {}
            }

            card?.classList.remove('extracting');
            renderExif(data);

            const noGpsAlert = document.getElementById('noGpsAlert');
            const noGpsText = document.getElementById('noGpsText');
            const deviceLocationAlert = document.getElementById('deviceLocationAlert');

            noGpsAlert.classList.add('d-none');
            deviceLocationAlert.classList.add('d-none');

            if (hasGps(data)) {
                const [lat, lng] = getGpsCoords(data);
                if (lat != null && lng != null) {
                    setMarker([lat, lng], 'Photo EXIF');
                } else {
                    clearMarker();
                    noGpsText.textContent = 'No GPS data in this photo.';
                    noGpsAlert.classList.remove('d-none');
                }
            } else {
                noGpsText.textContent = 'No GPS in photo. Getting your location…';
                noGpsAlert.classList.remove('d-none');

                const deviceLoc = await getDeviceLocation();
                if (deviceLoc) {
                    setMarker([deviceLoc.lat, deviceLoc.lng], 'Device location');
                    noGpsAlert.classList.add('d-none');
                    deviceLocationAlert.classList.remove('d-none');
                } else {
                    clearMarker();
                    noGpsText.textContent = 'Location unavailable. Allow location access or take photos with Camera location enabled.';
                }
            }
        }

        function renderExif(data) {
            const placeholder = document.getElementById('exifPlaceholder');
            const content = document.getElementById('exifContent');
            const table = document.getElementById('exifTable');

            if (!data || Object.keys(data).length === 0) {
                placeholder.textContent = 'No EXIF data found';
                content.classList.add('d-none');
                return;
            }

            placeholder.classList.add('d-none');
            content.classList.remove('d-none');
            table.innerHTML = '';

            const order = ['DateTime', 'CreateDate', 'ModifyDate', 'DateTimeOriginal', 'Make', 'Model', 'Software', 'latitude', 'longitude', 'GPSLatitude', 'GPSLatitudeRef', 'GPSLongitude', 'GPSLongitudeRef', 'GPSAltitude', 'GPSImgDirection'];
            const seen = new Set();

            for (const key of order) {
                if (data[key] === undefined) continue;
                seen.add(key);
                const val = formatExifValue(key, data[key]);
                table.insertAdjacentHTML('beforeend', `<tr><td>${key}</td><td>${val}</td></tr>`);
            }
            for (const key of Object.keys(data)) {
                if (seen.has(key)) continue;
                const val = formatExifValue(key, data[key]);
                table.insertAdjacentHTML('beforeend', `<tr><td>${key}</td><td>${val}</td></tr>`);
            }
        }

        function formatExifValue(key, val) {
            if (val instanceof Date) return val.toLocaleString();
            if (Array.isArray(val))
                return val.map(v => typeof v === 'object' && v?.denominator ? (v.numerator / v.denominator).toFixed(4) : String(v)).join(', ');
            if (typeof val === 'object' && val !== null && 'numerator' in val)
                return (val.numerator / (val.denominator || 1)).toFixed(4);
            if (typeof val === 'number' && (key === 'latitude' || key === 'longitude')) return val.toFixed(6);
            return String(val ?? '');
        }

        function setMarker(latLng, source = 'Photo location') {
            if (marker) marker.remove();
            marker = L.marker(latLng).addTo(map).bindPopup(`<b>${source}</b><br>${latLng[0].toFixed(6)}, ${latLng[1].toFixed(6)}`);
            map.setView(latLng, Math.max(map.getZoom(), 14));
        }

        function clearMarker() {
            if (marker) { marker.remove(); marker = null; }
            map.setView(BRUNEI_CENTER, 10);
        }

        function getDeviceLocation() {
            return new Promise((resolve) => {
                if (!navigator.geolocation) {
                    resolve(null);
                    return;
                }
                navigator.geolocation.getCurrentPosition(
                    pos => resolve({ lat: pos.coords.latitude, lng: pos.coords.longitude }),
                    () => resolve(null),
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
                );
            });
        }

        const zone = document.getElementById('uploadZone');
        const input = document.getElementById('photoInput');
        zone.addEventListener('click', () => input.click());
        zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('dragover'); });
        zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
        zone.addEventListener('drop', e => {
            e.preventDefault();
            zone.classList.remove('dragover');
            if (e.dataTransfer.files.length) handleFile(e.dataTransfer.files[0]);
        });
        input.addEventListener('change', e => { if (e.target.files.length) handleFile(e.target.files[0]); });

        document.getElementById('clearBtn').addEventListener('click', () => {
            input.value = '';
            const prevImg = document.getElementById('previewImg');
            if (prevImg.src?.startsWith('blob:')) URL.revokeObjectURL(prevImg.src);
            prevImg.src = '';
            prevImg.style.display = '';
            prevImg.parentElement.querySelectorAll('.heic-fallback').forEach(el => el.remove());
            document.getElementById('uploadZone').classList.remove('d-none');
            document.getElementById('previewWrap').classList.add('d-none');
            document.getElementById('exifPlaceholder').textContent = 'Upload a photo to see EXIF data';
            document.getElementById('exifPlaceholder').classList.remove('d-none');
            document.getElementById('exifContent').classList.add('d-none');
            document.getElementById('noGpsAlert').classList.add('d-none');
            document.getElementById('deviceLocationAlert').classList.add('d-none');
            clearMarker();
        });
    </script>
</body>
</html>
