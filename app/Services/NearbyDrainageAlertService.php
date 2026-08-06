<?php

namespace App\Services;

use App\Models\OperationsReport;
use App\Models\Report;
use App\Models\WorkOrder;
use Illuminate\Support\Str;

class NearbyDrainageAlertService
{
    public function radiusKm(): float
    {
        return (float) config('customer_drainage_issues.radius_km', 5.0);
    }

    /**
     * @return array{
     *   has_alert: bool,
     *   total: int,
     *   radius_km: float,
     *   by_type: array<string, int>,
     *   nearest_km: float|null,
     *   nearest_label: string|null,
     *   items: list<array<string, mixed>>
     * }
     */
    public function scan(float $lat, float $lng, ?float $radiusKm = null): array
    {
        $radiusKm ??= $this->radiusKm();
        $items = array_merge(
            $this->collectFromReports($lat, $lng, $radiusKm),
            $this->collectFromOperationsReports($lat, $lng, $radiusKm),
            $this->collectFromWorkOrders($lat, $lng, $radiusKm),
        );

        usort($items, static fn (array $a, array $b): int => $a['distance_km'] <=> $b['distance_km']);

        $byType = [];
        foreach ($items as $item) {
            $label = (string) $item['canonical_type'];
            $byType[$label] = ($byType[$label] ?? 0) + 1;
        }

        $nearest = $items[0] ?? null;

        return [
            'has_alert' => count($items) > 0,
            'total' => count($items),
            'radius_km' => $radiusKm,
            'by_type' => $byType,
            'nearest_km' => $nearest['distance_km'] ?? null,
            'nearest_label' => $nearest
                ? trim(($nearest['reference'] ?? '').' · '.($nearest['canonical_type'] ?? ''))
                : null,
            'items' => array_slice($items, 0, 12),
        ];
    }

    public function buildAlertMessage(array $scan): string
    {
        $radius = rtrim(rtrim(number_format((float) $scan['radius_km'], 1, '.', ''), '0'), '.');

        if (! ($scan['has_alert'] ?? false)) {
            return "good news — no active drainage reports within {$radius} km of you right now.\n\n"
                .'stay alert during heavy rain, especially near low areas and rivers. '
                .'if you see blockage, overflow, or bad smell, you can report it here.';
        }

        $total = (int) $scan['total'];
        $lines = [
            'heads up — there '.($total === 1 ? 'is' : 'are')." {$total} drainage "
            .($total === 1 ? 'report' : 'reports')." within {$radius} km of you. please stay safe.\n",
        ];

        foreach ((array) ($scan['by_type'] ?? []) as $type => $count) {
            $lines[] = '• '.strtolower((string) $type).': '.$count;
        }

        if (! empty($scan['nearest_km']) && ! empty($scan['nearest_label'])) {
            $lines[] = '';
            $lines[] = 'nearest: ~'.number_format((float) $scan['nearest_km'], 1).' km ('.$scan['nearest_label'].')';
        }

        $lines[] = '';
        $lines[] = 'avoid flooded or smelly areas if you can. report new issues if you see pooling, overflow, or odor.';

        return implode("\n", $lines);
    }

    public function buildDetailedNearbyReply(float $lat, float $lng): string
    {
        $scan = $this->scan($lat, $lng);
        if (! $scan['has_alert']) {
            return $this->buildAlertMessage($scan);
        }

        $intro = $this->buildAlertMessage($scan);
        $lines = [$intro, '', 'details', '────────', ''];

        foreach ($scan['items'] as $item) {
            $lines[] = '• '.strtolower((string) $item['canonical_type']);
            $lines[] = '  reference: '.($item['reference'] ?? 'n/a');
            $lines[] = '  status: '.($item['status'] ?? 'n/a');
            $lines[] = '  distance: ~'.number_format((float) $item['distance_km'], 1).' km';
            if (! empty($item['address'])) {
                $lines[] = '  address: '.$item['address'];
            }
            $lines[] = '';
        }

        return rtrim(implode("\n", $lines));
    }

