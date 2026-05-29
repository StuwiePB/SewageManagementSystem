<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\DmsArchiveWorkOrder;
use App\Models\DmsPaperReport;
use App\Models\OperationsReport;
use App\Models\Report;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AuditLogSeeder extends Seeder
{
    /**
     * Seed admin audit log entries for Operations and Admin areas.
     *
     * Best run after archive + ops data exist:
     *   php artisan db:seed --class=DmsArchiveSeeder
     *   php artisan db:seed --class=AuditLogSeeder
     */
    public function run(): void
    {
        $this->clearSeedLogs();

        $operator = User::query()->where('email', 'operator@ops.brudms.jkr.bn')->first();
        $superAdmin = User::query()->where('email', 'superadmin@admin.brudms.jkr.bn')->first();

        $this->seedArchiveOperationsLogs($operator);
        $this->seedLiveWorkOrderLogs($operator);
        $this->seedAdminToOperationsLogs($superAdmin);
    }

    private function clearSeedLogs(): void
    {
        AuditLog::query()
            ->whereNotNull('properties')
            ->get()
            ->each(function (AuditLog $log): void {
                if (str_starts_with((string) ($log->properties['seed_ref'] ?? ''), 'AUDIT-SEED-')) {
                    $log->delete();
                }
            });
    }

    private function seedArchiveOperationsLogs(?User $operator): void
    {
        $paperReports = DmsPaperReport::query()
            ->where('archive_number', 'like', 'OM-SEED-%')
            ->orderBy('archive_number')
            ->get();

        foreach ($paperReports as $index => $report) {
            $createdAt = $report->incident_at
                ? Carbon::parse($report->incident_at)->addDays(2)->setTime(9 + $index, 15)
                : now()->subDays(20 - $index);

            $this->insertLog([
                'seed_ref' => 'AUDIT-SEED-OM-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'user' => $operator,
                'area' => AuditLogger::AREA_OPERATIONS,
                'action' => 'old_report.created',
                'description' => sprintf(
                    'Digitized old report %s (%s)',
                    $report->archive_number,
                    $report->source === 'ocr' ? 'OCR upload' : 'manual entry',
                ),
                'subject' => $report,
                'properties' => [
                    'archive_number' => $report->archive_number,
                    'dds_file_reference' => $report->dds_file_reference,
                    'source' => $report->source,
                ],
                'created_at' => $createdAt,
            ]);
        }

        $archiveWorkOrders = DmsArchiveWorkOrder::query()
            ->with('paperReport')
            ->where('work_order_number', 'like', 'WO-ARC-SEED-%')
            ->orderBy('work_order_number')
            ->get();

        foreach ($archiveWorkOrders as $index => $workOrder) {
            $createdAt = $workOrder->record_created_at
                ? Carbon::parse($workOrder->record_created_at)->addHours(1)
                : now()->subDays(15 - $index);

            $this->insertLog([
                'seed_ref' => 'AUDIT-SEED-WO-ARC-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'user' => $operator,
                'area' => AuditLogger::AREA_OPERATIONS,
                'action' => 'old_work_order.created',
                'description' => sprintf('Created old work order %s', $workOrder->work_order_number),
                'subject' => $workOrder,
                'properties' => [
                    'work_order_number' => $workOrder->work_order_number,
                    'priority' => $workOrder->priority,
                    'status' => $workOrder->status,
                    'mukim' => $workOrder->mukim,
                    'archive_number' => $workOrder->paperReport?->archive_number,
                ],
                'created_at' => $createdAt,
            ]);
        }
    }

    private function seedLiveWorkOrderLogs(?User $operator): void
    {
        $workOrders = WorkOrder::query()
            ->withoutGlobalScopes()
            ->with('report')
            ->orderByDesc('id')
            ->limit(4)
            ->get();

        if ($workOrders->isEmpty()) {
            return;
        }

        foreach ($workOrders as $index => $workOrder) {
            $baseTime = $workOrder->created_at ?? now()->subDays(10 - $index);

            $this->insertLog([
                'seed_ref' => 'AUDIT-SEED-WO-LIVE-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT).'-created',
                'user' => $operator,
                'area' => AuditLogger::AREA_OPERATIONS,
                'action' => 'work_order.created',
                'description' => sprintf('Created work order %s for operations report', $workOrder->work_order_number),
                'subject' => $workOrder,
                'properties' => [
                    'work_order_number' => $workOrder->work_order_number,
                    'priority' => $workOrder->priority,
                    'status' => $workOrder->status,
                    'report_number' => $workOrder->report?->report_number,
                ],
                'created_at' => Carbon::parse($baseTime),
            ]);

            if ($index < 2) {
                $this->insertLog([
                    'seed_ref' => 'AUDIT-SEED-WO-LIVE-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT).'-status',
                    'user' => $operator,
                    'area' => AuditLogger::AREA_OPERATIONS,
                    'action' => 'work_order.status_updated',
                    'description' => sprintf(
                        'Changed work order %s status to %s',
                        $workOrder->work_order_number,
                        str_replace('_', ' ', $workOrder->status),
                    ),
                    'subject' => $workOrder,
                    'properties' => [
                        'work_order_number' => $workOrder->work_order_number,
                        'status' => $workOrder->status,
                    ],
                    'created_at' => Carbon::parse($baseTime)->addHours(4),
                ]);
            }
        }
    }

    private function seedAdminToOperationsLogs(?User $superAdmin): void
    {
        if ($superAdmin && $operator = User::query()->where('email', 'operator@ops.brudms.jkr.bn')->first()) {
            $this->insertLog([
                'seed_ref' => 'AUDIT-SEED-ADMIN-STAFF-0001',
                'user' => $superAdmin,
                'area' => AuditLogger::AREA_ADMIN,
                'action' => 'staff.created',
                'description' => sprintf('Created staff account for %s (Operations)', $operator->name),
                'subject' => $operator,
                'properties' => [
                    'email' => $operator->email,
                    'role' => User::ROLE_OPERATOR,
                ],
                'created_at' => now()->subDays(45)->setTime(10, 0),
            ]);

            $this->insertLog([
                'seed_ref' => 'AUDIT-SEED-ADMIN-STAFF-0002',
                'user' => $superAdmin,
                'area' => AuditLogger::AREA_ADMIN,
                'action' => 'staff.activated',
                'description' => sprintf('Activated Operations account %s', $operator->staffname ?? $operator->email),
                'subject' => $operator,
                'properties' => [
                    'email' => $operator->email,
                    'role' => User::ROLE_OPERATOR,
                ],
                'created_at' => now()->subDays(44)->setTime(11, 30),
            ]);
        }

        $customerReports = Report::query()
            ->whereNotNull('reference_code')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        foreach ($customerReports as $index => $report) {
            $this->insertLog([
                'seed_ref' => 'AUDIT-SEED-ADMIN-SENT-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'user' => $superAdmin,
                'area' => AuditLogger::AREA_ADMIN,
                'action' => 'customer_report.sent_to_operations',
                'description' => sprintf(
                    'Sent customer report %s to Operations queue',
                    $report->reference_code ?? '#'.$report->id,
                ),
                'subject' => $report,
                'properties' => [
                    'reference_code' => $report->reference_code,
                    'problem_type' => $report->problem_type,
                    'address' => $report->address,
                ],
                'created_at' => now()->subDays(12 - $index)->setTime(14, 20),
            ]);
        }

        $opsReport = OperationsReport::query()->orderByDesc('id')->first();

        if ($opsReport) {
            $this->insertLog([
                'seed_ref' => 'AUDIT-SEED-ADMIN-INCIDENT-0001',
                'user' => $superAdmin,
                'area' => AuditLogger::AREA_ADMIN,
                'action' => 'incident.sent_to_operations',
                'description' => sprintf('Routed incident %s to Operations for field response', $opsReport->report_number),
                'subject' => $opsReport,
                'properties' => [
                    'report_number' => $opsReport->report_number,
                    'issue_type' => $opsReport->issue_type,
                    'location_address' => $opsReport->location_address,
                ],
                'created_at' => now()->subDays(8)->setTime(9, 45),
            ]);
        }

        $deletedReport = $customerReports->first();

        if ($deletedReport && $superAdmin) {
            $this->insertLog([
                'seed_ref' => 'AUDIT-SEED-ADMIN-DELETED-0001',
                'user' => $superAdmin,
                'area' => AuditLogger::AREA_ADMIN,
                'action' => 'customer_report.deleted',
                'description' => sprintf(
                    'Deleted duplicate customer report %s before Operations pickup',
                    $deletedReport->reference_code ?? '#'.$deletedReport->id,
                ),
                'subject' => $deletedReport,
                'properties' => [
                    'reference_code' => $deletedReport->reference_code,
                    'deletion_reason' => 'duplicate_submission',
                ],
                'created_at' => now()->subDays(11)->setTime(16, 5),
            ]);
        }

        if ($superAdmin) {
            $this->insertLog([
                'seed_ref' => 'AUDIT-SEED-ADMIN-SCAN-0001',
                'user' => $superAdmin,
                'area' => AuditLogger::AREA_ADMIN,
                'action' => 'customer_report.scan_drainage',
                'description' => 'Ran drainage proximity scan on pending customer queue (Brunei-Muara)',
                'subject' => null,
                'properties' => [
                    'district' => 'brunei-muara',
                    'reports_scanned' => 5,
                ],
                'created_at' => now()->subDays(3)->setTime(8, 15),
            ]);
        }
    }

    /**
     * @param  array{
     *     seed_ref: string,
     *     user: ?User,
     *     area: string,
     *     action: string,
     *     description: string,
     *     subject: ?\Illuminate\Database\Eloquent\Model,
     *     properties?: array<string, mixed>,
     *     created_at: \Carbon\CarbonInterface,
     * }  $data
     */
    private function insertLog(array $data): void
    {
        $user = $data['user'];
        $properties = array_merge($data['properties'] ?? [], ['seed_ref' => $data['seed_ref']]);
        $subject = $data['subject'] ?? null;

        AuditLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? $user?->staffname ?? 'System',
            'user_role' => $user ? AuditLogger::resolveRole($user) : null,
            'area' => $data['area'],
            'action' => $data['action'],
            'description' => $data['description'],
            'subject_type' => $subject ? $subject->getMorphClass() : null,
            'subject_id' => $subject?->getKey(),
            'properties' => $properties,
            'ip_address' => '127.0.0.1',
            'created_at' => $data['created_at'],
        ]);
    }
}
