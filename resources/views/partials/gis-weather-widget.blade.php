@php
    $gisWeatherUrl = $gisWeatherUrl ?? route('gis.weather');
    $mapWeatherInitial = $mapWeather ?? ($weather ?? null);
@endphp
<style>
    .gis-weather-widget {
        position: absolute;
        top: 12px;
        right: 12px;
        z-index: 950;
        width: min(200px, calc(100% - 24px));
        padding: 0.55rem 0.65rem;
        border-radius: 10px;
        background: rgba(15, 23, 42, 0.88);
        border: 1px solid rgba(110, 207, 240, 0.35);
        color: #f8fafc;
        font-size: 0.72rem;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
        pointer-events: auto;
        backdrop-filter: blur(8px);
    }
    .gis-weather-widget.is-livemap {
        position: fixed;
        top: calc(12px + env(safe-area-inset-top, 0px));
        right: 12px;
        z-index: 1002;
    }
    .gis-weather-widget h5 {
        margin: 0 0 0.35rem;
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--brudms-primary, #6ECFF0);
        letter-spacing: 0.02em;
    }
    .gis-weather-widget .gis-weather-location {
        margin: 0 0 0.4rem;
        color: #94a3b8;
        font-size: 0.68rem;
        line-height: 1.3;
    }
    .gis-weather-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.35rem;
        margin-bottom: 0.3rem;
    }
    .gis-weather-row:last-child { margin-bottom: 0; }
    .gis-weather-label { font-weight: 600; color: #e2e8f0; }
    .gis-weather-pct { font-weight: 700; min-width: 2.2rem; text-align: right; }
    .gis-weather-bar {
        flex: 1;
        height: 6px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.12);
        overflow: hidden;
    }
    .gis-weather-bar-fill {
        height: 100%;
        border-radius: 999px;
        transition: width 0.4s ease;
    }
    .gis-weather-bar-fill.heavy { background: linear-gradient(90deg, #ca8a04, #eab308); }
    .gis-weather-bar-fill.light { background: linear-gradient(90deg, #38bdf8, #6ECFF0); }
    .gis-weather-condition {
        margin-top: 0.4rem;
        padding-top: 0.35rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        color: #cbd5e1;
        font-size: 0.68rem;
        line-height: 1.35;
    }
    .gis-weather-routing {
        margin-top: 0.25rem;
        color: #fde047;
        font-weight: 600;
    }
    .gis-weather-widget.is-unavailable .gis-weather-pct { color: #94a3b8; }
</style>
<script>
window.BruneiGisWeather = {
    url: @json($gisWeatherUrl),
    pollMs: 300000,
    initial: @json($mapWeatherInitial),

    init: function (map, options) {
        options = options || {};
        var host = options.hostId ? document.getElementById(options.hostId) : null;
        var widget = document.createElement('div');
        widget.className = 'gis-weather-widget' + (options.livemap ? ' is-livemap' : '');
        widget.setAttribute('role', 'status');
        widget.setAttribute('aria-live', 'polite');
        widget.innerHTML = ''
            + '<h5>Brunei weather</h5>'
            + '<p class="gis-weather-location" data-role="location">Live forecast</p>'
            + '<div class="gis-weather-row">'
            + '<span class="gis-weather-label">Heavy rain</span>'
            + '<span class="gis-weather-pct" data-role="heavy-pct">—</span>'
            + '</div>'
            + '<div class="gis-weather-bar"><div class="gis-weather-bar-fill heavy" data-role="heavy-bar" style="width:0%;"></div></div>'
            + '<div class="gis-weather-row" style="margin-top:0.35rem;">'
            + '<span class="gis-weather-label">Light rain</span>'
            + '<span class="gis-weather-pct" data-role="light-pct">—</span>'
            + '</div>'
            + '<div class="gis-weather-bar"><div class="gis-weather-bar-fill light" data-role="light-bar" style="width:0%;"></div></div>'
            + '<div class="gis-weather-condition" data-role="condition"></div>';

        if (host) {
            if (window.getComputedStyle(host).position === 'static') {
                host.style.position = 'relative';
            }
            host.appendChild(widget);
        } else {
            document.body.appendChild(widget);
        }

        var self = this;

        function render(w) {
            w = w || {};
            var heavy = Math.max(0, Math.min(100, parseInt(w.heavy_rain_pct, 10) || 0));
            var light = Math.max(0, Math.min(100, parseInt(w.light_rain_pct, 10) || 0));
            var loc = w.location || 'Brunei';
            var label = w.condition_label || (w.available === false ? 'Unavailable' : '—');
            var precip = w.precip_mm != null ? w.precip_mm + ' mm now' : '';
            var chance = w.rain_chance_today_pct != null ? w.rain_chance_today_pct + '% rain chance today' : '';

            widget.classList.toggle('is-unavailable', w.available === false);
            widget.querySelector('[data-role="location"]').textContent = loc + ' · Open-Meteo';
            widget.querySelector('[data-role="heavy-pct"]').textContent = heavy + '%';
            widget.querySelector('[data-role="light-pct"]').textContent = light + '%';
            widget.querySelector('[data-role="heavy-bar"]').style.width = heavy + '%';
            widget.querySelector('[data-role="light-bar"]').style.width = light + '%';

            var condEl = widget.querySelector('[data-role="condition"]');
            var parts = [label];
            if (precip) { parts.push(precip); }
            if (chance) { parts.push(chance); }
            if (w.impacts_routing) {
                parts.push('<span class="gis-weather-routing">Routing uses live rain data</span>');
            }
            condEl.innerHTML = parts.join(' · ');

            document.dispatchEvent(new CustomEvent('brudms:weather-update', {
                detail: { weather: w, layers: null }
            }));
        }

        function fetchWeather() {
            var lat = map && map.getCenter ? map.getCenter().lat : null;
            var lng = map && map.getCenter ? map.getCenter().lng : null;
            var qs = (lat != null && lng != null)
                ? '?lat=' + encodeURIComponent(lat) + '&lng=' + encodeURIComponent(lng)
                : '';
            return fetch(self.url + qs, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.weather) {
                        render(data.weather);
                    }
                    if (data.layers && window.BruneiGisLayers && window.BruneiGisLayers.refreshPayload) {
                        window.BruneiGisLayers.refreshPayload(data.layers);
                    }
                    document.dispatchEvent(new CustomEvent('brudms:weather-update', {
                        detail: { weather: data.weather, layers: data.layers }
                    }));
                })
                .catch(function () { /* keep last values */ });
        }

        if (this.initial) {
            render(this.initial);
        }
        fetchWeather();
        setInterval(fetchWeather, this.pollMs);

        if (map) {
            map.on('moveend', function () {
                clearTimeout(self._moveTimer);
                self._moveTimer = setTimeout(fetchWeather, 800);
            });
        }

        return { widget: widget, refresh: fetchWeather };
    }
};
</script>
