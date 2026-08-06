<?php

namespace App\Http\Controllers;

use App\Services\RouteRiskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SafeRouteController extends Controller
{
    public function analyze(Request $request, RouteRiskService $routeRisk): JsonResponse
    {
        $validated = $request->validate([
            'start_lat' => ['required', 'numeric', 'between:3.7,5.6'],
            'start_lng' => ['required', 'numeric', 'between:113.75,115.85'],
            'end_lat' => ['required', 'numeric', 'between:3.7,5.6'],
            'end_lng' => ['required', 'numeric', 'between:113.75,115.85'],
            'for_customer' => ['sometimes', 'boolean'],
        ]);

        $startLat = (float) $validated['start_lat'];
        $startLng = (float) $validated['start_lng'];
        $endLat = (float) $validated['end_lat'];
        $endLng = (float) $validated['end_lng'];

        $osrm = $this->fetchOsrmRoute($startLat, $startLng, $endLat, $endLng);
        if ($osrm === null) {
            return response()->json([
                'message' => 'Could not fetch a driving route. Check your points are on roads in Brunei and try again.',
            ], 502);
        }

        $coordinates = $this->simplifyCoordinates($osrm['coordinates']);
        $customerFacing = $request->boolean('for_customer');
        $analysis = $routeRisk->analyze($coordinates, $customerFacing);

        return response()->json([
            'start' => ['lat' => $startLat, 'lng' => $startLng],
            'end' => ['lat' => $endLat, 'lng' => $endLng],
            'distance_km' => round($osrm['distance_km'], 2),
            'duration_min' => round($osrm['duration_min'], 1),
            'segments' => $analysis['segments'],
            'counts' => $analysis['counts'],
            'summary' => $analysis['summary'],
            'legend' => $analysis['legend'],
            'weather' => $customerFacing ? null : ($analysis['weather'] ?? null),
        ]);
    }

    /**
     * @return array{coordinates: list<array{0: float, 1: float}>, distance_km: float, duration_min: float}|null
     */
    private function fetchOsrmRoute(float $startLat, float $startLng, float $endLat, float $endLng): ?array
    {
        $base = rtrim((string) config('safe_route.osrm_base_url'), '/');
        $path = sprintf(
            '%s,%s;%s,%s',
            $startLng,
            $startLat,
            $endLng,
            $endLat
        );
        $url = $base.'/route/v1/driving/'.$path.'?overview=full&geometries=geojson';

        try {
            $response = Http::timeout(20)->get($url);
        } catch (\Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $body = $response->json();
        $routes = is_array($body['routes'] ?? null) ? $body['routes'] : [];
        $route = $routes[0] ?? null;
        if (! is_array($route)) {
            return null;
        }

        $geometry = is_array($route['geometry'] ?? null) ? $route['geometry'] : [];
        $coords = is_array($geometry['coordinates'] ?? null) ? $geometry['coordinates'] : [];
        if (count($coords) < 2) {
            return null;
        }

        $normalized = [];
        foreach ($coords as $pair) {
            if (! is_array($pair) || count($pair) < 2) {
                continue;
            }
            $normalized[] = [(float) $pair[0], (float) $pair[1]];
        }

        if (count($normalized) < 2) {
            return null;
        }

        return [
            'coordinates' => $normalized,
            'distance_km' => ((float) ($route['distance'] ?? 0)) / 1000,
            'duration_min' => ((float) ($route['duration'] ?? 0)) / 60,
        ];
    }

    /**
     * @param  list<array{0: float, 1: float}>  $coordinates
     * @return list<array{0: float, 1: float}>
     */
    private function simplifyCoordinates(array $coordinates, int $maxPoints = 120): array
    {
        if (count($coordinates) <= $maxPoints) {
            return $coordinates;
        }

        $last = count($coordinates) - 1;
        $step = $last / ($maxPoints - 1);
        $sampled = [];

        for ($i = 0; $i < $maxPoints; $i++) {
            $idx = (int) round($i * $step);
            $sampled[] = $coordinates[$idx];
        }

        return $sampled;
    }
}