    public function alertSignature(array $scan): string
    {
        ksort($scan['by_type'] ?? []);

        return sha1(json_encode([
            'total' => $scan['total'] ?? 0,
            'by_type' => $scan['by_type'] ?? [],
        ]));
    }

    public function canonicalType(?string $raw): ?string
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return null;
        }

        $needle = Str::lower($raw);
        foreach ((array) config('customer_drainage_issues.matchers', []) as $canonical => $patterns) {
            if (Str::lower((string) $canonical) === $needle) {
                return (string) $canonical;
            }
            foreach ((array) $patterns as $pattern) {
                if ($pattern !== '' && str_contains($needle, Str::lower((string) $pattern))) {
                    return (string) $canonical;
                }
            }
        }

        return null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function collectFromReports(float $lat, float $lng, float $radiusKm): array
    {
        $active = (array) config('customer_drainage_issues.active_statuses', []);

        return Report::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->when($active !== [], fn ($q) => $q->whereIn('status', $active))
            ->get()
            ->map(function (Report $report) use ($lat, $lng, $radiusKm) {
                $canonical = $this->canonicalType($report->problem_type);
                if ($canonical === null) {
                    return null;
                }
                $distance = $this->haversineKm($lat, $lng, (float) $report->latitude, (float) $report->longitude);
                if ($distance > $radiusKm) {
                    return null;
                }

                return [
                    'reference' => $report->reference_code ?: ('CR-'.$report->id),
                    'status' => $report->status,
                    'canonical_type' => $canonical,
                    'address' => $report->address,
                    'lat' => (float) $report->latitude,
                    'lng' => (float) $report->longitude,
                    'distance_km' => round($distance, 2),
                    'source' => 'customer_report',
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function collectFromOperationsReports(float $lat, float $lng, float $radiusKm): array
    {
        $active = (array) config('customer_drainage_issues.active_statuses', []);

        return OperationsReport::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->when($active !== [], fn ($q) => $q->whereIn('status', $active))
            ->get()
            ->map(function (OperationsReport $report) use ($lat, $lng, $radiusKm) {
                $canonical = $this->canonicalType($report->issue_type);
                if ($canonical === null) {
                    return null;
                }
                $distance = $this->haversineKm($lat, $lng, (float) $report->latitude, (float) $report->longitude);
                if ($distance > $radiusKm) {
                    return null;
                }

                return [
                    'reference' => $report->report_number ?: ('OP-'.$report->id),
                    'status' => $report->status,
                    'canonical_type' => $canonical,
                    'address' => $report->location_address,
                    'lat' => (float) $report->latitude,
                    'lng' => (float) $report->longitude,
                    'distance_km' => round($distance, 2),
                    'source' => 'operations_report',
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function collectFromWorkOrders(float $lat, float $lng, float $radiusKm): array
    {
        $active = (array) config('customer_drainage_issues.active_statuses', []);

        return WorkOrder::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->when($active !== [], fn ($q) => $q->whereIn('status', $active))
            ->get()
            ->map(function (WorkOrder $order) use ($lat, $lng, $radiusKm) {
                $canonical = $this->canonicalType($order->type);
                if ($canonical === null) {
                    return null;
                }
                $distance = $this->haversineKm($lat, $lng, (float) $order->latitude, (float) $order->longitude);
                if ($distance > $radiusKm) {
                    return null;
                }

                return [
                    'reference' => $order->work_order_number ?: ('WO-'.$order->id),
                    'status' => $order->status,
                    'canonical_type' => $canonical,
                    'address' => $order->location_address,
                    'lat' => (float) $order->latitude,
                    'lng' => (float) $order->longitude,
                    'distance_km' => round($distance, 2),
                    'source' => 'work_order',
                ];
            })
            ->filter()
            ->values()
            ->all();
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
