<x-layouts::customer :title="__('History') . ' – BruDMS'" :bare="true">
    @push('styles')
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
        <style>
            .history-scroll { -ms-overflow-style: none; scrollbar-width: none; }
            .history-scroll::-webkit-scrollbar { display: none; }
            @keyframes dots-blink { 0%, 33% { opacity: 0.3; } 66%, 100% { opacity: 1; } }
            .in-progress-dots { animation: dots-blink 1.2s ease-in-out infinite; }
            .tab-btn { transition: outline-color 0.3s ease, transform 0.1s ease; }
            .tab-btn:active, .tab-btn-active:active { transform: scale(0.95); }
            .tab-btn-active { transition: transform 0.1s ease; }
            .press-btn { transition: transform 0.1s ease; }
            .press-btn:active { transform: scale(0.93) !important; }
            .tab-btn:hover { outline-color: #04BCFF !important; }
            .tab-btn { color: #9CA3AF; }
            .tab-btn-active { color: #040929; }
            .tab-btn:hover { color: #04BCFF; }
            .history-report-item[hidden] { display: none !important; }
            .history-detail-block {
                --history-detail-line: 12px;
                --history-detail-icon-col: 12px;
                display: grid;
                grid-template-columns: var(--history-detail-icon-col) 1fr;
                column-gap: 6px;
                row-gap: 3px;
                align-items: center;
            }
            .history-detail-icon {
                width: var(--history-detail-line);
                height: var(--history-detail-line);
                justify-self: center;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .history-detail-icon img {
                display: block;
                width: 12px;
                height: 12px;
                object-fit: contain;
                opacity: 0.6;
            }
            .history-detail-icon--sm img {
                width: 11px;
                height: 11px;
            }
            .history-detail-text {
                min-height: var(--history-detail-line);
                line-height: var(--history-detail-line);
                display: inline-flex;
                align-items: center;
                font-size: 9px;
                min-width: 0;
            }
            .history-detail-dates {
                display: flex;
                align-items: center;
                gap: 14px;
                min-width: 0;
                flex-wrap: nowrap;
                min-height: var(--history-detail-line);
            }
            .history-detail-date-complete {
                display: flex;
                align-items: center;
                gap: 6px;
                min-width: 0;
                flex-shrink: 1;
                margin-left: 2px;
            }
            .history-glass-filter-slot {
                position: relative;
                width: 32px;
                height: 32px;
                flex-shrink: 0;
            }
            .history-glass-filter-box {
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
            .history-glass-filter-box.is-open {
                width: 128px;
                height: 100px;
                border-radius: 9px;
            }
            .history-glass-filter-trigger { flex-shrink: 0; }
            .history-glass-filter-box.is-open .history-glass-filter-trigger,
            .history-glass-filter-box.is-closing .history-glass-filter-trigger {
                display: flex;
                justify-content: flex-end;
                height: 20px;
                padding: 0 3px 0;
                overflow: hidden;
                visibility: hidden;
                pointer-events: none;
            }
            .history-glass-filter-box.is-open .history-glass-filter-icon,
            .history-glass-filter-box.is-closing .history-glass-filter-icon {
                opacity: 0;
                visibility: hidden;
            }
            .history-glass-filter-box.is-closing .history-glass-filter-options {
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.12s ease;
            }
            .history-glass-filter-btn {
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
            .history-glass-filter-options {
                flex: 1;
                min-height: 0;
                overflow: hidden;
                opacity: 0;
                visibility: hidden;
                padding: 0 5px;
                transition: opacity 0.16s ease;
            }
            .history-glass-filter-box.is-open .history-glass-filter-options {
                flex: 0 0 auto;
                opacity: 1;
                visibility: visible;
                margin-top: 3px;
                padding: 2px 5px 6px;
                pointer-events: none;
                transition: opacity 0.2s ease 0.14s;
            }
            .history-glass-filter-option {
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
            .history-glass-filter-option + .history-glass-filter-option {
                margin-top: 3px;
            }
            .history-glass-filter-option + .history-glass-filter-option::before {
                content: '';
                position: absolute;
                top: -2px;
                left: 5px;
                right: 5px;
                border-top: 0.7px solid rgba(255, 255, 255, 0.18);
            }
            .history-glass-filter-option:hover,
            .history-glass-filter-option:focus,
            .history-glass-filter-option:active {
                background: transparent;
                outline: none;
            }
            .history-glass-filter-dot {
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
            .history-glass-filter-option.is-selected .history-glass-filter-dot::after {
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

    @php
        $user = auth()->user();
        $visibleReports = $visibleReports ?? collect();
    @endphp

    {{-- Header: logo + BruDMS + profile photo --}}
    <div style="position: fixed; top: 4vh; left: 20px; right: 20px; z-index: 10; display: flex; align-items: center; justify-content: space-between;">
        <a href="{{ route('customer.dashboard', ['name' => $user->profileSlug()]) }}" class="press-btn delayed-nav" style="display: flex; align-items: center; gap: 8px; text-decoration: none; cursor: pointer;" aria-label="{{ __('Home') }}">
            <img src="{{ asset('images/logo.png') }}" alt="" style="width: 36px; height: 36px; object-fit: contain;" />
            <span style="color: white; font-size: 24px; font-weight: 600; font-family: Poppins, sans-serif;">BruDMS</span>
        </a>
        <a href="{{ route('customer.general', ['name' => $user->profileSlug()]) }}" class="press-btn delayed-nav" style="cursor: pointer; display: flex; text-decoration: none;">
        @php $photoPath = $user->profile_photo_path ?? null; $photoUrl = $photoPath ? \Illuminate\Support\Facades\Storage::url($photoPath) : null; @endphp
        @if($photoUrl)
            <img src="{{ $photoUrl }}" alt="" style="width: 40px; height: 40px; border-radius: 9999px; object-fit: cover;" />
        @elseif(file_exists(public_path('images/default-avatar.png')))
            <img src="{{ asset('images/default-avatar.png') }}" alt="" style="width: 40px; height: 40px; border-radius: 9999px; object-fit: cover;" />
        @else
            <div style="width: 40px; height: 40px; border-radius: 9999px; background: #3f3f46; display: flex; align-items: center; justify-content: center; color: #e4e4e7; font-size: 14px; font-weight: 600;">{{ $user->initials() }}</div>
        @endif
        </a>
    </div>

    <div style="position: fixed; left: 6px; right: 6px; top: 11vh; bottom: -50vh; border-radius: 21px 21px 0 0; background: rgba(217, 217, 217, 0.07); backdrop-filter: blur(1.5px); -webkit-backdrop-filter: blur(1.5px); z-index: 1;"></div>

    {{-- Tab buttons --}}
    <div style="position: fixed; top: 13.5vh; left: 22px; right: 22px; z-index: 10; display: flex; justify-content: center; gap: 10px;">
        {{-- Home --}}
        <a href="{{ route('customer.dashboard', ['name' => $user->profileSlug()]) }}" class="tab-btn delayed-nav" style="flex: 1; height: 43px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: rgba(66, 106, 120, 0.16); outline: 1.7px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(1.5px); text-decoration: none;">
            <img src="{{ asset('images/Vectors/tab_home.svg') }}" alt="" style="width: 22px; height: 22px; object-fit: contain;" />
        </a>
        {{-- Chat --}}
        <a href="{{ route('customer.brudmsgpt', ['name' => $user->profileSlug()]) }}" class="tab-btn delayed-nav" style="flex: 1; height: 43px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: rgba(66, 106, 120, 0.16); outline: 1.7px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(1.5px); text-decoration: none;">
            <img src="{{ asset('images/Vectors/tab_chat.svg') }}" alt="" style="width: 22px; height: 22px; object-fit: contain;" />
        </a>
        {{-- History (active) --}}
        <a href="{{ route('customer.myhistory', ['name' => $user->profileSlug()]) }}" class="tab-btn-active" style="flex: 1; height: 43px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: #04BCFF; backdrop-filter: blur(1.5px); text-decoration: none;">
            <img src="{{ asset('images/Vectors/tab_myhistory-active.svg') }}" alt="" style="width: 22px; height: 22px; object-fit: contain;" />
        </a>
    </div>

    {{-- Content area --}}
    <div style="position: fixed; top: 22vh; left: 22px; right: 22px; bottom: 0; z-index: 10; display: flex; flex-direction: column;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px; gap: 10px; flex-shrink: 0;">
            <span style="color: white; font-size: 15px; font-weight: 700; font-family: Poppins, sans-serif; flex-shrink: 0; margin-left: 8px; line-height: 1;">My Reports History</span>
            <div class="history-glass-filter-slot">
                <div id="history-glass-filter-box" class="history-glass-filter-box" role="group" aria-label="{{ __('Filter reports') }}">
                    <div class="history-glass-filter-trigger">
                        <button id="history-glass-filter-btn" type="button" class="history-glass-filter-btn press-btn" aria-label="{{ __('Filter reports') }}" aria-expanded="false" aria-haspopup="menu">
                            <img class="history-glass-filter-icon" src="{{ asset('images/Vectors/livemap_filter.svg') }}" alt="" style="width: 12px; height: 12px; object-fit: contain;" />
                        </button>
                    </div>
                    <div id="history-glass-filter-options" class="history-glass-filter-options" role="menu">
                        <button type="button" class="history-glass-filter-option is-selected" data-history-filter="pending" role="menuitemcheckbox" aria-checked="true">
                            <span class="history-glass-filter-dot"></span>
                            <span>{{ __('Pending') }}</span>
                        </button>
                        <button type="button" class="history-glass-filter-option is-selected" data-history-filter="in_progress" role="menuitemcheckbox" aria-checked="true">
                            <span class="history-glass-filter-dot"></span>
                            <span>{{ __('In progress') }}</span>
                        </button>
                        <button type="button" class="history-glass-filter-option is-selected" data-history-filter="resolved" role="menuitemcheckbox" aria-checked="true">
                            <span class="history-glass-filter-dot"></span>
                            <span>{{ __('Resolved') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="history-scroll" style="margin-top: 8px; flex: 1; overflow-y: auto; min-height: 0;">
            @if($visibleReports->isNotEmpty())
                <div id="history-reports-list" style="display: flex; flex-direction: column; gap: 12px;">
                    @foreach($visibleReports as $report)
                        @php
                            $photoUrl = null;
                            if ($report->photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($report->photo_path)) {
                                $photoUrl = \Illuminate\Support\Facades\Storage::url($report->photo_path);
                            }
                            $st = $report->status ?? 'pending';
                            $statusText = $st === 'resolved'
                                ? 'Resolved'
                                : (in_array($st, ['in_progress', 'under_review'], true) ? 'In progress' : 'Active');
                            $statusColor = $st === 'resolved'
                                ? '#00ff73'
                                : (in_array($st, ['in_progress', 'under_review'], true) ? '#ffae00' : '#e00808');
                            $historyPending = ! in_array($st, ['resolved', 'in_progress', 'under_review'], true);
                            $historyInProgress = in_array($st, ['in_progress', 'under_review'], true);
                            $historyResolved = $st === 'resolved';
                        @endphp
                        <div class="history-report-item" data-history-pending="{{ $historyPending ? '1' : '0' }}" data-history-in-progress="{{ $historyInProgress ? '1' : '0' }}" data-history-resolved="{{ $historyResolved ? '1' : '0' }}" style="position: relative; width: 100%; min-height: 105px; border-radius: 9px; background: rgba(66, 106, 120, 0.16); border: 0.7px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(3px); -webkit-backdrop-filter: blur(3px); display: flex; flex-direction: row; align-items: center; padding: 5px; gap: 12px;">
                            @if($photoUrl)
                                <img src="{{ $photoUrl }}" alt="" style="width: 120px; height: 93px; object-fit: cover; border-radius: 5px; border: 0.7px solid rgba(255, 255, 255, 0.21); flex-shrink: 0;" />
                            @else
                                <div style="width: 120px; height: 93px; border-radius: 5px; border: 0.7px solid rgba(255, 255, 255, 0.21); flex-shrink: 0; background: rgba(55, 65, 81, 0.65); display: flex; align-items: center; justify-content: center;">
                                    <img src="{{ asset('images/Vectors/all_reportstatus.svg') }}" alt="" style="width: 28px; height: 28px; opacity: 0.35;" />
                                </div>
                            @endif
                            <span style="color: rgba(255, 255, 255, 0.4); font-family: Poppins, sans-serif; font-weight: 700; font-size: 14px; position: absolute; left: 134px; top: 8px; right: 10px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $report->problem_type ?? 'Report' }}</span>
                            <div class="history-detail-block" style="position: absolute; left: 138px; top: 33px; right: 10px;">
                                <span class="history-detail-icon" aria-hidden="true"><img src="{{ asset('images/Vectors/all_refcode.svg') }}" alt="" /></span>
                                <span class="history-detail-text" style="color: white; font-family: Poppins, sans-serif; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $report->reference_code ?? '—' }}</span>
                                <span class="history-detail-icon" aria-hidden="true"><img src="{{ asset('images/Vectors/all_reportstatus.svg') }}" alt="" /></span>
                                <span class="history-detail-text" style="color: {{ $statusColor }}; font-family: Poppins, sans-serif; font-weight: 600; white-space: nowrap;">{{ $statusText }}</span>
                                <span class="history-detail-icon history-detail-icon--sm" aria-hidden="true"><img src="{{ asset('images/Vectors/all_calempty.svg') }}" alt="" /></span>
                                <div class="history-detail-dates">
                                    <span class="history-detail-text" style="color: white; font-family: Poppins, sans-serif; font-weight: 400; white-space: nowrap;">{{ $report->created_at ? $report->created_at->format('jS M Y') : '—' }}</span>
                                    <div class="history-detail-date-complete">
                                        <span class="history-detail-icon history-detail-icon--sm" aria-hidden="true"><img src="{{ asset('images/Vectors/all_calcomplete.svg') }}" alt="" /></span>
                                        @if(($report->status ?? '') === 'resolved')
                                            <span class="history-detail-text" style="color: white; font-family: Poppins, sans-serif; font-weight: 400; white-space: nowrap;">{{ $report->updated_at ? $report->updated_at->format('jS M Y') : '—' }}</span>
                                        @else
                                            <span class="history-detail-text" style="color: rgba(255, 255, 255, 0.45); font-family: Poppins, sans-serif; font-weight: 400; white-space: nowrap;">In Progress<span class="in-progress-dots">...</span></span>
                                        @endif
                                    </div>
                                </div>
                                <span class="history-detail-icon" aria-hidden="true"><img src="{{ asset('images/Vectors/all_location.svg') }}" alt="" /></span>
                                <span class="history-detail-text" style="color: white; font-family: Poppins, sans-serif; font-weight: 400; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $report->address ?? '—' }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            <div id="history-empty-placeholder" @if($visibleReports->isNotEmpty()) hidden @endif style="width: 100%; height: 130px; border-radius: 9px; background: rgba(66, 106, 120, 0.16); border: 0.7px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(3px); -webkit-backdrop-filter: blur(3px); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 1px;">
                <img src="{{ asset('images/Vectors/myhistory_emptyhistory.svg') }}" alt="" style="width: 70px; height: 70px; object-fit: contain; opacity: 0.85; transform: translateY(-6px);" />
                <span style="color: #6B7280; font-size: 10px; font-family: Poppins, sans-serif; opacity: 0.9;">Looks like you haven't made any reports yet</span>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        (function () {
            var filterBox = document.getElementById('history-glass-filter-box');
            var filterBtn = document.getElementById('history-glass-filter-btn');
            var filterOptions = document.getElementById('history-glass-filter-options');
            var optionNodes = filterOptions
                ? Array.prototype.slice.call(filterOptions.querySelectorAll('.history-glass-filter-option'))
                : [];
            var items = document.querySelectorAll('.history-report-item');
            var listEl = document.getElementById('history-reports-list');
            var emptyEl = document.getElementById('history-empty-placeholder');

            if (!filterBox || !filterBtn || !optionNodes.length) {
                return;
            }

            var selectedFilters = {
                pending: true,
                in_progress: true,
                resolved: true
            };

            function applyHistoryFilter() {
                var visibleCount = 0;
                for (var i = 0; i < items.length; i++) {
                    var item = items[i];
                    var show = (selectedFilters.pending && item.getAttribute('data-history-pending') === '1')
                        || (selectedFilters.in_progress && item.getAttribute('data-history-in-progress') === '1')
                        || (selectedFilters.resolved && item.getAttribute('data-history-resolved') === '1');
                    if (show) {
                        item.removeAttribute('hidden');
                        visibleCount++;
                    } else {
                        item.setAttribute('hidden', 'hidden');
                    }
                }
                if (!emptyEl || !items.length) {
                    return;
                }
                if (visibleCount === 0) {
                    emptyEl.removeAttribute('hidden');
                    if (listEl) {
                        listEl.setAttribute('hidden', 'hidden');
                    }
                } else {
                    emptyEl.setAttribute('hidden', 'hidden');
                    if (listEl) {
                        listEl.removeAttribute('hidden');
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
                    var key = optionEl.getAttribute('data-history-filter');
                    if (!key || !(key in selectedFilters)) {
                        return;
                    }
                    selectedFilters[key] = !selectedFilters[key];
                    optionEl.classList.toggle('is-selected', selectedFilters[key]);
                    optionEl.setAttribute('aria-checked', selectedFilters[key] ? 'true' : 'false');
                    applyHistoryFilter();
                });
            });

            document.addEventListener('click', function () {
                closeFilterBox();
            });

            applyHistoryFilter();
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
                    setTimeout(function() { window.location.href = href; }, 100);
                }, 100);
            });
        });
    </script>
    @endpush
</x-layouts::customer>
