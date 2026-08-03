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

    private const ELEVATION_BASE_URL = 'https://api.open-meteo.com/v1/elevation';

    /**
     * Open-Meteo's own documented ceiling per request; chunking keeps every request well inside
     * it regardless of how many cells the grid grows to.
     */
    private const MAX_POINTS_PER_REQUEST = 100;

    /**
     * Hourly rainfall + rain probability for many points at once, keyed back to the same keys
     * the caller passed in `$points` (so callers can key by h3_index and get results keyed the
     * same way). Includes `$pastHours` hours of already-observed rainfall ahead of "now" in the
     * same series (Open-Meteo's own historical/observed hourly values, not a forecast) — this is
     * what lets the grid show areas that ARE or HAVE BEEN raining, not just what's forecast, and
     * gives the landslide factor enough trailing data to compute a rolling accumulation.
     *
     * @param  array<string, array{lat: float, lng: float}>  $points
     * @return array<string, array{times: list<string>, precipitation: list<float>, probability: list<int>}>
     */
    public function hourlyRainfall(array $points, int $forecastDays = 3, int $pastHours = 24): array
    {
        $results = [];
        $chunks = array_chunk($points, self::MAX_POINTS_PER_REQUEST, true);

        foreach ($chunks as $i => $chunk) {
            if ($i > 0) {
                // Open-Meteo's free tier enforces a per-minute request cap. This runs hourly via
                // cron (risk:sync), not in a user-facing request, so it can afford to spend a
                // couple of minutes pacing chunks rather than bursting straight into a 429 — a
                // grid full of stale/missing cells is worse than a sync that takes longer.
                sleep(6);
            }

            $chunkResult = $this->fetchChunk($chunk, $forecastDays, $pastHours);

            if ($chunkResult === [] && count($chunk) > 0) {
                sleep(15);
                $chunkResult = $this->fetchChunk($chunk, $forecastDays, $pastHours);
            }

            $results += $chunkResult;
        }

        return $results;
    }

    /**
     * @param  array<string, array{lat: float, lng: float}>  $chunk
     * @return array<string, array{times: list<string>, precipitation: list<float>, probability: list<int>}>
     */
    private function fetchChunk(array $chunk, int $forecastDays, int $pastHours): array
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
                // 'forecast_hours' (not 'forecast_days') is what actually bounds the future
                // window when 'past_hours' is also set — Open-Meteo's API otherwise ignores the
                // forecast_days cap and falls back to its full ~16-day max range, which would
                // silently balloon every sync request to 5-6x the data it needs.
                'forecast_hours' => $forecastDays * 24,
                'past_hours' => $pastHours,
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

    /**
     * Real elevation (metres, Open-Meteo's SRTM/Copernicus DEM) for many points at once, keyed
     * back to the same keys as `$points` — same batching/keying convention as hourlyRainfall().
     * Elevation doesn't change day to day, so callers should treat this as an occasional
     * backfill, not something to run on every sync.
     *
     * @param  array<string, array{lat: float, lng: float}>  $points
     * @return array<string, float>
     */
    public function elevations(array $points): array
    {
        $results = [];
        $chunks = array_chunk($points, self::MAX_POINTS_PER_REQUEST, true);

        foreach ($chunks as $i => $chunk) {
            if ($i > 0) {
                // The elevation endpoint's free-tier per-minute cap is tighter than the forecast
                // endpoint's — 300ms between chunks (fine for hourlyRainfall) was enough to trip
                // a 429 here after ~6 chunks in testing, so this backfill (occasional, not
                // hourly) can afford to go slower in exchange for actually finishing.
                sleep(8);
            }

            $chunkResult = $this->fetchElevationChunk($chunk);

            // A 429 mid-backfill would otherwise silently leave those cells at their old
            // elevation (or null) with no signal to the caller that a retry is needed.
            if ($chunkResult === [] && count($chunk) > 0) {
                sleep(15);
                $chunkResult = $this->fetchElevationChunk($chunk);
            }

            $results += $chunkResult;
        }

        return $results;
    }

    /**
     * @param  array<string, array{lat: float, lng: float}>  $chunk
     * @return array<string, float>
     */
    private function fetchElevationChunk(array $chunk): array
    {
        $keys = array_keys($chunk);
        $lats = array_map(static fn (array $p): float => (float) $p['lat'], $chunk);
        $lngs = array_map(static fn (array $p): float => (float) $p['lng'], $chunk);

        try {
            $response = Http::retry(2, 500)->timeout(20)->get(self::ELEVATION_BASE_URL, [
                'latitude' => implode(',', $lats),
                'longitude' => implode(',', $lngs),
            ]);
        } catch (\Throwable $e) {
            Log::error('WeatherService: elevations request failed', [
                'error' => $e->getMessage(),
                'points' => count($chunk),
            ]);

            return [];
        }

        if (! $response->successful()) {
            Log::error('WeatherService: elevations non-success response', [
                'status' => $response->status(),
                'points' => count($chunk),
            ]);

            return [];
        }

        $data = $response->json();
        $elevations = is_array($data['elevation'] ?? null) ? $data['elevation'] : [];

        $out = [];
        foreach ($keys as $i => $key) {
            if (isset($elevations[$i]) && is_numeric($elevations[$i])) {
                $out[$key] = (float) $elevations[$i];
            }
        }

        return $out;
    }
}
