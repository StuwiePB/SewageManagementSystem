<?php

return [

    'osrm_base_url' => env('OSRM_BASE_URL', 'https://router.project-osrm.org'),

    'segment_colors' => [
        'yellow' => ['stroke' => '#eab308', 'label' => 'Higher risk — rain / drainage stress', 'weight' => 6],
        'green' => ['stroke' => '#22c55e', 'label' => 'Lower risk', 'weight' => 5],
    ],

    /** Route segments use yellow when weather heavy-rain share is at or above this (0–100). */
    'heavy_rain_route_boost_pct' => 35,

    /** Active reports within this distance (km) of a route segment raise severity. */
    'report_buffer_km' => 0.45,

];
