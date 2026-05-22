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
            .dashboard-incident-card[hidden] { display: none !important; }
            .dashboard-incident-cards-track {
                overflow-y: visible;
            }
            .dashboard-glass-filter-slot {
                position: relative;
                width: 32px;
                height: 32px;
                flex-shrink: 0;
            }
            .dashboard-glass-filter-box {
                position: absolute;
                top: 0;
                right: 0;
                width: 32px;
                height: 32px;
                display: flex;
                flex-direction: column;
                border-radius: 8px;
                background: rgba(97, 107, 110, 0.15);
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                border: 0.7px solid rgba(255, 255, 255, 0.18);
                box-sizing: border-box;
                overflow: hidden;
                z-index: 12;
                transition:
                    width 0.32s cubic-bezier(0.22, 1, 0.36, 1),
                    height 0.32s cubic-bezier(0.22, 1, 0.36, 1),
                    border-radius 0.32s cubic-bezier(0.22, 1, 0.36, 1);
            }
            .dashboard-glass-filter-box.is-open {
                width: 128px;
                height: 108px;
                border-radius: 9px;
            }
            .dashboard-glass-filter-trigger { flex-shrink: 0; }
            .dashboard-glass-filter-box.is-open .dashboard-glass-filter-trigger,
            .dashboard-glass-filter-box.is-closing .dashboard-glass-filter-trigger {
                display: flex;
                justify-content: flex-end;
                height: 20px;
                padding: 0 3px 0;
                overflow: hidden;
                visibility: hidden;
                pointer-events: none;
            }
            .dashboard-glass-filter-box.is-open .dashboard-glass-filter-icon,
            .dashboard-glass-filter-box.is-closing .dashboard-glass-filter-icon {
                opacity: 0;
                visibility: hidden;
            }
            .dashboard-glass-filter-box.is-closing .dashboard-glass-filter-options {
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.12s ease;
            }
            .dashboard-glass-filter-btn {
                width: 32px;
                height: 32px;
                border: none;
                background: transparent;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                padding: 0;
                flex-shrink: 0;
            }
            .dashboard-glass-filter-options {
                flex: 1;
                min-height: 0;
                overflow: hidden;
                opacity: 0;
                visibility: hidden;
                padding: 0 5px;
                transition: opacity 0.16s ease;
            }
            .dashboard-glass-filter-box.is-open .dashboard-glass-filter-options {
                flex: 0 0 auto;
                opacity: 1;
                visibility: visible;
                margin-top: -5px;
                padding: 0 5px 6px;
                pointer-events: none;
                transition: opacity 0.2s ease 0.14s;
            }
            .dashboard-glass-filter-option {
                width: 100%;
                border: none;
                background: transparent;
                color: rgba(255, 255, 255, 0.88);
                display: flex;
                align-items: center;
                gap: 6px;
                padding: 4px 4px;
                white-space: nowrap;
                border-radius: 6px;
                font-family: Poppins, sans-serif;
                font-size: 8px;
                font-weight: 500;
                text-align: left;
                cursor: pointer;
                position: relative;
                flex-shrink: 0;
                pointer-events: auto;
            }
            .dashboard-glass-filter-option + .dashboard-glass-filter-option {
                margin-top: 3px;
            }
            .dashboard-glass-filter-option + .dashboard-glass-filter-option::before {
                content: '';
                position: absolute;
                top: -2px;
                left: 5px;
                right: 5px;
                border-top: 0.7px solid rgba(255, 255, 255, 0.18);
            }
            .dashboard-glass-filter-option:hover,
            .dashboard-glass-filter-option:focus,
            .dashboard-glass-filter-option:active {
                background: transparent;
                outline: none;
            }
            .dashboard-glass-filter-dot {
                width: 10px;
                min-width: 10px;
                height: 10px;
                border-radius: 9999px;
                border: 0.7px solid rgba(255, 255, 255, 0.85);
                background: transparent;
                flex-shrink: 0;
                box-sizing: border-box;
                position: relative;
            }
            .dashboard-glass-filter-option.is-selected .dashboard-glass-filter-dot::after {
                content: '';
                position: absolute;
                left: 50%;
                top: 50%;
                width: 4px;
                height: 4px;
                border-radius: 9999px;
                background: #ffffff;
                transform: translate(-50%, -50%);
            }

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
        <a href="{{ $guestMode ? route('guest.explore') : route('customer.dashboard', ['name' => $user->profileSlug()]) }}" class="press-btn {{ $guestMode ? '' : 'delayed-nav' }}" style="display: flex; align-items: center; gap: 8px; text-decoration: none; cursor: pointer;" aria-label="{{ __('Home') }}">
            <img src="{{ asset('images/logo.png') }}" alt="" style="width: 36px; height: 36px; object-fit: contain;" />
            <span style="color: white; font-size: 24px; font-weight: 600; font-family: Poppins, sans-serif;">BruDMS</span>
        </a>
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

    {{-- Tab buttons — Home | History --}}
    <div style="position: fixed; top: 13.5vh; left: 22px; right: 22px; z-index: 10; display: flex; justify-content: center; gap: 10px;">
        <a href="{{ $guestMode ? route('guest.explore') : route('customer.dashboard', ['name' => $user->profileSlug()]) }}" class="tab-btn-active" style="flex: 1; min-width: 0; height: 43px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: #04BCFF; backdrop-filter: blur(1.5px); text-decoration: none;">
            <img src="{{ asset('images/Vectors/tab_home-active.svg') }}" alt="" style="width: 22px; height: 22px; object-fit: contain;" />
        </a>
        <a href="{{ $guestMode ? route('login') : route('customer.myhistory', ['name' => $user->profileSlug()]) }}" class="tab-btn {{ $guestMode ? 'tab-btn-locked' : 'delayed-nav' }}" style="flex: 1; min-width: 0; height: 43px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: rgba(66, 106, 120, 0.16); outline: 1.7px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(1.5px); text-decoration: none;">
            <img src="{{ asset('images/Vectors/tab_myhistory.svg') }}" alt="" style="width: 22px; height: 22px; object-fit: contain;" />
        </a>
    </div>

    {{-- Active incidents section --}}
    <div style="position: fixed; top: 22vh; left: 22px; right: 22px; z-index: 10;">
        {{-- Header row --}}
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px; gap: 10px;">
            <span style="color: white; font-size: 15px; font-weight: 700; font-family: Poppins, sans-serif; flex-shrink: 0; margin-left: 8px; line-height: 1;">Active incidents</span>
            <div class="dashboard-glass-filter-slot">
            <div id="dashboard-glass-filter-box" class="dashboard-glass-filter-box" role="group" aria-label="{{ __('Filter incidents') }}">
                <div class="dashboard-glass-filter-trigger">
                    <button id="dashboard-glass-filter-btn" type="button" class="dashboard-glass-filter-btn press-btn" aria-label="{{ __('Filter incidents') }}" aria-expanded="false" aria-haspopup="menu">
                        <img class="dashboard-glass-filter-icon" src="{{ asset('images/Vectors/livemap_filter.svg') }}" alt="" style="width: 12px; height: 12px; object-fit: contain;" />
                    </button>
                </div>
                <div id="dashboard-glass-filter-options" class="dashboard-glass-filter-options" role="menu">
                    <button type="button" class="dashboard-glass-filter-option is-selected" data-dashboard-filter="owner_review" role="menuitemcheckbox" aria-checked="true">
                        <span class="dashboard-glass-filter-dot"></span>
                        <span>{{ __('Under review') }}</span>
                    </button>
                    <button type="button" class="dashboard-glass-filter-option is-selected" data-dashboard-filter="pending" role="menuitemcheckbox" aria-checked="true">
                        <span class="dashboard-glass-filter-dot"></span>
                        <span>{{ __('Pending') }}</span>
                    </button>
                    <button type="button" class="dashboard-glass-filter-option is-selected" data-dashboard-filter="in_progress" role="menuitemcheckbox" aria-checked="true">
                        <span class="dashboard-glass-filter-dot"></span>
                        <span>{{ __('In progress') }}</span>
                    </button>
                    <button type="button" class="dashboard-glass-filter-option is-selected" data-dashboard-filter="resolved" role="menuitemcheckbox" aria-checked="true">
                        <span class="dashboard-glass-filter-dot"></span>
                        <span>{{ __('Resolved') }}</span>
                    </button>
                </div>
            </div>
            </div>
        </div>

        {{-- Horizontally scrolling cards --}}
        <div class="dashboard-incident-cards-track" style="display: flex; gap: 10px; overflow-x: auto; padding: 2px 4px 10px 4px; -webkit-overflow-scrolling: touch; scrollbar-width: none;">
            {{-- Add new card: requests location permission before navigating --}}
            <a href="{{ $guestMode ? route('login') : route('customer.rproblem', ['name' => $user->profileSlug()]) }}" id="add-report-card" class="press-btn" style="width: 110px; min-width: 110px; height: 160px; display: flex; align-items: center; justify-content: center; border-radius: 9px; background: rgba(66, 106, 120, 0.16); outline: 0.7px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(1.5px); flex-shrink: 0; cursor: pointer; text-decoration: none;">
                <img src="{{ asset('images/Vectors/dashboard_addreport.svg') }}" alt="" style="width: 55px; height: 55px; object-fit: contain;" />
            </a>

            @php
                $statusColors = ['pending' => '#ff0000', 'in_progress' => '#FFAE00', 'under_review' => '#FFAE00', 'resolved' => '#00FF26'];
            @endphp
            @foreach($reports ?? [] as $i => $report)
            @php
                $isOwner = !$guestMode && $user && $report->user_id === $user->id;
                $isRejected = $report->wasRejectedByAdmin();
                $isNotSentToOperations = !($report->operationsReport ?? null);
                $isOwnerUnderReview = ! $isRejected && $isNotSentToOperations && $isOwner;
                if ($isNotSentToOperations && ! $isOwner && ! $isRejected) {
                    continue;
                }
                $reportPhotoOk = $report->photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($report->photo_path);
                $stForFilter = $report->status ?? 'pending';
                $cardOwnerReview = $isOwnerUnderReview;
                $cardInProgress = ! $isRejected && ! $cardOwnerReview && in_array($stForFilter, ['in_progress', 'under_review'], true);
                $cardResolved = ! $isRejected && $stForFilter === 'resolved';
                $cardPending = ! $isRejected && ! $cardOwnerReview && ! $cardResolved && ! $cardInProgress;
            @endphp
            <div class="incident-card dashboard-incident-card" data-card-owner-review="{{ $cardOwnerReview ? '1' : '0' }}" data-card-pending="{{ $cardPending ? '1' : '0' }}" data-card-in-progress="{{ $cardInProgress ? '1' : '0' }}" data-card-resolved="{{ $cardResolved ? '1' : '0' }}" style="width: 110px; min-width: 110px; height: 160px; border-radius: 9px; background: rgba(66, 106, 120, 0.16); outline: 0.7px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(1.5px); flex-shrink: 0; display: flex; flex-direction: column; overflow: hidden; animation-delay: {{ 0.1 + ($i + 1) * 0.15 }}s;">
                @if($reportPhotoOk)
                <div style="position: relative; width: 100%; height: 75px; min-width: 0; border-top-left-radius: 9px; border-top-right-radius: 9px; overflow: hidden;">
                    <img src="{{ Storage::url($report->photo_path) }}" alt="" style="width: 100%; height: 75px; min-width: 0; object-fit: cover; filter: {{ $isOwnerUnderReview ? 'brightness(0.42) blur(1.6px)' : ($isRejected ? 'brightness(0.55) grayscale(0.35)' : 'none') }};" />
                    @if($isOwnerUnderReview)
                    <div style="position: absolute; inset: 0; background: rgba(6, 10, 22, 0.38); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; padding: 6px;">
                        <img src="{{ asset('images/Vectors/dashboard_pending.svg') }}" alt="" style="width: 36px; height: 36px; object-fit: contain; transform: translateY(3px);" />
                        <span style="color: rgba(255,255,255,0.72); font-size: 7px; font-weight: 300; font-family: Poppins, sans-serif; text-align: center; line-height: 1.25; transform: translateY(6px);">Currently under review</span>
                    </div>
                    @elseif($isRejected)
                    <div style="position: absolute; inset: 0; background: rgba(6, 10, 22, 0.42); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 2px; padding: 4px;">
                        <span style="color: #9CA3AF; font-size: 7px; font-weight: 600; font-family: Poppins, sans-serif; text-align: center; line-height: 1.2;">Rejected</span>
                    </div>
                    @endif
                </div>
                @else
                <div style="width: 100%; height: 75px; background: {{ $isRejected ? 'rgba(75, 85, 99, 0.55)' : 'rgba(139, 105, 20, 0.5)' }}; border-top-left-radius: 9px; border-top-right-radius: 9px; display: flex; align-items: center; justify-content: center;">
                    @if($isRejected)
                        <span style="color: #9CA3AF; font-size: 7px; font-weight: 600; font-family: Poppins, sans-serif;">Rejected</span>
                    @endif
                </div>
                @endif
                <div style="padding: 6px 8px; flex: 1; display: flex; flex-direction: column;">
                    <span style="color: white; font-size: 9px; font-weight: 600; font-family: Poppins, sans-serif; line-height: 1.3; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $report->problem_type }}</span>
                    <span style="color: white; font-size: 9px; font-weight: 300; font-family: Poppins, sans-serif; line-height: 1.3; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ Str::limit($report->address ?? '—', 20) }}</span>
                    @if($isRejected)
                    <span style="color: #9CA3AF; font-size: 6px; font-weight: 500; font-family: Poppins, sans-serif; line-height: 1.25; margin-top: 2px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">{{ Str::limit('Rejected - '.$report->customerDeletionReasonShortLabel(), 72) }}</span>
                    @endif
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
                        @php
                            $st = $report->status ?? 'pending';
                            $glowClass = $isRejected ? '' : (($st === 'resolved') ? '' : (($st === 'in_progress' || $st === 'under_review') ? ' status-yellow' : ' status-red'));
                            $dotBg = $isRejected ? '#9CA3AF' : ($isOwnerUnderReview ? '#9CA3AF' : ($statusColors[$st] ?? '#ff0000'));
                            $dotStyleExtra = ($isRejected || $isOwnerUnderReview) ? ' animation: none;' : '';
                        @endphp
                        <div class="status-dot{{ $glowClass }}" style="width: 6px; height: 6px; border-radius: 9999px; background: {{ $dotBg }}; flex-shrink: 0;{{ $dotStyleExtra }}"></div>
                    </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- View Live Map section --}}
        <div style="margin-top: 20px;">
            <span style="color: white; font-size: 15px; font-weight: 700; font-family: Poppins, sans-serif; display: block; margin-bottom: 10px; margin-left: 8px;">View Live Map</span>
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
        {{-- Statistics (links to customer statistics) --}}
        <div style="margin-top: 20px;">
            <span style="display: block; color: white; font-size: 15px; font-weight: 700; font-family: Poppins, sans-serif; margin-bottom: 10px; margin-left: 8px;">{{ __('Statistics') }}</span>
            @if($guestMode)
            <a href="{{ route('login') }}" class="press-btn" style="position: relative; width: 100%; height: 15vh; min-height: 132px; border-radius: 9px; background: rgba(66, 106, 120, 0.16); outline: 0.7px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(1.5px); padding: 3px; box-sizing: border-box; display: block; text-decoration: none;">
                <div style="position: relative; width: 100%; height: 100%; min-height: 120px; border-radius: 7px; overflow: hidden; isolation: isolate;">
                    <svg style="position: absolute; inset: 0; width: 100%; height: 100%;" preserveAspectRatio="none">
                        <defs>
                            <pattern id="grid-guest" width="40" height="40" patternUnits="userSpaceOnUse">
                                <path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(255,255,255,0.07)" stroke-width="0.7"/>
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" fill="url(#grid-guest)" />
                    </svg>
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 3; pointer-events: none; display: flex; flex-direction: column; align-items: center; gap: 6px; max-width: 88%; text-align: center;">
                        <img src="{{ asset('images/Vectors/explore_lock.svg') }}" alt="" style="width: 50px; height: 45px; object-fit: contain; flex-shrink: 0;" />
                        <span style="color: rgba(255, 255, 255, 0.42); font-size: 9px; font-weight: 500; font-family: Poppins, sans-serif; line-height: 1.25;">{{ __('Log in to use this feature') }}</span>
                    </div>
                </div>
            </a>
            @else
            <a href="{{ route('customer.custatistics', ['name' => $user->profileSlug()]) }}" class="press-btn delayed-nav" style="position: relative; width: 100%; height: 15vh; min-height: 132px; border-radius: 9px; background: rgba(66, 106, 120, 0.16); outline: 0.7px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(1.5px); padding: 3px; box-sizing: border-box; display: block; text-decoration: none;">
                <div style="width: 100%; height: 100%; min-height: 120px; border-radius: 7px; outline: 0.7px solid rgba(255, 255, 255, 0.21); overflow: hidden; position: relative;">
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
            </a>
            @endif
        </div>
    </div>
    @push('scripts')
    <script>
        window.addEventListener('pageshow', function(e) {
            if (e.persisted) window.location.reload();
        });
    </script>
    <script>
        (function () {
            try {
                [
                    'rpicture',
                    'rproblem_photo',
                    'rproblem_choice',
                    'rproblem_address',
                    'rproblem_lat',
                    'rproblem_lng',
                    'rproblem_description'
                ].forEach(function (k) { sessionStorage.removeItem(k); });
            } catch (e) {}
        })();
    </script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @unless($guestMode ?? false)
    {{-- Request location permission as soon as dashboard loads so browser prompts user --}}
    <script>
        (function() {
            if (!navigator.geolocation) return;
            navigator.geolocation.getCurrentPosition(
                function() {},
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
        (function () {
            var filterBox = document.getElementById('dashboard-glass-filter-box');
            var filterBtn = document.getElementById('dashboard-glass-filter-btn');
            var filterOptions = document.getElementById('dashboard-glass-filter-options');
            var optionNodes = filterOptions
                ? Array.prototype.slice.call(filterOptions.querySelectorAll('.dashboard-glass-filter-option'))
                : [];
            var cards = document.querySelectorAll('.dashboard-incident-card');

            if (!filterBox || !filterBtn || !optionNodes.length) {
                return;
            }

            var selectedFilters = {
                owner_review: true,
                pending: true,
                in_progress: true,
                resolved: true
            };

            function applyDashboardFilter() {
                for (var i = 0; i < cards.length; i++) {
                    var card = cards[i];
                    var isOwnerReview = card.getAttribute('data-card-owner-review') === '1';
                    var isPending = card.getAttribute('data-card-pending') === '1';
                    var isInProgress = card.getAttribute('data-card-in-progress') === '1';
                    var isResolved = card.getAttribute('data-card-resolved') === '1';
                    var show = (selectedFilters.owner_review && isOwnerReview)
                        || (selectedFilters.pending && isPending)
                        || (selectedFilters.in_progress && isInProgress)
                        || (selectedFilters.resolved && isResolved);
                    if (show) {
                        card.removeAttribute('hidden');
                    } else {
                        card.setAttribute('hidden', 'hidden');
                    }
                }
            }

            function closeFilterBox() {
                if (!filterBox.classList.contains('is-open')) {
                    return;
                }
                filterBox.classList.add('is-closing');
                filterBox.classList.remove('is-open');
                filterBtn.setAttribute('aria-expanded', 'false');
            }

            filterBox.addEventListener('transitionend', function (ev) {
                if (ev.target !== filterBox) {
                    return;
                }
                if (ev.propertyName !== 'width' && ev.propertyName !== 'height') {
                    return;
                }
                filterBox.classList.remove('is-closing');
            });

            filterBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                if (filterBox.classList.contains('is-open')) {
                    closeFilterBox();
                } else if (!filterBox.classList.contains('is-closing')) {
                    filterBox.classList.add('is-open');
                    filterBtn.setAttribute('aria-expanded', 'true');
                }
            });

            filterBox.addEventListener('click', function (e) {
                e.stopPropagation();
            });

            optionNodes.forEach(function (optionEl) {
                optionEl.addEventListener('click', function (e) {
                    e.stopPropagation();
                    var key = optionEl.getAttribute('data-dashboard-filter');
                    if (!key || !(key in selectedFilters)) {
                        return;
                    }
                    selectedFilters[key] = !selectedFilters[key];
                    optionEl.classList.toggle('is-selected', selectedFilters[key]);
                    optionEl.setAttribute('aria-checked', selectedFilters[key] ? 'true' : 'false');
                    applyDashboardFilter();
                });
            });

            document.addEventListener('click', function () {
                closeFilterBox();
            });

            applyDashboardFilter();
        })();
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
        @unless($guestMode ?? false)
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
        @endunless
        @if(session('status') === 'report-submitted')
        try { ['rpicture','rproblem_photo','rproblem_choice','rproblem_address','rproblem_lat','rproblem_lng','rproblem_description'].forEach(function(k){ sessionStorage.removeItem(k); }); } catch(x){}
        alert('Submitted!');
        @endif
    </script>
    @endpush
</x-layouts::customer>
