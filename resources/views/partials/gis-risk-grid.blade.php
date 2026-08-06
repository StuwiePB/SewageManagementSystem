<style>
    .risk-grid-panel {
        position: absolute;
        top: 12px;
        right: 12px;
        z-index: 900;
        width: 280px;
        max-width: calc(100% - 24px);
        max-height: calc(100% - 24px);
        overflow-y: auto;
        background: var(--bg-secondary, rgba(15, 17, 26, 0.94));
        border: 1px solid rgba(106, 150, 255, 0.25);
        border-radius: 12px;
        padding: 0.9rem;
        color: var(--text-primary, #e5e7eb);
        font-size: 0.8rem;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
    }
    .risk-grid-panel h4 { margin: 0 0 0.5rem; font-size: 0.85rem; color: var(--text-primary, #fff); }
    .risk-grid-legend { display: flex; flex-direction: column; gap: 0.3rem; margin-bottom: 0.75rem; }
    .risk-grid-legend-item { display: flex; align-items: center; gap: 0.4rem; text-transform: capitalize; }
    .risk-grid-legend-swatch { width: 0.75rem; height: 0.75rem; border-radius: 3px; flex-shrink: 0; }
    .risk-grid-counts { display: flex; gap: 0.4rem; margin-bottom: 0.75rem; flex-wrap: wrap; }
    .risk-grid-count-chip { padding: 0.2rem 0.5rem; border-radius: 999px; font-weight: 700; font-size: 0.72rem; text-transform: capitalize; }
    .risk-grid-slider-row { margin-bottom: 0.75rem; }
    .risk-grid-slider-row input[type="range"] { width: 100%; }
    .risk-grid-slider-label { display: flex; justify-content: space-between; font-size: 0.72rem; color: var(--text-secondary, #94a3b8); margin-bottom: 0.25rem; }
    .risk-grid-detail { border-top: 1px solid rgba(255, 255, 255, 0.12); padding-top: 0.6rem; margin-top: 0.2rem; }
    .risk-grid-detail-empty { color: var(--text-secondary, #94a3b8); font-style: italic; margin: 0; }
    .risk-grid-factor-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.3rem; gap: 0.5rem; }
    .risk-grid-factor-name { width: 62px; text-transform: capitalize; flex-shrink: 0; }
    .risk-grid-factor-bar-wrap { flex: 1; height: 6px; background: rgba(255, 255, 255, 0.1); border-radius: 4px; overflow: hidden; }
    .risk-grid-factor-bar { height: 100%; background: var(--accent-blue, #6a96ff); }
    .risk-grid-factor-value { width: 44px; text-align: right; flex-shrink: 0; }
    .risk-grid-disclaimer { margin-top: 0.6rem; padding-top: 0.5rem; border-top: 1px solid rgba(255, 255, 255, 0.12); font-size: 0.68rem; line-height: 1.35; color: var(--text-secondary, #94a3b8); }
    .risk-grid-rain-icon { display: flex; align-items: center; justify-content: center; width: 16px; height: 16px; border-radius: 50%; background: rgba(56, 132, 255, 0.85); box-shadow: 0 0 0 2px rgba(255,255,255,0.5); font-size: 9px; line-height: 1; pointer-events: none; }
    .risk-grid-slide-icon { display: flex; align-items: center; justify-content: center; width: 18px; height: 18px; border-radius: 4px; background: rgba(120, 72, 24, 0.92); box-shadow: 0 0 0 2px rgba(255,255,255,0.5); font-size: 10px; line-height: 1; pointer-events: none; }
</style>
<script src="https://unpkg.com/h3-js@4.5.0/dist/h3-js.umd.js"></script>
<script>
window.RiskGrid = {
    _instances: {},

    /**
     * Colour ramp for the 4 risk bands — matches RiskScoreService::band() bucketing exactly.
     * The client only buckets an already-computed score (or re-buckets a cached forecast-hour
     * score when the slider moves); it never recomputes the score itself.
     */
    BAND_COLORS: {
        low: '#FAEEDA',
        moderate: '#FAC775',
        high: '#EF9F27',
        critical: '#A32D2D'
    },

    BAND_RANK: { low: 0, moderate: 1, high: 2, critical: 3 },

    /** How many forecast hours ahead count as "about to rain" for the dashed early-warning outline. */
    LOOKAHEAD_HOURS: 6,

    /** mm/hour at/above which a cell is marked with the "raining now/then" badge at a given hour. */
    RAIN_VISIBLE_MM: 0.2,

    bandForScore: function (score) {
        if (score >= 75) return 'critical';
        if (score >= 50) return 'high';
        if (score >= 25) return 'moderate';
        return 'low';
    },

    init: function (map, options) {
        options = options || {};
        var instanceKey = options.instanceKey || 'default';
        if (this._instances[instanceKey]) {
            return this._instances[instanceKey];
        }

        var self = this;
        var BAND_COLORS = this.BAND_COLORS;
        var indexUrl = options.indexUrl || '{{ route('risk-grid.index') }}';
        var showUrlBase = options.showUrlBase || '{{ url('/api/risk-grid') }}';
        var panelHost = options.panelHostId ? document.getElementById(options.panelHostId) : null;

        var terrainLayer = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
            maxZoom: 17,
            attribution: 'Map data: &copy; OpenStreetMap, SRTM | Style: OpenTopoMap'
        });
        var hexLayer = L.layerGroup();
        var rainLayer = L.layerGroup();
        var landslideLayer = L.layerGroup();
        var satelliteLayer = options.satelliteLayer || null;
        var terrainActive = false;
        var payload = null;

        function escapeHtml(v) {
            return String(v == null ? '' : v)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        }

        function scoreAtHour(cell, hour) {
            if (cell.forecast_scores && cell.forecast_scores.length > hour) {
                return cell.forecast_scores[hour];
            }

            return cell.score;
        }

        function rainfallAtHour(cell, hour) {
            if (cell.forecast_rainfall_mm && cell.forecast_rainfall_mm.length > hour) {
                return cell.forecast_rainfall_mm[hour];
            }

            return 0;
        }

        /** "now" / "+3h" / "-2h" relative to the grid's shared now_index, for the slider label. */
        function hourLabel(hour) {
            var nowIndex = (payload && payload.now_index) || 0;
            var offset = hour - nowIndex;
            if (offset === 0) return 'now';

            return (offset > 0 ? '+' : '') + offset + 'h';
        }

        /**
         * Looks at the next LOOKAHEAD_HOURS forecast points after `hour` and returns the
         * highest band this cell reaches in that window, if it's a step up from its band at
         * `hour` — used to draw a dashed "rain expected soon" outline even while the cell's fill
         * still reflects present conditions.
         */
        function risingSoonBand(cell, hour) {
            var currentBand = self.bandForScore(scoreAtHour(cell, hour));
            var scores = cell.forecast_scores || [];
            var worstBand = currentBand;

            for (var i = hour + 1; i <= hour + self.LOOKAHEAD_HOURS && i < scores.length; i++) {
                var band = self.bandForScore(scores[i]);
                if (self.BAND_RANK[band] > self.BAND_RANK[worstBand]) {
                    worstBand = band;
                }
            }

            return self.BAND_RANK[worstBand] > self.BAND_RANK[currentBand] ? worstBand : null;
        }

        function renderCounts(hour) {
            if (!panelHost) return;
            var el = panelHost.querySelector('[data-role="risk-grid-counts"]');
            if (!el || !payload) return;

            var counts = { low: 0, moderate: 0, high: 0, critical: 0 };
            (payload.cells || []).forEach(function (cell) {
                counts[self.bandForScore(scoreAtHour(cell, hour))]++;
            });

            el.innerHTML = Object.keys(BAND_COLORS).map(function (band) {
                return '<span class="risk-grid-count-chip" style="background:' + BAND_COLORS[band] + '22;color:' + BAND_COLORS[band] + ';border:1px solid ' + BAND_COLORS[band] + '55;">'
                    + band + ': ' + counts[band] + '</span>';
            }).join('');
        }

        function redrawHour(hour) {
            hexLayer.clearLayers();
            rainLayer.clearLayers();
            if (!payload) return;

            (payload.cells || []).forEach(function (cell) {
                var score = scoreAtHour(cell, hour);
                var band = self.bandForScore(score);
                var soonBand = risingSoonBand(cell, hour);
                var boundary = h3.cellToBoundary(cell.h3_index); // [[lat, lng], ...] — Leaflet-ready

                var style = {
                    color: BAND_COLORS[band],
                    weight: 1,
                    fillColor: BAND_COLORS[band],
                    fillOpacity: 0.45,
                    opacity: 0.75
                };

                if (soonBand) {
                    // Fill stays at the CURRENT band's colour; the dashed outline in the
                    // higher upcoming band's colour is the "rain expected soon" signal.
                    style.color = BAND_COLORS[soonBand];
                    style.weight = 3;
                    style.opacity = 0.95;
                    style.dashArray = '6, 4';
                }

                var polygon = L.polygon(boundary, style);
                polygon.on('click', function () {
                    showDetail(cell.h3_index);
                });
                polygon.addTo(hexLayer);

                // Actual observed/forecast rain at this hour — a distinct signal from the risk
                // band fill, so a cell can visibly show "it's raining here right now" separately
                // from "this cell is at elevated risk" (which also factors in blockages/terrain).
                var rainMm = rainfallAtHour(cell, hour);
                if (rainMm >= self.RAIN_VISIBLE_MM && typeof cell.lat === 'number') {
                    L.marker([cell.lat, cell.lng], {
                        icon: L.divIcon({
                            className: '',
                            html: '<div class="risk-grid-rain-icon" title="' + rainMm.toFixed(1) + 'mm/h">💧</div>',
                            iconSize: [16, 16],
                            iconAnchor: [8, 8]
                        }),
                        interactive: false
                    }).addTo(rainLayer);
                }
            });

            renderCounts(hour);
        }

        /**
         * Slope + rainfall landslide screening markers — drawn once from the grid's current
         * (not per-forecast-hour) snapshot, since landslide_score/band aren't scored across the
         * whole forecast series the way flood risk is. Always paired with LANDSLIDE_DISCLAIMER
         * in the panel so this never reads as a validated geotechnical assessment.
         */
        function renderLandslideLayer() {
            landslideLayer.clearLayers();
            if (!payload) return;

            (payload.cells || []).forEach(function (cell) {
                if ((cell.landslide_band !== 'high' && cell.landslide_band !== 'critical') || typeof cell.lat !== 'number') {
                    return;
                }

                L.marker([cell.lat, cell.lng], {
                    icon: L.divIcon({
                        className: '',
                        html: '<div class="risk-grid-slide-icon" title="Landslide screening: ' + escapeHtml(cell.landslide_band) + ' (' + cell.landslide_score + ')">⛰</div>',
                        iconSize: [18, 18],
                        iconAnchor: [9, 9]
                    })
                })
                    .on('click', function () { showDetail(cell.h3_index); })
                    .addTo(landslideLayer);
            });
        }

        function showDetail(h3Index) {
            if (!panelHost) return;
            var detailEl = panelHost.querySelector('[data-role="risk-grid-detail"]');
            if (!detailEl) return;

            detailEl.innerHTML = '<p class="risk-grid-detail-empty">Loading…</p>';

            fetch(showUrlBase + '/' + encodeURIComponent(h3Index), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    var cell = data.cell || {};
                    var factors = data.factors || {};
                    var landslide = data.landslide || {};

                    var rows = Object.keys(factors).map(function (name) {
                        var f = factors[name] || {};
                        var pct = Math.round((f.value || 0) * 100);

                        return '<div class="risk-grid-factor-row">'
                            + '<span class="risk-grid-factor-name">' + escapeHtml(name) + '</span>'
                            + '<span class="risk-grid-factor-bar-wrap"><span class="risk-grid-factor-bar" style="width:' + pct + '%;"></span></span>'
                            + '<span class="risk-grid-factor-value">+' + f.weighted_contribution + '</span>'
                            + '</div>';
                    }).join('');

                    var landslideHtml = '';
                    if (landslide.band && landslide.band !== 'low') {
                        landslideHtml = '<div class="risk-grid-detail" style="margin-top:0.6rem;">'
                            + '<div style="margin-bottom:0.3rem;">⛰ Landslide screening: ' + landslide.score + ' · <span style="text-transform:capitalize;">' + escapeHtml(landslide.band) + '</span>'
                            + (landslide.slope_pct != null ? ' <span style="color:var(--text-secondary,#94a3b8);">(slope ~' + landslide.slope_pct + '%)</span>' : '')
                            + '</div>'
                            + '<div class="risk-grid-disclaimer" style="margin-top:0;padding-top:0;border-top:none;">' + escapeHtml(landslide.disclaimer || '') + '</div>'
                            + '</div>';
                    }

                    detailEl.innerHTML = '<h4>' + escapeHtml(cell.district || 'Cell') + '</h4>'
                        + '<div style="margin-bottom:0.4rem;color:var(--text-secondary,#94a3b8);">Score ' + cell.score + ' · <span style="text-transform:capitalize;">' + escapeHtml(cell.band) + '</span></div>'
                        + rows
                        + landslideHtml
                        + '<div style="margin-top:0.5rem;font-size:0.7rem;color:var(--text-secondary,#94a3b8);">' + escapeHtml(cell.h3_index) + '</div>';
                })
                .catch(function () {
                    detailEl.innerHTML = '<p class="risk-grid-detail-empty">Could not load this cell.</p>';
                });
        }

        function buildPanel() {
            if (!panelHost) return;

            var legendHtml = Object.keys(BAND_COLORS).map(function (band) {
                return '<span class="risk-grid-legend-item"><span class="risk-grid-legend-swatch" style="background:' + BAND_COLORS[band] + ';"></span>' + band + '</span>';
            }).join('')
                + '<span class="risk-grid-legend-item"><span class="risk-grid-legend-swatch" style="background:transparent;border:2px dashed ' + BAND_COLORS.high + ';"></span>rain expected within ' + self.LOOKAHEAD_HOURS + 'h</span>'
                + '<span class="risk-grid-legend-item">💧 raining (this hour)</span>'
                + '<span class="risk-grid-legend-item">⛰ landslide screening (high/critical)</span>';

            panelHost.innerHTML =
                '<h4>Drainage risk grid</h4>'
                + '<div class="risk-grid-legend">' + legendHtml + '</div>'
                + '<div class="risk-grid-counts" data-role="risk-grid-counts"></div>'
                + '<div class="risk-grid-slider-row">'
                + '<div class="risk-grid-slider-label"><span>Hour (past ↔ forecast)</span><span data-role="risk-grid-slider-time">now</span></div>'
                + '<input type="range" min="0" max="0" value="0" data-role="risk-grid-slider">'
                + '</div>'
                + '<div class="risk-grid-detail" data-role="risk-grid-detail"><p class="risk-grid-detail-empty">Click a hexagon to see why.</p></div>'
                + '<div class="risk-grid-disclaimer" data-role="risk-grid-landslide-disclaimer"></div>';

            panelHost.querySelector('[data-role="risk-grid-slider"]').addEventListener('input', function () {
                var hour = parseInt(this.value, 10) || 0;
                panelHost.querySelector('[data-role="risk-grid-slider-time"]').textContent = hourLabel(hour);
                redrawHour(hour);
            });
        }

        function load() {
            fetch(indexUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    payload = data;
                    var nowIndex = payload.now_index || 0;

                    if (panelHost) {
                        var slider = panelHost.querySelector('[data-role="risk-grid-slider"]');
                        if (slider) {
                            slider.max = Math.max(0, (payload.forecast_times || []).length - 1);
                            slider.value = nowIndex;
                        }
                        var timeEl = panelHost.querySelector('[data-role="risk-grid-slider-time"]');
                        if (timeEl) {
                            timeEl.textContent = hourLabel(nowIndex);
                        }
                        var disclaimerEl = panelHost.querySelector('[data-role="risk-grid-landslide-disclaimer"]');
                        if (disclaimerEl && payload.landslide_disclaimer) {
                            disclaimerEl.textContent = '⛰ ' + payload.landslide_disclaimer;
                        }
                    }

                    redrawHour(nowIndex);
                    renderLandslideLayer();
                })
                .catch(function () {
                    if (panelHost) {
                        var detailEl = panelHost.querySelector('[data-role="risk-grid-detail"]');
                        if (detailEl) {
                            detailEl.innerHTML = '<p class="risk-grid-detail-empty">Could not load the risk grid.</p>';
                        }
                    }
                });
        }

        function setBaseTerrain(on) {
            terrainActive = !!on;
            if (on) {
                if (satelliteLayer && map.hasLayer(satelliteLayer)) {
                    map.removeLayer(satelliteLayer);
                }
                if (!map.hasLayer(terrainLayer)) {
                    terrainLayer.addTo(map);
                }
            } else {
                if (map.hasLayer(terrainLayer)) {
                    map.removeLayer(terrainLayer);
                }
                if (satelliteLayer && !map.hasLayer(satelliteLayer)) {
                    satelliteLayer.addTo(map);
                }
            }
        }

        if (panelHost) {
            buildPanel();
        }
        hexLayer.addTo(map);
        rainLayer.addTo(map);
        landslideLayer.addTo(map);
        load();

        var api = {
            terrainLayer: terrainLayer,
            hexLayer: hexLayer,
            rainLayer: rainLayer,
            landslideLayer: landslideLayer,
            isTerrainActive: function () { return terrainActive; },
            setBaseTerrain: setBaseTerrain,
            toggleGrid: function (on) {
                if (on && !map.hasLayer(hexLayer)) {
                    hexLayer.addTo(map);
                }
                if (!on && map.hasLayer(hexLayer)) {
                    map.removeLayer(hexLayer);
                }
            },
            toggleRain: function (on) {
                if (on && !map.hasLayer(rainLayer)) {
                    rainLayer.addTo(map);
                }
                if (!on && map.hasLayer(rainLayer)) {
                    map.removeLayer(rainLayer);
                }
            },
            toggleLandslide: function (on) {
                if (on && !map.hasLayer(landslideLayer)) {
                    landslideLayer.addTo(map);
                }
                if (!on && map.hasLayer(landslideLayer)) {
                    map.removeLayer(landslideLayer);
                }
            }
        };

        this._instances[instanceKey] = api;

        return api;
    }
};
</script>
