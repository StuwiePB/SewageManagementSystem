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

    @if(
        auth()->check()
        && auth()->user()->isCustomer()
        && auth()->user()->needsEmailBinding()
        && ! session('email_bind_prompt_dismissed')
        && ! request()->routeIs('customer.profilesettings', 'customer.email.bind.otp')
    )
        @include('partials.customer-email-bind-popup')
    @endif

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
        .cr {
            --cr-accent: #04BCFF;
            --cr-accent-2: #7b5cff;
            --cr-bg: linear-gradient(155deg, rgba(32,120,130,0.42) 0%, rgba(10,16,28,0.92) 45%, rgba(6,10,18,0.96) 100%);
            --cr-panel-border: rgba(255,255,255,0.16);
            --cr-text: #ffffff;
            --cr-text-muted: rgba(255,255,255,0.55);
            --cr-radius: 18px;
            --cr-font: Poppins, sans-serif;
            position: fixed;
            right: 16px;
            bottom: 20px;
            z-index: 10050;
        }
        .cr-launcher {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            height: 48px;
            padding: 0 18px;
            border-radius: 9999px;
            border: 0.7px solid var(--cr-panel-border);
            background: rgba(97,107,110,0.22);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            color: var(--cr-text);
            font-family: var(--cr-font);
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.3px;
            cursor: pointer;
            box-shadow: 0 6px 24px rgba(0,0,0,0.25);
            transition: background 0.2s ease, transform 0.2s ease;
        }
        .cr-launcher:hover { background: rgba(97,107,110,0.32); }
        .cr-launcher:active { transform: scale(0.96); }
        .cr.is-open .cr-launcher { display: none; }
        .cr-launcher-dot { width: 8px; height: 8px; border-radius: 50%; background: #37e08c; box-shadow: 0 0 0 0 rgba(55,224,140,0.6); animation: cr-pulse 2s infinite; flex-shrink: 0; }
        .cr-panel {
            display: none;
            flex-direction: column;
            position: absolute;
            right: 0;
            bottom: 0;
            width: min(340px, calc(100vw - 40px));
            height: min(460px, calc(100vh - 120px));
            border-radius: var(--cr-radius);
            border: 0.7px solid var(--cr-panel-border);
            background: var(--cr-bg);
            backdrop-filter: blur(18px) saturate(1.15);
            -webkit-backdrop-filter: blur(18px) saturate(1.15);
            box-shadow: 0 12px 48px rgba(0,0,0,0.4);
            overflow: hidden;
            box-sizing: border-box;
            opacity: 0;
            transform: translateY(12px) scale(0.98);
            transition: opacity 0.2s ease, transform 0.2s ease;
        }
        .cr.is-open .cr-panel { display: flex; }
        .cr.is-open .cr-panel.is-visible { opacity: 1; transform: translateY(0) scale(1); }
        @media (prefers-reduced-motion: reduce) {
            .cr-panel { transition: opacity 0.01ms; transform: none; }
            .cr-launcher-dot { animation: none; }
        }
        .cr-header { flex-shrink: 0; display: flex; align-items: center; gap: 8px; padding: 14px 8px 14px 16px; border-bottom: 0.7px solid var(--cr-panel-border); }
        .cr-status-dot { width: 8px; height: 8px; border-radius: 50%; background: #37e08c; box-shadow: 0 0 0 0 rgba(55,224,140,0.6); animation: cr-pulse 2s infinite; flex-shrink: 0; }
        .cr-title { flex: 1; font-family: var(--cr-font); font-size: 14px; font-weight: 700; color: var(--cr-text); }
        .cr-close { width: 32px; height: 32px; border: none; background: transparent; color: rgba(255,255,255,0.85); font-size: 20px; line-height: 1; cursor: pointer; display: flex; align-items: center; justify-content: center; padding: 0; border-radius: 8px; }
        .cr-close:hover { background: rgba(255,255,255,0.08); }
        .cr-log { flex: 1; min-height: 0; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; padding: 12px; scrollbar-width: none; }
        .cr-log::-webkit-scrollbar { display: none; }
        .cr-log .msg-user { align-self: flex-end; background: var(--cr-accent); color: #040929; border-radius: 14px 14px 4px 14px; padding: 9px 12px; max-width: 85%; font-size: 12px; font-family: var(--cr-font); }
        .cr-log .msg-bot { align-self: flex-start; background: rgba(255,255,255,0.08); color: var(--cr-text); border-radius: 14px 14px 14px 4px; padding: 9px 12px; max-width: 85%; font-size: 12px; font-family: var(--cr-font); font-weight: 300; }
        .cr-log .msg-bot .cr-msg-p { margin: 0 0 8px; }
        .cr-log .msg-bot .cr-msg-p:last-child { margin-bottom: 0; }
        .cr-log .msg-bot .cr-msg-list { margin: 0 0 8px; padding-left: 18px; display: flex; flex-direction: column; gap: 4px; }
        .cr-log .msg-bot .cr-msg-list:last-child { margin-bottom: 0; }
        .cr-log .msg-bot .cr-msg-list li { padding-left: 2px; }
        .cr-log .msg-error { align-self: flex-start; background: rgba(255,80,80,0.16); border: 0.7px solid rgba(255,110,110,0.4); color: #ffd7d7; border-radius: 14px 14px 14px 4px; padding: 9px 12px; max-width: 85%; font-size: 12px; font-family: var(--cr-font); }
        .cr-log .msg-img { max-width: 180px; border-radius: 10px; display: block; }
        .cr-log .msg-action-btn { display: inline-flex; width: 100%; margin-top: 8px; height: 32px; border-radius: 8px; background: var(--cr-accent); color: #0a1628; text-decoration: none; font-size: 11px; font-family: var(--cr-font); font-weight: 700; align-items: center; justify-content: center; box-sizing: border-box; }
        .cr-typing { align-self: flex-start; display: flex; align-items: center; gap: 4px; padding: 9px 12px; }
        .cr-typing span { width: 6px; height: 6px; border-radius: 50%; background: var(--cr-text-muted); animation: cr-typing-bounce 1.2s infinite ease-in-out; }
        .cr-typing span:nth-child(2) { animation-delay: 0.15s; }
        .cr-typing span:nth-child(3) { animation-delay: 0.3s; }
        @media (prefers-reduced-motion: reduce) { .cr-typing span { animation: none; opacity: 0.6; } }
        @keyframes cr-typing-bounce { 0%, 60%, 100% { transform: translateY(0); opacity: 0.5; } 30% { transform: translateY(-4px); opacity: 1; } }
        @keyframes cr-pulse { 0% { box-shadow: 0 0 0 0 rgba(55,224,140,0.5); } 70% { box-shadow: 0 0 0 6px rgba(55,224,140,0); } 100% { box-shadow: 0 0 0 0 rgba(55,224,140,0); } }
        .cr-preview { display: none; align-items: flex-start; gap: 8px; padding: 0 12px 8px; }
        .cr-footer { flex-shrink: 0; padding: 8px 12px 10px; border-top: 0.7px solid var(--cr-panel-border); }
        .cr-input-wrap { display: flex; align-items: flex-end; gap: 8px; border-radius: 14px; border: 0.7px solid var(--cr-panel-border); background: rgba(0,0,0,0.32); padding: 6px 10px 6px 4px; box-sizing: border-box; }
        .cr-attach { width: 36px; height: 36px; border: none; background: transparent; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; padding: 0; }
        .cr-input { flex: 1; resize: none; border: none; outline: none; background: transparent; color: var(--cr-text); font-size: 13px; font-family: var(--cr-font); min-width: 0; max-height: 96px; line-height: 1.4; padding: 8px 0; }
        .cr-input::placeholder { color: var(--cr-text-muted); }
        .cr-send { width: 32px; height: 32px; border: none; border-radius: 9999px; background: transparent; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; padding: 0; color: rgba(255,255,255,0.75); }
        .cr-send:disabled, .cr-attach:disabled { opacity: 0.4; cursor: not-allowed; }
        .cr-disclaimer { margin: 8px 0 0; text-align: center; font-family: var(--cr-font); font-size: 10px; color: var(--cr-text-muted); line-height: 1.35; }
        @media (max-width: 420px) {
            .cr { right: 12px; bottom: 16px; }
            .cr-panel { width: calc(100vw - 40px); height: min(460px, calc(100vh - 140px)); }
        }
    </style>
    <div class="cr" id="ziqah-cr">
        <button type="button" class="cr-launcher" id="ziqah-fab" aria-label="Open Ziqah help" title="Ziqah (AI)">
            <span class="cr-launcher-dot" aria-hidden="true"></span>
            AI
        </button>
        <div class="cr-panel" id="ziqah-panel" role="dialog" aria-modal="true" aria-labelledby="ziqah-panel-title" aria-hidden="true">
            <div class="cr-header">
                <span class="cr-status-dot" aria-hidden="true"></span>
                <span class="cr-title" id="ziqah-panel-title">Ziqah</span>
                <button type="button" class="cr-close" id="ziqah-sheet-close" aria-label="Close">&times;</button>
            </div>
            <div class="cr-log" id="ziqah-chat-messages"><div class="msg-bot">Ziqah handles the flow. What's clogged, leaking, or overflowing? Show me.</div></div>
            <div class="cr-preview" id="ziqah-chat-preview">
                <img id="ziqah-chat-preview-img" alt="" style="max-width:56px;max-height:56px;border-radius:8px;object-fit:cover;" />
                <button type="button" id="ziqah-chat-preview-remove" style="width:20px;height:20px;border-radius:9999px;border:none;background:rgba(255,255,255,0.2);color:#fff;cursor:pointer;">&times;</button>
            </div>
            <div class="cr-footer">
                <input id="ziqah-chat-file" type="file" accept="image/*" style="display:none;" />
                <div class="cr-input-wrap">
                    <button type="button" class="cr-attach" id="ziqah-chat-attach" aria-label="Attach image">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    </button>
                    <textarea id="ziqah-chat-input" class="cr-input" rows="1" placeholder="How else can I help?"></textarea>
                    <button type="button" class="cr-send" id="ziqah-chat-send" aria-label="Send message">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/></svg>
                    </button>
                </div>
                <p class="cr-disclaimer">AI can make mistakes. Double-check for accuracy.</p>
            </div>
        </div>
    </div>
    <script>
        window.BrudmsZiqahConfig = {
            userId: {{ (int) $ziqahUser->id }},
            chatUrl: @json(route('customer.chat')),
            reportUrl: @json(route('customer.rproblem', ['name' => $ziqahUser->profileSlug()])),
            csrf: @json(csrf_token()),
            currentPage: @json(request()->route()?->getName()),
        };
    </script>
    <script src="{{ asset('js/ziqah-widget.js') }}?v=32" defer></script>
    @endif

    @stack('scripts')
</body>
</html>
