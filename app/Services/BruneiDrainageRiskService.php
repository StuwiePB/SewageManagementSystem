<?php

namespace App\Services;

class BruneiDrainageRiskService
{
    /**
     * @return list<array<string, mixed>>
     */
    public function areas(): array
    {
        return array_values((array) config('brunei_drainage_risk.areas', []));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByMessage(string $message): ?array
    {
        $normalized = strtolower(trim($message));
        if ($normalized === '') {
            return null;
        }

        $best = null;
        $bestLen = 0;

        foreach ($this->areas() as $area) {
            $candidates = array_merge(
                [strtolower((string) ($area['name'] ?? ''))],
                array_map('strtolower', (array) ($area['aliases'] ?? []))
            );

            foreach ($candidates as $candidate) {
                $candidate = trim($candidate);
                if ($candidate === '' || strlen($candidate) < 3) {
                    continue;
                }
                $pattern = '/\b'.preg_quote($candidate, '/').'\b/iu';
                if (preg_match($pattern, $normalized) && strlen($candidate) > $bestLen) {
                    $best = $area;
                    $bestLen = strlen($candidate);
                }
            }
        }

        return $best;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function areasNear(float $lat, float $lng, int $limit = 5): array
    {
        $ranked = [];

        foreach ($this->areas() as $area) {
            $aLat = (float) ($area['lat'] ?? 0);
            $aLng = (float) ($area['lng'] ?? 0);
            $km = $this->haversineKm($lat, $lng, $aLat, $aLng);
            $radius = (float) ($area['radius_km'] ?? 2);
            if ($km <= $radius + 2) {
                $ranked[] = ['area' => $area, 'distance_km' => round($km, 1)];
            }
        }

        usort($ranked, static fn (array $a, array $b): int => $a['distance_km'] <=> $b['distance_km']);

        return array_slice(array_map(static fn (array $row) => $row['area'], $ranked), 0, $limit);
    }

    public function buildChatReplyForArea(array $area): string
    {
        $level = (string) ($area['risk_level'] ?? 'moderate');
        $label = $this->riskLabel($level);
        $colorWord = $this->riskColorWord($level);
        $issues = (array) ($area['issues'] ?? []);
        $notes = trim((string) ($area['notes'] ?? ''));

        $lines = [];
        $lines[] = $area['name'].' — '.$label.' ('.$colorWord.' on gis map)';
        if ($notes !== '') {
            $lines[] = $notes;
        }
        if ($issues !== []) {
            $lines[] = '';
            $lines[] = 'likely problems:';
            foreach ($issues as $issue) {
                $lines[] = '• '.$issue;
            }
        }

        return implode("\n", $lines);
    }

    public function buildHighRiskOverviewReply(): string
    {
        $lines = ["highest risk areas in brunei-muara (gis: red = very high/high, yellow = moderate, green = lower):\n"];

        foreach (['very_high', 'high', 'moderate'] as $level) {
            $bucket = array_filter($this->areas(), static fn (array $a): bool => ($a['risk_level'] ?? '') === $level);
            if ($bucket === []) {
                continue;
            }
            $lines[] = $this->riskLabel($level).':';
            foreach ($bucket as $area) {
                $lines[] = '• '.$area['name'];
            }
            $lines[] = '';
        }

        $lines[] = 'top repeat-problem zones: kedayan (d2), damuan (d6), sungai brunei / kampong ayer, gadong, subok, kota batu.';

        return trim(implode("\n", $lines));
    }

    public function buildAiContextBlock(): string
    {
        $lines = [
            'BRUNEI DRAINAGE RISK ZONES (reference for weather + infrastructure guidance — not live DB counts):',
            'Map colours: yellow = higher drainage/rain risk, green = lower.',
            '',
        ];

        foreach ($this->areas() as $area) {
            $issues = implode('; ', (array) ($area['issues'] ?? []));
            $lines[] = sprintf(
                '- %s [%s]: %s',
                $area['name'] ?? 'Area',
                $this->riskLabel((string) ($area['risk_level'] ?? 'moderate')),
                $issues !== '' ? $issues : 'general drainage stress'
            );
        }

        return implode("\n", $lines);
    }

    /**
     * @param  array<string, mixed>|null  $weather
     * @return array<string, mixed>
     */
    public function mapLayerPayload(?array $weather = null): array
    {
        $colors = (array) config('brunei_drainage_risk.map_colors', []);
        $heavyRainPct = (int) ($weather['heavy_rain_pct'] ?? 0);
        $boostZones = $heavyRainPct >= 25 || ($weather['impacts_routing'] ?? false);

        $zones = array_map(function (array $area) use ($colors, $boostZones) {
            $level = (string) ($area['risk_level'] ?? 'moderate');
            if ($boostZones && in_array($level, ['low', 'moderate'], true)) {
                $level = $level === 'low' ? 'moderate' : 'high';
            }
            $palette = is_array($colors[$level] ?? null) ? $colors[$level] : ['fill' => '#eab308', 'stroke' => '#a16207', 'label' => 'Risk'];
            $isHighSegment = in_array((string) ($area['risk_level'] ?? ''), ['very_high', 'high'], true);

            return [
                'id' => $area['id'] ?? null,
                'name' => $area['name'] ?? 'Area',
                'risk_level' => $level,
                'base_risk_level' => $area['risk_level'] ?? 'moderate',
                'is_high_risk_segment' => $isHighSegment,
                'lat' => (float) ($area['lat'] ?? 0),
                'lng' => (float) ($area['lng'] ?? 0),
                'radius_m' => (float) ($area['radius_km'] ?? 1.5) * 1000,
                'fill' => $palette['fill'],
                'stroke' => $palette['stroke'],
                'label' => $palette['label'],
                'issues' => $area['issues'] ?? [],
                'notes' => $area['notes'] ?? null,
            ];
        }, $this->areas());

        $highRiskZones = array_values(array_filter($zones, static fn (array $z): bool => ! empty($z['is_high_risk_segment'])));

        return [
            'zones' => $zones,
            'high_risk_zones' => $highRiskZones,
            'legend' => [
                ['key' => 'elevated', 'label' => 'Higher risk (drainage + rain)', 'color' => '#eab308'],
                ['key' => 'lower', 'label' => 'Lower risk', 'color' => $colors['low']['fill'] ?? '#22c55e'],
            ],
            'weather_boost' => $boostZones,
        ];
    }

    public function riskLabel(string $level): string
    {
        return match ($level) {
            'very_high' => 'VERY HIGH RISK',
            'high' => 'HIGH RISK',
            'moderate' => 'MODERATE RISK',
            'low' => 'LOWER RISK',
            default => strtoupper(str_replace('_', ' ', $level)),
        };
    }

    public function riskColorWord(string $level): string
    {
        return match ($level) {
            'very_high', 'high' => 'red',
            'moderate' => 'yellow',
            default => 'green',
        };
    }

    private function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earth = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earth * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
