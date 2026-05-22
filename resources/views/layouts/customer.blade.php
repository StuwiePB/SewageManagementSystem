@props(['title' => 'BruDMS', 'bare' => false])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
    <style>
        .press-btn { transition: transform 0.1s ease; }
        .press-btn:active { transform: scale(0.93) !important; }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen bg-zinc-950 text-zinc-100 flex flex-col {{ $bare ? '' : 'pb-20 lg:pb-0' }}">
    @unless($bare)
    {{-- Top bar: left = BruDMS, right = Welcome back + profile photo (frosted) --}}
    <header class="sticky top-0 z-30 flex items-center justify-between gap-4 px-4 py-3 border-b border-zinc-700/50 bg-zinc-900/70" style="backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);">
        <a href="{{ route('customer.dashboard', ['name' => auth()->user()->profileSlug()]) }}" class="press-btn flex items-center gap-2 shrink-0" wire:navigate aria-label="{{ __('Home') }}">
            <img src="{{ asset('images/logo.png') }}" alt="" class="h-8 w-8 object-contain" />
            <span class="font-semibold text-zinc-100">BruDMS</span>
        </a>
        @php
            $user = auth()->user();
            $photoPath = $user->profile_photo_path ?? null;
            $photoUrl = $photoPath ? \Illuminate\Support\Facades\Storage::url($photoPath) : null;
        @endphp
        <div class="flex items-center gap-2 shrink-0">
            <span class="text-sm text-zinc-400">{{ __('Welcome back') }}</span>
            <a href="{{ route('customer.general', ['name' => auth()->user()->profileSlug()]) }}" class="press-btn cursor-pointer" wire:navigate>
            @if($photoUrl)
                <img src="{{ $photoUrl }}" alt="" class="w-8 h-8 rounded-full object-cover ring-2 ring-zinc-600/50" />
            @elseif(file_exists(public_path('images/default-avatar.png')))
                <img src="{{ asset('images/default-avatar.png') }}" alt="" class="w-8 h-8 rounded-full object-cover ring-2 ring-zinc-600/50" />
            @else
                <div class="w-8 h-8 rounded-full bg-zinc-600 flex items-center justify-center text-xs font-semibold text-zinc-200 ring-2 ring-zinc-600/50" title="{{ $user->name ?? '' }}">{{ $user ? $user->initials() : '?' }}</div>
            @endif
            </a>
        </div>
    </header>
    @endunless

    {{-- Main content: extra bottom padding so fixed tab bar doesn't cover content when scrolling --}}
    <main class="flex-1 overflow-auto {{ $bare ? '' : 'pb-24 lg:pb-0' }}">
        {{ $slot }}
    </main>

    @unless($bare)
    {{-- Bottom tab bar: Home | Map | + Report | Chatbot | Profile (frosted glass – inline blur so it’s always visible) --}}
    <nav class="fixed bottom-0 left-0 right-0 z-40 flex items-center justify-around py-2 safe-area-pb lg:relative lg:border-0 lg:py-0 lg:mt-auto border-t border-zinc-700/50 bg-zinc-900/70 lg:bg-zinc-900/80" style="backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);" aria-label="{{ __('Main navigation') }}">
        <a href="{{ route('customer.dashboard', ['name' => auth()->user()->profileSlug()]) }}" class="customer-tab flex flex-col items-center gap-1 px-4 py-2 text-zinc-400 hover:text-zinc-100 rounded-lg transition-colors {{ request()->routeIs('customer.dashboard') ? 'text-white' : '' }}" wire:navigate>
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span class="text-xs">{{ __('Home') }}</span>
        </a>
    </nav>
    @endunless

    @fluxScripts
    <script>
        (function () {
            function clearPersistedAiChats() {
                try {
                    for (var i = localStorage.length - 1; i >= 0; i--) {
                        var key = localStorage.key(i);
                        if (!key) continue;
                        if (key.indexOf('brudms_ai_chat_') === 0) {
                            localStorage.removeItem(key);
                        }
                    }
                } catch (e) {}
            }

            document.addEventListener('submit', function (e) {
                var form = e.target;
                if (!form || form.tagName !== 'FORM') return;
                var action = (form.getAttribute('action') || '').toLowerCase();
                if (!action) return;
                if (action.indexOf('/logout') !== -1) {
                    clearPersistedAiChats();
                }
            }, true);
        })();
    </script>
    @if($bare && auth()->check() && ! request()->routeIs('customer.brudmsgpt'))
    @php($ziqahUser = auth()->user())
    <style>
        #ziqah-fab { position: fixed; z-index: 10050; width: 44px; height: 44px; border-radius: 50%; border: 0.7px solid rgba(255,255,255,0.18); background: rgba(97,107,110,0.15); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); box-shadow: none; cursor: pointer; touch-action: none; user-select: none; -webkit-user-select: none; display: flex; align-items: center; justify-content: center; overflow: hidden; right: 12px; bottom: 96px; transition: left 0.28s cubic-bezier(0.22,1,0.36,1), top 0.2s ease, background 0.2s ease; }
        #ziqah-fab:hover { background: rgba(97,107,110,0.24); }
        #ziqah-fab.is-dragging { cursor: grabbing; transition: none; background: rgba(97,107,110,0.22); }
        #ziqah-fab .ziqah-fab-mark { color: rgba(255,255,255,0.92); font-size: 17px; font-weight: 600; font-family: Arial, Helvetica, sans-serif; line-height: 1; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; margin: 0; padding: 0; pointer-events: none; user-select: none; }
        #ziqah-overlay { position: fixed; inset: 0; z-index: 10045; visibility: hidden; pointer-events: none; }
        #ziqah-overlay.is-open { visibility: visible; pointer-events: auto; }
        #ziqah-overlay-bg { position: absolute; inset: 0; background: rgba(0,0,0,0); transition: background 0.32s ease; }
        #ziqah-overlay.is-open #ziqah-overlay-bg { background: rgba(0,0,0,0.45); }
        #ziqah-sheet { position: fixed; left: 0; right: 0; bottom: 0; z-index: 10055; height: var(--ziqah-sheet-h, 96vh); max-height: 96vh; min-height: 280px; display: flex; flex-direction: column; border-radius: 22px 22px 0 0; border: 0.7px solid rgba(255,255,255,0.18); border-bottom: none; background: linear-gradient(155deg, rgba(32,120,130,0.42) 0%, rgba(10,16,28,0.82) 45%, rgba(6,10,18,0.88) 100%); backdrop-filter: blur(18px) saturate(1.15); -webkit-backdrop-filter: blur(18px) saturate(1.15); box-shadow: 0 -8px 40px rgba(0,0,0,0.35); transform: translateY(100%); transition: transform 0.38s cubic-bezier(0.22,1,0.36,1); pointer-events: none; overflow: hidden; box-sizing: border-box; padding: 0 20px 16px; }
        #ziqah-sheet.is-open { transform: translateY(0); pointer-events: auto; }
        #ziqah-sheet.is-open:not(.is-resizing) { transition: transform 0.38s cubic-bezier(0.22,1,0.36,1), height 0.34s cubic-bezier(0.22,1,0.36,1); }
        #ziqah-sheet.is-resizing { transition: none !important; }
        #ziqah-sheet-top { position: relative; flex-shrink: 0; padding: 12px 0 10px; display: flex; align-items: center; justify-content: center; cursor: grab; touch-action: none; user-select: none; -webkit-user-select: none; }
        #ziqah-sheet-top.is-dragging { cursor: grabbing; }
        #ziqah-sheet-grabber { width: 40px; height: 5px; border-radius: 9999px; background: rgba(255,255,255,0.28); pointer-events: none; }
        #ziqah-sheet-close { position: absolute; right: 0; top: 6px; width: 36px; height: 36px; border: none; background: transparent; color: rgba(255,255,255,0.85); font-size: 22px; line-height: 1; cursor: pointer; display: flex; align-items: center; justify-content: center; padding: 0; }
        #ziqah-sheet-hero { flex-shrink: 0; text-align: center; padding: 8px 8px 16px; }
        #ziqah-sheet-hero h2 { margin: 0; font-family: Poppins, sans-serif; font-size: clamp(22px, 5.5vw, 28px); font-weight: 700; color: #fff; line-height: 1.25; letter-spacing: -0.02em; }
        #ziqah-sheet-hero h2 .ziqah-grad { background: linear-gradient(90deg, #04BCFF 0%, #7b5cff 100%); -webkit-background-clip: text; background-clip: text; color: transparent; }
        #ziqah-sheet-hero p { margin: 10px 0 0; font-family: Poppins, sans-serif; font-size: 13px; font-weight: 400; color: rgba(255,255,255,0.55); }
        #ziqah-chat-messages { flex: 1; min-height: 0; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; padding: 4px 2px 12px; scrollbar-width: none; }
        #ziqah-chat-messages::-webkit-scrollbar { display: none; }
        #ziqah-chat-messages .msg-user { align-self: flex-end; background: #04BCFF; color: #040929; border-radius: 16px 16px 4px 16px; padding: 10px 14px; max-width: 85%; font-size: 11px; font-family: Poppins, sans-serif; }
        #ziqah-chat-messages .msg-bot { align-self: flex-start; background: rgba(255,255,255,0.08); color: #fff; border-radius: 16px 16px 16px 4px; padding: 10px 14px; max-width: 85%; font-size: 11px; font-family: Poppins, sans-serif; font-weight: 300; }
        #ziqah-chat-messages .msg-typing { align-self: flex-start; color: rgba(255,255,255,0.4); font-size: 11px; font-family: Poppins, sans-serif; }
        #ziqah-chat-messages .msg-img { max-width: 200px; border-radius: 12px; display: block; }
        #ziqah-chat-messages .msg-action-btn { display: inline-flex; width: 100%; margin-top: 10px; height: 34px; border-radius: 10px; background: #04BCFF; color: #0a1628; text-decoration: none; font-size: 11px; font-family: Poppins, sans-serif; font-weight: 700; align-items: center; justify-content: center; box-sizing: border-box; }
        #ziqah-sheet-footer { flex-shrink: 0; padding-top: 8px; }
        #ziqah-sheet-input-wrap { display: flex; align-items: center; gap: 10px; height: 48px; border-radius: 14px; border: 0.7px solid rgba(255,255,255,0.22); background: rgba(0,0,0,0.32); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); padding: 0 14px 0 4px; box-sizing: border-box; }
        #ziqah-chat-attach { width: 40px; height: 40px; border: none; background: transparent; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; padding: 0; }
        #ziqah-chat-input { flex: 1; height: 44px; border: none; outline: none; background: transparent; color: #fff; font-size: 14px; font-family: Poppins, sans-serif; min-width: 0; }
        #ziqah-chat-input::placeholder { color: rgba(255,255,255,0.38); }
        #ziqah-chat-send { width: 36px; height: 36px; border: none; border-radius: 9999px; background: transparent; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; padding: 0; color: rgba(255,255,255,0.75); }
        #ziqah-sheet-disclaimer { margin: 10px 0 0; text-align: center; font-family: Poppins, sans-serif; font-size: 10px; color: rgba(255,255,255,0.38); line-height: 1.35; }
        #ziqah-chat-preview { display: none; align-items: flex-start; gap: 8px; margin-bottom: 8px; }
    </style>
    <div id="ziqah-overlay" aria-hidden="true">
        <div id="ziqah-overlay-bg"></div>
    </div>
    <div id="ziqah-sheet" role="dialog" aria-modal="true" aria-labelledby="ziqah-sheet-title" aria-hidden="true">
        <div id="ziqah-sheet-top">
            <div id="ziqah-sheet-grabber" title="Drag up or down to resize"></div>
            <button type="button" id="ziqah-sheet-close" aria-label="Close">&times;</button>
        </div>
        <div id="ziqah-sheet-hero">
            <h2 id="ziqah-sheet-title">Get <span class="ziqah-grad">help</span> with BruDMS</h2>
            <p>Fast answers. Powered by AI.</p>
        </div>
        <div id="ziqah-chat-messages"><div class="msg-bot">Ziqah handles the flow. What's clogged, leaking, or overflowing? Show me.</div></div>
        <div id="ziqah-sheet-footer">
            <input id="ziqah-chat-file" type="file" accept="image/*" style="display:none;" />
            <div id="ziqah-chat-preview">
                <img id="ziqah-chat-preview-img" alt="" style="max-width:56px;max-height:56px;border-radius:8px;object-fit:cover;" />
                <button type="button" id="ziqah-chat-preview-remove" style="width:20px;height:20px;border-radius:9999px;border:none;background:rgba(255,255,255,0.2);color:#fff;cursor:pointer;">&times;</button>
            </div>
            <div id="ziqah-sheet-input-wrap">
                <button type="button" id="ziqah-chat-attach" aria-label="Attach image">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </button>
                <input id="ziqah-chat-input" type="text" placeholder="How else can I help?" autocomplete="off" />
                <button type="button" id="ziqah-chat-send" aria-label="Send message">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/></svg>
                </button>
            </div>
            <p id="ziqah-sheet-disclaimer">AI can make mistakes. Double-check for accuracy.</p>
        </div>
    </div>
    <button type="button" id="ziqah-fab" aria-label="Open Ziqah help" title="Ziqah (AI)"><span class="ziqah-fab-mark" aria-hidden="true">?</span></button>
    <script>
        window.BrudmsZiqahConfig = {
            userId: {{ (int) $ziqahUser->id }},
            chatUrl: @json(route('customer.chat')),
            reportUrl: @json(route('customer.rproblem', ['name' => $ziqahUser->profileSlug()])),
            csrf: @json(csrf_token()),
        };
    </script>
    <script src="{{ asset('js/ziqah-widget.js') }}?v=29" defer></script>
    @endif

    @stack('scripts')
</body>
</html>
