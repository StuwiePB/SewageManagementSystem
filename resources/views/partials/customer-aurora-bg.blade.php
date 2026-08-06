{{-- Light-mode-only background: atmospheric sky + sun glow + grand drifting cloud/aurora
     blobs + flowing waterway contour lines + grain. Hidden in dark mode, where the
     crdboard topo background (customer-crdboard-bg.blade.php) is used instead. --}}
<style>
    .brudms-aurora-bg { display: none; }
    html[data-theme='light'] .brudms-aurora-bg { display: block; }

    @keyframes brudms-aurora-drift {
        0%   { transform: translate(0, 0) scale(1) rotate(0deg); }
        33%  { transform: translate(-6%, 5%) scale(1.14) rotate(4deg); }
        66%  { transform: translate(5%, -4%) scale(1.06) rotate(-3deg); }
        100% { transform: translate(0, 0) scale(1) rotate(0deg); }
    }
    @keyframes brudms-aurora-drift-slow {
        0%, 100% { transform: translate(0, 0) scale(1); }
        50% { transform: translate(7%, -5%) scale(1.18); }
    }
    @keyframes brudms-cloud-drift {
        0%, 100% { transform: translateX(-6%) translateY(0); }
        50% { transform: translateX(6%) translateY(-2%); }
    }
    @keyframes brudms-sun-pulse {
        0%, 100% { opacity: 0.55; transform: scale(1); }
        50% { opacity: 0.8; transform: scale(1.1); }
    }
    @keyframes brudms-aurora-flow {
        to { stroke-dashoffset: -1400; }
    }

    .brudms-aurora-blob {
        animation: brudms-aurora-drift 34s ease-in-out infinite;
        will-change: transform;
        transform: translateZ(0);
    }
    .brudms-aurora-blob-slow {
        animation: brudms-aurora-drift-slow 46s ease-in-out infinite;
        will-change: transform;
        transform: translateZ(0);
    }
    .brudms-cloud {
        animation: brudms-cloud-drift 60s ease-in-out infinite;
        will-change: transform;
        transform: translateZ(0);
    }
    .brudms-sun-glow {
        animation: brudms-sun-pulse 12s ease-in-out infinite;
        will-change: transform, opacity;
    }
    .brudms-aurora-contour path { stroke-dasharray: 14 22; animation: brudms-aurora-flow 55s linear infinite; }

    /* Blur + blend-mode compositing is expensive on phone GPUs — shrink both the element
       size AND the blur radius a lot, and drop the blend-mode grain layer, on small
       screens. A ~900px-wide blurred layer being animated every frame is heavy enough
       on some phone GPUs that the animation can appear to stall/freeze entirely rather
       than just running slower, which is why size (not just blur) needs to come down too.
       (Blur lives on the inner child div now, see the WebKit note above.) */
    @media (max-width: 767px) {
        .brudms-aurora-blob, .brudms-aurora-blob-slow { width: 22rem !important; height: 22rem !important; }
        .brudms-cloud { width: 20rem !important; }
        .brudms-sun-glow { width: 16rem !important; height: 16rem !important; }
        .brudms-aurora-blob > div, .brudms-aurora-blob-slow > div { filter: blur(40px) !important; }
        .brudms-cloud > div { filter: blur(28px) !important; }
        .brudms-sun-glow > div { filter: blur(20px) !important; }
        .brudms-aurora-grain { display: none; }
    }

    @media (prefers-reduced-motion: reduce) {
        .brudms-aurora-blob,
        .brudms-aurora-blob-slow,
        .brudms-cloud,
        .brudms-sun-glow,
        .brudms-aurora-contour path { animation: none; }
    }
</style>
<div class="fixed inset-0 overflow-hidden brudms-aurora-bg" style="z-index: 0;">
    <div class="absolute inset-0 bg-gradient-to-br from-[#8ecdec] via-[#78bfe4] to-[#549fd0]"></div>
    <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-transparent to-[#eaf7ff]/25"></div>

    {{-- Sun glow: adds photographic depth/realism to the top-right sky.
         Blur lives on the inner div, animation on the outer — WebKit/mobile Safari
         freezes the blur rasterization when filter and animated transform are on the
         same element, which made this look static on phones. --}}
    <div class="brudms-sun-glow absolute -top-28 right-[6%] h-[28rem] w-[28rem]">
        <div class="h-full w-full rounded-full blur-[50px]" style="background: radial-gradient(circle, rgba(255,252,235,0.85) 0%, rgba(255,244,214,0.4) 40%, rgba(255,244,214,0) 70%);"></div>
    </div>

    {{-- Grand, slow-drifting aurora blobs --}}
    <div class="brudms-aurora-blob absolute -top-56 -left-44 h-[56rem] w-[56rem]">
        <div class="h-full w-full rounded-full bg-sky-400/55 blur-[150px]"></div>
    </div>
    <div class="brudms-aurora-blob-slow absolute top-1/4 -right-52 h-[50rem] w-[50rem]" style="animation-delay:-10s">
        <div class="h-full w-full rounded-full bg-teal-300/55 blur-[160px]"></div>
    </div>
    <div class="brudms-aurora-blob absolute -bottom-60 left-1/4 h-[46rem] w-[46rem]" style="animation-delay:-20s">
        <div class="h-full w-full rounded-full bg-indigo-300/50 blur-[170px]"></div>
    </div>

    {{-- Soft, wide cloud shapes drifting slowly for atmospheric realism --}}
    <div class="brudms-cloud absolute top-[12%] left-[8%] h-40 w-[44rem]">
        <div class="h-full w-full rounded-full bg-white/35 blur-[60px]"></div>
    </div>
    <div class="brudms-cloud absolute top-[40%] right-[4%] h-32 w-[38rem]" style="animation-delay:-24s">
        <div class="h-full w-full rounded-full bg-white/25 blur-[55px]"></div>
    </div>

    <svg class="brudms-aurora-contour absolute inset-0 h-full w-full" viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice" fill="none">
        <g stroke="#0c4a6e" stroke-opacity=".38" stroke-width="3.2">
            <path d="M-50 180C220 60 420 300 700 210s520-140 840-30"/>
            <path d="M-50 260C230 150 430 380 710 290s500-120 830-10"/>
            <path d="M-50 340C240 240 440 460 720 370s480-100 820 10"/>
            <path d="M-50 460C250 350 450 580 730 480s470-90 810 20"/>
            <path d="M-50 580C260 470 460 700 740 600s460-80 800 30"/>
            <path d="M-50 700C270 590 470 810 750 710s450-70 790 40"/>
        </g>
        <g stroke="#134e4a" stroke-opacity=".32" stroke-width="2.8">
            <path d="M-50 120C300 240 520 20 820 140s420 240 720 120"/>
            <path d="M-50 820C300 930 540 690 840 810s400 200 700 90"/>
        </g>
    </svg>

    <div class="brudms-aurora-grain absolute inset-0 opacity-[0.035] mix-blend-multiply" style="background-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.85' numOctaves='3'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='.35'/%3E%3C/svg%3E&quot;);"></div>
</div>
