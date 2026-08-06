<?php

namespace App\Services;

class RouteRiskService
{
    /** @var list<array<string, mixed>>|null */
    private ?array $activeReportPoints = null;

    /** @var array<string, mixed>|null */
    private ?array $weatherPayload = null;

    private bool $customerFacing = false;

    public function __construct(
        private BruneiDrainageRiskService $riskZones,
        private NearbyDrainageAlertService $nearbyReports,
        private BruneiWeatherService $weather,
    ) {}

    /**
     * @param  list<array{0: float, 1: float}>  $coordinates  [lng, lat] pairs from GeoJSON
     * @return array<string, mixed>
     */
    public function analyze(array $coordinates, bool $customerFacing = false): array
    {
        $this->customerFacing = $customerFacing;

        if (count($coordinates) < 2) {
            return [
                'segments' => [],
                'summary' => ['message' => 'Route too short to analyze.'],
                'distance_km' => 0,
                'duration_min' => 0,
                'weather' => null,
            ];
        }

        $this->primeCaches($coordinates);

        $segments = [];
        $counts = ['yellow' => 0, 'green' => 0];

        for ($i = 0; $i < count($coordinates) - 1; $i++) {
            $lng1 = (float) $coordinates[$i][0];
            $lat1 = (float) $coordinates[$i][1];
            $lng2 = (float) $coordinates[$i + 1][0];
            $lat2 = (float) $coordinates[$i + 1][1];
            $midLat = ($lat1 + $lat2) / 2;
            $midLng = ($lng1 + $lng2) / 2;

            $scored = $this->scorePoint($midLat, $midLng);
            $color = $this->routeColorForLevel($scored['level']);
            $counts[$color] = ($counts[$color] ?? 0) + 1;

            $palette = config('safe_route.segment_colors.'.$color, []);

            $segments[] = [
                'from' => ['lat' => $lat1, 'lng' => $lng1],
                'to' => ['lat' => $lat2, 'lng' => $lng2],
                'color' => $color,
                'stroke' => $palette['stroke'] ?? '#22c55e',
                'weight' => $palette['weight'] ?? 5,
                'level' => $scored['level'],
                'reasons' => $scored['reasons'],
            ];
        }

        return [
            'segments' => $segments,
            'counts' => $counts,
            'summary' => $this->buildSummary($counts),
            'legend' => [
                ['color' => 'yellow', 'label' => config('safe_route.segment_colors.yellow.label')],
                ['color' => 'green', 'label' => config('safe_route.segment_colors.green.label')],
            ],
            'weather' => $this->customerFacing ? null : $this->weatherPayload,
        ];
    }

    /**
     * @param  list<array{0: float, 1: float}>  $coordinates
     */
    private function primeCaches(array $coordinates): void
    {
        $lats = array_map(fn (array $c) => (float) $c[1], $coordinates);
        $lngs = array_map(fn (array $c) => (float) $c[0], $coordinates);
        $centerLat = array_sum($lats) / count($lats);
        $centerLng = array_sum($lngs) / count($lngs);

        $scan = $this->nearbyReports->scan($centerLat, $centerLng, 25.0);
        $this->activeReportPoints = [];
        foreach ($scan['items'] as $item) {
            if (! isset($item['lat'], $item['lng'])) {
                continue;
            }
            $this->activeReportPoints[] = [
                'lat' => (float) $item['lat'],
                'lng' => (float) $item['lng'],
                'type' => $item['canonical_type'] ?? 'issue',
                'reference' => $item['reference'] ?? '',
            ];
        }

        $this->weatherPayload = $this->customerFacing
            ? null
            : $this->weather->buildMapWidgetPayload($centerLat, $centerLng);
    }

