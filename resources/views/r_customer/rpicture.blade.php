<x-layouts::customer :title="__('Report Problem') . ' – BruDMS'" :bare="true">
    @push('styles')
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;600;700&display=swap" rel="stylesheet">
        @include('r_customer.partials.report-flow-spacing')
        <style>
            .press-btn { transition: transform 0.06s ease; }
            .press-btn:active { transform: scale(0.92) !important; }
            #camera-btn { cursor: pointer; }
            #camera-btn:active { transform: translate(-50%, -50%) scale(0.98) !important; }
            #confirm-photo-btn:active { transform: translateX(-50%) scale(0.92) !important; }
            #camera-overlay { display: none; position: fixed; inset: 0; z-index: 20; background: rgba(0,0,0,0.9); flex-direction: column; align-items: center; justify-content: center; }
            #camera-overlay.active { display: flex; }
            #camera-overlay video { max-width: 100%; max-height: 70vh; border-radius: 12px; }
            #camera-overlay .capture-bar { margin-top: 20px; display: flex; gap: 16px; align-items: center; }
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
    <a href="{{ $reportStep('rproblem') }}" class="press-btn delayed-nav" style="position: fixed; top: 4vh; left: 20px; z-index: 10; display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 9999px; text-decoration: none;" aria-label="{{ __('Back') }}">
        <img src="{{ asset('images/Vectors/all_backarrow.svg') }}" alt="" style="width: 21px; height: 21px;" />
    </a>

    <div style="position: fixed; left: 6px; right: 6px; top: 11vh; bottom: -50vh; border-radius: 21px 21px 0 0; background: rgba(217, 217, 217, 0.07); backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px); z-index: 1;"></div>

    <img src="{{ asset('images/Vectors/report_rpicture.svg') }}" alt="" style="position: fixed; left: calc(20px + (40px - 21px) / 2); top: calc(11vh + 30px); z-index: 5; width: 30px; height: 31px;" />

    <label for="camera-file-input" id="camera-btn" style="position: fixed; left: 50%; top: 42%; transform: translate(-50%, -50%); width: 85%; max-width: 320px; height: 260px; border-radius: 16px; background: rgba(217, 217, 217, 0.06); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 0.7px solid rgba(255, 255, 255, 0.25); z-index: 4; display: flex; align-items: center; justify-content: center; padding: 0; cursor: pointer; margin: 0; overflow: hidden;">
        <img id="captured-preview" src="" alt="" style="display: none; position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; border-radius: 16px; z-index: 0;" />
        <div id="captured-overlay" style="display: none; position: absolute; inset: 0; background: rgba(0, 0, 0, 0.4); border-radius: 16px; z-index: 1; pointer-events: none;"></div>
        <img src="{{ asset('images/Vectors/report_maincamera.svg') }}" alt="" id="camera-icon" style="width: 64px; height: 64px; opacity: 0.9; pointer-events: none; position: relative; z-index: 2;" />
    </label>
    <input type="file" id="camera-file-input" accept="image/*" capture="environment" style="display: none;">

    <div id="camera-overlay">
        <video id="camera-video" autoplay playsinline muted></video>
        <canvas id="camera-canvas" style="display: none;"></canvas>
        <div class="capture-bar">
            <button type="button" id="capture-btn" style="width: 64px; height: 64px; border-radius: 50%; border: 4px solid white; background: transparent; cursor: pointer;"></button>
            <button type="button" id="close-camera-btn" style="padding: 10px 20px; border-radius: 12px; background: rgba(255,255,255,0.2); color: white; border: none; font-family: Poppins, sans-serif; cursor: pointer;">Close</button>
        </div>
    </div>

    <div style="position: fixed; left: 6px; right: 6px; bottom: 0; height: 100px; border-radius: 0; background: linear-gradient(to top, rgba(217, 217, 217, 0.02), transparent); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); mask-image: linear-gradient(to top, black 60%, transparent); -webkit-mask-image: linear-gradient(to top, black 60%, transparent); z-index: 6; pointer-events: none;"></div>
    <button type="button" id="confirm-photo-btn" class="press-btn" style="position: fixed; left: 50%; transform: translateX(-50%); bottom: 24px; z-index: 7; width: 82%; max-width: 360px; height: 48px; border: none; border-radius: 16px; background: #04BCFF; color: #0a1628; font-family: Poppins, sans-serif; font-weight: 700; font-size: 15px; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(4, 188, 255, 0.3);">Confirm photo</button>

    <div class="rflow-main-stack">
        <div class="rflow-header-col">
            <span class="rflow-header-line-primary">Clear visuals lead to faster action on the ground</span>
            <span class="rflow-header-line-secondary">Capture the problem using your camera</span>
        </div>
    </div>

    @push('scripts')
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
        var cameraStream = null;
        var useLiveCamera = !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
        function compressAndStore(dataUrl, cb) {
            var img = new Image();
            img.onload = function() {
                var max = 800;
                var w = img.width, h = img.height;
                if (w > max || h > max) {
                    if (w > h) { h = Math.round(h * max / w); w = max; }
                    else { w = Math.round(w * max / h); h = max; }
                }
                var c = document.createElement('canvas');
                c.width = w; c.height = h;
                var ctx = c.getContext('2d');
                ctx.drawImage(img, 0, 0, w, h);
                var compressed = c.toDataURL('image/jpeg', 0.82);
                try { sessionStorage.setItem('rpicture', compressed); } catch (x) {}
                if (cb) cb(compressed);
            };
            img.onerror = function() { if (cb) cb(dataUrl); };
            img.src = dataUrl;
        }
        function showCapturedImage(dataUrl) {
            compressAndStore(dataUrl, function(stored) {
                document.getElementById('captured-preview').src = stored;
                document.getElementById('captured-preview').style.display = 'block';
                document.getElementById('captured-overlay').style.display = 'block';
            });
        }
        document.getElementById('camera-file-input').addEventListener('change', function(e) {
            var file = e.target.files[0];
            if (file) {
                var r = new FileReader();
                r.onload = function() { showCapturedImage(r.result); };
                r.readAsDataURL(file);
            }
            e.target.value = '';
        });
        try {
            var saved = sessionStorage.getItem('rpicture');
            if (saved) showCapturedImage(saved);
        } catch (e) {}
        document.getElementById('camera-btn').addEventListener('click', function(e) {
            e.preventDefault();
            if (useLiveCamera) {
                navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } }).then(function(stream) {
                    cameraStream = stream;
                    document.getElementById('camera-video').srcObject = stream;
                    document.getElementById('camera-overlay').classList.add('active');
                }).catch(function() {
                    useLiveCamera = false;
                    document.getElementById('camera-file-input').click();
                });
            } else {
                document.getElementById('camera-file-input').click();
            }
        });
        document.getElementById('close-camera-btn').addEventListener('click', function() {
            if (cameraStream) {
                cameraStream.getTracks().forEach(function(t) { t.stop(); });
                cameraStream = null;
            }
            document.getElementById('camera-video').srcObject = null;
            document.getElementById('camera-overlay').classList.remove('active');
        });
        document.getElementById('capture-btn').addEventListener('click', function() {
            var video = document.getElementById('camera-video');
            var canvas = document.getElementById('camera-canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            var ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0);
            showCapturedImage(canvas.toDataURL('image/jpeg'));
            if (cameraStream) {
                cameraStream.getTracks().forEach(function(t) { t.stop(); });
                cameraStream = null;
            }
            document.getElementById('camera-video').srcObject = null;
            document.getElementById('camera-overlay').classList.remove('active');
        });
        document.getElementById('confirm-photo-btn').addEventListener('click', function() {
            if (!sessionStorage.getItem('rpicture')) return;
            window.location.href = '{{ $reportStep("rlocation") }}';
        });
    </script>
    @endpush
</x-layouts::customer>
