<?php

namespace Database\Seeders;

use App\Models\OperationsReport;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class StatisticsSeeder extends Seeder
{
    /**
     * Seed operations_reports and work_orders for Statistics and hotspot views (not customer `reports`).
     * Run: php artisan db:seed --class=StatisticsSeeder
     */
    public function run(): void
    {
        WorkOrder::query()->delete();
        OperationsReport::query()->delete();

        // Weight districts so Brunei-Muara and Belait are hotspots (more incidents)
        $districtWeights = [
            'brunei-muara' => 45,
            'belait' => 30,
            'tutong' => 18,
            'temburong' => 7,
        ];

        $issueTypes = ['blockage', 'overflow', 'odor', 'maintenance', 'other'];
        $severities = ['urgent', 'nonurgent'];
        $reportStatuses = ['pending', 'pending', 'in_progress', 'in_progress', 'resolved', 'resolved', 'resolved'];

        $locationsByDistrict = [
            'brunei-muara' => [
                ['mukim' => 'gadong-a', 'address' => 'Jalan Gadong, near commercial area', 'lat' => 4.9012, 'lng' => 114.9089],
                ['mukim' => 'gadong-b', 'address' => 'Kampung Gadong, Jalan Pasir', 'lat' => 4.8980, 'lng' => 114.9120],
                ['mukim' => 'sengkurong', 'address' => 'Kampung Sengkurong, Jalan Bengkurong', 'lat' => 4.8850, 'lng' => 114.8210],
                ['mukim' => 'sengkurong', 'address' => 'Jalan Sengkurong, near school', 'lat' => 4.8820, 'lng' => 114.8180],
                ['mukim' => 'berakas-a', 'address' => 'Jalan Berakas, Berakas', 'lat' => 4.9450, 'lng' => 114.9280],
                ['mukim' => 'berakas-b', 'address' => 'Kampung Berakas, residential zone', 'lat' => 4.9420, 'lng' => 114.9320],
                ['mukim' => 'kianggeh', 'address' => 'Kianggeh, Bandar area', 'lat' => 4.8901, 'lng' => 114.9416],
                ['mukim' => 'mentiri', 'address' => 'Kampung Mentiri, Jalan Muara', 'lat' => 4.9650, 'lng' => 115.0100],
                ['mukim' => 'serasa', 'address' => 'Serasa, near port', 'lat' => 4.9720, 'lng' => 115.0550],
                ['mukim' => 'kota-batu', 'address' => 'Kota Batu, historical area', 'lat' => 4.8780, 'lng' => 114.9480],
                ['mukim' => 'lumapas', 'address' => 'Kampung Lumapas', 'lat' => 4.8550, 'lng' => 114.9650],
                ['mukim' => 'gadong-a', 'address' => 'Gadong Central, behind mall', 'lat' => 4.9030, 'lng' => 114.9050],
            ],
            'belait' => [
                ['mukim' => 'seria', 'address' => 'Pump Station, Seria', 'lat' => 4.6142, 'lng' => 114.3194],
                ['mukim' => 'seria', 'address' => 'Jalan Bunga Raya, Seria', 'lat' => 4.6100, 'lng' => 114.3220],
                ['mukim' => 'kuala-belait', 'address' => 'Kuala Belait town centre', 'lat' => 4.5833, 'lng' => 114.1833],
                ['mukim' => 'kuala-belait', 'address' => 'Kampung Kuala Belait, Jalan Sungai', 'lat' => 4.5810, 'lng' => 114.1880],
                ['mukim' => 'liang', 'address' => 'Kampung Liang', 'lat' => 4.6520, 'lng' => 114.4120],
                ['mukim' => 'labi', 'address' => 'Kampung Labi, Labi Road', 'lat' => 4.4200, 'lng' => 114.4500],
                ['mukim' => 'seria', 'address' => 'Seria Bypass, industrial area', 'lat' => 4.6180, 'lng' => 114.3150],
                ['mukim' => 'kuala-balai', 'address' => 'Kuala Balai village', 'lat' => 4.5500, 'lng' => 114.3500],
            ],
            'tutong' => [
                ['mukim' => 'pekan-tutong', 'address' => 'Pekan Tutong, town centre', 'lat' => 4.8028, 'lng' => 114.6492],
                ['mukim' => 'telisai', 'address' => 'Jalan Telisai, Kampung Telisai', 'lat' => 4.7200, 'lng' => 114.5800],
                ['mukim' => 'keriam', 'address' => 'Kampung Keriam', 'lat' => 4.7800, 'lng' => 114.6200],
                ['mukim' => 'lamunin', 'address' => 'Kampung Lamunin', 'lat' => 4.6500, 'lng' => 114.7000],
                ['mukim' => 'pekan-tutong', 'address' => 'Tutong residential, Jalan Bukit', 'lat' => 4.8050, 'lng' => 114.6520],
            ],
            'temburong' => [
                ['mukim' => 'bangar', 'address' => 'Bangar town, Temburong', 'lat' => 4.7081, 'lng' => 115.0717],
                ['mukim' => 'batu-apoi', 'address' => 'Kampung Batu Apoi', 'lat' => 4.6800, 'lng' => 115.1000],
                ['mukim' => 'labu', 'address' => 'Kampung Labu', 'lat' => 4.6200, 'lng' => 115.1500],
                ['mukim' => 'bangar', 'address' => 'Jalan Bangar, near river', 'lat' => 4.7100, 'lng' => 115.0750],
            ],
        ];

        $reporterNames = ['Ahmad bin Abdullah', 'Siti Nurhaliza', 'Mohammad Hassan', 'Linda Wong', 'Abdul Rahman', 'Noraini binti Omar', 'James Lee', 'Fatimah binti Ali', 'Public Works Monitor', 'Resident Committee'];

        $reports = [];
        $now = Carbon::now();
        $reportId = 0;

        foreach (range(1, 55) as $i) {
            $district = $this->weightedRandom($districtWeights);
            $locations = $locationsByDistrict[$district] ?? $locationsByDistrict['brunei-muara'];
            $loc = $locations[array_rand($locations)];

            $issueType = $issueTypes[array_rand($issueTypes)];
            $severity = $severities[array_rand($severities)];
            $status = $reportStatuses[array_rand($reportStatuses)];

            // Keep rows inside the current calendar month so "This month" on Statistics matches seeded data
            $createdAt = $this->randomTimestampInCurrentMonth($now);

            $report = OperationsReport::create([
                'report_number' => OperationsReport::generateReportNumber(),
                'issue_type' => $issueType,
                'severity' => $severity,
                'description' => 'Report #' . ($reportId + 1) . ': ' . ucfirst($issueType) . ' at ' . $loc['address'],
                'reporter_name' => $reporterNames[array_rand($reporterNames)],
                'reporter_contact' => '+673 7' . rand(10, 99) . ' ' . rand(1000, 9999),
                'location_address' => $loc['address'],
                'district' => $district,
                'mukim' => $loc['mukim'],
                'latitude' => $loc['lat'] + (rand(-100, 100) / 10000),
                'longitude' => $loc['lng'] + (rand(-100, 100) / 10000),
                'status' => $status,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
            $reports[] = $report;
            $reportId++;
        }

        $woTypes = ['Blockage Removal', 'Emergency Overflow Response', 'Maintenance', 'Inspection', 'Pump Repair', 'Line Clearing'];
        $woPriorities = ['low', 'medium', 'medium', 'high', 'high', 'critical'];
        $woStatuses = ['pending', 'pending', 'assigned', 'in_progress', 'on_the_way', 'on_site', 'completed', 'completed', 'completed', 'cancelled'];

        $usedReportIds = [];
        foreach (range(1, 45) as $i) {
            $district = $this->weightedRandom($districtWeights);
            $locations = $locationsByDistrict[$district] ?? $locationsByDistrict['brunei-muara'];
            $loc = $locations[array_rand($locations)];

            $status = $woStatuses[array_rand($woStatuses)];
            $priority = $woPriorities[array_rand($woPriorities)];
            $type = $woTypes[array_rand($woTypes)];

            $createdAt = $this->randomTimestampInCurrentMonth($now);
            $report = null;
            if (rand(1, 100) <= 50 && count($usedReportIds) < count($reports)) {
                $candidates = array_filter($reports, fn ($r) => ! in_array($r->id, $usedReportIds));
                if (! empty($candidates)) {
                    $report = $candidates[array_rand($candidates)];
                    $usedReportIds[] = $report->id;
                }
            }

            $assignedAt = in_array($status, ['assigned', 'in_progress', 'on_the_way', 'on_site', 'completed']) ? $createdAt->copy()->addHours(rand(1, 24)) : null;
            $startedAt = in_array($status, ['in_progress', 'on_the_way', 'on_site', 'completed']) ? ($assignedAt ? $assignedAt->copy()->addMinutes(rand(15, 120)) : $createdAt->copy()->addHours(2)) : null;
            $completedAt = $status === 'completed' ? ($startedAt ? $startedAt->copy()->addHours(rand(1, 8)) : $createdAt->copy()->addDays(1)) : null;

            WorkOrder::create([
                'work_order_number' => WorkOrder::generateWorkOrderNumber(),
                'report_id' => $report?->id,
                'crew_id' => null,
                'type' => $type,
                'priority' => $priority,
                'location_address' => $loc['address'],
                'district' => $district,
                'mukim' => $loc['mukim'],
                'latitude' => $loc['lat'] + (rand(-80, 80) / 10000),
                'longitude' => $loc['lng'] + (rand(-80, 80) / 10000),
                'description' => $type . ' – ' . $loc['address'],
                'notes' => $status === 'completed' ? 'Job completed and signed off.' : null,
                'status' => $status,
                'assigned_at' => $assignedAt,
                'started_at' => $startedAt,
                'completed_at' => $completedAt,
                'created_at' => $createdAt,
                'updated_at' => $completedAt ?? $startedAt ?? $assignedAt ?? $createdAt,
            ]);
        }

        $this->command->info('Seeded ' . count($reports) . ' reports and 45 work orders. Hotspots: Brunei-Muara and Belait.');
    }

    private function randomTimestampInCurrentMonth(Carbon $now): Carbon
    {
        $start = $now->copy()->startOfMonth();
        $end = $now->copy();
        $seconds = max(1, (int) $start->diffInSeconds($end));

        return $start->copy()->addSeconds(random_int(0, $seconds));
    }

    private function weightedRandom(array $weights): string
    {
        $total = array_sum($weights);
        $r = rand(1, (int) $total);
        foreach ($weights as $key => $w) {
            $r -= $w;
            if ($r <= 0) {
                return $key;
            }
        }
        return array_key_first($weights);
    }
}
