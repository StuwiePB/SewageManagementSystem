<?php

namespace Database\Seeders;

use App\Models\Crew;
use Illuminate\Database\Seeder;

class CrewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $crews = [
            [
                'name' => 'Ahmad Hassan',
                'employee_id' => 'WL-001',
                'role' => 'Work Leader',
                'phone' => '+673 712 3456',
                'email' => 'ahmad.hassan@sewage.gov.bn',
                'status' => 'Active',
                'hire_date' => '2018-03-15',
                'specialization' => 'Team coordination, emergency response planning',
            ],
            [
                'name' => 'Hjh Siti Aminah',
                'employee_id' => 'WL-002',
                'role' => 'Work Leader',
                'phone' => '+673 723 4567',
                'email' => 'siti.aminah@sewage.gov.bn',
                'status' => 'Active',
                'hire_date' => '2019-06-20',
                'specialization' => 'Project management, resource allocation',
            ],
            [
                'name' => 'Mohammad Yusof',
                'employee_id' => 'ST-001',
                'role' => 'Senior Technician',
                'phone' => '+673 734 5678',
                'email' => 'mohammad.yusof@sewage.gov.bn',
                'status' => 'Active',
                'hire_date' => '2016-01-10',
                'specialization' => 'Advanced diagnostics, system troubleshooting',
            ],
            [
                'name' => 'Pg Norazlina',
                'employee_id' => 'ST-002',
                'role' => 'Senior Technician',
                'phone' => '+673 745 6789',
                'email' => 'norazlina@sewage.gov.bn',
                'status' => 'Active',
                'hire_date' => '2017-08-22',
                'specialization' => 'Water quality testing, contamination analysis',
            ],
            [
                'name' => 'Haji Ibrahim',
                'employee_id' => 'T-001',
                'role' => 'Technician',
                'phone' => '+673 756 7890',
                'email' => 'haji.ibrahim@sewage.gov.bn',
                'status' => 'Active',
                'hire_date' => '2020-02-15',
                'specialization' => 'Pipe repairs, maintenance operations',
            ],
            [
                'name' => 'Dk Haziq',
                'employee_id' => 'T-002',
                'role' => 'Technician',
                'phone' => '+673 767 8901',
                'email' => 'dk.haziq@sewage.gov.bn',
                'status' => 'Active',
                'hire_date' => '2020-05-10',
                'specialization' => 'Drainage systems, blockage clearing',
            ],
            [
                'name' => 'Azizah Rahman',
                'employee_id' => 'T-003',
                'role' => 'Technician',
                'phone' => '+673 778 9012',
                'email' => 'azizah.rahman@sewage.gov.bn',
                'status' => 'On Leave',
                'hire_date' => '2021-03-18',
                'specialization' => 'Equipment maintenance, tool management',
            ],
            [
                'name' => 'Pengiran Sufian',
                'employee_id' => 'EO-001',
                'role' => 'Equipment Operator',
                'phone' => '+673 789 0123',
                'email' => 'pengiran.sufian@sewage.gov.bn',
                'status' => 'Active',
                'hire_date' => '2019-11-05',
                'specialization' => 'Heavy machinery operation, excavator certified',
            ],
            [
                'name' => 'Awang Kamal',
                'employee_id' => 'EO-002',
                'role' => 'Equipment Operator',
                'phone' => '+673 790 1234',
                'email' => 'awang.kamal@sewage.gov.bn',
                'status' => 'Active',
                'hire_date' => '2020-07-12',
                'specialization' => 'Vacuum truck operation, waste disposal',
            ],
            [
                'name' => 'Dayang Nurulhuda',
                'employee_id' => 'SO-001',
                'role' => 'Safety Officer',
                'phone' => '+673 801 2345',
                'email' => 'dayang.nurulhuda@sewage.gov.bn',
                'status' => 'Active',
                'hire_date' => '2018-09-01',
                'specialization' => 'Safety protocols, hazard assessment, PPE management',
            ],
            [
                'name' => 'Haji Roslan',
                'employee_id' => 'MW-001',
                'role' => 'Maintenance Worker',
                'phone' => '+673 812 3456',
                'email' => 'haji.roslan@sewage.gov.bn',
                'status' => 'Active',
                'hire_date' => '2021-01-20',
                'specialization' => 'General maintenance, facility upkeep',
            ],
            [
                'name' => 'Hjh Ramlah',
                'employee_id' => 'MW-002',
                'role' => 'Maintenance Worker',
                'phone' => '+673 823 4567',
                'email' => 'hjh.ramlah@sewage.gov.bn',
                'status' => 'Active',
                'hire_date' => '2021-06-15',
                'specialization' => 'Cleaning operations, waste management',
            ],
            [
                'name' => 'Awg Hafiz',
                'employee_id' => 'MW-003',
                'role' => 'Maintenance Worker',
                'phone' => '+673 834 5678',
                'email' => 'awg.hafiz@sewage.gov.bn',
                'status' => 'Active',
                'hire_date' => '2022-02-10',
                'specialization' => 'Field support, equipment transport',
            ],
        ];

        foreach ($crews as $crew) {
            Crew::create($crew);
        }
    }
}
