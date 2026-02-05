<?php

namespace Database\Seeders;

use App\Models\Crew;
use App\Models\Report;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\Worker;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed a demo admin user
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User']
        );

        // Seed crews
        $crews = collect([
            [
                'name' => 'Crew Alpha',
                'contact_phone' => '+1-555-1000',
                'contact_email' => 'crew-alpha@example.com',
                'status' => 'available',
                'specialization' => 'Emergency Response',
                'notes' => 'Covers northern districts',
            ],
            [
                'name' => 'Crew Bravo',
                'contact_phone' => '+1-555-2000',
                'contact_email' => 'crew-bravo@example.com',
                'status' => 'on_site',
                'specialization' => 'Maintenance',
                'notes' => 'Pump stations and planned maintenance',
            ],
            [
                'name' => 'Crew Charlie',
                'contact_phone' => '+1-555-3000',
                'contact_email' => 'crew-charlie@example.com',
                'status' => 'available',
                'specialization' => 'Overflow & Blockage',
                'notes' => 'Rapid response for blockages',
            ],
        ])->map(fn ($data) => Crew::create($data));

        // Seed reports
        $reports = collect([
            [
                'issue_type' => 'overflow',
                'severity' => 'critical',
                'description' => 'Major overflow reported near main street junction.',
                'reporter_name' => 'Jane Doe',
                'reporter_contact' => '+1-555-4444',
                'location_address' => '123 Main St',
                'latitude' => 40.7128,
                'longitude' => -74.0060,
                'status' => 'in_progress',
            ],
            [
                'issue_type' => 'blockage',
                'severity' => 'high',
                'description' => 'Sewage backup in residential block.',
                'reporter_name' => 'John Smith',
                'reporter_contact' => '+1-555-5555',
                'location_address' => '45 Pine Ave',
                'latitude' => 40.7138,
                'longitude' => -74.0010,
                'status' => 'new',
            ],
            [
                'issue_type' => 'maintenance',
                'severity' => 'medium',
                'description' => 'Routine inspection request for pump station.',
                'reporter_name' => 'City Monitor',
                'reporter_contact' => '+1-555-6666',
                'location_address' => 'Pump Station 7',
                'latitude' => 40.7150,
                'longitude' => -74.0100,
                'status' => 'resolved',
            ],
        ])->map(function ($data) {
            return Report::create(array_merge($data, [
                'report_number' => Report::generateReportNumber(),
            ]));
        });

        // Seed work orders
        $wo1 = WorkOrder::create([
            'work_order_number' => WorkOrder::generateWorkOrderNumber(),
            'report_id' => $reports[0]->id,
            'crew_id' => $crews[1]->id,
            'type' => 'Emergency Overflow Response',
            'priority' => 'critical',
            'location_address' => $reports[0]->location_address,
            'latitude' => $reports[0]->latitude,
            'longitude' => $reports[0]->longitude,
            'description' => 'Contain overflow, deploy pumps, and clear main line.',
            'status' => 'assigned',
            'assigned_at' => now()->subHours(2),
        ]);
        $crews[1]->update(['status' => 'on_site']);

        $wo2 = WorkOrder::create([
            'work_order_number' => WorkOrder::generateWorkOrderNumber(),
            'report_id' => $reports[1]->id,
            'crew_id' => $crews[2]->id,
            'type' => 'Blockage Removal',
            'priority' => 'high',
            'location_address' => $reports[1]->location_address,
            'latitude' => $reports[1]->latitude,
            'longitude' => $reports[1]->longitude,
            'description' => 'Clear residential line blockage and inspect for damage.',
            'status' => 'in_progress',
            'assigned_at' => now()->subHours(1),
            'started_at' => now()->subMinutes(45),
        ]);
        $crews[2]->update(['status' => 'on_site']);
        $reports[1]->update(['status' => 'in_progress']);

        $wo3 = WorkOrder::create([
            'work_order_number' => WorkOrder::generateWorkOrderNumber(),
            'report_id' => $reports[2]->id,
            'crew_id' => null,
            'type' => 'Scheduled Maintenance',
            'priority' => 'medium',
            'location_address' => $reports[2]->location_address,
            'latitude' => $reports[2]->latitude,
            'longitude' => $reports[2]->longitude,
            'description' => 'Plan maintenance for pump station 7.',
            'status' => 'pending',
        ]);

        // Seed unassigned workers (admin-created; operations will assign later)
        $unassignedWorkers = [
            [
                'name' => 'Alex Rivera',
                'photo' => 'https://i.pravatar.cc/150?img=12',
                'employee_id' => 'EMP-001',
                'date_of_birth' => '1985-03-15',
                'address' => '123 Oak Street, City',
                'role' => 'Field Technician',
                'contact_phone' => '+1-555-7100',
                'contact_email' => 'alex.r@example.com',
                'status' => 'available',
                'skills' => 'Blockage removal, CCTV inspection',
                'crew_id' => null,
            ],
            [
                'name' => 'Brianna Lee',
                'photo' => 'https://i.pravatar.cc/150?img=47',
                'employee_id' => 'EMP-002',
                'date_of_birth' => '1990-07-22',
                'address' => '456 Pine Avenue, City',
                'role' => 'Maintenance Specialist',
                'contact_phone' => '+1-555-7200',
                'contact_email' => 'brianna.lee@example.com',
                'status' => 'available',
                'skills' => 'Pump maintenance, valve repair',
                'crew_id' => null,
            ],
            [
                'name' => 'Carlos Mendes',
                'photo' => 'https://i.pravatar.cc/150?img=33',
                'employee_id' => 'EMP-003',
                'date_of_birth' => '1988-11-08',
                'address' => '789 Elm Road, City',
                'role' => 'Emergency Responder',
                'contact_phone' => '+1-555-7300',
                'contact_email' => 'carlos.m@example.com',
                'status' => 'off_duty',
                'skills' => 'Overflow containment, hazard response',
                'crew_id' => null,
            ],
            [
                'name' => 'Dana Gupta',
                'photo' => 'https://i.pravatar.cc/150?img=51',
                'employee_id' => 'EMP-004',
                'date_of_birth' => '1992-05-30',
                'address' => '321 Maple Drive, City',
                'role' => 'Inspector',
                'contact_phone' => '+1-555-7400',
                'contact_email' => 'dana.g@example.com',
                'status' => 'available',
                'skills' => 'Site inspection, reporting',
                'crew_id' => null,
            ],
        ];

        $createdWorkers = [];
        foreach ($unassignedWorkers as $worker) {
            $createdWorkers[] = Worker::create($worker);
        }

        // Assign some workers to crews for demo
        if (isset($createdWorkers[0]) && isset($crews[0])) {
            $createdWorkers[0]->update(['crew_id' => $crews[0]->id]); // Alex to Crew Alpha
        }
        if (isset($createdWorkers[1]) && isset($crews[1])) {
            $createdWorkers[1]->update(['crew_id' => $crews[1]->id]); // Brianna to Crew Bravo
        }
    }
}
