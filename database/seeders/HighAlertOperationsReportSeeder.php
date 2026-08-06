<?php

namespace Database\Seeders;

use App\Models\OperationsReport;
use Illuminate\Database\Seeder;

class HighAlertOperationsReportSeeder extends Seeder
{
    /**
     * Seed 10 high-alert (urgent) operations reports for testing.
     */
    public function run(): void
    {
        $rows = [
            ['report_number' => 'RPT-HA-0001', 'issue_type' => 'overflow', 'location_address' => 'Jalan Gadong, Bandar Seri Begawan', 'district' => 'brunei-muara', 'mukim' => 'gadong-b', 'latitude' => 4.90510000, 'longitude' => 114.93120000],
            ['report_number' => 'RPT-HA-0002', 'issue_type' => 'blockage', 'location_address' => 'Jalan Muara, Kg Serusop', 'district' => 'brunei-muara', 'mukim' => 'berakas-a', 'latitude' => 4.94240000, 'longitude' => 114.92560000],
            ['report_number' => 'RPT-HA-0003', 'issue_type' => 'overflow', 'location_address' => 'Jalan Tutong, Sengkurong', 'district' => 'brunei-muara', 'mukim' => 'sengkurong', 'latitude' => 4.90870000, 'longitude' => 114.84590000],
            ['report_number' => 'RPT-HA-0004', 'issue_type' => 'blockage', 'location_address' => 'Jalan Jerudong, Jerudong', 'district' => 'brunei-muara', 'mukim' => 'sengkurong', 'latitude' => 4.94810000, 'longitude' => 114.83240000],
            ['report_number' => 'RPT-HA-0005', 'issue_type' => 'maintenance', 'location_address' => 'Tutong Town Area', 'district' => 'tutong', 'mukim' => 'kedayan', 'latitude' => 4.80330000, 'longitude' => 114.65150000],
            ['report_number' => 'RPT-HA-0006', 'issue_type' => 'overflow', 'location_address' => 'Kuala Belait Waterfront', 'district' => 'belait', 'mukim' => 'kuala-belait', 'latitude' => 4.58260000, 'longitude' => 114.19870000],
            ['report_number' => 'RPT-HA-0007', 'issue_type' => 'blockage', 'location_address' => 'Seria Town Drain Mainline', 'district' => 'belait', 'mukim' => 'seria', 'latitude' => 4.60670000, 'longitude' => 114.32580000],
            ['report_number' => 'RPT-HA-0008', 'issue_type' => 'overflow', 'location_address' => 'Bangar Riverside, Temburong', 'district' => 'temburong', 'mukim' => 'bangar', 'latitude' => 4.70870000, 'longitude' => 115.07190000],
            ['report_number' => 'RPT-HA-0009', 'issue_type' => 'odor', 'location_address' => 'Jalan Kebangsaan, Bandar Seri Begawan', 'district' => 'brunei-muara', 'mukim' => 'kianggeh', 'latitude' => 4.88960000, 'longitude' => 114.94260000],
            ['report_number' => 'RPT-HA-0010', 'issue_type' => 'other', 'location_address' => 'Jalan Subok, Bandar Seri Begawan', 'district' => 'brunei-muara', 'mukim' => 'kota-batu', 'latitude' => 4.87530000, 'longitude' => 114.96600000],
        ];

        foreach ($rows as $row) {
            OperationsReport::updateOrCreate(
                ['report_number' => $row['report_number']],
                [
                    'issue_type' => $row['issue_type'],
                    'description' => 'High alert case for rapid response and immediate field verification.',
                    'reporter_name' => 'High Alert Test Seeder',
                    'reporter_contact' => '+6738000000',
                    'location_address' => $row['location_address'],
                    'district' => $row['district'],
                    'mukim' => $row['mukim'],
                    'latitude' => $row['latitude'],
                    'longitude' => $row['longitude'],
                    'status' => 'pending',
                ]
            );
        }
    }
}