    /**
     * @return array{level: string, reasons: list<string>}
     */
    private function scorePoint(float $lat, float $lng): array
    {
        $level = 'low';
        $reasons = [];
        $heavyPct = $this->customerFacing ? 0 : (int) ($this->weatherPayload['heavy_rain_pct'] ?? 0);
        $lightPct = $this->customerFacing ? 0 : (int) ($this->weatherPayload['light_rain_pct'] ?? 0);

        foreach ($this->riskZones->areas() as $area) {
            $aLat = (float) ($area['lat'] ?? 0);
            $aLng = (float) ($area['lng'] ?? 0);
            $radiusKm = (float) ($area['radius_km'] ?? 1.5);
            $km = $this->haversineKm($lat, $lng, $aLat, $aLng);
            if ($km <= $radiusKm) {
                $zoneLevel = (string) ($area['risk_level'] ?? 'moderate');
                if ($this->levelRank($zoneLevel) > $this->levelRank($level)) {
                    $level = $zoneLevel;
                }
                $reasons[] = ($area['name'] ?? 'Risk zone').' ('.$this->riskZones->riskLabel($zoneLevel).')';
            }
        }

        $buffer = (float) config('safe_route.report_buffer_km', 0.45);
        foreach ($this->activeReportPoints ?? [] as $point) {
            $km = $this->haversineKm($lat, $lng, (float) $point['lat'], (float) $point['lng']);
            if ($km <= $buffer) {
                if ($this->levelRank('high') > $this->levelRank($level)) {
                    $level = 'high';
                }
                $reasons[] = 'Active report nearby: '.($point['type'] ?? 'issue')
                    .($point['reference'] ? ' ('.$point['reference'].')' : '');
            }
        }

        if (! $this->customerFacing) {
            $boostThreshold = (int) config('safe_route.heavy_rain_route_boost_pct', 35);
            if ($heavyPct >= $boostThreshold) {
                if ($this->levelRank('moderate') > $this->levelRank($level)) {
                    $level = 'moderate';
                }
                if ($this->levelRank($level) >= $this->levelRank('moderate')) {
                    $reasons[] = "Live weather: heavy rain ~{$heavyPct}% (Brunei forecast)";
                }
            } elseif ($lightPct >= 50 && $this->levelRank($level) >= $this->levelRank('moderate')) {
                $reasons[] = "Live weather: light rain ~{$lightPct}%";
            }

            if ($heavyPct >= 55 && in_array($level, ['low', 'moderate'], true)) {
                $level = 'high';
            }
        }

        $reasons = array_values(array_unique($reasons));

        return ['level' => $level, 'reasons' => array_slice($reasons, 0, 4)];
    }

    private function routeColorForLevel(string $level): string
    {
        return match ($level) {
            'very_high', 'high', 'moderate' => 'yellow',
            default => 'green',
        };
    }

    /**
     * @param  array<string, int>  $counts
     * @return array<string, mixed>
     */
    private function buildSummary(array $counts): array
    {
        $yellow = (int) ($counts['yellow'] ?? 0);
        $green = (int) ($counts['green'] ?? 0);
        $total = $yellow + $green;
        $heavyPct = $this->customerFacing ? 0 : (int) ($this->weatherPayload['heavy_rain_pct'] ?? 0);

        if ($this->customerFacing) {
            if ($yellow > 0) {
                $message = "Notice: {$yellow} sections pass elevated drainage risk. Slow down in those areas.";
            } else {
                $message = 'Route shows lower drainage stress along this path.';
            }
        } elseif ($yellow > 0 && $heavyPct >= 35) {
            $message = "Caution: {$yellow} of {$total} sections overlap higher drainage/rain risk. Heavy rain ~{$heavyPct}% in Brunei now.";
        } elseif ($yellow > 0) {
            $message = "Notice: {$yellow} sections pass elevated drainage risk. Slow down in those areas.";
        } else {
            $message = 'Route shows lower drainage stress. Stay alert if rain increases.';
        }

        return [
            'message' => $message,
            'yellow' => $yellow,
            'green' => $green,
            'total_segments' => $total,
            'heavy_rain_pct' => $this->customerFacing ? null : $heavyPct,
        ];
    }

    private function levelRank(string $level): int
    {
        return match ($level) {
            'very_high' => 4,
            'high' => 3,
            'moderate' => 2,
            'low' => 1,
            default => 0,
        };
    }

    private function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earth = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earth * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
