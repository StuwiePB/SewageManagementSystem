{{-- Real-refraction "liquid glass" SVG filter, shared by every glass button on the admin
     console (see .btn/.btn-primary/etc in r_admin/layout.blade.php). One filter, not one per
     button — regenerating a per-element displacement map via ResizeObserver for every button
     on every admin page would violate the "never on more than a couple of elements at once"
     performance rule this technique comes with, so this is tuned for a representative
     ~180x48 pill-button footprint rather than pixel-matched to each button's exact size. The
     rim-bending reads correctly across the button family's similar pill/rounded shapes even
     when the literal width differs.

     Chromium-only (feImage + backdrop-filter: url(#...) isn't supported by Safari/Firefox) —
     see the .no-refraction fallback wired up in layout.blade.php's feature-detect script. --}}
<svg class="glass-defs" aria-hidden="true" style="position: absolute; width: 0; height: 0; overflow: hidden;">
    <filter id="glass-rim" x="-20%" y="-40%" width="140%" height="180%" color-interpolation-filters="sRGB">
        <feImage href="data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22180%22%20height%3D%2248%22%3E%3Cdefs%3E%3ClinearGradient%20id%3D%22x%22%20x1%3D%220%22%20y1%3D%220%22%20x2%3D%221%22%20y2%3D%220%22%3E%3Cstop%20offset%3D%220%22%20stop-color%3D%22%23000%22%2F%3E%3Cstop%20offset%3D%221%22%20stop-color%3D%22%23f00%22%2F%3E%3C%2FlinearGradient%3E%3ClinearGradient%20id%3D%22y%22%20x1%3D%220%22%20y1%3D%220%22%20x2%3D%220%22%20y2%3D%221%22%3E%3Cstop%20offset%3D%220%22%20stop-color%3D%22%23000%22%2F%3E%3Cstop%20offset%3D%221%22%20stop-color%3D%22%230f0%22%2F%3E%3C%2FlinearGradient%3E%3C%2Fdefs%3E%3Crect%20width%3D%22180%22%20height%3D%2248%22%20fill%3D%22%23808080%22%2F%3E%3Crect%20width%3D%22180%22%20height%3D%2248%22%20rx%3D%2224%22%20fill%3D%22url%28%23x%29%22%20style%3D%22mix-blend-mode%3Ascreen%22%2F%3E%3Crect%20width%3D%22180%22%20height%3D%2248%22%20rx%3D%2224%22%20fill%3D%22url%28%23y%29%22%20style%3D%22mix-blend-mode%3Ascreen%22%2F%3E%3Crect%20x%3D%2214%22%20y%3D%2214%22%20width%3D%22152%22%20height%3D%2220%22%20rx%3D%2224%22%20fill%3D%22%23808080%22%20style%3D%22filter%3Ablur%285px%29%22%2F%3E%3C%2Fsvg%3E" result="map"/>

        {{-- Map's inner rect uses rx=24, so base scale sits at ~0.42x that (within the
             0.35-0.5x radius guideline) — chromatic offset kept to ±8% of the base 10. --}}
        {{-- red channel, slightly larger scale --}}
        <feDisplacementMap in="SourceGraphic" in2="map" scale="10.8" xChannelSelector="R" yChannelSelector="G" result="dR"/>
        <feColorMatrix in="dR" type="matrix" result="R" values="1 0 0 0 0  0 0 0 0 0  0 0 0 0 0  0 0 0 1 0"/>

        {{-- green channel, base scale --}}
        <feDisplacementMap in="SourceGraphic" in2="map" scale="10" xChannelSelector="R" yChannelSelector="G" result="dG"/>
        <feColorMatrix in="dG" type="matrix" result="G" values="0 0 0 0 0  0 1 0 0 0  0 0 0 0 0  0 0 0 1 0"/>

        {{-- blue channel, slightly smaller scale --}}
        <feDisplacementMap in="SourceGraphic" in2="map" scale="9.2" xChannelSelector="R" yChannelSelector="G" result="dB"/>
        <feColorMatrix in="dB" type="matrix" result="B" values="0 0 0 0 0  0 0 0 0 0  0 0 1 0 0  0 0 0 1 0"/>

        <feBlend in="R" in2="G" mode="screen" result="RG"/>
        <feBlend in="RG" in2="B" mode="screen" result="RGB"/>
        <feGaussianBlur in="RGB" stdDeviation="0.4"/>
    </filter>
</svg>
