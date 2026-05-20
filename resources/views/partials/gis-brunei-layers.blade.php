<script>
window.BruneiGisLayers = {
    payload: @json($bruneiGisLayers ?? ['zones' => [], 'legend' => []]),

    init: function (map, options) {
        options = options || {};
        var zones = (this.payload && this.payload.zones) ? this.payload.zones : [];
        var terrainLayer = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
            maxZoom: 17,
            attribution: 'Map data: &copy; OpenStreetMap, SRTM | Style: OpenTopoMap'
        });
        var riskLayer = L.layerGroup();
        var satelliteLayer = options.satelliteLayer || null;
        var terrainActive = false;

        function escapeHtml(v) {
            return String(v || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        }

        zones.forEach(function (z) {
            var circle = L.circle([z.lat, z.lng], {
                radius: z.radius_m || 1500,
                color: z.stroke || '#b91c1c',
                fillColor: z.fill || '#dc2626',
                fillOpacity: 0.22,
                weight: 2,
                opacity: 0.85
            });
            var issues = (z.issues || []).slice(0, 5).map(function (i) {
                return '<li>' + escapeHtml(i) + '</li>';
            }).join('');
            circle.bindPopup(
                '<div class="gis-risk-popup"><h4>' + escapeHtml(z.name || 'Zone') + '</h4>'
                + '<div><strong>' + escapeHtml(z.label || 'Risk') + '</strong></div>'
                + (z.notes ? '<p style="margin:0.35rem 0 0;">' + escapeHtml(z.notes) + '</p>' : '')
                + (issues ? '<ul>' + issues + '</ul>' : '') + '</div>'
            );
            circle.addTo(riskLayer);
        });

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

        riskLayer.addTo(map);

        return {
            terrainLayer: terrainLayer,
            riskLayer: riskLayer,
            isTerrainActive: function () { return terrainActive; },
            setBaseTerrain: setBaseTerrain,
            toggleRisk: function (on) {
                if (on && !map.hasLayer(riskLayer)) {
                    riskLayer.addTo(map);
                }
                if (!on && map.hasLayer(riskLayer)) {
                    map.removeLayer(riskLayer);
                }
            }
        };
    }
};
</script>
