@props(['title' => 'BruFlow'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
    @stack('styles')
</head>
<body class="min-h-screen bg-zinc-950 text-zinc-100 flex flex-col pb-20 lg:pb-0">
    {{-- Top bar: left = BruFlow, right = Welcome back, {name} (frosted) --}}
    <header class="sticky top-0 z-30 flex items-center justify-between gap-4 px-4 py-3 border-b border-zinc-700/50 bg-zinc-900/70" style="backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);">
        <a href="{{ route('customer.dashboard') }}" class="flex items-center gap-2 shrink-0" wire:navigate>
            <img src="{{ asset('images/logo.png') }}" alt="BruFlow" class="h-8 w-8 object-contain" />
            <span class="font-semibold text-zinc-100">BruFlow</span>
        </a>
        <span class="text-sm text-zinc-400 truncate">{{ __('Welcome back, :name', ['name' => auth()->user()->name ?? '']) }}</span>
    </header>

    {{-- Main content: extra bottom padding so fixed tab bar doesn't cover content when scrolling --}}
    <main class="flex-1 overflow-auto pb-24 lg:pb-0">
        {{ $slot }}
    </main>

    {{-- Bottom tab bar: Home | Map | + Report | Chatbot | Profile (frosted glass – inline blur so it’s always visible) --}}
    <nav class="fixed bottom-0 left-0 right-0 z-40 flex items-center justify-around py-2 safe-area-pb lg:relative lg:border-0 lg:py-0 lg:mt-auto border-t border-zinc-700/50 bg-zinc-900/70 lg:bg-zinc-900/80" style="backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);" aria-label="{{ __('Main navigation') }}">
        <a href="{{ route('customer.dashboard') }}" class="customer-tab flex flex-col items-center gap-1 px-4 py-2 text-zinc-400 hover:text-zinc-100 rounded-lg transition-colors {{ request()->routeIs('customer.dashboard') ? 'text-white' : '' }}" wire:navigate>
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span class="text-xs">{{ __('Home') }}</span>
        </a>
        <a href="{{ route('customer.map') }}" class="customer-tab flex flex-col items-center gap-1 px-4 py-2 text-zinc-400 hover:text-zinc-100 rounded-lg transition-colors {{ request()->routeIs('customer.map') ? 'text-white' : '' }}" wire:navigate>
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
            <span class="text-xs">{{ __('Map') }}</span>
        </a>
        <a href="{{ route('customer.reports.create') }}" class="customer-tab flex flex-col items-center gap-1 px-5 py-3 -mt-4 rounded-full bg-blue-500 text-white hover:bg-blue-400 shadow-lg transition-transform hover:scale-105" wire:navigate aria-label="{{ __('Report new incident') }}">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            <span class="text-xs font-medium">{{ __('Report') }}</span>
        </a>
        <a href="{{ route('customer.chatbot') }}" class="customer-tab flex flex-col items-center gap-1 px-4 py-2 text-zinc-400 hover:text-zinc-100 rounded-lg transition-colors {{ request()->routeIs('customer.chatbot') ? 'text-white' : '' }}" wire:navigate>
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            <span class="text-xs">{{ __('Chatbot') }}</span>
        </a>
        <a href="{{ route('customer.profile') }}" class="customer-tab flex flex-col items-center gap-1 px-4 py-2 text-zinc-400 hover:text-zinc-100 rounded-lg transition-colors {{ request()->routeIs('customer.profile') ? 'text-white' : '' }}" wire:navigate>
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span class="text-xs">{{ __('Profile') }}</span>
        </a>
    </nav>

    @fluxScripts
    @stack('scripts')
</body>
</html>
