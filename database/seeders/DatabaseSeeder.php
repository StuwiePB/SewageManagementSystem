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

        // Clear old operations data so only Brunei locations remain
        WorkOrder::query()->delete();
        Report::query()->delete();
        Worker::query()->update(['crew_id' => null]);
        Crew::query()->delete();
        Worker::query()->delete();

        // Seed crews (Brunei)
        $crews = collect([
            [
                'name' => 'Crew Alpha',
                'contact_phone' => '+673 555 1000',
                'contact_email' => 'crew-alpha@example.com',
                'status' => 'available',
                'specialization' => 'Emergency Response',
                'notes' => 'Covers northern districts',
            ],
            [
                'name' => 'Crew Bravo',
                'contact_phone' => '+673 555 2000',
                'contact_email' => 'crew-bravo@example.com',
                'status' => 'on_site',
                'specialization' => 'Maintenance',
                'notes' => 'Pump stations and planned maintenance',
            ],
            [
                'name' => 'Crew Charlie',
                'contact_phone' => '+673 555 3000',
                'contact_email' => 'crew-charlie@example.com',
                'status' => 'available',
                'specialization' => 'Overflow & Blockage',
                'notes' => 'Rapid response for blockages',
            ],
        ])->map(fn ($data) => Crew::create($data));

        // Seed reports (Brunei Darussalam only)
        $reports = collect([
            [
                'issue_type' => 'overflow',
                'severity' => 'critical',
                'description' => 'Major overflow reported near main road junction.',
                'reporter_name' => 'Jane Doe',
                'reporter_contact' => '+673 555 4444',
                'location_address' => 'Jalan Gadong, near commercial area',
                'district' => 'brunei-muara',
                'mukim' => 'gadong-a',
                'latitude' => 4.9012,
                'longitude' => 114.9089,
                'status' => 'in_progress',
            ],
            [
                'issue_type' => 'blockage',
                'severity' => 'high',
                'description' => 'Sewage backup in residential area.',
                'reporter_name' => 'John Smith',
                'reporter_contact' => '+673 555 5555',
                'location_address' => 'Kampung Sengkurong, Jalan Bengkurong',
                'district' => 'brunei-muara',
                'mukim' => 'sengkurong',
                'latitude' => 4.8850,
                'longitude' => 114.8210,
                'status' => 'new',
            ],
            [
                'issue_type' => 'maintenance',
                'severity' => 'medium',
                'description' => 'Routine inspection request for pump station.',
                'reporter_name' => 'City Monitor',
                'reporter_contact' => '+673 555 6666',
                'location_address' => 'Pump Station, Seria',
                'district' => 'belait',
                'mukim' => 'seria',
                'latitude' => 4.6142,
                'longitude' => 114.3194,
                'status' => 'resolved',
            ],
        ])->map(function ($data) {
            return Report::create(array_merge($data, [
                'report_number' => Report::generateReportNumber(),
            ]));
        });

        // Seed work orders (Brunei locations)
        $wo1 = WorkOrder::create([
            'work_order_number' => WorkOrder::generateWorkOrderNumber(),
            'report_id' => $reports[0]->id,
            'crew_id' => $crews[1]->id,
            'type' => 'Emergency Overflow Response',
            'priority' => 'critical',
            'location_address' => $reports[0]->location_address,
            'district' => $reports[0]->district,
            'mukim' => $reports[0]->mukim,
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
            'district' => $reports[1]->district,
            'mukim' => $reports[1]->mukim,
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
            'district' => $reports[2]->district,
            'mukim' => $reports[2]->mukim,
            'latitude' => $reports[2]->latitude,
            'longitude' => $reports[2]->longitude,
            'description' => 'Plan maintenance for pump station.',
            'status' => 'pending',
        ]);

        // Seed unassigned workers (admin-created; operations will assign later)
        $unassignedWorkers = [
            [
                'name' => 'Alex Rivera',
                'photo' => 'https://i.pravatar.cc/150?img=12',
                'employee_id' => 'EMP-001',
                'date_of_birth' => '1985-03-15',
                'address' => 'Kampung Berakas, Jalan Berakas, Brunei-Muara',
                'role' => 'Field Technician',
                'contact_phone' => '+673 555 7100',
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
                'address' => 'Kampung Seria, Belait',
                'role' => 'Maintenance Specialist',
                'contact_phone' => '+673 555 7200',
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
                'address' => 'Kampung Pekan Tutong, Tutong',
                'role' => 'Emergency Responder',
                'contact_phone' => '+673 555 7300',
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
                'address' => 'Kampung Bangar, Temburong',
                'role' => 'Inspector',
                'contact_phone' => '+673 555 7400',
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
