@props(['title' => 'BruDMS', 'bare' => false])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
    <style>
        :root {
            --customer-page-bg: #121820;
        }
        .customer-page-bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            background: var(--customer-page-bg);
        }
        .press-btn { transition: transform 0.1s ease; }
        .press-btn:active { transform: scale(0.93) !important; }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen bg-zinc-950 text-zinc-100 flex flex-col {{ $bare ? '' : 'pb-20 lg:pb-0' }}">
    @unless($bare)
    {{-- Top bar: left = BruDMS, right = Welcome back + profile photo (frosted) --}}
    <header class="sticky top-0 z-30 flex items-center justify-between gap-4 px-4 py-3 border-b border-zinc-700/50 bg-zinc-900/70" style="backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);">
        <a href="{{ route('customer.dashboard', ['name' => auth()->user()->profileSlug()]) }}" class="flex items-center gap-2 shrink-0" wire:navigate>
            <img src="{{ asset('images/logo.png') }}" alt="BruDMS" class="h-8 w-8 object-contain" />
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
        <a href="{{ route('customer.brudmsgpt', ['name' => auth()->user()->profileSlug()]) }}" class="customer-tab flex flex-col items-center gap-1 px-4 py-2 text-zinc-400 hover:text-zinc-100 rounded-lg transition-colors {{ request()->routeIs('customer.brudmsgpt') ? 'text-white' : '' }}" wire:navigate>
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            <span class="text-xs">{{ __('Chatbot') }}</span>
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
    @stack('scripts')
</body>
</html>
