<?php

namespace Database\Seeders;

use App\Models\DmsArchiveWorkOrder;
use App\Models\DmsPaperReport;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DmsArchiveSeeder extends Seeder
{
    /**
     * Seed sample old reports (OM/DDS paper archive) and linked old work orders.
     *
     * Run: php artisan db:seed --class=DmsArchiveSeeder
     */
    public function run(): void
    {
        $digitizedBy = User::query()
            ->where('email', 'operator@ops.brudms.jkr.bn')
            ->value('id');

        $reports = [
            [
                'archive_number' => 'OM-SEED-0001',
                'source' => 'manual',
                'dds_file_reference' => 'DDS-2024-1042',
                'service_request_reference' => 'SR-88231',
                'contact_name' => 'Ahmad Razali bin Hj Md Yusof',
                'incident_at' => '2024-11-14 09:30:00',
                'investigated_at' => '2024-11-15 14:00:00',
                'form' => $this->sampleFormData([
                    'house_location' => 'No. 12, Jalan Gadong, Bandar Seri Begawan',
                    'incident_location' => 'Drainage channel beside Jalan Gadong near Shell station',
                    'problem_code' => 'BLK-DRN-01',
                    'mukim' => 'gadong-b',
                    'assigned_crew' => 'Crew Alpha',
                    'work_details' => 'Severe blockage in main roadside drain causing overflow during rain.',
                    'contact' => [
                        'phone_home' => '+6732234567',
                        'phone_mobile' => '+6738012345',
                        'incident_datetime' => '2024-11-14 09:30',
                    ],
                    'jenis_masalah' => ['tersumbat', 'limpahan'],
                    'kategori_masalah' => ['drain'],
                ]),
            ],
            [
                'archive_number' => 'OM-SEED-0002',
                'source' => 'ocr',
                'dds_file_reference' => 'DDS-2025-0087',
                'service_request_reference' => 'SR-91004',
                'contact_name' => 'Siti Norfazila binti Hj Ahmad',
                'incident_at' => '2025-01-22 16:45:00',
                'investigated_at' => '2025-01-23 10:15:00',
                'low_confidence_fields' => ['contact_name', 'incident_location'],
                'ocr_raw_text' => 'DDS FILE REF: DDS-2025-0087 ... SR-91004 ... Siti Norfazila ...',
                'form' => $this->sampleFormData([
                    'house_location' => 'Kg Sungai Kebun, Unit 4B',
                    'incident_location' => 'Manhole near residential junction, Kg Sungai Kebun',
                    'problem_code' => 'OVF-MH-12',
                    'mukim' => 'sungai-kebun',
                    'assigned_crew' => 'Crew Bravo',
                    'work_details' => 'Manhole overflow reported by nearby residents after heavy rainfall.',
                    'contact' => [
                        'phone_home' => '',
                        'phone_mobile' => '+6738123456',
                        'incident_datetime' => '2025-01-22 16:45',
                    ],
                    'jenis_masalah' => ['limpahan', 'berbau'],
                    'kategori_masalah' => ['manhole'],
                ]),
            ],
            [
                'archive_number' => 'OM-SEED-0003',
                'source' => 'manual',
                'dds_file_reference' => 'DDS-2023-2210',
                'service_request_reference' => 'SR-77419',
                'contact_name' => 'Mohd Faris bin Abdullah',
                'incident_at' => '2023-08-05 11:00:00',
                'investigated_at' => '2023-08-06 08:30:00',
                'form' => $this->sampleFormData([
                    'house_location' => 'Jalan Tutong, Sengkurong',
                    'incident_location' => 'Open drain along Jalan Tutong near school crossing',
                    'problem_code' => 'MNT-DRN-08',
                    'mukim' => 'sengkurong',
                    'assigned_crew' => 'Crew Charlie',
                    'work_details' => 'Routine cleanup needed; sediment buildup reducing drain capacity.',
                    'contact' => [
                        'phone_home' => '+6732345678',
                        'phone_mobile' => '+6738234567',
                        'incident_datetime' => '2023-08-05 11:00',
                    ],
                    'keadaan_kawasan' => ['berlumpur', 'berumput'],
                    'jenis_masalah' => ['tersumbat'],
                    'kategori_masalah' => ['drain', 'culvert'],
                    'approval_decision' => 'approve',
                ]),
            ],
            [
                'archive_number' => 'OM-SEED-0004',
                'source' => 'ocr',
                'dds_file_reference' => 'DDS-2025-0312',
                'service_request_reference' => 'SR-92588',
                'contact_name' => 'Hjh Rosnah binti Hj Mohd',
                'incident_at' => '2025-03-10 07:20:00',
                'investigated_at' => '2025-03-10 15:45:00',
                'form' => $this->sampleFormData([
                    'house_location' => 'Taman Berakas, Block C-22',
                    'incident_location' => 'Retention pond outlet, Taman Berakas',
                    'problem_code' => 'ODR-RP-03',
                    'mukim' => 'berakas-a',
                    'assigned_crew' => 'Crew Delta',
                    'work_details' => 'Persistent sewage odor in morning hours near park walkway.',
                    'contact' => [
                        'phone_mobile' => '+6738345678',
                        'incident_datetime' => '2025-03-10 07:20',
                    ],
                    'jenis_masalah' => ['berbau'],
                    'kategori_masalah' => ['retention_pond'],
                ]),
            ],
            [
                'archive_number' => 'OM-SEED-0005',
                'source' => 'manual',
                'dds_file_reference' => 'DDS-2024-1899',
                'service_request_reference' => 'SR-90112',
                'contact_name' => 'Hamzah Pg Damit',
                'incident_at' => '2024-12-28 18:10:00',
                'investigated_at' => '2024-12-29 09:00:00',
                'form' => $this->sampleFormData([
                    'house_location' => 'Jalan Muara, Kg Serasa',
                    'incident_location' => 'Collapsed drain cover on Jalan Muara main carriageway',
                    'problem_code' => 'HZD-COV-01',
                    'mukim' => 'serasa',
                    'assigned_crew' => 'Crew Echo',
                    'work_details' => 'Collapsed drain cover poses immediate hazard to road users.',
                    'contact' => [
                        'phone_mobile' => '+6738456789',
                        'incident_datetime' => '2024-12-28 18:10',
                    ],
                    'keadaan_kawasan' => ['berlubang_besar'],
                    'jenis_masalah' => ['hilang_penutup', 'rosak'],
                    'kategori_masalah' => ['drain'],
                    'approval_decision' => 'not_approve',
                    'approval_reason' => 'Pending site survey by superintendent before approval.',
                ]),
            ],
        ];

        $reportIds = [];

        foreach ($reports as $row) {
            $form = $row['form'];
            unset($row['form']);

            $report = DmsPaperReport::updateOrCreate(
                ['archive_number' => $row['archive_number']],
                [
                    ...$row,
                    'digitized_by' => $digitizedBy,
                    'form_data' => $form,
                    'created_at' => Carbon::parse($row['incident_at'])->addDays(2),
                    'updated_at' => Carbon::parse($row['investigated_at'] ?? $row['incident_at'])->addDays(3),
                ],
            );

            $reportIds[$row['archive_number']] = $report->id;
        }

        $workOrders = [
            [
                'work_order_number' => 'WO-ARC-SEED-0001',
                'paper_report' => 'OM-SEED-0001',
                'priority' => 'high',
                'assigned_crew' => 'Crew Alpha',
                'work_type' => 'repair',
                'estimated_completion_date' => '2024-11-20 17:00:00',
                'location_address' => 'Jalan Gadong, Bandar Seri Begawan',
                'mukim' => 'gadong-b',
                'problem_category' => 'drain',
                'description' => 'Clear blockage and flush main roadside drain channel.',
                'site_notes' => 'Access via rear lane; coordinate with traffic control.',
                'status' => 'completed',
                'record_created_at' => '2024-11-15 08:00:00',
                'record_started_at' => '2024-11-16 07:30:00',
                'record_completed_at' => '2024-11-18 16:45:00',
            ],
            [
                'work_order_number' => 'WO-ARC-SEED-0002',
                'paper_report' => 'OM-SEED-0002',
                'priority' => 'critical',
                'assigned_crew' => 'Crew Bravo',
                'work_type' => 'emergency',
                'estimated_completion_date' => '2025-01-24 12:00:00',
                'location_address' => 'Kg Sungai Kebun, Bandar Seri Begawan',
                'mukim' => 'sungai-kebun',
                'problem_category' => 'manhole',
                'description' => 'Pump down overflow and inspect manhole chamber.',
                'site_notes' => 'Residents notified; after-hours access approved.',
                'status' => 'completed',
                'record_created_at' => '2025-01-23 09:00:00',
                'record_started_at' => '2025-01-23 11:00:00',
                'record_completed_at' => '2025-01-24 10:30:00',
            ],
            [
                'work_order_number' => 'WO-ARC-SEED-0003',
                'paper_report' => 'OM-SEED-0003',
                'priority' => 'medium',
                'assigned_crew' => 'Crew Charlie',
                'work_type' => 'maintenance',
                'estimated_completion_date' => '2023-08-12 17:00:00',
                'location_address' => 'Jalan Tutong, Sengkurong',
                'mukim' => 'sengkurong',
                'problem_category' => 'drain',
                'description' => 'Desilt open drain and remove vegetation encroachment.',
                'site_notes' => 'Completed under term contract maintenance schedule.',
                'status' => 'completed',
                'record_created_at' => '2023-08-06 08:30:00',
                'record_started_at' => '2023-08-08 07:00:00',
                'record_completed_at' => '2023-08-11 15:00:00',
            ],
            [
                'work_order_number' => 'WO-ARC-SEED-0004',
                'paper_report' => 'OM-SEED-0004',
                'priority' => 'low',
                'assigned_crew' => 'Crew Delta',
                'work_type' => 'inspection',
                'estimated_completion_date' => '2025-03-15 17:00:00',
                'location_address' => 'Taman Berakas retention pond outlet',
                'mukim' => 'berakas-a',
                'problem_category' => 'retention_pond',
                'description' => 'Inspect outlet valve and sample water quality.',
                'site_notes' => 'Follow-up from OM-SEED-0004 odor complaint at retention pond.',
                'status' => 'completed',
                'record_created_at' => '2025-03-11 08:00:00',
                'record_started_at' => '2025-03-12 09:30:00',
                'record_completed_at' => '2025-03-14 16:00:00',
            ],
            [
                'work_order_number' => 'WO-ARC-SEED-0005',
                'paper_report' => 'OM-SEED-0005',
                'priority' => 'critical',
                'assigned_crew' => 'Crew Echo',
                'work_type' => 'emergency',
                'estimated_completion_date' => '2024-12-30 18:00:00',
                'location_address' => 'Jalan Muara, Kg Serasa',
                'mukim' => 'serasa',
                'problem_category' => 'drain',
                'description' => 'Install temporary cover and schedule permanent replacement.',
                'site_notes' => 'Road lane partially closed; JKR signage deployed.',
                'status' => 'completed',
                'record_created_at' => '2024-12-29 09:00:00',
                'record_started_at' => '2024-12-29 10:00:00',
                'record_completed_at' => '2024-12-30 17:30:00',
            ],
        ];

        foreach ($workOrders as $row) {
            $paperKey = $row['paper_report'];
            unset($row['paper_report']);

            if ($paperKey === null || ! isset($reportIds[$paperKey])) {
                throw new \RuntimeException("Old work order {$row['work_order_number']} must be linked to an old report.");
            }

            DmsArchiveWorkOrder::updateOrCreate(
                ['work_order_number' => $row['work_order_number']],
                [
                    ...$row,
                    'dms_paper_report_id' => $reportIds[$paperKey],
                    'digitized_by' => $digitizedBy,
                ],
            );
        }
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function sampleFormData(array $overrides = []): array
    {
        $contact = array_merge([
            'phone_home' => '',
            'phone_mobile' => '',
            'incident_datetime' => '',
        ], $overrides['contact'] ?? []);
        unset($overrides['contact']);

        $base = [
            'house_location' => 'Sample house location',
            'incident_location' => 'Sample incident location',
            'problem_code' => 'GEN-001',
            'mukim' => 'gadong-b',
            'assigned_crew' => 'Crew Alpha',
            'work_details' => 'Sample work details for archived OM/DDS form.',
            'contact' => $contact,
            'service_group' => [
                'type' => 'jkr',
                'jkr' => 'dds',
                'other_govt' => '',
            ],
            'term_contract' => [
                'catchment' => 'BSB Central',
                'contractor' => 'JKR Term Contractor A',
                'in_term_contract' => 'yes',
                'last_maintained_date' => '2024-06-01',
                'next_maintain_date' => '2024-12-01',
            ],
            'suggestions_approval' => [
                'inspection_suggestions' => 'Inspect upstream channel for debris.',
                'review_cta_sta' => 'Reviewed by CTA STA',
                'support_superintendent' => 'Supported by Superintendent',
                'approval_decision' => $overrides['approval_decision'] ?? 'approve',
                'approval_reason' => $overrides['approval_reason'] ?? '',
            ],
            'routing_slip' => [
                [
                    'route' => 'OM Desk',
                    'activity' => 'Receive & log',
                    'tick' => 'Y',
                    'datetime_in' => '09:00',
                    'datetime_out' => '09:30',
                    'tpor' => '15',
                ],
            ],
            'page_two' => [
                'site_sketch' => 'Sketch attached on original paper form.',
                'notes' => 'Digitized from historical paper archive.',
                'comments' => 'No further action required at time of filing.',
                'sketch_by_complainant' => [
                    'signature' => '',
                    'name' => '',
                    'date' => '',
                ],
                'investigated' => [
                    'name' => 'Field Investigator',
                    'datetime' => $contact['incident_datetime'] !== ''
                        ? Carbon::parse($contact['incident_datetime'])->addDay()->format('Y-m-d H:i')
                        : '',
                ],
                'keadaan_kawasan' => $overrides['keadaan_kawasan'] ?? ['bersih'],
                'jenis_masalah' => $overrides['jenis_masalah'] ?? ['tersumbat'],
                'kategori_masalah' => $overrides['kategori_masalah'] ?? ['drain'],
            ],
        ];

        unset($overrides['approval_decision'], $overrides['approval_reason'], $overrides['keadaan_kawasan'], $overrides['jenis_masalah'], $overrides['kategori_masalah']);

        return array_replace_recursive($base, $overrides);
    }
}
