<script>
window.BruneiGisLayers = {
    payload: @json($bruneiGisLayers ?? ['zones' => [], 'legend' => [], 'high_risk_zones' => []]),
    _instances: {},

    refreshPayload: function (newPayload) {
        this.payload = newPayload || this.payload;
        Object.keys(this._instances).forEach(function (key) {
            var inst = window.BruneiGisLayers._instances[key];
            if (inst && inst.redraw) {
                inst.redraw(window.BruneiGisLayers.payload);
            }
        });
    },

    init: function (map, options) {
        options = options || {};
        var instanceKey = options.instanceKey || 'default';
        if (this._instances[instanceKey]) {
            return this._instances[instanceKey];
        }

        var self = this;
        var terrainLayer = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
            maxZoom: 17,
            attribution: 'Map data: &copy; OpenStreetMap, SRTM | Style: OpenTopoMap'
        });
        var riskLayer = L.layerGroup();
        var highRiskLayer = L.layerGroup();
        var satelliteLayer = options.satelliteLayer || null;
        var terrainActive = false;

        function escapeHtml(v) {
            return String(v || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        }

        function zoneColor(z) {
            var level = z.risk_level || z.base_risk_level || 'moderate';
            if (level === 'low') {
                return { fill: z.fill || '#22c55e', stroke: z.stroke || '#15803d' };
            }
            return { fill: z.fill || '#eab308', stroke: z.stroke || '#a16207' };
        }

        function redraw(payload) {
            riskLayer.clearLayers();
            highRiskLayer.clearLayers();
            var zones = (payload && payload.zones) ? payload.zones : [];
            var highZones = (payload && payload.high_risk_zones) ? payload.high_risk_zones : [];

            zones.forEach(function (z) {
                var colors = zoneColor(z);
                var circle = L.circle([z.lat, z.lng], {
                    radius: z.radius_m || 1500,
                    color: colors.stroke,
                    fillColor: colors.fill,
                    fillOpacity: 0.2,
                    weight: 2,
                    opacity: 0.85
                });
                var issues = (z.issues || []).slice(0, 5).map(function (i) {
                    return '<li>' + escapeHtml(i) + '</li>';
                }).join('');
                var rainNote = payload.weather_boost
                    ? '<p style="margin:0.35rem 0 0;color:#fde047;">Rain boost active — zone severity may be elevated.</p>'
                    : '';
                circle.bindPopup(
                    '<div class="gis-risk-popup"><h4>' + escapeHtml(z.name || 'Zone') + '</h4>'
                    + '<div><strong>' + escapeHtml(z.label || 'Risk') + '</strong></div>'
                    + rainNote
                    + (z.notes ? '<p style="margin:0.35rem 0 0;">' + escapeHtml(z.notes) + '</p>' : '')
                    + (issues ? '<ul>' + issues + '</ul>' : '') + '</div>'
                );
                circle.addTo(riskLayer);
            });

            highZones.forEach(function (z) {
                var ring = L.circle([z.lat, z.lng], {
                    radius: (z.radius_m || 1500) * 0.72,
                    color: '#854d0e',
                    fillColor: '#ca8a04',
                    fillOpacity: 0.08,
                    weight: 3,
                    opacity: 0.95,
                    dashArray: '10, 8'
                });
                ring.bindPopup(
                    '<div class="gis-risk-popup"><h4>' + escapeHtml(z.name || 'High-risk segment') + '</h4>'
                    + '<p>Core high-risk drainage area — prioritize inspections during heavy rain.</p></div>'
                );
                ring.addTo(highRiskLayer);
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

        redraw(this.payload);
        riskLayer.addTo(map);
        highRiskLayer.addTo(map);

        document.addEventListener('brudms:weather-update', function (ev) {
            if (ev.detail && ev.detail.layers) {
                self.refreshPayload(ev.detail.layers);
            }
        });

        var api = {
            terrainLayer: terrainLayer,
            riskLayer: riskLayer,
            highRiskLayer: highRiskLayer,
            isTerrainActive: function () { return terrainActive; },
            setBaseTerrain: setBaseTerrain,
            redraw: redraw,
            toggleRisk: function (on) {
                if (on && !map.hasLayer(riskLayer)) {
                    riskLayer.addTo(map);
                }
                if (!on && map.hasLayer(riskLayer)) {
                    map.removeLayer(riskLayer);
                }
                if (on && !map.hasLayer(highRiskLayer)) {
                    highRiskLayer.addTo(map);
                }
                if (!on && map.hasLayer(highRiskLayer)) {
                    map.removeLayer(highRiskLayer);
                }
            }
        };

        this._instances[instanceKey] = api;
        return api;
    }
};
</script>
