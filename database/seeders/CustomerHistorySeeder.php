<?php

namespace Database\Seeders;

use App\Models\OperationsReport;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class CustomerHistorySeeder extends Seeder
{
    /**
     * Seed customer reports that are already linked to operations,
     * so they appear in Customer -> My History.
     */
    public function run(): void
    {
        $customer = User::query()->where('role', User::ROLE_CUSTOMER)->orderBy('id')->first();

        if (! $customer) {
            $payload = [
                'name' => 'Customer Demo',
                'email' => 'customer.demo@brudms.local',
                'password' => Hash::make('Brudms#Customer2026!'),
                'role' => User::ROLE_CUSTOMER,
                'email_verified_at' => now(),
            ];

            if (Schema::hasColumn('users', 'staffname')) {
                $payload['staffname'] = 'customer.demo';
            }
            if (Schema::hasColumn('users', 'is_active')) {
                $payload['is_active'] = true;
            }

            $customer = User::query()->create($payload);
        }

        $rows = [
            ['code' => 'CUS-HIS-0001', 'type' => 'overflow', 'address' => 'Jalan Gadong, Bandar Seri Begawan', 'district' => 'brunei-muara', 'mukim' => 'gadong-b', 'lat' => 4.90510000, 'lng' => 114.93120000],
            ['code' => 'CUS-HIS-0002', 'type' => 'blockage', 'address' => 'Jalan Muara, Kg Serusop', 'district' => 'brunei-muara', 'mukim' => 'berakas-a', 'lat' => 4.94240000, 'lng' => 114.92560000],
            ['code' => 'CUS-HIS-0003', 'type' => 'maintenance', 'address' => 'Jalan Tutong, Sengkurong', 'district' => 'brunei-muara', 'mukim' => 'sengkurong', 'lat' => 4.90870000, 'lng' => 114.84590000],
            ['code' => 'CUS-HIS-0004', 'type' => 'odor', 'address' => 'Tutong Town Area', 'district' => 'tutong', 'mukim' => 'kedayan', 'lat' => 4.80330000, 'lng' => 114.65150000],
            ['code' => 'CUS-HIS-0005', 'type' => 'other', 'address' => 'Kuala Belait Waterfront', 'district' => 'belait', 'mukim' => 'kuala-belait', 'lat' => 4.58260000, 'lng' => 114.19870000],
        ];

        foreach ($rows as $row) {
            $report = Report::query()->updateOrCreate(
                ['reference_code' => $row['code']],
                [
                    'user_id' => $customer->id,
                    'phone' => '+6738000001',
                    'reporter_name' => $customer->name,
                    'problem_type' => $row['type'],
                    'description' => 'Customer history seeded report linked to operations.',
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
                    'issue_type' => $row['type'],
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
