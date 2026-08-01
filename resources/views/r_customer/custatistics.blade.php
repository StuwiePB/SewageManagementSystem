<x-layouts::customer :title="__('Customer Statistics') . ' – BruDMS'" :bare="true">
    @php
        $user = auth()->user();
        $chartLabels = $chartLabels ?? [];
        $chartValues = $chartValues ?? [];
        $chartDayIso = $chartDayIso ?? [];
        $barChartMax = (count($chartValues) > 0) ? max(1, ...$chartValues) : 1;
        $statusReportLabels = $statusReportLabels ?? [__('Pending'), __('In progress'), __('Resolved')];
        $weekdayOptions = [
            1 => __('Monday'),
            2 => __('Tuesday'),
            3 => __('Wednesday'),
            4 => __('Thursday'),
            5 => __('Friday'),
            6 => __('Saturday'),
            7 => __('Sunday'),
        ];
        $statusByDay = $statusByDay ?? [];
        $statusReportValues = $statusReportValues ?? [0, 0, 0];
        if (is_array($statusByDay) && count($statusByDay) > 0) {
            $statusReportValues = [
                (int) array_sum(array_column($statusByDay, 0)),
                (int) array_sum(array_column($statusByDay, 1)),
                (int) array_sum(array_column($statusByDay, 2)),
            ];
        }
        $statusTotal = array_sum($statusReportValues);
        $incidentsBarMaxPx = 64;
        $statusRingColors = ['#ff0000', '#FFAE00', '#00FF26'];
        $annulusD = function (float $t0, float $t1) use (&$annulusD): string {
            if ($t1 - $t0 < 1.0E-5) {
                return '';
            }
            if ($t1 - $t0 > 0.5) {
                $m = ($t0 + $t1) / 2;

                return trim($annulusD($t0, $m).' '.$annulusD($m, $t1));
            }
            $cx = 50.0;
            $cy = 50.0;
            $R = 50.0;
            $r = 14.0;
            $a0 = $t0 * 2 * M_PI - M_PI / 2;
            $a1 = $t1 * 2 * M_PI - M_PI / 2;
            $xo0 = $cx + $R * cos($a0);
            $yo0 = $cy + $R * sin($a0);
            $xo1 = $cx + $R * cos($a1);
            $yo1 = $cy + $R * sin($a1);
            $xi0 = $cx + $r * cos($a0);
            $yi0 = $cy + $r * sin($a0);
            $xi1 = $cx + $r * cos($a1);
            $yi1 = $cy + $r * sin($a1);
            $large = ($t1 - $t0) > 0.5 ? 1 : 0;

            return sprintf(
                'M%F,%F A%F,%F 0 %d 1 %F,%F L%F,%F A%F,%F 0 %d 0 %F,%F Z',
                $xo0, $yo0, $R, $R, $large, $xo1, $yo1, $xi1, $yi1, $r, $r, $large, $xi0, $yi0
            );
        };
        $initSliceD = ['', '', ''];
        $initPcts = [0, 0, 0];
        if ($statusTotal > 0) {
            $t1 = $statusReportValues[0] / $statusTotal;
            $t2 = $t1 + $statusReportValues[1] / $statusTotal;
            $initSliceD[0] = $annulusD(0, $t1);
            $initSliceD[1] = $annulusD($t1, $t2);
            $initSliceD[2] = $annulusD($t2, 1);
            for ($si = 0; $si < 3; $si++) {
                $initPcts[$si] = (int) round(100 * $statusReportValues[$si] / $statusTotal);
            }
        }
    @endphp

    @push('styles')
        <style>
            #status-donut-svg .status-slice-path { cursor: pointer; transition: filter 0.1s ease, opacity 0.1s ease; }
            #status-donut-svg .status-slice-path:hover { filter: brightness(1.15); }
            .status-legend-dot:hover { filter: brightness(1.2); }
            #status-slice-floater {
                position: absolute; z-index: 5; display: none; padding: 5px 9px; border-radius: 8px; pointer-events: none;
                background: rgba(6, 10, 22, 0.92); border: 0.7px solid rgba(255,255,255,0.18);
                color: rgba(255,255,255,0.95); font-family: Poppins, sans-serif; font-size: 10px; font-weight: 600; white-space: nowrap;
                box-shadow: 0 4px 16px rgba(0,0,0,0.35); min-width: 0;
            }
        </style>
    @endpush

    <div style="position: fixed; top: 4vh; left: 20px; right: 20px; z-index: 10; display: flex; align-items: center; gap: 6px;">
        <a href="{{ route('customer.dashboard', ['name' => $user->profileSlug()]) }}" class="press-btn delayed-nav" style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 9999px; text-decoration: none; transition: transform 0.1s ease;" aria-label="{{ __('Back') }}">
            <img src="{{ asset('images/Vectors/all_backarrow.svg') }}" alt="" style="width: 21px; height: 21px;" />
        </a>
        <span class="cust-title" style="font-size: 16px; font-weight: 600; font-family: Poppins, sans-serif;">Statistics</span>
    </div>

    <div style="position: fixed; top: 12vh; left: 22px; right: 22px; bottom: 20px; z-index: 5; overflow-y: auto; -webkit-overflow-scrolling: touch;">
        <span style="color: rgba(255, 255, 255, 0.72); font-size: 14px; font-weight: 700; font-family: Poppins, sans-serif; display: block; margin-bottom: 10px; margin-left: 8px;">{{ __('Incidents last 7 days') }}</span>
        <div class="cust-stat-panel" style="position: relative; width: 100%; margin-top: 6px; height: 15vh; min-height: 132px; border-radius: 9px; backdrop-filter: blur(1.5px); padding: 3px; box-sizing: border-box;">
            <div style="width: 100%; height: 100%; min-height: 120px; border-radius: 7px; outline: 0.7px solid rgba(255, 255, 255, 0.21); overflow: hidden; position: relative; padding: 12px 10px 10px; box-sizing: border-box;">
                <svg style="position: absolute; inset: 0; width: 100%; height: 100%;" preserveAspectRatio="none">
                    <defs>
                        <pattern id="grid-custatistics" width="36" height="30" patternUnits="userSpaceOnUse">
                            <path d="M 36 0 L 0 0 0 30" fill="none" stroke="rgba(255,255,255,0.07)" stroke-width="0.7"/>
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#grid-custatistics)" />
                </svg>
                <div style="position: relative; z-index: 1; height: 100%; display: flex; flex-direction: column;">
                    <div style="color: rgba(255,255,255,0.65); font-family: Poppins, sans-serif; font-size: 10px; font-weight: 600; line-height: 1;">{{ __('Total:') }} {{ array_sum($chartValues) }} {{ __('incidents') }}</div>
                    <div style="margin-top: 17px; display: flex; align-items: flex-end; justify-content: space-between; gap: 6px; flex: 1; min-height: 0;">
                        @foreach($chartValues as $i => $v)
                            @php
                                $h = (int) round(($v / $barChartMax) * $incidentsBarMaxPx);
                                $height = $v > 0 ? max(10, $h) : 0;
                            @endphp
                            <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; gap: 4px;">
                                <span style="color: rgba(255,255,255,0.6); font-family: Poppins, sans-serif; font-size: 8px; line-height: 1;">{{ $v }}</span>
                                <div style="width: 100%; max-width: 18px; height: {{ $height }}px; border-radius: 6px 6px 2px 2px; background: linear-gradient(to top, rgba(4,188,255,0.68), rgba(4,188,255,0.95)); box-shadow: 0 0 6px rgba(4,188,255,0.3);"></div>
                                <span style="color: rgba(255,255,255,0.58); font-family: Poppins, sans-serif; font-size: 8px; line-height: 1;">{{ $chartLabels[$i] ?? '' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-top: 20px; margin-bottom: 10px; margin-left: 8px; margin-right: 2px;">
            <span style="color: rgba(255, 255, 255, 0.72); font-size: 14px; font-weight: 700; font-family: Poppins, sans-serif; flex: 1; min-width: 0; line-height: 1.25;">{{ __('Status report') }}</span>
            @php
                $statusDowOptions = ['all' => __('All')] + $weekdayOptions;
            @endphp
            <x-custom-dropdown
                id="status-dow"
                value="all"
                :options="$statusDowOptions"
                :aria-label="__('Filter status by day of week')"
            />
        </div>
        <div class="cust-stat-panel" style="position: relative; width: 100%; margin-top: 6px; height: min(28vh, 240px); min-height: 200px; border-radius: 9px; backdrop-filter: blur(1.5px); padding: 3px; box-sizing: border-box;">
            <div style="width: 100%; height: 100%; min-height: 188px; border-radius: 7px; outline: 0.7px solid rgba(255, 255, 255, 0.21); overflow: hidden; position: relative; padding: 12px 10px 10px; box-sizing: border-box;">
                <svg style="position: absolute; inset: 0; width: 100%; height: 100%;" preserveAspectRatio="none">
                    <defs>
                        <pattern id="grid-custatistics-status" width="36" height="30" patternUnits="userSpaceOnUse">
                            <path d="M 36 0 L 0 0 0 30" fill="none" stroke="rgba(255,255,255,0.07)" stroke-width="0.7"/>
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#grid-custatistics-status)" />
                </svg>
                <div style="position: relative; z-index: 1; height: 100%; display: flex; flex-direction: column;">
                    <div style="color: rgba(255,255,255,0.65); font-family: Poppins, sans-serif; font-size: 10px; font-weight: 600; line-height: 1;">{{ __('Last 7 days') }} — <span id="status-line-total">{{ $statusTotal }}</span> {{ __('total') }}</div>
                    <div style="margin-top: 14px; flex: 1; min-height: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 22px; padding: 4px 0 8px;">
                        <div
                            id="status-donut-wrap"
                            role="img"
                            aria-label="{{ __('Status report') }}: {{ $statusReportValues[0] }} {{ __('Pending') }}, {{ $statusReportValues[1] }} {{ __('In progress') }}, {{ $statusReportValues[2] }} {{ __('Resolved') }}"
                            style="position: relative; width: min(32vw, 110px); height: min(32vw, 110px); flex-shrink: 0; touch-action: manipulation;"
                        >
                            <div id="status-slice-floater" role="status" aria-live="polite" aria-hidden="true"></div>
                            <svg
                                id="status-donut-svg"
                                viewBox="0 0 100 100"
                                width="100%"
                                height="100%"
                                style="position: absolute; inset: 0; z-index: 1; filter: drop-shadow(0 0 0.5px rgba(255,255,255,0.1)) drop-shadow(0 4px 12px rgba(0,0,0,0.3));"
                                aria-hidden="true"
                            >
                                <path
                                    id="status-slice-bg"
                                    d="{{ $annulusD(0, 1) }}"
                                    fill="rgba(107, 114, 128, 0.55)"
                                    style="pointer-events: none; {{ $statusTotal > 0 ? 'display: none' : '' }}"
                                />
                                @for ($i = 0; $i < 3; $i++)
                                    <path
                                        id="status-slice-{{ $i }}"
                                        class="status-slice-path"
                                        data-slice-idx="{{ $i }}"
                                        d="{{ $initSliceD[$i] ?: 'M0,0' }}"
                                        fill="{{ $statusRingColors[$i] }}"
                                        @if($statusTotal < 1 || $initSliceD[$i] === '') style="display: none" @endif
                                    >
                                        <title id="status-slice-tt-{{ $i }}">@if($statusTotal > 0){{ $statusReportLabels[$i] ?? '' }}: {{ $initPcts[$i] }}%@endif</title>
                                    </path>
                                @endfor
                            </svg>
                            <div style="position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); width: 56%; height: 56%; border-radius: 50%; background: #060A16; box-shadow: inset 0 0 0 1px rgba(255,255,255,0.06); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 1px; z-index: 2; pointer-events: none;">
                                <span id="status-donut-total" style="color: rgba(255,255,255,0.92); font-family: Poppins, sans-serif; font-size: 16px; font-weight: 700; line-height: 1;">{{ $statusTotal }}</span>
                                <span style="color: rgba(255,255,255,0.45); font-family: Poppins, sans-serif; font-size: 7px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.04em;">{{ __('Total') }}</span>
                            </div>
                        </div>
                        <div id="status-legend" style="width: 100%; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-around; gap: 8px 12px; margin-top: 4px; padding: 0 4px 2px;">
                            @foreach($statusReportValues as $i => $v)
                                <div class="status-legend-row" style="display: flex; align-items: center; gap: 6px; min-width: 0; cursor: default;">
                                    <span class="status-legend-dot" data-slice="{{ $i }}" style="width: 8px; height: 8px; border-radius: 9999px; flex-shrink: 0; background: {{ $statusRingColors[$i] }}; box-shadow: 0 0 0 1px rgba(0,0,0,0.2); cursor: pointer; touch-action: manipulation;"></span>
                                    <div style="display: flex; flex-direction: column; min-width: 0;">
                                        <span style="color: rgba(255,255,255,0.5); font-family: Poppins, sans-serif; font-size: 7px; font-weight: 500; line-height: 1.1;">{{ $statusReportLabels[$i] ?? '' }}</span>
                                        <span class="status-legend-n" data-i="{{ $i }}" style="color: rgba(255,255,255,0.85); font-family: Poppins, sans-serif; font-size: 12px; font-weight: 700; line-height: 1.1;">{{ $v }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (function () {
                var statusByDay = @json($statusByDay);
                var dayIso = @json($chartDayIso);
                var colors = @json($statusRingColors);
                var allVals = @json($statusReportValues);
                var ariaL = {!! json_encode([
                    'h' => __('Status report'),
                    'p' => __('Pending'),
                    'i' => __('In progress'),
                    'r' => __('Resolved'),
                ]) !!};
                var sel = document.getElementById('status-dow');
                var totalEl = document.getElementById('status-donut-total');
                var lineTotal = document.getElementById('status-line-total');
                var wrap = document.getElementById('status-donut-wrap');
                var floater = document.getElementById('status-slice-floater');
                var bg = document.getElementById('status-slice-bg');
                var sliceLabels = [ariaL.p, ariaL.i, ariaL.r];
                if (!sel) return;

                function indexForIso(n) {
                    for (var i = 0; i < dayIso.length; i++) {
                        if (dayIso[i] === n) return i;
                    }
                    return -1;
                }

                function annulusPath(t0, t1) {
                    if (t1 - t0 < 1e-5) return '';
                    if (t1 - t0 > 0.5) {
                        var m = (t0 + t1) / 2;
                        return (annulusPath(t0, m) + ' ' + annulusPath(m, t1)).trim();
                    }
                    var cx = 50, cy = 50, R = 50, r = 14;
                    var a0 = t0 * 2 * Math.PI - Math.PI / 2, a1 = t1 * 2 * Math.PI - Math.PI / 2;
                    var xo0 = cx + R * Math.cos(a0), yo0 = cy + R * Math.sin(a0);
                    var xo1 = cx + R * Math.cos(a1), yo1 = cy + R * Math.sin(a1);
                    var xi0 = cx + r * Math.cos(a0), yi0 = cy + r * Math.sin(a0);
                    var xi1 = cx + r * Math.cos(a1), yi1 = cy + r * Math.sin(a1);
                    var large = t1 - t0 > 0.5 ? 1 : 0;
                    return 'M' + xo0 + ',' + yo0 + ' A' + R + ',' + R + ' 0 ' + large + ' 1 ' + xo1 + ',' + yo1 + ' L' + xi1 + ',' + yi1 + ' A' + r + ',' + r + ' 0 ' + large + ' 0 ' + xi0 + ',' + yi0 + ' Z';
                }

                function pickValues() {
                    var mode = String(sel.value || 'all');
                    if (mode === 'all') {
                        if (allVals && allVals.length === 3) {
                            return [allVals[0], allVals[1], allVals[2]];
                        }
                        var a = 0, b = 0, c = 0, j, row;
                        for (j = 0; j < (statusByDay ? statusByDay.length : 0); j++) {
                            row = statusByDay[j];
                            if (row) {
                                a += row[0] || 0;
                                b += row[1] || 0;
                                c += row[2] || 0;
                            }
                        }
                        return [a, b, c];
                    }
                    var iso = parseInt(mode, 10);
                    if (isNaN(iso) || !statusByDay || !statusByDay.length) return [0, 0, 0];
                    var idx = indexForIso(iso);
                    if (idx < 0 || !statusByDay[idx]) return [0, 0, 0];
                    return [statusByDay[idx][0] || 0, statusByDay[idx][1] || 0, statusByDay[idx][2] || 0];
                }

                function hideFloater() {
                    if (!floater) return;
                    floater.style.display = 'none';
                    floater.setAttribute('aria-hidden', 'true');
                }

                function showSliceTip(text, e) {
                    if (!floater || !wrap) return;
                    e.stopPropagation();
                    floater.textContent = text;
                    floater.style.display = 'block';
                    floater.setAttribute('aria-hidden', 'false');
                    var r = wrap.getBoundingClientRect();
                    var cl = e.clientX, ct = e.clientY;
                    if (e.changedTouches && e.changedTouches[0]) {
                        cl = e.changedTouches[0].clientX;
                        ct = e.changedTouches[0].clientY;
                    }
                    floater.style.left = (cl - r.left) + 'px';
                    floater.style.top = (ct - r.top) + 'px';
                    floater.style.transform = 'translate(-50%, calc(-100% - 6px))';
                    setTimeout(function () {
                        document.addEventListener(
                            'click',
                            function () {
                                hideFloater();
                            },
                            { once: true, capture: true }
                        );
                    }, 0);
                }

                function render() {
                    hideFloater();
                    var p = pickValues();
                    var total = p[0] + p[1] + p[2];
                    if (lineTotal) lineTotal.textContent = String(total);
                    if (totalEl) totalEl.textContent = String(total);
                    var k, ii, els, el, tt, t0, t1, d, pi;
                    els = document.querySelectorAll('.status-legend-n');
                    for (k = 0; k < els.length; k++) {
                        ii = parseInt(els[k].getAttribute('data-i'), 10);
                        if (!isNaN(ii) && p[ii] != null) els[k].textContent = String(p[ii]);
                    }
                    for (k = 0; k < 3; k++) {
                        el = document.getElementById('status-slice-' + k);
                        tt = document.getElementById('status-slice-tt-' + k);
                        var dot = document.querySelector('.status-legend-dot[data-slice="' + k + '"]');
                        pi = total > 0 ? Math.round((100 * p[k]) / total) : 0;
                        if (total < 1 || p[k] < 1) {
                            if (el) { el.setAttribute('d', 'M0,0'); el.style.display = 'none'; }
                            if (tt) tt.textContent = total > 0 ? sliceLabels[k] + ': 0%' : '';
                            if (dot) dot.setAttribute('title', sliceLabels[k] + ': 0%');
                        } else {
                            t0 = k === 0 ? 0 : k === 1 ? p[0] / total : (p[0] + p[1]) / total;
                            t1 = k === 0 ? p[0] / total : k === 1 ? (p[0] + p[1]) / total : 1;
                            d = annulusPath(t0, t1);
                            if (el) { el.setAttribute('d', d); el.style.display = ''; }
                            if (tt) tt.textContent = sliceLabels[k] + ': ' + pi + '%';
                            if (dot) dot.setAttribute('title', sliceLabels[k] + ': ' + p[k] + ' (' + pi + '%)');
                        }
                    }
                    if (bg) bg.style.display = total < 1 ? '' : 'none';
                    if (wrap && ariaL) {
                        wrap.setAttribute(
                            'aria-label',
                            ariaL.h + ': ' + p[0] + ' ' + ariaL.p + ', ' + p[1] + ' ' + ariaL.i + ', ' + p[2] + ' ' + ariaL.r
                        );
                    }
                }

                if (wrap) {
                    wrap.addEventListener('click', function (e) {
                        var t = e.target;
                        if (!t) return;
                        if (t.classList && t.classList.contains('status-slice-path')) {
                            var i = parseInt(t.getAttribute('data-slice-idx'), 10);
                            if (isNaN(i)) return;
                            var pv = pickValues();
                            var tot = pv[0] + pv[1] + pv[2];
                            if (!tot || !pv[i]) return;
                            showSliceTip(sliceLabels[i] + ': ' + Math.round((100 * pv[i]) / tot) + '%', e);
                        }
                    });
                }
                document.querySelectorAll('.status-legend-dot').forEach(function (dot) {
                    dot.addEventListener('click', function (e) {
                        e.stopPropagation();
                        var i = parseInt(dot.getAttribute('data-slice'), 10);
                        if (isNaN(i)) return;
                        var pv = pickValues();
                        var tot = pv[0] + pv[1] + pv[2];
                        if (!tot) {
                            showSliceTip(sliceLabels[i] + ': 0%', e);
                        } else {
                            showSliceTip(sliceLabels[i] + ': ' + Math.round((100 * pv[i]) / tot) + '%', e);
                        }
                    });
                });

                sel.addEventListener('change', render);
                render();
            })();
        </script>
    @endpush
</x-layouts::customer>
