<?php

namespace Database\Seeders;

use App\Models\OperationsReport;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Seeder;

class HighAlertCustomerOperationsSeeder extends Seeder
{
    /**
     * Seed 10 high-alert customer reports and link them to operations.
     */
    public function run(): void
    {
        $customer = User::query()->where('role', User::ROLE_CUSTOMER)->orderBy('id')->first();

        if (! $customer) {
            $this->command?->warn('No customer user found. Seed a customer account first.');

            return;
        }

        $rows = [
            ['code' => 'HA-CUS-0001', 'problem_type' => 'overflow', 'address' => 'Jalan Gadong, Bandar Seri Begawan', 'district' => 'brunei-muara', 'mukim' => 'gadong-b', 'lat' => 4.90510000, 'lng' => 114.93120000],
            ['code' => 'HA-CUS-0002', 'problem_type' => 'blockage', 'address' => 'Jalan Muara, Kg Serusop', 'district' => 'brunei-muara', 'mukim' => 'berakas-a', 'lat' => 4.94240000, 'lng' => 114.92560000],
            ['code' => 'HA-CUS-0003', 'problem_type' => 'overflow', 'address' => 'Jalan Tutong, Sengkurong', 'district' => 'brunei-muara', 'mukim' => 'sengkurong', 'lat' => 4.90870000, 'lng' => 114.84590000],
            ['code' => 'HA-CUS-0004', 'problem_type' => 'blockage', 'address' => 'Jalan Jerudong, Jerudong', 'district' => 'brunei-muara', 'mukim' => 'sengkurong', 'lat' => 4.94810000, 'lng' => 114.83240000],
            ['code' => 'HA-CUS-0005', 'problem_type' => 'maintenance', 'address' => 'Tutong Town Area', 'district' => 'tutong', 'mukim' => 'kedayan', 'lat' => 4.80330000, 'lng' => 114.65150000],
            ['code' => 'HA-CUS-0006', 'problem_type' => 'overflow', 'address' => 'Kuala Belait Waterfront', 'district' => 'belait', 'mukim' => 'kuala-belait', 'lat' => 4.58260000, 'lng' => 114.19870000],
            ['code' => 'HA-CUS-0007', 'problem_type' => 'blockage', 'address' => 'Seria Town Drain Mainline', 'district' => 'belait', 'mukim' => 'seria', 'lat' => 4.60670000, 'lng' => 114.32580000],
            ['code' => 'HA-CUS-0008', 'problem_type' => 'overflow', 'address' => 'Bangar Riverside, Temburong', 'district' => 'temburong', 'mukim' => 'bangar', 'lat' => 4.70870000, 'lng' => 115.07190000],
            ['code' => 'HA-CUS-0009', 'problem_type' => 'odor', 'address' => 'Jalan Kebangsaan, Bandar Seri Begawan', 'district' => 'brunei-muara', 'mukim' => 'kianggeh', 'lat' => 4.88960000, 'lng' => 114.94260000],
            ['code' => 'HA-CUS-0010', 'problem_type' => 'other', 'address' => 'Jalan Subok, Bandar Seri Begawan', 'district' => 'brunei-muara', 'mukim' => 'kota-batu', 'lat' => 4.87530000, 'lng' => 114.96600000],
        ];

        foreach ($rows as $row) {
            $report = Report::query()->updateOrCreate(
                ['reference_code' => $row['code']],
                [
                    'user_id' => $customer->id,
                    'phone' => '+6738000000',
                    'reporter_name' => $customer->name,
                    'problem_type' => $row['problem_type'],
                    'description' => 'High alert customer test report for rapid response validation.',
                    'address' => $row['address'],
                    'latitude' => $row['lat'],
                    'longitude' => $row['lng'],
                    'status' => 'in_progress',
                ]
            );

            OperationsReport::query()->updateOrCreate(
                ['customer_report_id' => $report->id],
                [
                    'report_number' => $report->reference_code,
                    'issue_type' => $row['problem_type'],
                    'description' => $report->description,
                    'reporter_name' => $report->reporter_name,
                    'reporter_contact' => $report->phone,
                    'location_address' => $row['address'],
                    'district' => $row['district'],
                    'mukim' => $row['mukim'],
                    'latitude' => $row['lat'],
                    'longitude' => $row['lng'],
                    'status' => 'pending',
                ]
            );
        }
    }
}
