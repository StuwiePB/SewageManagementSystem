<?php

namespace App\Http\Controllers;

use App\Services\BruneiWeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MapWeatherController extends Controller
{
    public function show(Request $request, BruneiWeatherService $weather): JsonResponse
    {
        $validated = $request->validate([
            'lat' => ['nullable', 'numeric', 'between:3.7,5.6'],
            'lng' => ['nullable', 'numeric', 'between:113.75,115.85'],
        ]);

        $lat = isset($validated['lat']) ? (float) $validated['lat'] : (float) config('brunei_drainage_risk.weather.default_lat', 4.9031);
        $lng = isset($validated['lng']) ? (float) $validated['lng'] : (float) config('brunei_drainage_risk.weather.default_lng', 114.9398);

        $payload = $weather->buildMapWidgetPayload($lat, $lng);

        return response()->json([
            'weather' => $payload,
        ]);
    }
}
