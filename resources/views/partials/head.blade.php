<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="{{ csrf_token() }}" />

<title>{{ $title ?? 'BruDMS' }}</title>

@php
    $headThemeDefault = auth()->check()
        ? (auth()->user()->preference_appearance ?? 'light')
        : 'light';
    $headThemeDefault = in_array($headThemeDefault, ['light', 'dark'], true) ? $headThemeDefault : 'light';
@endphp
<meta name="brudms-theme-default" content="{{ $headThemeDefault }}">
@if(auth()->check())
<meta name="brudms-theme-prefer-server" content="1">
@endif

@include('partials.favicon')
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

@include('partials.brudms-theme')
@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
<script>
(function () {
    if (typeof window.__brudmsApplyTheme === 'function') {
        var meta = document.querySelector('meta[name="brudms-theme-default"]');
        var preferServer = document.querySelector('meta[name="brudms-theme-prefer-server"]');
        var theme = 'light';
        if (preferServer && meta) {
            theme = meta.getAttribute('content') || 'light';
        } else {
            try {
                theme = localStorage.getItem('brudms-appearance') || meta?.getAttribute('content') || 'light';
            } catch (e) {
                theme = meta?.getAttribute('content') || 'light';
            }
        }
        window.__brudmsApplyTheme(theme === 'light' ? 'light' : 'dark');
    }
})();
</script>
@stack('styles')
