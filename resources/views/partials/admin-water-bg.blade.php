{{-- Animated "drainage flow" background for the admin console: slow horizontal current lines +
     a couple of soft glow blobs, themed via existing --brudms-* vars so it adapts automatically
     between light/dark without a separate per-theme block. Deliberately restrained (few
     elements, slow motion, low opacity) — this sits behind dense data tables, so it needs to
     read as "professional infrastructure" ambience, not a decorative animation competing for
     attention. --}}
<style>
    .admin-water-bg { position: fixed; inset: 0; z-index: 0; overflow: hidden; pointer-events: none; }

    @keyframes admin-water-flow {
        to { stroke-dashoffset: -2000; }
    }
    @keyframes admin-water-glow-drift {
        0%, 100% { transform: translate(0, 0) scale(1); }
        50% { transform: translate(3%, -3%) scale(1.08); }
    }
    @keyframes admin-water-bubble-rise {
        0% { transform: translateY(0) scale(1); opacity: 0; }
        10% { opacity: var(--bubble-opacity, 0.35); }
        90% { opacity: var(--bubble-opacity, 0.35); }
        100% { transform: translateY(-60px) scale(1.15); opacity: 0; }
    }

    .admin-water-flow path {
        fill: none;
        stroke: var(--brudms-primary);
        stroke-linecap: round;
        animation: admin-water-flow linear infinite;
    }

    .admin-water-glow {
        position: absolute;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(var(--brudms-primary-rgb), 0.35) 0%, rgba(var(--brudms-primary-rgb), 0) 70%);
        filter: blur(80px);
        animation: admin-water-glow-drift 40s ease-in-out infinite;
        will-change: transform;
    }

    .admin-water-bubble {
        position: absolute;
        bottom: 6%;
        width: var(--bubble-size, 6px);
        height: var(--bubble-size, 6px);
        border-radius: 50%;
        background: rgba(var(--brudms-primary-rgb), 0.5);
        animation: admin-water-bubble-rise var(--bubble-duration, 14s) ease-in infinite;
        animation-delay: var(--bubble-delay, 0s);
    }

    @media (max-width: 900px) {
        .admin-water-glow { filter: blur(50px); }
        .admin-water-bg .admin-water-flow { opacity: 0.6; }
    }

    @media (prefers-reduced-motion: reduce) {
        .admin-water-flow path,
        .admin-water-glow,
        .admin-water-bubble { animation: none; }
    }
</style>
<div class="admin-water-bg" aria-hidden="true">
    <div class="admin-water-glow" style="top: -12%; left: -8%; width: 34rem; height: 34rem;"></div>
    <div class="admin-water-glow" style="bottom: -16%; right: -10%; width: 30rem; height: 30rem; animation-delay: -18s;"></div>

    <svg class="admin-water-flow" viewBox="0 0 1600 900" preserveAspectRatio="none" style="position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0.22;">
        <path d="M-100 120C150 60 350 180 620 120S1100 40 1700 120" stroke-width="2.5" stroke-dasharray="18 26" style="animation-duration: 46s;" />
        <path d="M-100 260C180 320 400 200 680 260S1150 340 1700 260" stroke-width="2" stroke-dasharray="14 22" style="animation-duration: 58s; animation-delay: -12s;" />
        <path d="M-100 480C200 420 420 540 700 480S1180 400 1700 480" stroke-width="3" stroke-dasharray="20 30" style="animation-duration: 52s; animation-delay: -6s;" />
        <path d="M-100 660C220 720 440 600 720 660S1200 740 1700 660" stroke-width="2" stroke-dasharray="14 22" style="animation-duration: 64s; animation-delay: -24s;" />
        <path d="M-100 820C240 760 460 880 740 820S1220 760 1700 820" stroke-width="2.5" stroke-dasharray="18 26" style="animation-duration: 50s; animation-delay: -30s;" />
    </svg>

    <div class="admin-water-bubble" style="left: 12%; --bubble-size: 5px; --bubble-duration: 16s; --bubble-delay: -2s; --bubble-opacity: 0.3;"></div>
    <div class="admin-water-bubble" style="left: 28%; --bubble-size: 7px; --bubble-duration: 19s; --bubble-delay: -9s; --bubble-opacity: 0.25;"></div>
    <div class="admin-water-bubble" style="left: 47%; --bubble-size: 4px; --bubble-duration: 14s; --bubble-delay: -5s; --bubble-opacity: 0.35;"></div>
    <div class="admin-water-bubble" style="left: 64%; --bubble-size: 6px; --bubble-duration: 20s; --bubble-delay: -13s; --bubble-opacity: 0.28;"></div>
    <div class="admin-water-bubble" style="left: 81%; --bubble-size: 5px; --bubble-duration: 17s; --bubble-delay: -1s; --bubble-opacity: 0.3;"></div>
    <div class="admin-water-bubble" style="left: 93%; --bubble-size: 4px; --bubble-duration: 15s; --bubble-delay: -8s; --bubble-opacity: 0.25;"></div>
</div>
