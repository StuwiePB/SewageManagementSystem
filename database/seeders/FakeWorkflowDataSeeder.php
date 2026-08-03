<?php

namespace Database\Seeders;

use App\Models\Crew;
use App\Models\OperationsReport;
use App\Models\Report;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class FakeWorkflowDataSeeder extends Seeder
{
    /**
     * Seed a realistic end-to-end pipeline of fake data: dedicated demo customers,
     * crews, and reports spanning every stage (customer-only, sent to operations,
     * work order in progress, fully resolved) so the app has believable demo/test
     * content across dashboards, admin queues, operations, and maps.
     */
    public function run(): void
    {
        $customers = $this->demoCustomers();
        $crews = $this->demoCrews();

        $locations = [
            ['district' => 'brunei-muara', 'mukim' => 'gadong-b', 'address' => 'Jalan Gadong, Bandar Seri Begawan', 'lat' => 4.90510000, 'lng' => 114.93120000],
            ['district' => 'brunei-muara', 'mukim' => 'kianggeh', 'address' => 'Jalan Kianggeh, Bandar Seri Begawan', 'lat' => 4.88960000, 'lng' => 114.94260000],
            ['district' => 'brunei-muara', 'mukim' => 'berakas-a', 'address' => 'Jalan Berakas, Kg Serusop', 'lat' => 4.94240000, 'lng' => 114.92560000],
            ['district' => 'brunei-muara', 'mukim' => 'sengkurong', 'address' => 'Jalan Tutong, Sengkurong', 'lat' => 4.90870000, 'lng' => 114.84590000],
            ['district' => 'tutong', 'mukim' => 'pekan-tutong', 'address' => 'Pekan Tutong, Tutong', 'lat' => 4.80330000, 'lng' => 114.65150000],
            ['district' => 'belait', 'mukim' => 'kuala-belait', 'address' => 'Kuala Belait Town Centre', 'lat' => 4.58360000, 'lng' => 114.19160000],
            ['district' => 'belait', 'mukim' => 'seria', 'address' => 'Seria Town Drain Mainline', 'lat' => 4.60670000, 'lng' => 114.32580000],
            ['district' => 'temburong', 'mukim' => 'bangar', 'address' => 'Pekan Bangar, Temburong', 'lat' => 4.70870000, 'lng' => 115.07190000],
            ['district' => 'brunei-muara', 'mukim' => 'kilanas', 'address' => 'Jalan Kilanas, Kg Kilanas', 'lat' => 4.86680000, 'lng' => 114.89230000],
            ['district' => 'brunei-muara', 'mukim' => 'kota-batu', 'address' => 'Jalan Kota Batu, Bandar Seri Begawan', 'lat' => 4.88390000, 'lng' => 114.97220000],
            ['district' => 'brunei-muara', 'mukim' => 'serasa', 'address' => 'Muara Serasa Waterfront', 'lat' => 5.01730000, 'lng' => 115.06710000],
            ['district' => 'tutong', 'mukim' => 'telisai', 'address' => 'Jalan Telisai, Tutong', 'lat' => 4.75940000, 'lng' => 114.58380000],
        ];

        $problemTypes = ['overflow', 'blockage', 'maintenance', 'odor', 'other'];

        $priorities = ['low', 'medium', 'high', 'critical'];

        $stageCount = count($locations);
        $index = 0;

        // Stage 1: customer-only reports, still pending admin triage (not sent to operations).
        foreach (array_slice($locations, 0, 3) as $location) {
            $this->makeCustomerReport(
                $customers[$index % count($customers)],
                $problemTypes[$index % count($problemTypes)],
                $location,
                'pending'
            );
            $index++;
        }

        // Stage 2: sent to operations, awaiting work order creation.
        foreach (array_slice($locations, 3, 3) as $location) {
            $report = $this->makeCustomerReport(
                $customers[$index % count($customers)],
                $problemTypes[$index % count($problemTypes)],
                $location,
                'in_progress'
            );
            $this->makeOperationsReport($report, $location, 'pending');
            $index++;
        }

        // Stage 3: work order created and actively being worked.
        $inProgressWoStatuses = ['assigned', 'on_site', 'in_progress'];
        foreach (array_slice($locations, 6, 3) as $i => $location) {
            $report = $this->makeCustomerReport(
                $customers[$index % count($customers)],
                $problemTypes[$index % count($problemTypes)],
                $location,
                'in_progress'
            );
            $opsReport = $this->makeOperationsReport($report, $location, 'in_progress');
            $this->makeWorkOrder(
                $opsReport,
                $location,
                $inProgressWoStatuses[$i % count($inProgressWoStatuses)],
                $priorities[$index % count($priorities)],
                $crews[$index % count($crews)],
                false
            );
            $index++;
        }

        // Stage 4: fully resolved, end to end.
        foreach (array_slice($locations, 9, 3) as $location) {
            $report = $this->makeCustomerReport(
                $customers[$index % count($customers)],
                $problemTypes[$index % count($problemTypes)],
                $location,
                'resolved'
            );
            $opsReport = $this->makeOperationsReport($report, $location, 'resolved');
            $this->makeWorkOrder(
                $opsReport,
                $location,
                'completed',
                $priorities[$index % count($priorities)],
                $crews[$index % count($crews)],
                true
            );
            $index++;
        }

        $this->command?->info("Seeded fake workflow data across {$stageCount} reports (customer-only, sent-to-operations, in-progress, and resolved stages).");
    }

    /**
     * @return array<int, User>
     */
    protected function demoCustomers(): array
    {
        $seeds = [
            ['name' => 'Ahmad Zulkifli', 'email' => 'demo.ahmad@brudms.local', 'phone' => '+6738700001'],
            ['name' => 'Siti Rahman', 'email' => 'demo.siti@brudms.local', 'phone' => '+6738700002'],
            ['name' => 'Hassan Bakar', 'email' => 'demo.hassan@brudms.local', 'phone' => '+6738700003'],
        ];

        return collect($seeds)->map(function (array $seed) {
            $payload = [
                'name' => $seed['name'],
                'password' => Hash::make('Brudms#Demo2026!'),
                'role' => User::ROLE_CUSTOMER,
                'email_verified_at' => now(),
                'phone' => $seed['phone'],
            ];

            if (Schema::hasColumn('users', 'is_active')) {
                $payload['is_active'] = true;
            }

            $user = User::query()->updateOrCreate(['email' => $seed['email']], $payload);

            if (! $user->hasRole(User::ROLE_CUSTOMER)) {
                try {
                    $user->assignRole(User::ROLE_CUSTOMER);
                } catch (\Throwable) {
                    // Spatie role table may not have this role seeded yet; the users.role
                    // column fallback (already set above) still makes the account work.
                }
            }

            return $user;
        })->all();
    }

    /**
     * @return array<int, Crew>
     */
    protected function demoCrews(): array
    {
        $seeds = [
            ['name' => 'Crew Alpha', 'specialization' => 'Drainage clearing & blockage response', 'contact_phone' => '+6738800001'],
            ['name' => 'Crew Bravo', 'specialization' => 'Overflow & emergency response', 'contact_phone' => '+6738800002'],
            ['name' => 'Crew Charlie', 'specialization' => 'Routine maintenance & inspection', 'contact_phone' => '+6738800003'],
        ];

        return collect($seeds)->map(fn (array $seed) => Crew::query()->updateOrCreate(
            ['name' => $seed['name']],
            [
                'contact_phone' => $seed['contact_phone'],
                'status' => 'available',
                'specialization' => $seed['specialization'],
            ]
        ))->all();
    }

    /**
     * @param  array{district: string, mukim: string, address: string, lat: float, lng: float}  $location
     */
    protected function makeCustomerReport(User $customer, string $problemType, array $location, string $status): Report
    {
        $reference = Report::generateReferenceCode();

        return Report::query()->create([
            'reference_code' => $reference,
            'user_id' => $customer->id,
            'phone' => $customer->phone ?? '+6738700000',
            'reporter_name' => $customer->name,
            'problem_type' => $problemType,
            'description' => $this->descriptionFor($problemType, $location['address']),
            'address' => $location['address'],
            'latitude' => $location['lat'],
            'longitude' => $location['lng'],
            'status' => $status,
        ]);
    }

    /**
     * @param  array{district: string, mukim: string, address: string, lat: float, lng: float}  $location
     */
    protected function makeOperationsReport(Report $report, array $location, string $status): OperationsReport
    {
        return OperationsReport::query()->updateOrCreate(
            ['customer_report_id' => $report->id],
            [
                'report_number' => OperationsReport::reportNumberForCustomerReport($report),
                'issue_type' => $report->problem_type,
                'description' => $report->description,
                'reporter_name' => $report->reporter_name,
                'reporter_contact' => $report->phone,
                'location_address' => $location['address'],
                'district' => $location['district'],
                'mukim' => $location['mukim'],
                'latitude' => $location['lat'],
                'longitude' => $location['lng'],
                'status' => $status,
            ]
        );
    }

    /**
     * @param  array{district: string, mukim: string, address: string, lat: float, lng: float}  $location
     */
    protected function makeWorkOrder(OperationsReport $opsReport, array $location, string $status, string $priority, Crew $crew, bool $completed): WorkOrder
    {
        $assignedAt = now()->subDays(3);
        $startedAt = in_array($status, ['on_site', 'in_progress', 'completed'], true) ? now()->subDays(2) : null;
        $completedAt = $completed ? now()->subDay() : null;

        return WorkOrder::query()->updateOrCreate(
            ['report_id' => $opsReport->id],
            [
                'work_order_number' => WorkOrder::workOrderNumberForOperationsReport($opsReport),
                'crew_id' => $crew->id,
                'type' => $opsReport->issue_type,
                'priority' => $priority,
                'location_address' => $location['address'],
                'district' => $location['district'],
                'mukim' => $location['mukim'],
                'latitude' => $location['lat'],
                'longitude' => $location['lng'],
                'description' => $opsReport->description,
                'notes' => $completed ? 'Site visit completed; issue resolved and verified.' : 'Crew dispatched; work in progress.',
                'status' => $status,
                'assigned_at' => $assignedAt,
                'started_at' => $startedAt,
                'completed_at' => $completedAt,
            ]
        );
    }

    protected function descriptionFor(string $problemType, string $address): string
    {
        $descriptions = [
            'overflow' => "Drain overflow reported near {$address}, water pooling onto the roadway during heavy rain.",
            'blockage' => "Blocked drain observed at {$address}; debris buildup preventing normal water flow.",
            'maintenance' => "Routine maintenance needed at {$address}; sediment accumulation in the drain channel.",
            'odor' => "Persistent foul odor reported around the drain area at {$address}.",
            'other' => "Drainage issue reported at {$address} requiring inspection.",
        ];

        return $descriptions[$problemType] ?? $descriptions['other'];
    }
}
