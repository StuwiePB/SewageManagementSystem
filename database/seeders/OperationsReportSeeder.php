<?php

namespace Database\Seeders;

use App\Models\OperationsReport;
use App\Models\Report;
use Illuminate\Database\Seeder;

class OperationsReportSeeder extends Seeder
{
    /**
     * Seed sample operations reports (FR SAL references).
     */
    public function run(): void
    {
        $rows = [
            [
                'report_number' => 'RPT-TEST-0001',
                'issue_type' => 'blockage',
                'description' => 'Severe blockage observed near roadside drain causing overflow during rain.',
                'reporter_name' => 'Test Reporter A',
                'reporter_contact' => '+6738012345',
                'location_address' => 'Jalan Gadong, Bandar Seri Begawan',
                'district' => 'brunei-muara',
                'mukim' => 'gadong-b',
                'latitude' => 4.90360000,
                'longitude' => 114.93970000,
                'status' => 'pending',
            ],
            [
                'report_number' => 'RPT-TEST-0002',
                'issue_type' => 'overflow',
                'description' => 'Manhole overflow reported by nearby residents.',
                'reporter_name' => 'Test Reporter B',
                'reporter_contact' => '+6738123456',
                'location_address' => 'Jalan Muara, Kg Salar',
                'district' => 'brunei-muara',
                'mukim' => 'sabak',
                'latitude' => 4.97010000,
                'longitude' => 115.07140000,
                'status' => 'in_progress',
            ],
            [
                'report_number' => 'RPT-TEST-0003',
                'issue_type' => 'maintenance',
                'description' => 'Routine cleanup needed; sediment buildup in drain channel.',
                'reporter_name' => 'Test Reporter C',
                'reporter_contact' => '+6738234567',
                'location_address' => 'Jalan Tutong, Sengkurong',
                'district' => 'brunei-muara',
                'mukim' => 'sengkurong',
                'latitude' => 4.91610000,
                'longitude' => 114.84120000,
                'status' => 'pending',
            ],
            [
                'report_number' => 'RPT-TEST-0004',
                'issue_type' => 'odor',
                'description' => 'Persistent odor around roadside drain area in residential zone.',
                'reporter_name' => 'Test Reporter D',
                'reporter_contact' => '+6738345678',
                'location_address' => 'Pekan Tutong, Tutong',
                'district' => 'tutong',
                'mukim' => 'kedayan',
                'latitude' => 4.80770000,
                'longitude' => 114.65990000,
                'status' => 'resolved',
            ],
            [
                'report_number' => 'RPT-TEST-0005',
                'issue_type' => 'other',
                'description' => 'Collapsed drain cover poses immediate hazard to road users.',
                'reporter_name' => 'Test Reporter E',
                'reporter_contact' => '+6738456789',
                'location_address' => 'Kuala Belait Town Centre',
                'district' => 'belait',
                'mukim' => 'kuala-belait',
                'latitude' => 4.58360000,
                'longitude' => 114.19160000,
                'status' => 'pending',
            ],
        ];

        foreach ($rows as $row) {
            $existing = OperationsReport::query()
                ->where('description', $row['description'])
                ->where('location_address', $row['location_address'])
                ->first();

            $reference = ($existing && Report::isFrSalReference($existing->report_number))
                ? $existing->report_number
                : Report::generateReferenceCode();

            $payload = $row;
            $payload['report_number'] = $reference;

            OperationsReport::updateOrCreate(
                [
                    'description' => $row['description'],
                    'location_address' => $row['location_address'],
                ],
                $payload
            );
        }
    }
}
