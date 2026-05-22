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
        </style>
    @endpush

    @include('r_customer.partials.page-background')


    @php
        $user = auth()->user();
        $visibleReports = $visibleReports ?? collect();
    @endphp

    {{-- Header: logo + BruDMS + profile photo --}}
    <div style="position: fixed; top: 4vh; left: 20px; right: 20px; z-index: 10; display: flex; align-items: center; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <img src="{{ asset('images/logo.png') }}" alt="BruDMS" style="width: 36px; height: 36px; object-fit: contain;" />
            <span style="color: white; font-size: 24px; font-weight: 600; font-family: Poppins, sans-serif;">BruDMS</span>
        </div>
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
        <span style="color: white; font-size: 14px; font-weight: 700; font-family: Poppins, sans-serif; flex-shrink: 0; margin-left: 8px;">My Reports History</span>
        <div class="history-scroll" style="margin-top: 24px; flex: 1; overflow-y: auto; min-height: 0;">
            @if($visibleReports->isNotEmpty())
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    @foreach($visibleReports as $report)
                        @php
                            $photoUrl = null;
                            if ($report->photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($report->photo_path)) {
                                $photoUrl = \Illuminate\Support\Facades\Storage::url($report->photo_path);
                            }
                            $isRejected = $report->wasRejectedByAdmin();
                            if ($isRejected) {
                                $statusText = 'Rejected - '.$report->customerDeletionReasonShortLabel();
                                $statusColor = '#9CA3AF';
                            } else {
                                $st = $report->status ?? 'pending';
                                $statusText = $st === 'resolved'
                                    ? 'Resolved'
                                    : ($st === 'cancelled' ? 'Cancelled' : (in_array($st, ['in_progress', 'under_review'], true) ? 'In progress' : 'Active'));
                                $statusColor = $st === 'resolved'
                                    ? '#00ff73'
                                    : ($st === 'cancelled' ? '#9CA3AF' : (in_array($st, ['in_progress', 'under_review'], true) ? '#ffae00' : '#e00808'));
                            }
                        @endphp
                        <div style="position: relative; width: 100%; min-height: 105px; border-radius: 9px; background: rgba(66, 106, 120, 0.16); border: 0.7px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(3px); -webkit-backdrop-filter: blur(3px); display: flex; flex-direction: row; align-items: center; padding: 5px; gap: 12px;">
                            @if($photoUrl)
                                <img src="{{ $photoUrl }}" alt="" style="width: 120px; height: 93px; object-fit: cover; border-radius: 5px; border: 0.7px solid rgba(255, 255, 255, 0.21); flex-shrink: 0;" />
                            @else
                                <div style="width: 120px; height: 93px; border-radius: 5px; border: 0.7px solid rgba(255, 255, 255, 0.21); flex-shrink: 0; background: rgba(55, 65, 81, 0.65); display: flex; align-items: center; justify-content: center;">
                                    <img src="{{ asset('images/Vectors/all_reportstatus.svg') }}" alt="" style="width: 28px; height: 28px; opacity: 0.35;" />
                                </div>
                            @endif
                            <span style="color: rgba(255, 255, 255, 0.4); font-family: Poppins, sans-serif; font-weight: 700; font-size: 14px; position: absolute; left: 137px; top: 8px; right: 10px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $report->problem_type ?? 'Report' }}</span>
                            <div style="position: absolute; left: 137px; top: 31px; right: 10px; display: flex; flex-direction: column; align-items: flex-start; gap: 1px;">
                                <div style="display: flex; align-items: flex-start; gap: 6px; min-width: 0; width: 100%;">
                                    <img src="{{ asset('images/Vectors/all_reportstatus.svg') }}" alt="" style="width: 12px; height: 12px; object-fit: contain; opacity: 0.6; flex-shrink: 0; margin-top: 2px;" />
                                    <span style="color: {{ $statusColor }}; font-family: Poppins, sans-serif; font-weight: 600; font-size: 10px; line-height: 1.35; white-space: normal;">{{ $statusText }}</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <img src="{{ asset('images/Vectors/all_calempty.svg') }}" alt="" style="width: 12px; height: 12px; object-fit: contain; opacity: 0.6;" />
                                    <span style="color: white; font-family: Poppins, sans-serif; font-weight: 400; font-size: 10px;">{{ $report->created_at ? $report->created_at->format('jS M Y') : '—' }}</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <img src="{{ asset('images/Vectors/all_calcomplete.svg') }}" alt="" style="width: 12px; height: 12px; object-fit: contain; opacity: 0.6;" />
                                    @if($isRejected)
                                        <span style="color: rgba(255, 255, 255, 0.55); font-family: Poppins, sans-serif; font-weight: 400; font-size: 10px;">N/A</span>
                                    @elseif(!$isRejected && ($report->status ?? '') === 'resolved')
                                        <span style="color: white; font-family: Poppins, sans-serif; font-weight: 400; font-size: 10px;">{{ $report->updated_at ? $report->updated_at->format('jS M Y') : '—' }}</span>
                                    @elseif(!$isRejected && ($report->status ?? '') === 'cancelled')
                                        <span style="color: rgba(255, 255, 255, 0.55); font-family: Poppins, sans-serif; font-weight: 400; font-size: 10px;">Cancelled</span>
                                    @else
                                        <span style="color: rgba(255, 255, 255, 0.45); font-family: Poppins, sans-serif; font-weight: 400; font-size: 10px;">In Progress<span class="in-progress-dots">...</span></span>
                                    @endif
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px; min-width: 0; max-width: 100%;">
                                    <img src="{{ asset('images/Vectors/all_location.svg') }}" alt="" style="width: 12px; height: 12px; object-fit: contain; opacity: 0.6; flex-shrink: 0;" />
                                    <span style="color: white; font-family: Poppins, sans-serif; font-weight: 400; font-size: 10px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ Str::limit($report->address ?? '—', 27) }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="width: 100%; height: 130px; border-radius: 9px; background: rgba(66, 106, 120, 0.16); border: 0.7px solid rgba(255, 255, 255, 0.21); backdrop-filter: blur(3px); -webkit-backdrop-filter: blur(3px); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 1px;">
                    <img src="{{ asset('images/Vectors/myhistory_emptyhistory.svg') }}" alt="" style="width: 70px; height: 70px; object-fit: contain; opacity: 0.85; transform: translateY(-6px);" />
                    <span style="color: #6B7280; font-size: 10px; font-family: Poppins, sans-serif; opacity: 0.9;">Looks like you haven't made any reports yet</span>
                </div>
            @endif
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
    </script>
    @endpush
</x-layouts::customer>
