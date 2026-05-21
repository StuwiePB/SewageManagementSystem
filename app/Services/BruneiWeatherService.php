<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class BruneiWeatherService
{
    /**
     * @return array<string, mixed>|null
     */
    public function fetchCurrent(?float $lat = null, ?float $lng = null): ?array
    {
        $lat ??= (float) config('brunei_drainage_risk.weather.default_lat', 4.9031);
        $lng ??= (float) config('brunei_drainage_risk.weather.default_lng', 114.9398);

        $cacheKey = sprintf('brunei_weather:%.3f:%.3f', $lat, $lng);

        return Cache::remember($cacheKey, now()->addMinutes(20), function () use ($lat, $lng) {
            try {
                $response = Http::timeout(8)->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'current' => 'temperature_2m,relative_humidity_2m,apparent_temperature,precipitation,weather_code,wind_speed_10m,wind_direction_10m',
                    'daily' => 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_sum,precipitation_probability_max',
                    'timezone' => 'Asia/Brunei',
                    'forecast_days' => 3,
                ]);
            } catch (\Throwable) {
                return null;
            }

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();
            if (! is_array($data)) {
                return null;
            }

            return $data;
        });
    }

    public function buildSummaryForAi(?float $lat = null, ?float $lng = null): string
    {
        $data = $this->fetchCurrent($lat, $lng);
        if ($data === null) {
            return 'Live Brunei weather is temporarily unavailable. Use general monsoon-season guidance: heavy rain increases drain blockage and overflow risk, especially in low-lying and river-adjacent zones.';
        }

        return $this->formatSummary($data);
    }

    /**
     * Live rain breakdown for GIS weather widget (Open-Meteo, Asia/Brunei).
     *
     * @return array<string, mixed>
     */
    public function buildMapWidgetPayload(?float $lat = null, ?float $lng = null): array
    {
        $lat ??= (float) config('brunei_drainage_risk.weather.default_lat', 4.9031);
        $lng ??= (float) config('brunei_drainage_risk.weather.default_lng', 114.9398);
        $location = (string) config('brunei_drainage_risk.weather.location_name', 'Brunei');

        $data = $this->fetchCurrent($lat, $lng);
        if ($data === null) {
            return [
                'available' => false,
                'location' => $location,
                'lat' => $lat,
                'lng' => $lng,
                'heavy_rain_pct' => 0,
                'light_rain_pct' => 0,
                'other_pct' => 100,
                'condition' => 'unavailable',
                'condition_label' => 'Weather unavailable',
                'precip_mm' => null,
                'rain_chance_today_pct' => null,
                'impacts_routing' => false,
            ];
        }

        $current = is_array($data['current'] ?? null) ? $data['current'] : [];
        $daily = is_array($data['daily'] ?? null) ? $data['daily'] : [];
        $code = (int) ($current['weather_code'] ?? 0);
        $precip = max(0.0, (float) ($current['precipitation'] ?? 0));
        $rainChance = (int) ($daily['precipitation_probability_max'][0] ?? 0);
        $rainSumToday = (float) ($daily['precipitation_sum'][0] ?? 0);

        $heavyScore = 0.0;
        $lightScore = 0.0;

        if (in_array($code, [65, 82, 95, 96, 99], true)) {
            $heavyScore += 55;
        } elseif (in_array($code, [63], true)) {
            $heavyScore += 40;
        } elseif (in_array($code, [61, 80, 81], true)) {
            $lightScore += 45;
        } elseif (in_array($code, [51, 53, 55, 56, 57, 66, 67], true)) {
            $lightScore += 35;
        }

        $heavyScore += min(35.0, $precip * 12);
        $heavyScore += min(25.0, $rainSumToday * 1.5);
        $lightScore += min(20.0, $rainChance * 0.25);

        if ($heavyScore + $lightScore < 8 && $rainChance > 40) {
            $lightScore += $rainChance * 0.35;
        }

        $total = max(1.0, $heavyScore + $lightScore);
        $scale = min(100.0, $heavyScore + $lightScore) / $total;
        $heavyPct = (int) round(($heavyScore / $total) * $scale * 100);
        $lightPct = (int) round(($lightScore / $total) * $scale * 100);
        if ($heavyPct + $lightPct > 100) {
            $lightPct = max(0, 100 - $heavyPct);
        }
        $otherPct = max(0, 100 - $heavyPct - $lightPct);

        $heavyRainPct = $heavyPct;
        $impactsRouting = $heavyRainPct >= (int) config('safe_route.heavy_rain_route_boost_pct', 35)
            || $precip >= 2.0
            || in_array($code, [65, 82, 95, 96, 99], true);

        return [
            'available' => true,
            'location' => $location,
            'lat' => $lat,
            'lng' => $lng,
            'heavy_rain_pct' => $heavyRainPct,
            'light_rain_pct' => $lightPct,
            'other_pct' => $otherPct,
            'condition' => $this->rainIntensityKey($code, $precip),
            'condition_label' => $this->weatherCodeLabel($code),
            'precip_mm' => round($precip, 1),
            'rain_chance_today_pct' => $rainChance,
            'rain_sum_today_mm' => round($rainSumToday, 1),
            'temperature_c' => isset($current['temperature_2m']) ? round((float) $current['temperature_2m'], 1) : null,
            'impacts_routing' => $impactsRouting,
            'updated_at' => now()->toIso8601String(),
        ];
    }

    private function rainIntensityKey(int $code, float $precipMm): string
    {
        if (in_array($code, [65, 82, 95, 96, 99], true) || $precipMm >= 2.0) {
            return 'heavy';
        }
        if (in_array($code, [51, 53, 55, 61, 63, 80, 81], true) || $precipMm >= 0.2) {
            return 'light';
        }

        return 'clear';
    }

    public function buildChatReply(?float $lat = null, ?float $lng = null): string
    {
        $location = config('brunei_drainage_risk.weather.location_name', 'Brunei');
        $data = $this->fetchCurrent($lat, $lng);

        if ($data === null) {
            return "sorry, i couldn't fetch live weather for {$location} right now. during heavy rain, drainage and sewer problems are more likely — especially in low areas like kedayan, damuan, and near sungai brunei.";
        }

        $summary = $this->formatSummary($data);
        $drainageNote = $this->drainageImpactNote($data);

        return "here's the current weather for brunei ({$location}):\n\n{$summary}\n\n{$drainageNote}";
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function formatSummary(array $data): string
    {
        $current = is_array($data['current'] ?? null) ? $data['current'] : [];
        $daily = is_array($data['daily'] ?? null) ? $data['daily'] : [];

        $temp = $current['temperature_2m'] ?? null;
        $humidity = $current['relative_humidity_2m'] ?? null;
        $precip = $current['precipitation'] ?? null;
        $wind = $current['wind_speed_10m'] ?? null;
        $code = (int) ($current['weather_code'] ?? 0);

        $lines = [];
        $lines[] = '• conditions: '.$this->weatherCodeLabel($code);
        if ($temp !== null) {
            $lines[] = '• temperature: '.round((float) $temp, 1).'°C';
        }
        if ($humidity !== null) {
            $lines[] = '• humidity: '.round((float) $humidity).'%';
        }
        if ($precip !== null) {
            $lines[] = '• rain now: '.round((float) $precip, 1).' mm';
        }
        if ($wind !== null) {
            $lines[] = '• wind: '.round((float) $wind, 1).' km/h';
        }

        $times = is_array($daily['time'] ?? null) ? $daily['time'] : [];
        $maxTemps = is_array($daily['temperature_2m_max'] ?? null) ? $daily['temperature_2m_max'] : [];
        $rainSum = is_array($daily['precipitation_sum'] ?? null) ? $daily['precipitation_sum'] : [];
        $rainChance = is_array($daily['precipitation_probability_max'] ?? null) ? $daily['precipitation_probability_max'] : [];

        if (count($times) > 0) {
            $lines[] = '';
            $lines[] = '3-day outlook:';
            foreach ($times as $i => $day) {
                $max = isset($maxTemps[$i]) ? round((float) $maxTemps[$i], 1).'°C max' : '';
                $sum = isset($rainSum[$i]) ? round((float) $rainSum[$i], 1).' mm rain' : '';
                $chance = isset($rainChance[$i]) ? (int) $rainChance[$i].'% rain chance' : '';
                $lines[] = '• '.$day.': '.trim(implode(', ', array_filter([$max, $sum, $chance])));
            }
        }

        return implode("\n", $lines);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function drainageImpactNote(array $data): string
    {
        $current = is_array($data['current'] ?? null) ? $data['current'] : [];
        $daily = is_array($data['daily'] ?? null) ? $data['daily'] : [];
        $precip = (float) ($current['precipitation'] ?? 0);
        $code = (int) ($current['weather_code'] ?? 0);
        $rainSum = is_array($daily['precipitation_sum'] ?? null) ? (float) ($daily['precipitation_sum'][0] ?? 0) : 0;

        $isWet = $precip > 0.5 || in_array($code, [51, 53, 55, 56, 57, 61, 63, 65, 66, 67, 80, 81, 82, 95, 96, 99], true) || $rainSum >= 5;

        if ($isWet) {
            return 'drainage impact: higher risk today — watch kedayan, damuan, kampong ayer, gadong, subok, and other low or river-adjacent areas for flooding, backflow, and clogged drains.';
        }

        return 'drainage impact: lower immediate flood stress right now, but monsoon bursts can still overload older drains quickly — report blockages early if you see pooling or odor.';
    }

    private function weatherCodeLabel(int $code): string
    {
        return match (true) {
            $code === 0 => 'clear',
            in_array($code, [1, 2, 3], true) => 'partly cloudy',
            in_array($code, [45, 48], true) => 'fog',
            in_array($code, [51, 53, 55], true) => 'drizzle',
            in_array($code, [61, 63, 65], true) => 'rain',
            in_array($code, [80, 81, 82], true) => 'rain showers',
            in_array($code, [95, 96, 99], true) => 'thunderstorm',
            default => 'mixed conditions',
        };
    }
}
