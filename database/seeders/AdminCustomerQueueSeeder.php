<?php

namespace Database\Seeders;

use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminCustomerQueueSeeder extends Seeder
{
    /**
     * Seed unsent customer reports so they appear in Admin customer queue (Unsent).
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
            ['code' => 'ADM-QUE-0001', 'type' => 'overflow', 'address' => 'Jalan Kota Batu, Bandar Seri Begawan', 'lat' => 4.88390000, 'lng' => 114.97220000],
            ['code' => 'ADM-QUE-0002', 'type' => 'blockage', 'address' => 'Jalan Jerudong, Jerudong', 'lat' => 4.94810000, 'lng' => 114.83240000],
            ['code' => 'ADM-QUE-0003', 'type' => 'maintenance', 'address' => 'Jalan Telanai, Bandar Seri Begawan', 'lat' => 4.90130000, 'lng' => 114.90960000],
            ['code' => 'ADM-QUE-0004', 'type' => 'odor', 'address' => 'Pekan Bangar, Temburong', 'lat' => 4.70870000, 'lng' => 115.07190000],
            ['code' => 'ADM-QUE-0005', 'type' => 'other', 'address' => 'Seria Town Drain Mainline', 'lat' => 4.60670000, 'lng' => 114.32580000],
        ];

        foreach ($rows as $row) {
            Report::query()->updateOrCreate(
                ['reference_code' => $row['code']],
                [
                    'user_id' => $customer->id,
                    'phone' => '+6738000002',
                    'reporter_name' => $customer->name,
                    'problem_type' => $row['type'],
                    'description' => 'Admin queue seeded report (not yet sent to operations).',
                    'address' => $row['address'],
                    'latitude' => $row['lat'],
                    'longitude' => $row['lng'],
                    'status' => 'pending',
                ]
            );
        }
    }
}
