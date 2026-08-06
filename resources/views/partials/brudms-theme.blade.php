@php
    $brudmsThemeDefault = $brudmsThemeDefault
        ?? (auth()->check() ? (auth()->user()->preference_appearance ?? 'light') : 'light');
    $brudmsThemeDefault = in_array($brudmsThemeDefault, ['light', 'dark'], true) ? $brudmsThemeDefault : 'light';
    $brudmsThemePreferServer = auth()->check();
@endphp
{{-- Critical CSS: works even if Vite bundle fails to load --}}
<style id="brudms-theme-critical">
html[data-theme='light'] {
    --brudms-primary: #ffffff;
    --brudms-primary-dark: #f5f5f5;
    --brudms-primary-rgb: 255, 255, 255;
    --brudms-secondary: #e8e8e8;
    --brudms-tertiary: #78bfe4;
    --bg-primary: #78bfe4;
    --bg-secondary: #ffffff;
    --text-primary: #1a3d52;
    --text-secondary: #2f4d61;
    --accent-blue: #0a5f8f;
    --border-light: rgba(78, 184, 224, 0.25);
    --brudms-chip-bg: rgba(255, 255, 255, 0.28);
    --brudms-chip-border: rgba(26, 61, 82, 0.45);
    --brudms-panel-glass: rgba(255, 255, 255, 0.96);
}
html[data-theme='dark'],
html.dark {
    --brudms-primary: #6ecff0;
    --brudms-primary-dark: #8ad8f5;
    --brudms-primary-rgb: 110, 207, 240;
    --brudms-secondary: #272b3c;
    --brudms-tertiary: #1a1d2b;
    --bg-primary: #1a1d2b;
    --bg-secondary: #272b3c;
    --text-primary: #f1f5f9;
    --text-secondary: #94a3b8;
    --accent-blue: #6ecff0;
    --border-light: rgba(110, 207, 240, 0.18);
    --brudms-chip-bg: rgba(66, 106, 120, 0.28);
    --brudms-chip-border: rgba(255, 255, 255, 0.18);
    --brudms-panel-glass: rgba(39, 43, 60, 0.95);
}
html[data-theme='light'] body.brudms-customer-ui,
html[data-theme='light'] body.brudms-portal {
    background-color: #78bfe4 !important;
    color: #1a3d52 !important;
}
html[data-theme='light'] .brudms-customer-ui .cust-page-bg,
html[data-theme='light'] .brudms-customer-ui .cust-page-bg > div {
    display: none !important;
}
</style>
<script>
(function () {
    var key = 'brudms-appearance';
    var server = @json($brudmsThemeDefault);
    var preferServer = @json($brudmsThemePreferServer);

    function resolveTheme() {
        var theme = server;
        try {
            if (preferServer) {
                theme = server;
            } else {
                var stored = localStorage.getItem(key);
                if (stored === 'light' || stored === 'dark') {
                    theme = stored;
                }
            }
        } catch (e) {}
        return theme === 'light' ? 'light' : 'dark';
    }

    window.__brudmsApplyTheme = function (theme) {
        var value = theme === 'light' ? 'light' : 'dark';
        var root = document.documentElement;
        root.setAttribute('data-theme', value);
        if (value === 'dark') {
            root.classList.add('dark');
        } else {
            root.classList.remove('dark');
        }
        try {
            localStorage.setItem(key, value);
        } catch (e) {}
        if (window.Flux && typeof window.Flux.appearance !== 'undefined') {
            try { window.Flux.appearance = value; } catch (e) {}
        }
        if (document.body) {
            var bg = value === 'light' ? '#78bfe4' : '#1a1d2b';
            var fg = value === 'light' ? '#1a3d52' : '#f1f5f9';
            document.body.style.setProperty('background-color', bg, 'important');
            document.body.style.setProperty('color', fg, 'important');
        }
        window.dispatchEvent(new CustomEvent('brudms-theme-change', { detail: { theme: value } }));
        return value;
    };

    window.__brudmsApplyTheme(resolveTheme());

    /* Re-apply after Flux / Livewire may override html.dark */
    function reapply() {
        window.__brudmsApplyTheme(resolveTheme());
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', reapply);
    } else {
        reapply();
    }
    window.addEventListener('load', reapply);
    setTimeout(reapply, 50);
    setTimeout(reapply, 300);
})();
</script>
