<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Open-Meteo client for the hex risk grid. Separate from BruneiWeatherService (which serves the
 * single-point "current conditions" widget) because this one is shaped for batch, per-cell,
 * per-forecast-hour data feeding RiskScoreService — a different access pattern, not a duplicate.
 *
 * No API key: Open-Meteo's free tier is keyless and its terms allow commercial use.
 */
class WeatherService
{
    private const FORECAST_BASE_URL = 'https://api.open-meteo.com/v1/forecast';

    private const FLOOD_BASE_URL = 'https://flood-api.open-meteo.com/v1/flood';

    /**
     * Open-Meteo's own documented ceiling per request; chunking keeps every request well inside
     * it regardless of how many cells the grid grows to.
     */
    private const MAX_POINTS_PER_REQUEST = 100;

    /**
     * Hourly rainfall + rain probability for many points at once, keyed back to the same keys
     * the caller passed in `$points` (so callers can key by h3_index and get results keyed the
     * same way).
     *
     * @param  array<string, array{lat: float, lng: float}>  $points
     * @return array<string, array{times: list<string>, precipitation: list<float>, probability: list<int>}>
     */
    public function hourlyRainfall(array $points, int $forecastDays = 3): array
    {
        $results = [];
        $chunks = array_chunk($points, self::MAX_POINTS_PER_REQUEST, true);

        foreach ($chunks as $i => $chunk) {
            if ($i > 0) {
                // Open-Meteo's free tier enforces a per-minute request cap; a small gap between
                // chunks spreads a many-chunk sync out instead of bursting all requests at once,
                // reducing (not eliminating) how often a large sync trips that limit.
                usleep(300_000);
            }

            $results += $this->fetchChunk($chunk, $forecastDays);
        }

        return $results;
    }

    /**
     * @param  array<string, array{lat: float, lng: float}>  $chunk
     * @return array<string, array{times: list<string>, precipitation: list<float>, probability: list<int>}>
     */
    private function fetchChunk(array $chunk, int $forecastDays): array
    {
        $keys = array_keys($chunk);
        $lats = array_map(static fn (array $p): float => (float) $p['lat'], $chunk);
        $lngs = array_map(static fn (array $p): float => (float) $p['lng'], $chunk);

        try {
            $response = Http::retry(2, 500)->timeout(20)->get(self::FORECAST_BASE_URL, [
                'latitude' => implode(',', $lats),
                'longitude' => implode(',', $lngs),
                'hourly' => 'precipitation,precipitation_probability',
                'timezone' => 'Asia/Brunei',
                'forecast_days' => $forecastDays,
            ]);
        } catch (\Throwable $e) {
            Log::error('WeatherService: hourlyRainfall request failed', [
                'error' => $e->getMessage(),
                'points' => count($chunk),
            ]);

            return [];
        }

        if (! $response->successful()) {
            Log::error('WeatherService: hourlyRainfall non-success response', [
                'status' => $response->status(),
                'points' => count($chunk),
            ]);

            return [];
        }

        $data = $response->json();
        if (! is_array($data)) {
            return [];
        }

        // Open-Meteo returns a single object for one location but a JSON array (one object per
        // location, same order as the request) for multiple — normalize both to a list here.
        $entries = array_is_list($data) ? $data : [$data];

        $out = [];
        foreach ($entries as $i => $entry) {
            $key = $keys[$i] ?? null;
            if ($key === null || ! is_array($entry)) {
                continue;
            }

            $hourly = is_array($entry['hourly'] ?? null) ? $entry['hourly'] : [];
            $out[$key] = [
                'times' => is_array($hourly['time'] ?? null) ? $hourly['time'] : [],
                'precipitation' => is_array($hourly['precipitation'] ?? null)
                    ? array_map(static fn ($v) => (float) $v, $hourly['precipitation'])
                    : [],
                'probability' => is_array($hourly['precipitation_probability'] ?? null)
                    ? array_map(static fn ($v) => (int) $v, $hourly['precipitation_probability'])
                    : [],
            ];
        }

        return $out;
    }

    /**
     * River discharge (m³/s) for flood-prone low-lying cells near a waterway. Cached 6h since
     * this data source updates far less often than rainfall forecasts.
     *
     * @return array<string, mixed>|null
     */
    public function riverDischarge(float $lat, float $lng): ?array
    {
        $cacheKey = sprintf('river_discharge:%.3f:%.3f', $lat, $lng);

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($lat, $lng) {
            try {
                $response = Http::retry(2, 500)->timeout(15)->get(self::FLOOD_BASE_URL, [
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'daily' => 'river_discharge',
                ]);
            } catch (\Throwable $e) {
                Log::error('WeatherService: riverDischarge request failed', [
                    'error' => $e->getMessage(),
                    'lat' => $lat,
                    'lng' => $lng,
                ]);

                return null;
            }

            if (! $response->successful()) {
                Log::error('WeatherService: riverDischarge non-success response', [
                    'status' => $response->status(),
                    'lat' => $lat,
                    'lng' => $lng,
                ]);

                return null;
            }

            $data = $response->json();

            return is_array($data) ? $data : null;
        });
    }
}
