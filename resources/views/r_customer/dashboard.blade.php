<x-layouts::customer :title="__('Home') . ' – BruDMS'" :bare="true">
    @push('styles')
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <style>
            @keyframes pulseGlowGreen {
                0%, 100% { box-shadow: 0 0 3px 1px rgba(0, 255, 38, 0.4); }
                50% { box-shadow: 0 0 8px 3px rgba(0, 255, 38, 0.7); }
            }
            @keyframes pulseGlowYellow {
                0%, 100% { box-shadow: 0 0 3px 1px rgba(255, 174, 0, 0.4); }
                50% { box-shadow: 0 0 8px 3px rgba(255, 174, 0, 0.7); }
            }
            @keyframes pulseGlowRed {
                0%, 100% { box-shadow: 0 0 3px 1px rgba(255, 0, 0, 0.4); }
                50% { box-shadow: 0 0 8px 3px rgba(255, 0, 0, 0.7); }
            }
            .status-dot { animation: pulseGlowGreen 2s ease-in-out infinite; }
            .status-dot.status-yellow { animation: pulseGlowYellow 2s ease-in-out infinite; }
            .status-dot.status-red { animation: pulseGlowRed 2s ease-in-out infinite; }
            @keyframes slideInRight {
                from { opacity: 0; transform: translateX(30px); }
                to { opacity: 1; transform: translateX(0); }
            }
            .incident-card {
                opacity: 0;
                animation: slideInRight 0.5s ease-out forwards;
                transition: transform 0.1s ease;
            }
            .incident-card.press-btn:active {
                animation: none;
                opacity: 1;
                transform: scale(0.95);
            }
            .tab-btn {
                transition: outline-color 0.3s ease, transform 0.1s ease;
            }
            .tab-btn:active, .tab-btn-active:active {
                transform: scale(0.95);
            }
            .tab-btn-active {
                transition: transform 0.1s ease;
            }
            .press-btn {
                transition: transform 0.1s ease;
            }
            .press-btn:active {
                transform: scale(0.93) !important;
            }
            .tab-btn:hover {
                outline-color: #04BCFF !important;
            }
            .tab-btn { color: #9CA3AF; }
            .tab-btn-active { color: #040929; }
            .tab-btn:hover { color: #04BCFF; }
            .tab-btn-locked { opacity: 0.55; position: relative; }
        </style>
    @endpush
    {{-- Desktop: normal background --}}
    <div class="hidden lg:block fixed inset-0 z-0" style="background-image: url('/images/crdboard.png'); background-size: cover; background-position: center; background-repeat: no-repeat;"></div>

    {{-- Mobile: rotated -90deg background --}}
    <div class="lg:hidden" style="position: fixed; inset: 0; overflow: hidden; z-index: 0;">
        <div style="width: 100vh; height: 100vw; transform: rotate(-90deg); transform-origin: top left; position: absolute; top: 100%; left: 0; background-image: url('/images/crdboard.png'); background-size: cover; background-position: center; background-repeat: no-repeat;"></div>
    </div>

    {{-- Header: logo + BruDMS + profile photo --}}
    @php
        $guestMode = filter_var($guestMode ?? false, FILTER_VALIDATE_BOOLEAN);
        $user = auth()->user();
        $photoPath = $user?->profile_photo_path ?? null;
        $photoUrl = $photoPath ? \Illuminate\Support\Facades\Storage::url($photoPath) : null;
    @endphp
    <div style="position: fixed; top: 4vh; left: 20px; right: 20px; z-index: 10; display: flex; align-items: center; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <img src="{{ asset('images/logo.png') }}" alt="BruDMS" style="width: 36px; height: 36px; object-fit: contain;" />
            <span style="color: white; font-size: 24px; font-weight: 600; font-family: Poppins, sans-serif;">BruDMS</span>
        </div>
        <a href="{{ $guestMode ? route('login') : route('customer.general', ['name' => $user->profileSlug()]) }}" class="press-btn {{ $guestMode ? '' : 'delayed-nav' }}" style="cursor: pointer; display: flex; align-items: center; justify-content: center; text-decoration: none;" title="{{ $guestMode ? __('Account — sign in') : '' }}">
        @if($guestMode)
            <span style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <img src="{{ asset('images/Vectors/dashboard_unlogged.svg') }}" alt="" width="24" height="24" style="width: 24px; height: 24px; object-fit: contain; display: block;" />
            </span>
        @elseif($photoUrl)
            <img src="{{ $photoUrl }}" alt="" style="width: 40px; height: 40px; border-radius: 9999px; object-fit: cover;" />
        @elseif(file_exists(public_path('images/default-avatar.png')))
            <img src="{{ asset('images/default-avatar.png') }}" alt="" style="width: 40px; height: 40px; border-radius: 9999px; object-fit: cover;" />
        @else
            <div style="width: 40px; height: 40px; border-radius: 9999px; background: #3f3f46; display: flex; align-items: center; justify-content: center; color: #e4e4e7; font-size: 14px; font-weight: 600;">{{ $user->initials() }}</div>
        @endif
        </a>
    </div>

    <div style="position: fixed; left: 6px; right: 6px; top: 11vh; bottom: -50vh; border-radius: 21px 21px 0 0; background: rgba(217, 217, 217, 0.07); backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px); z-index: 1;"></div>

    {{-- Tab buttons (inside rectangle) --}}
    <div style="position: fixed; top: 13.5vh; left: 22px; right: 22px; z-index: 10; display: flex; justify-content: center; gap: 10px;">
        {{-- Home (active) --}}
        <a href="{{ $guestMode ? route('guest.explore') : route('customer.dashboard', ['name' => $user->profileSlug()]) }}" class="tab-btn-active" style="flex: 1; height: 43px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: #04BCFF; backdrop-filter: blur(1.5px);">
            <img src="{{ asset('images/Vectors/tab_home-active.svg') }}" alt="" style="width: 22px; height: 22px; object-fit: contain;" />
        </a>
        {{-- Chat (Ziqah AI) — sign in when guest --}}
        <a href="{{ $guestMode ? route('login') : route('customer.brudmsgpt', ['name' => $user->profileSlug()]) }}" class="tab-btn {{ $guestMode ? 'tab-btn-locked' : 'delayed-nav' }}" style="flex: 1; height: 43px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: rgba(66, 106, 120, 0.16); outline: 1.7px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(1.5px); text-decoration: none;">
            <img src="{{ asset('images/Vectors/tab_chat.svg') }}" alt="" style="width: 22px; height: 22px; object-fit: contain;" />
        </a>
        {{-- History — your reports only; sign in when guest --}}
        <a href="{{ $guestMode ? route('login') : route('customer.myhistory', ['name' => $user->profileSlug()]) }}" class="tab-btn {{ $guestMode ? 'tab-btn-locked' : 'delayed-nav' }}" style="flex: 1; height: 43px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: rgba(66, 106, 120, 0.16); outline: 1.7px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(1.5px); text-decoration: none;">
            <img src="{{ asset('images/Vectors/tab_myhistory.svg') }}" alt="" style="width: 22px; height: 22px; object-fit: contain;" />
        </a>
    </div>

    {{-- Active incidents section --}}
    <div style="position: fixed; top: 22vh; left: 22px; right: 22px; z-index: 10;">
        {{-- Header row --}}
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
            <span style="color: white; font-size: 14px; font-weight: 700; font-family: Poppins, sans-serif; flex-shrink: 0; margin-left: 8px;">Active incidents</span>
            <div style="display: flex; gap: 6px; overflow-x: auto; -webkit-overflow-scrolling: touch; scrollbar-width: none; padding: 2px; margin-left: 16px;">
                <div style="padding: 3px 8px; border-radius: 7px; background: rgba(66, 106, 120, 0.16); outline: 1px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(1.5px); flex-shrink: 0; display: flex; align-items: center;">
                    <span style="color: white; font-size: 8px; font-weight: 700; font-family: Poppins, sans-serif;">All</span>
                </div>
                <div style="padding: 3px 8px; border-radius: 7px; background: rgba(66, 106, 120, 0.16); outline: 1px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(1.5px); flex-shrink: 0; display: flex; align-items: center;">
                    <span style="color: white; font-size: 8px; font-weight: 700; font-family: Poppins, sans-serif;">Under Review</span>
                </div>
                <div style="padding: 3px 8px; border-radius: 7px; background: rgba(66, 106, 120, 0.16); outline: 1px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(1.5px); flex-shrink: 0; display: flex; align-items: center;">
                    <span style="color: white; font-size: 8px; font-weight: 700; font-family: Poppins, sans-serif;">In Progress</span>
                </div>
                <div style="padding: 3px 8px; border-radius: 7px; background: rgba(66, 106, 120, 0.16); outline: 1px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(1.5px); flex-shrink: 0; display: flex; align-items: center;">
                    <span style="color: white; font-size: 8px; font-weight: 700; font-family: Poppins, sans-serif;">Resolved</span>
                </div>
            </div>
        </div>

        {{-- Horizontally scrolling cards --}}
        <div style="display: flex; gap: 10px; overflow-x: auto; padding: 4px 4px 8px 4px; -webkit-overflow-scrolling: touch; scrollbar-width: none;">
            {{-- Add new card: requests location permission before navigating --}}
            <a href="{{ $guestMode ? route('guest.report.rproblem') : route('customer.rproblem', ['name' => $user->profileSlug()]) }}" id="add-report-card" class="press-btn" style="width: 110px; min-width: 110px; height: 160px; display: flex; align-items: center; justify-content: center; border-radius: 9px; background: rgba(66, 106, 120, 0.16); outline: 0.7px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(1.5px); flex-shrink: 0; cursor: pointer; text-decoration: none;">
                <img src="{{ asset('images/Vectors/dashboard_addreport.svg') }}" alt="" style="width: 55px; height: 55px; object-fit: contain;" />
            </a>

            @php
                $statusColors = ['pending' => '#ff0000', 'in_progress' => '#FFAE00', 'under_review' => '#FFAE00', 'resolved' => '#00FF26'];
            @endphp
            @foreach($reports ?? [] as $i => $report)
            <div class="incident-card" style="width: 110px; min-width: 110px; height: 160px; border-radius: 9px; background: rgba(66, 106, 120, 0.16); outline: 0.7px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(1.5px); flex-shrink: 0; display: flex; flex-direction: column; overflow: hidden; animation-delay: {{ 0.1 + ($i + 1) * 0.15 }}s;">
                @if($report->photo_path)
                <img src="{{ Storage::url($report->photo_path) }}" alt="" style="width: 100%; height: 75px; min-width: 0; object-fit: cover; border-top-left-radius: 9px; border-top-right-radius: 9px; outline: 0.7px solid rgba(255, 255, 255, 0.21);" />
                @else
                <div style="width: 100%; height: 75px; background: rgba(139, 105, 20, 0.5); border-top-left-radius: 9px; border-top-right-radius: 9px; outline: 0.7px solid rgba(255, 255, 255, 0.21);"></div>
                @endif
                <div style="padding: 6px 8px; flex: 1; display: flex; flex-direction: column;">
                    <span style="color: white; font-size: 9px; font-weight: 600; font-family: Poppins, sans-serif; line-height: 1.3; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $report->problem_type }}</span>
                    <span style="color: white; font-size: 9px; font-weight: 300; font-family: Poppins, sans-serif; line-height: 1.3; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ Str::limit($report->address ?? '—', 20) }}</span>
                    <div style="margin-top: auto; display: flex; flex-direction: column; gap: 3px;">
                        <span style="color: rgba(255,255,255,0.4); font-size: 7px; font-weight: 300; font-family: Poppins, sans-serif; line-height: 1;">{{ $report->created_at->format('jS M Y') }}</span>
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 4px; min-width: 0; flex: 1;">
                            @php $reportUser = $report->user; @endphp
                            @if($reportUser && $reportUser->profile_photo_path)
                                <img src="{{ Storage::url($reportUser->profile_photo_path) }}" alt="" style="width: 14px; height: 14px; border-radius: 9999px; object-fit: cover; flex-shrink: 0;" />
                            @else
                                <div style="width: 14px; height: 14px; border-radius: 9999px; background: #3f3f46; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <span style="color: #e4e4e7; font-size: 6px; font-weight: 600; font-family: Poppins, sans-serif;">{{ $reportUser ? substr($reportUser->name, 0, 1) : '?' }}</span>
                                </div>
                            @endif
                            <span style="color: rgba(255,255,255,0.6); font-size: 7px; font-weight: 400; font-family: Poppins, sans-serif; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; position: relative; top: 2px;">{{ $reportUser->name ?? 'Unknown' }}</span>
                        </div>
                        @php $st = $report->status ?? 'pending'; $glowClass = ($st === 'resolved') ? '' : (($st === 'in_progress' || $st === 'under_review') ? ' status-yellow' : ' status-red'); @endphp
                        <div class="status-dot{{ $glowClass }}" style="width: 6px; height: 6px; border-radius: 9999px; background: {{ $statusColors[$st] ?? '#ff0000' }}; flex-shrink: 0;"></div>
                    </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- View Live Map section --}}
        <div style="margin-top: 20px;">
            <span style="color: white; font-size: 14px; font-weight: 700; font-family: Poppins, sans-serif; display: block; margin-bottom: 10px; margin-left: 8px;">View Live Map</span>
            @if($guestMode)
            {{-- Map = real Leaflet tiles (background). Not inside <a> so tiles mount correctly; tap overlay goes to login. --}}
            <div class="press-btn" style="position: relative; width: 100%; height: 15vh; min-height: 132px; border-radius: 9px; background: rgba(66, 106, 120, 0.16); outline: 0.7px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(1.5px); padding: 3px; box-sizing: border-box;">
                <div style="position: relative; width: 100%; height: 100%; min-height: 120px; border-radius: 7px; overflow: hidden; isolation: isolate;">
                    <div id="live-map" style="position: absolute; inset: 0; width: 100%; height: 100%; min-height: 120px; filter: brightness(0.5); z-index: 1;"></div>
                    <a href="{{ route('login') }}" aria-label="{{ __('Sign in') }}" style="position: absolute; inset: 0; z-index: 2; border-radius: 7px; text-decoration: none; cursor: pointer;"></a>
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 3; pointer-events: none; display: flex; flex-direction: column; align-items: center; gap: 6px; max-width: 88%; text-align: center;">
                        <img src="{{ asset('images/Vectors/explore_lock.svg') }}" alt="" style="width: 50px; height: 45px; object-fit: contain; flex-shrink: 0;" />
                        <span style="color: rgba(255, 255, 255, 0.42); font-size: 9px; font-weight: 500; font-family: Poppins, sans-serif; line-height: 1.25;">{{ __('Log in to use this feature') }}</span>
                    </div>
                </div>
            </div>
            @else
            <div class="press-btn" style="position: relative; width: 100%; height: 15vh; min-height: 132px; border-radius: 9px; background: rgba(66, 106, 120, 0.16); outline: 0.7px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(1.5px); padding: 3px; box-sizing: border-box;">
                <div style="position: relative; width: 100%; height: 100%; min-height: 120px; border-radius: 7px; overflow: hidden; isolation: isolate;">
                    <div id="live-map" style="position: absolute; inset: 0; width: 100%; height: 100%; min-height: 120px; filter: brightness(0.5); z-index: 1;"></div>
                    <a href="{{ route('customer.livemap', ['name' => $user->profileSlug()]) }}" class="delayed-nav" aria-label="{{ __('Open live map') }}" style="position: absolute; inset: 0; z-index: 2; border-radius: 7px; text-decoration: none; cursor: pointer;"></a>
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 3; pointer-events: none;">
                        <img src="{{ asset('images/Vectors/dashboard_livemap.svg') }}" alt="" style="width: 50px; height: 45px; object-fit: contain;" />
                    </div>
                </div>
            </div>
            @endif
        </div>
        {{-- Incidents last 7 days section --}}
        <div style="margin-top: 20px;">
            <span style="color: white; font-size: 14px; font-weight: 700; font-family: Poppins, sans-serif; display: block; margin-bottom: 10px; margin-left: 8px;">Incidents last 7 days</span>
            @if($guestMode)
            <div class="press-btn" style="position: relative; width: 100%; height: 15vh; min-height: 132px; border-radius: 9px; background: rgba(66, 106, 120, 0.16); outline: 0.7px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(1.5px); padding: 3px; box-sizing: border-box;">
                <div style="position: relative; width: 100%; height: 100%; min-height: 120px; border-radius: 7px; overflow: hidden; isolation: isolate;">
                    <svg style="position: absolute; inset: 0; width: 100%; height: 100%;" preserveAspectRatio="none">
                        <defs>
                            <pattern id="grid-guest" width="40" height="40" patternUnits="userSpaceOnUse">
                                <path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(255,255,255,0.07)" stroke-width="0.7"/>
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#grid-guest)" />
                    </svg>
                    <a href="{{ route('login') }}" aria-label="{{ __('Sign in') }}" style="position: absolute; inset: 0; z-index: 2; border-radius: 7px; text-decoration: none; cursor: pointer;"></a>
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 3; pointer-events: none; display: flex; flex-direction: column; align-items: center; gap: 6px; max-width: 88%; text-align: center;">
                        <img src="{{ asset('images/Vectors/explore_lock.svg') }}" alt="" style="width: 50px; height: 45px; object-fit: contain; flex-shrink: 0;" />
                        <span style="color: rgba(255, 255, 255, 0.42); font-size: 9px; font-weight: 500; font-family: Poppins, sans-serif; line-height: 1.25;">{{ __('Log in to use this feature') }}</span>
                    </div>
                </div>
            </div>
            @else
            <div style="position: relative; width: 100%; height: 15vh;">
            <div style="width: 100%; height: 100%; border-radius: 9px; background: rgba(66, 106, 120, 0.16); outline: 0.7px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(1.5px); position: relative; padding: 3px;">
                <div style="width: 100%; height: 100%; border-radius: 7px; outline: 0.7px solid rgba(255, 255, 255, 0.21); overflow: hidden; position: relative;">
                    {{-- Grid pattern --}}
                    <svg style="position: absolute; inset: 0; width: 100%; height: 100%;" preserveAspectRatio="none">
                        <defs>
                            <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                                <path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(255,255,255,0.07)" stroke-width="0.7"/>
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#grid)" />
                    </svg>
                    {{-- Chart icon overlay --}}
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); pointer-events: none;">
                        <img src="{{ asset('images/Vectors/dashboard_analytics.svg') }}" alt="" style="width: 55px; height: 55px; object-fit: contain;" />
                    </div>
                </div>
            </div>
            </div>
            @endif
        </div>
    </div>
    @push('scripts')
    <script>
        window.addEventListener('pageshow', function(e) {
            if (e.persisted) window.location.reload();
        });
    </script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @unless($guestMode ?? false)
    {{-- Request location permission as soon as dashboard loads so browser prompts user --}}
    <script>
        (function() {
            if (!navigator.geolocation) return;
            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    try {
                        sessionStorage.setItem('rproblem_lat', String(pos.coords.latitude));
                        sessionStorage.setItem('rproblem_lng', String(pos.coords.longitude));
                    } catch (e) {}
                },
                function() {},
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
            );
        })();
    </script>
    @endunless
    <script>
        function initDashboardLiveMap() {
            var el = document.getElementById('live-map');
            if (!el || typeof L === 'undefined') return;
            if (el.getAttribute('data-leaflet-mounted') === '1') return;
            el.setAttribute('data-leaflet-mounted', '1');
            var defaultLat = 4.9031;
            var defaultLng = 114.9398;
            var map = L.map('live-map', {
                zoomControl: false,
                attributionControl: false,
                dragging: false,
                scrollWheelZoom: false,
                doubleClickZoom: false,
                touchZoom: false,
                boxZoom: false,
                keyboard: false
            }).setView([defaultLat, defaultLng], 14);

            L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}').addTo(map);

            @unless($guestMode ?? false)
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(pos) {
                    map.setView([pos.coords.latitude, pos.coords.longitude], 15);
                });
            }
            @endunless

            function reflow() { try { map.invalidateSize(); } catch (e) {} }
            setTimeout(reflow, 0);
            setTimeout(reflow, 200);
            setTimeout(reflow, 600);
            window.addEventListener('load', reflow);
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initDashboardLiveMap);
        } else {
            initDashboardLiveMap();
        }
    </script>
    <script>
        document.querySelectorAll('.delayed-nav').forEach(function(el) {
            el.style.transition = 'transform 0.1s ease';
            el.addEventListener('click', function(e) {
                e.preventDefault();
                var href = el.getAttribute('href');
                el.style.transform = 'scale(0.95)';
                setTimeout(function() {
                    el.style.transform = 'scale(1)';
                    setTimeout(function() {
                        window.location.href = href;
                    }, 100);
                }, 100);
            });
        });
        var addCard = document.getElementById('add-report-card');
        if (addCard && navigator.geolocation) {
            addCard.addEventListener('click', function(e) {
                e.preventDefault();
                var href = this.getAttribute('href');
                navigator.geolocation.getCurrentPosition(
                    function(pos) {
                        try {
                            sessionStorage.setItem('rproblem_lat', String(pos.coords.latitude));
                            sessionStorage.setItem('rproblem_lng', String(pos.coords.longitude));
                        } catch (x) {}
                        window.location.href = href;
                    },
                    function() {
                        window.location.href = href;
                    },
                    { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                );
            });
        }
        @if(session('status') === 'report-submitted')
        try { ['rpicture','rproblem_photo','rproblem_choice','rproblem_address','rproblem_lat','rproblem_lng','rproblem_severity','rproblem_description'].forEach(function(k){ sessionStorage.removeItem(k); }); } catch(x){}
        alert('Submitted!');
        @endif
    </script>
    @endpush
</x-layouts::customer>
