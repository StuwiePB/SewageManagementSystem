<?php

/**
 * Brunei drainage / sewer infrastructure risk reference data.
 *
 * The live risk-visualization overlay on the GIS map is now the H3 hex grid (RiskCell model,
 * RiskScoreService, `risk:sync`, /api/risk-grid) — see config/services.php-free WeatherService for
 * its Open-Meteo client. This file only holds what that system still shares with the rest of the
 * app: the default map center/location used by BruneiWeatherService's single-point widget, and a
 * legacy `areas` list (see note below) still read by RouteRiskService and Ziqah's chat context.
 *
 * risk_level: very_high | high | moderate | low
 * rain_sensitive: true when this area's own documented issues/notes already flag river, tidal,
 *   flash-flood, or "drainage can't handle heavy rain" exposure — not surveyed elevation data.
 *
 * `areas` is intentionally empty — the previous list was hand-curated placeholder data with no
 * cited official source. Populate it with verified JKR/drainage-survey data before relying on
 * RouteRiskService's zone-name reasons or Ziqah's area-specific replies again.
 */
return [

    'weather' => [
        'default_lat' => 4.9031,
        'default_lng' => 114.9398,
        'location_name' => 'Bandar Seri Begawan, Brunei',
    ],

    'areas' => [],

];
