<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $adminThemeDefault = in_array(auth()->user()->preference_appearance ?? 'light', ['light', 'dark'], true)
            ? auth()->user()->preference_appearance
            : 'light';
        $unsentCustomerReportsCount = \App\Models\Report::whereDoesntHave('operationsReport')->count();
    @endphp
    <meta name="brudms-theme-default" content="{{ $adminThemeDefault }}">
    <meta name="brudms-theme-prefer-server" content="1">
    @include('partials.brudms-theme', ['brudmsThemeDefault' => $adminThemeDefault])
    <script>
        (function () {
            // Runs synchronously before body paint so there's no flash of the wrong glass
            // style — CSS.supports is the only reliable way to know if this browser actually
            // renders an SVG filter reference inside backdrop-filter (Chromium only, as of
            // this writing); Safari/Firefox parse the value fine but don't render the
            // refraction, which is exactly the ambiguous case feature detection avoids.
            var supportsRefraction = false;
            try {
                supportsRefraction = window.CSS && CSS.supports && CSS.supports('backdrop-filter', 'url(#glass-rim) blur(4px)');
            } catch (e) {}
            document.documentElement.classList.toggle('no-refraction', !supportsRefraction);
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/css/brudms-theme.css', 'resources/js/app.js'])
    <title>Admin Console - @yield('title', 'Dashboard')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=Instrument+Sans:wght@400;500;600&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Admin console design tokens — dark default now (deep ink/teal, to carry the water
           animation), with light kept as an explicit opt-in for anyone whose saved appearance
           preference is 'light' — same data-theme attr / .dark class toggling mechanism
           partials/brudms-theme.blade.php's inline script already applies elsewhere, just with
           the default/override roles swapped from the previous light-first mockup pass. */
        :root {
            /* Constant regardless of light/dark toggle — the rail, avatar chips, and any text
               sitting on the (always-amber) accent surfaces need to stay dark ink either way,
               since --adm-ink itself now flips to near-white in dark mode as body text. */
            --adm-ink-fixed: #10262E;
            --adm-ink2-fixed: #1C3B45;

            --adm-ink: #EAF2F0;
            --adm-ink2: #C9DDE0;
            --adm-page: #0B1A1F;
            --adm-surface: #13262C;
            --adm-surface-rgb: 19, 38, 44;
            --adm-muted: #8FA3A8;
            --adm-line: #22383E;
            --adm-accent: #F0A202;
            --adm-accent2: #FFC24B;
            --adm-flow: #4FA3B5;
            --adm-flow2: #2E7D8F;
            --adm-alert: #E2685F;
            --adm-ok: #4FAE86;
        }
        html[data-theme='light']:not(.dark) {
            --adm-ink: #10262E;
            --adm-ink2: #1C3B45;
            --adm-page: #EDF0EE;
            --adm-surface: #FFFFFF;
            --adm-surface-rgb: 255, 255, 255;
            --adm-muted: #6B7C82;
            --adm-line: #DDE3E1;
            --adm-accent: #F0A202;
            --adm-accent2: #FFC24B;
            --adm-flow: #2E7D8F;
            --adm-flow2: #7FB8C4;
            --adm-alert: #C2453D;
            --adm-ok: #3E8E6E;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        .tnum { font-variant-numeric: tabular-nums; }
        body {
            font-family: var(--font-adm-sans);
            background: var(--adm-page);
            color: var(--adm-ink);
            display: flex;
            min-height: 100vh;
        }
        :focus-visible { outline: 2px solid var(--adm-accent); outline-offset: 3px; border-radius: 6px; }

        /* ===== Rail sidebar + flyout submenus ===== */
        .adm-rail {
            position: sticky; top: 1.5rem; z-index: 20;
            height: calc(100vh - 3rem);
            width: 84px; flex-shrink: 0;
            display: flex; flex-direction: column; align-items: center;
            background: var(--adm-ink-fixed);
            border-radius: var(--radius-adm-rail);
            padding: 1.75rem 0;
        }
        .adm-rail-logo { display: grid; place-items: center; height: 44px; width: 44px; border-radius: 16px; background: var(--adm-accent); }
        .adm-rail-logo img { width: 26px; height: auto; filter: drop-shadow(0 2px 4px rgba(0,0,0,.35)); }
        .adm-rail-nav { margin-top: 2.25rem; flex: 1; display: flex; flex-direction: column; align-items: center; gap: 0.6rem; width: 100%; }
        .rail-btn {
            position: relative; display: grid; place-items: center;
            height: 48px; width: 48px; border-radius: 16px;
            color: rgba(255,255,255,.5); background: transparent; border: none; cursor: pointer;
            transition: background .15s, color .15s;
        }
        .rail-btn:hover { background: rgba(255,255,255,.1); color: #fff; }
        .rail-btn.is-active { background: rgba(240,162,2,.16); color: var(--adm-accent2); }
        .rail-btn.is-active::before {
            content: ''; position: absolute; left: -14px; top: 50%; transform: translateY(-50%);
            width: 3px; height: 20px; border-radius: 3px; background: var(--adm-accent);
        }
        .rail-badge {
            position: absolute; top: 4px; right: 4px; min-width: 8px; height: 8px;
            border-radius: 999px; background: var(--adm-alert); box-shadow: 0 0 0 2px var(--adm-ink-fixed);
        }
        .rail-flyout {
            position: absolute; left: calc(100% + 12px); top: 0; z-index: 40;
            min-width: 220px; border-radius: 18px; background: var(--adm-ink-fixed);
            box-shadow: 0 18px 40px -12px rgba(0,0,0,.45);
            padding: 0.5rem;
        }
        .rail-flyout-title { padding: 0.5rem 0.75rem 0.35rem; font-size: 11px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: rgba(255,255,255,.4); }
        .rail-flyout a {
            display: flex; align-items: center; gap: 0.6rem;
            padding: 0.6rem 0.75rem; border-radius: 10px;
            color: rgba(255,255,255,.75); text-decoration: none; font-size: 13.5px; font-weight: 500;
        }
        .rail-flyout a:hover { background: rgba(255,255,255,.08); color: #fff; }
        .rail-flyout a.active { background: rgba(240,162,2,.18); color: var(--adm-accent2); }
        .rail-flyout a svg { flex-shrink: 0; opacity: .85; }
        .adm-rail-bottom { margin-top: 0.6rem; display: flex; flex-direction: column; align-items: center; gap: 0.4rem; }

        @media (max-width: 1024px) {
            .adm-rail { width: 68px; }
            .rail-btn { height: 42px; width: 42px; }
        }

        /* ===== Glass surfaces: every "white" card/topbar is translucent + blurred, with a
           soft prismatic edge (a gradient-border pseudo-element, mask-composited down to just
           the 1px ring) standing in for light refracting through a glass edge. .chart-card is
           declared per-page (dashboard.blade.php's own @push('styles')) but included here since
           it's the same surface type — its own rule only adds padding/radius, no conflict. */
        .adm-topbar, .card, .chart-card {
            position: relative;
            background: rgba(var(--adm-surface-rgb), 0.55);
            backdrop-filter: blur(22px) saturate(1.7);
            -webkit-backdrop-filter: blur(22px) saturate(1.7);
            box-shadow: var(--shadow-adm-card), inset 0 1px 0 rgba(255, 255, 255, 0.55), inset 0 -1px 0 rgba(16, 38, 46, 0.06);
        }
        .adm-topbar::before, .card::before, .chart-card::before {
            content: '';
            position: absolute; inset: 0;
            border-radius: inherit;
            padding: 1px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.22) 0%, rgba(255, 255, 255, 0) 28%, rgba(79, 163, 181, 0.55) 65%, rgba(240, 162, 2, 0.4) 100%);
            -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
        }
        html[data-theme='light']:not(.dark) .adm-topbar::before,
        html[data-theme='light']:not(.dark) .card::before,
        html[data-theme='light']:not(.dark) .chart-card::before {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0) 28%, rgba(127, 184, 196, 0.5) 65%, rgba(240, 162, 2, 0.4) 100%);
        }

        /* ===== Topbar ===== */
        .adm-topbar {
            display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem;
            border-radius: var(--radius-adm-card);
            padding: 0.9rem 1.25rem; margin-bottom: 1.25rem;
        }
        .adm-topbar-brand h1 { font-family: var(--font-adm-display); font-size: 19px; font-weight: 800; line-height: 1; color: var(--adm-ink); }
        .adm-topbar-brand p { margin-top: 4px; font-size: 12.5px; color: var(--adm-muted); }
        .adm-search {
            margin-left: auto; display: none; align-items: center; gap: 0.6rem;
            background: var(--adm-page); border-radius: 999px; padding: 0.6rem 1rem;
        }
        @media (min-width: 768px) { .adm-search { display: flex; } }
        .adm-search input { width: 220px; background: transparent; border: none; outline: none; font-size: 13px; color: var(--adm-ink); }
        .adm-search input::placeholder { color: var(--adm-muted); }
        .adm-bell {
            position: relative; display: grid; place-items: center;
            height: 40px; width: 40px; border-radius: 999px;
            background: var(--adm-page); color: var(--adm-ink); border: none; cursor: pointer; text-decoration: none;
        }
        .adm-bell:hover { background: var(--adm-line); }
        .adm-bell-dot { position: absolute; top: 8px; right: 8px; height: 8px; width: 8px; border-radius: 999px; background: var(--adm-alert); box-shadow: 0 0 0 2px var(--adm-surface); }
        .adm-cta {
            border-radius: 999px; background: color-mix(in srgb, var(--adm-accent) 46%, transparent); color: var(--adm-ink-fixed);
            padding: 0.6rem 1.25rem; font-size: 13px; font-weight: 600; text-decoration: none;
            box-shadow: var(--shadow-adm-hero);
        }
        .adm-cta:hover { background: color-mix(in srgb, var(--adm-accent2) 52%, transparent); }
        .adm-profile { display: flex; align-items: center; gap: 0.6rem; background: var(--adm-page); border-radius: 999px; padding: 0.35rem 1rem 0.35rem 0.35rem; }
        .adm-profile-avatar { display: grid; place-items: center; height: 32px; width: 32px; border-radius: 999px; background: var(--adm-ink-fixed); color: var(--adm-accent); font-size: 12px; font-weight: 600; }
        .adm-profile span { font-size: 13px; font-weight: 500; color: var(--adm-ink); }

        /* ===== Shared content components (used across every admin page) ===== */
        .adm-main { position: relative; z-index: 1; flex: 1; min-width: 0; padding: 1.5rem 1.5rem 1.5rem 0; }
        .header { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.25rem; }
        .header > div:first-child { flex: 1; min-width: 0; }
        .header h1 { font-family: var(--font-adm-display); font-size: 1.7rem; font-weight: 800; color: var(--adm-ink); margin-bottom: 0.35rem; letter-spacing: -.01em; }
        .header p { color: var(--adm-muted); font-size: 0.9rem; }
        .card {
            border-radius: var(--radius-adm-card);
            padding: 1.5rem; margin-bottom: 1.5rem;
        }
        .btn { display: inline-block; padding: 0.55rem 1.1rem; border-radius: 999px; font-weight: 600; text-decoration: none; cursor: pointer; border: none; font-size: 0.85rem; }
        .btn-primary { background: color-mix(in srgb, var(--adm-accent) 42%, transparent); color: var(--adm-ink-fixed); }
        .btn-primary:hover { background: color-mix(in srgb, var(--adm-accent2) 48%, transparent); }
        .btn-secondary { background: color-mix(in srgb, var(--adm-page) 30%, transparent); color: var(--adm-flow); }
        .btn-secondary:hover { background: color-mix(in srgb, var(--adm-line) 36%, transparent); }
        .back-link { display: inline-flex; align-items: center; padding: 0.55rem 1.1rem; border-radius: 999px; text-decoration: none; font-weight: 600; font-size: 0.85rem; background: color-mix(in srgb, var(--adm-page) 30%, transparent); color: var(--adm-flow); border: none; cursor: pointer; }
        .back-link:hover { background: color-mix(in srgb, var(--adm-line) 36%, transparent); }
        .btn-danger { background: rgba(194, 69, 61, 0.09); color: var(--adm-alert); }

        /* ===== Liquid glass buttons — real refraction (Chromium) via #glass-rim (see
           partials/admin-glass-defs.blade.php), layered fallback for Safari/Firefox.
           Structural glass properties live once here; each button class above/below only
           sets its own tinted background so primary/secondary/danger stay visually distinct
           instead of all reading as the same clear glass. Deliberately NOT applied to
           .rail-btn / .adm-bell — they sit on the rail's flat opaque background, and glass
           over flat color has nothing to refract (per the brief: don't fake it, skip it). */
        .btn, .btn-submit, .btn-edit, .btn-page, .back-link, .adm-cta {
            position: relative;
            isolation: isolate;
            backdrop-filter: blur(10px) saturate(180%) brightness(1.05);
            -webkit-backdrop-filter: blur(10px) saturate(180%);
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, 0.55),
                inset 0 -1px 0 rgba(255, 255, 255, 0.14),
                inset 1px 0 0 rgba(255, 255, 255, 0.16),
                inset -1px 0 0 rgba(255, 255, 255, 0.16),
                0 10px 26px -10px rgba(16, 38, 46, 0.35);
            text-shadow: 0 1px 0 rgba(255, 255, 255, 0.4);
            transition: background 180ms ease-out, box-shadow 180ms ease-out, filter 180ms ease-out;
        }
        /* The real refraction layer — only wins where the browser actually supports an SVG
           filter reference inside backdrop-filter. html.no-refraction (see the feature-detect
           script near the end of <body>) means this rule is skipped entirely there, leaving
           the plain blur+saturate above as the fallback rather than a silently broken look. */
        html:not(.no-refraction) .btn,
        html:not(.no-refraction) .btn-submit,
        html:not(.no-refraction) .btn-edit,
        html:not(.no-refraction) .btn-page,
        html:not(.no-refraction) .back-link,
        html:not(.no-refraction) .adm-cta {
            backdrop-filter: url(#glass-rim) blur(4px) saturate(180%) brightness(1.05);
        }
        .btn::before, .btn-submit::before, .btn-edit::before, .btn-page::before, .back-link::before, .adm-cta::before {
            content: ''; position: absolute; inset: 0; border-radius: inherit; pointer-events: none; z-index: 1;
            background: linear-gradient(150deg, rgba(255, 255, 255, 0.30) 0%, rgba(255, 255, 255, 0.08) 32%, transparent 62%);
            transition: background 180ms ease-out;
        }
        .btn:hover, .btn-submit:hover, .btn-edit:hover, .btn-page:hover:not(.disabled), .back-link:hover, .adm-cta:hover {
            filter: brightness(1.06);
        }
        .btn:hover::before, .btn-submit:hover::before, .btn-edit:hover::before, .btn-page:hover:not(.disabled)::before, .back-link:hover::before, .adm-cta:hover::before {
            background: linear-gradient(150deg, rgba(255, 255, 255, 0.42) 0%, rgba(255, 255, 255, 0.12) 32%, transparent 62%);
        }
        .btn:active, .btn-submit:active, .btn-edit:active, .btn-page:active:not(.disabled), .back-link:active, .adm-cta:active {
            transform: scale(0.985);
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, 0.4),
                inset 0 -1px 0 rgba(255, 255, 255, 0.1),
                0 4px 12px -6px rgba(16, 38, 46, 0.3);
        }
        /* prefers-reduced-transparency: reduce — drop to an opaque surface, no blur, no sheen. */
        @media (prefers-reduced-transparency: reduce) {
            .btn, .btn-submit, .btn-edit, .btn-page, .back-link, .adm-cta {
                backdrop-filter: none !important; -webkit-backdrop-filter: none !important;
            }
            .btn-primary, .btn-submit, .adm-cta { background: var(--adm-accent) !important; }
            .btn-secondary, .btn-edit, .btn-page, .back-link { background: var(--adm-page) !important; }
            .btn::before, .btn-submit::before, .btn-edit::before, .btn-page::before, .back-link::before, .adm-cta::before { display: none; }
        }
        /* No-refraction fallback (Safari/Firefox) — heavier tint compensates for the missing
           bent-edge detail, so the button still reads as a distinct surface, not unfinished. */
        .no-refraction .btn-primary, .no-refraction .btn-submit, .no-refraction .adm-cta {
            background: color-mix(in srgb, var(--adm-accent) 58%, transparent);
        }
        .no-refraction .btn-primary:hover, .no-refraction .btn-submit:hover, .no-refraction .adm-cta:hover {
            background: color-mix(in srgb, var(--adm-accent2) 62%, transparent);
        }
        .no-refraction .btn-secondary, .no-refraction .btn-edit, .no-refraction .btn-page, .no-refraction .back-link {
            background: color-mix(in srgb, var(--adm-page) 52%, transparent);
        }
        .no-refraction .btn-secondary:hover, .no-refraction .btn-edit:hover, .no-refraction .btn-page:hover:not(.disabled), .no-refraction .back-link:hover {
            background: color-mix(in srgb, var(--adm-line) 55%, transparent);
        }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; color: var(--adm-muted); font-size: 0.85rem; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.55rem 0.8rem; background: var(--adm-page); border: 1px solid var(--adm-line); border-radius: 12px; color: var(--adm-ink); font-size: 0.9rem; }
        .form-group input::placeholder, .form-group textarea::placeholder { color: var(--adm-muted); opacity: 0.8; }
        .search-filter-bar { display: flex; gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap; }
        .search-filter-bar input { flex: 1; min-width: 200px; padding: 0.55rem 1rem; background: var(--adm-page); border: 1px solid var(--adm-line); border-radius: 12px; color: var(--adm-ink); }
        .search-filter-bar select { padding: 0.55rem 1rem; background: var(--adm-page); border: 1px solid var(--adm-line); border-radius: 12px; color: var(--adm-ink); }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid var(--adm-line); }
        th { color: var(--adm-muted); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
        td { font-size: 0.9rem; color: var(--adm-ink); }
        .badge { padding: 0.25rem 0.6rem; border-radius: 999px; font-size: 0.72rem; font-weight: 700; }
        .badge-admin { background: rgba(124, 108, 240, 0.16); color: #6f5fe0; }
        .badge-operator { background: rgba(46, 125, 143, 0.14); color: var(--adm-flow); }
        .badge-crew_leader { background: rgba(62, 142, 110, 0.16); color: var(--adm-ok); }
        .badge-maintenance { background: rgba(240, 162, 2, 0.16); color: #9A6A00; }
        .badge-first_aider { background: rgba(194, 69, 61, 0.14); color: var(--adm-alert); }
        .tab-nav { display: flex; gap: 0.5rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--adm-line); }
        .tab-nav a { padding: 0.75rem 1rem; color: var(--adm-muted); text-decoration: none; font-weight: 600; font-size: 0.875rem; border-bottom: 2px solid transparent; margin-bottom: -1px; }
        .tab-nav a:hover, .tab-nav a.active { color: var(--adm-flow); border-bottom-color: var(--adm-accent); }
        .link-primary { color: var(--adm-flow); text-decoration: none; font-weight: 600; }
        .link-primary:hover { text-decoration: underline; }
        .btn-submit { padding: 0.55rem 1.3rem; background: color-mix(in srgb, var(--adm-accent) 42%, transparent); color: var(--adm-ink-fixed); border: none; border-radius: 999px; font-weight: 600; cursor: pointer; font-size: 0.85rem; }
        .btn-submit:hover { background: color-mix(in srgb, var(--adm-accent2) 48%, transparent); }
        .form-control { width: 100%; padding: 0.55rem 0.8rem; background: var(--adm-page); border: 1px solid var(--adm-line); border-radius: 12px; color: var(--adm-ink); font-size: 0.9rem; }
        .form-label { display: block; margin-bottom: 0.5rem; color: var(--adm-muted); font-size: 0.85rem; font-weight: 600; }
        .grid-filters { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 1rem; width: 100%; }
        .grid-filters .form-group { margin-bottom: 0; }
        .grid-filters .form-group.flex-grow { flex: 1; min-width: 0; }
        .badge-low { background: rgba(46, 125, 143, 0.14); color: var(--adm-flow); }
        .badge-medium { background: rgba(240, 162, 2, 0.16); color: #9A6A00; }
        .badge-high { background: rgba(194, 69, 61, 0.16); color: var(--adm-alert); }
        .badge-critical { background: rgba(194, 69, 61, 0.3); color: #8f2c26; }
        .badge-status { background: rgba(46, 125, 143, 0.14); color: var(--adm-flow); }
        .badge-status-amber { background: rgba(240, 162, 2, 0.16); color: #9A6A00; }
        .badge-status-teal { background: rgba(127, 184, 196, 0.25); color: var(--adm-flow); }
        .badge-status-green { background: rgba(62, 142, 110, 0.16); color: var(--adm-ok); }
        .badge-status-red { background: rgba(194, 69, 61, 0.16); color: var(--adm-alert); }
        .card-mb { margin-bottom: 1.5rem; }
        .cell-muted { color: var(--adm-muted); font-size: 0.9rem; padding: 1.5rem !important; text-align: center; }
        .pagination-bar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--adm-line); }
        .pagination-info { color: var(--adm-muted); font-size: 0.85rem; }
        .pagination-btns { display: flex; gap: 0.5rem; }
        .btn-page { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.85rem; border-radius: 999px; font-size: 0.85rem; color: var(--adm-flow); text-decoration: none; background: color-mix(in srgb, var(--adm-page) 30%, transparent); }
        .btn-page:hover:not(.disabled) { background: color-mix(in srgb, var(--adm-line) 36%, transparent); }
        .btn-page.disabled { color: var(--adm-muted); opacity: 0.6; cursor: default; }
        .grid-2col { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem; }
        .card-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1rem; }
        .section-head { font-family: var(--font-adm-display); font-size: 1.05rem; font-weight: 700; color: var(--adm-ink); }
        .btn-edit { padding: 0.4rem 0.95rem; background: color-mix(in srgb, var(--adm-page) 30%, transparent); color: var(--adm-flow); border-radius: 999px; text-decoration: none; font-size: 0.85rem; font-weight: 600; }
        .btn-edit:hover { background: color-mix(in srgb, var(--adm-line) 36%, transparent); }
        .detail-row { margin-bottom: 1rem; }
        .detail-row:last-child { margin-bottom: 0; }
        .info-label { font-size: 0.78rem; color: var(--adm-muted); margin-bottom: 0.25rem; }
        .info-value, .info-value-muted { font-size: 0.9rem; color: var(--adm-ink); margin: 0; }
        .info-value-muted { color: var(--adm-muted); }
        .info-value-small { font-size: 0.78rem; color: var(--adm-muted); margin: 0.25rem 0 0; }
        .alert-success { background: rgba(62, 142, 110, 0.14); color: var(--adm-ok); padding: 0.75rem 1rem; border-radius: 14px; margin-bottom: 1.5rem; font-size: 0.9rem; }
        .section-desc { color: var(--adm-muted); font-size: 0.85rem; margin-bottom: 1rem; }
        .stats-grid-4 { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
        .stats-grid-4 .card { padding: 1rem; }
        .stats-grid-4 .card p { font-size: 0.8rem; color: var(--adm-muted); margin: 0 0 0.25rem; }
        .stats-grid-4 .card h3 { font-size: 1.4rem; font-weight: 700; color: var(--adm-ink); margin: 0; }
        .chart-card-title { font-family: var(--font-adm-display); font-size: 1.05rem; font-weight: 700; color: var(--adm-ink); margin: 0 0 1rem; }

        @media (prefers-reduced-motion: reduce) { * { animation: none !important; transition: none !important; } }
        @media (max-width: 1024px) { .adm-main { padding: 1.5rem 1rem 1.5rem 0.75rem; } }
    </style>
    @stack('styles')
</head>
<body>
    @include('partials.admin-water-bg')
    @include('partials.admin-glass-defs')
    <div class="relative mx-auto flex w-full max-w-[1600px] gap-5 p-4 lg:p-6" style="z-index: 1;">

        <aside class="adm-rail">
            <a href="{{ route('admin.dashboard') }}" class="adm-rail-logo" title="BruDMS Admin">
                <img src="{{ asset('images/admin-shield.svg') }}" alt="">
            </a>

            <nav class="adm-rail-nav">
                {{-- Overview --}}
                <a href="{{ route('admin.dashboard') }}" class="rail-btn {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}" title="Overview">
                    <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="3" width="7" height="9" rx="2"/><rect x="14" y="3" width="7" height="5" rx="2"/><rect x="14" y="12" width="7" height="9" rx="2"/><rect x="3" y="16" width="7" height="5" rx="2"/></svg>
                </a>

                {{-- AI — lives on a single page (Customer Reports, where the real drainage-AI
                     verdicts and scan actions are). The separate Incident-upload pages (AI
                     Incidents dashboard / Review Queue) are intentionally not linked here
                     anymore; their routes/controller are left intact since other code
                     (AdminController's recent-activity feed) still references them. --}}
                <a href="{{ route('admin.customer-reports.index') }}" class="rail-btn {{ request()->routeIs('admin.customer-reports.*') ? 'is-active' : '' }}" title="AI &middot; Customer Reports">
                    <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="4" y="7" width="16" height="12" rx="3"/><path d="M9 3v4M15 3v4M8 13h.01M16 13h.01M9 17h6"/></svg>
                    @if($unsentCustomerReportsCount > 0)<span class="rail-badge"></span>@endif
                </a>

                {{-- GIS Map --}}
                @php $gisActive = request()->routeIs('admin.gis-map'); @endphp
                <div class="relative" data-rail-group>
                    <button type="button" class="rail-btn {{ $gisActive ? 'is-active' : '' }}" data-rail-toggle title="GIS Map">
                        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="m9 4 6 2.5L21 4v15l-6 2.5L9 19l-6 2.5v-15L9 4Z"/><path d="M9 4v15M15 6.5v15"/></svg>
                    </button>
                    <div class="rail-flyout hidden" data-rail-flyout>
                        <p class="rail-flyout-title">GIS Map</p>
                        <a href="{{ route('admin.gis-map', ['view' => 'admin_ops']) }}" class="{{ $gisActive && request()->query('view', 'admin_ops') === 'admin_ops' ? 'active' : '' }}">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                            Admin/Ops GIS map
                        </a>
                        <a href="{{ route('admin.gis-map', ['view' => 'customer']) }}" class="{{ $gisActive && request()->query('view') === 'customer' ? 'active' : '' }}">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 21s7-6.2 7-11a7 7 0 1 0-14 0c0 4.8 7 11 7 11Z"/><circle cx="12" cy="10" r="2.4"/></svg>
                            Customer GIS map
                        </a>
                    </div>
                </div>

                {{-- Statistics --}}
                <a href="{{ route('admin.statistics.index') }}" class="rail-btn {{ request()->routeIs('admin.statistics.*') ? 'is-active' : '' }}" title="Statistics">
                    <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M4 20V10M12 20V4M20 20v-7"/></svg>
                </a>

                {{-- Old Reports --}}
                <a href="{{ route('admin.old-reports.index') }}" class="rail-btn {{ request()->routeIs('admin.old-reports.*') ? 'is-active' : '' }}" title="Old Reports">
                    <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M6 2h9l5 5v15H6z"/><path d="M15 2v5h5M9 13h6M9 17h6"/></svg>
                </a>

                {{-- Work Orders --}}
                @php $woActive = (request()->routeIs('admin.work-orders.*') && ! request()->routeIs('admin.work-orders.archived')) || request()->routeIs('admin.old-work-orders.*'); @endphp
                <div class="relative" data-rail-group>
                    <button type="button" class="rail-btn {{ $woActive ? 'is-active' : '' }}" data-rail-toggle title="Work orders">
                        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="4" y="3" width="16" height="18" rx="3"/><path d="M9 8h6M9 12h6M9 16h3"/></svg>
                    </button>
                    <div class="rail-flyout hidden" data-rail-flyout>
                        <p class="rail-flyout-title">Work Orders</p>
                        <a href="{{ route('admin.work-orders.index') }}" class="{{ request()->routeIs('admin.work-orders.index') || request()->routeIs('admin.work-orders.show') || request()->routeIs('admin.work-orders.create') || request()->routeIs('admin.work-orders.edit') ? 'active' : '' }}">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                            Live work orders
                        </a>
                        <a href="{{ route('admin.old-work-orders.index') }}" class="{{ request()->routeIs('admin.old-work-orders.*') ? 'active' : '' }}">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M21 8v13H3V8M1 3h22v5H1zM10 12h4"/></svg>
                            Old work orders
                        </a>
                        @if(auth()->user()?->isSuperAdmin())
                            <a href="{{ route('admin.work-orders.archived') }}" class="{{ request()->routeIs('admin.work-orders.archived') ? 'active' : '' }}">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="4" width="18" height="4" rx="1"/><path d="M5 8v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V8M10 13h4"/></svg>
                                Work Order Archive
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Customer chat --}}
                <a href="{{ route('admin.support.chat') }}" class="rail-btn {{ request()->routeIs('admin.support.chat') ? 'is-active' : '' }}" title="Customer chat">
                    <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                </a>

                {{-- Staff accounts --}}
                @if(auth()->user()?->hasRole(\App\Models\User::ROLE_ADMIN) || auth()->user()?->hasRole(\App\Models\User::ROLE_SUPER_ADMIN))
                    @php $staffActive = request()->routeIs('admin.staff.*'); @endphp
                    <div class="relative" data-rail-group>
                        <button type="button" class="rail-btn {{ $staffActive ? 'is-active' : '' }}" data-rail-toggle title="Staff accounts">
                            <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="9" cy="8" r="3.2"/><path d="M3 20c0-3.3 2.7-5.5 6-5.5s6 2.2 6 5.5"/><path d="M16 5.5a3.2 3.2 0 0 1 0 6M17 14.8c2.4.6 4 2.6 4 5.2"/></svg>
                        </button>
                        <div class="rail-flyout hidden" data-rail-flyout>
                            <p class="rail-flyout-title">Staff accounts</p>
                            <a href="{{ route('admin.staff.index') }}" class="{{ request()->routeIs('admin.staff.index') ? 'active' : '' }}">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="9" cy="7" r="4"/><path d="M2 21v-2a4 4 0 0 1 4-4h6a4 4 0 0 1 4 4v2M16 3.13a4 4 0 0 1 0 7.75M22 21v-2a4 4 0 0 0-3-3.87"/></svg>
                                View admins &amp; operations
                            </a>
                            @if(auth()->user()?->isSuperAdmin())
                                <a href="{{ route('admin.staff.users.create') }}" class="{{ request()->routeIs('admin.staff.users.*') ? 'active' : '' }}">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
                                    Add user
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </nav>

            <div class="adm-rail-bottom">
                <a href="{{ route('admin.audit-log.index') }}" class="rail-btn {{ request()->routeIs('admin.audit-log.*') ? 'is-active' : '' }}" title="Audit logging">
                    <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M9 2h9a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6"/><path d="m5 4-2 2 2 2M9 6H3M9 12h8M9 16h8"/></svg>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rail-btn" title="Sign out">
                        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5M21 12H9"/></svg>
                    </button>
                </form>
            </div>
        </aside>

        <div class="adm-main">
            <header class="adm-topbar">
                <div class="adm-topbar-brand">
                    <h1>Admin Console</h1>
                    <p>Jabatan Kerja Raya &middot; Sewage &amp; Drainage</p>
                </div>

                <form action="{{ route('admin.customer-reports.index') }}" method="GET" class="adm-search">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--adm-muted)" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.2-3.2"/></svg>
                    <input type="search" name="search" placeholder="Search customer reports" value="{{ request('search') }}">
                </form>

                <a href="{{ route('admin.customer-reports.index') }}" class="adm-bell" title="Reports needing review">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M18 8a6 6 0 1 0-12 0c0 7-3 8-3 8h18s-3-1-3-8"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
                    @if($unsentCustomerReportsCount > 0)<span class="adm-bell-dot"></span>@endif
                </a>

                <a href="{{ route('admin.work-orders.create') }}" class="adm-cta">New work order</a>

                <div class="adm-profile">
                    <div class="adm-profile-avatar">{{ \Illuminate\Support\Str::of(auth()->user()->name ?? 'A')->explode(' ')->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('') }}</div>
                    <span>{{ \Illuminate\Support\Str::of(auth()->user()->name ?? 'Admin')->explode(' ')->first() }}</span>
                </div>
            </header>

            @yield('content')
        </div>
    </div>

    <script>
        (function () {
            document.querySelectorAll('[data-rail-group]').forEach(function (group) {
                var toggle = group.querySelector('[data-rail-toggle]');
                var flyout = group.querySelector('[data-rail-flyout]');
                if (!toggle || !flyout) return;
                toggle.addEventListener('click', function (ev) {
                    ev.stopPropagation();
                    var isOpen = !flyout.classList.contains('hidden');
                    document.querySelectorAll('[data-rail-flyout]').forEach(function (f) { f.classList.add('hidden'); });
                    if (!isOpen) flyout.classList.remove('hidden');
                });
            });
            document.addEventListener('click', function () {
                document.querySelectorAll('[data-rail-flyout]').forEach(function (f) { f.classList.add('hidden'); });
            });
            document.addEventListener('keydown', function (ev) {
                if (ev.key === 'Escape') {
                    document.querySelectorAll('[data-rail-flyout]').forEach(function (f) { f.classList.add('hidden'); });
                }
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
