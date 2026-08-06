<?php

use App\Models\OperationsReport;
use App\Models\Report;
use App\Models\WorkOrder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (class_exists(\App\Models\DmsArchiveWorkOrder::class) && Schema::hasTable('dms_archive_work_orders')) {
            \App\Models\DmsArchiveWorkOrder::query()
                ->orderBy('id')
                ->each(function (\App\Models\DmsArchiveWorkOrder $archive): void {
                    if (! Report::isLegacyReference($archive->work_order_number)) {
                        return;
                    }

                    $archive->forceFill([
                        'work_order_number' => Report::generateReferenceCode(),
                    ])->save();
                });
        }

        OperationsReport::query()
            ->with('customerReport')
            ->orderBy('id')
            ->each(function (OperationsReport $operationsReport): void {
                if ($operationsReport->customerReport !== null) {
                    $operationsReport->alignsReportNumberWithCustomerReference();

                    return;
                }

                if (! Report::isLegacyReference($operationsReport->report_number)) {
                    return;
                }

                $operationsReport->forceFill([
                    'report_number' => Report::generateReferenceCode(),
                ])->save();
            });

        WorkOrder::query()
            ->withoutGlobalScopes()
            ->with('report.customerReport')
            ->orderBy('id')
            ->each(function (WorkOrder $workOrder): void {
                $target = null;

                if ($workOrder->report !== null) {
                    if ($workOrder->report->customerReport !== null) {
                        $workOrder->report->alignsReportNumberWithCustomerReference();
                        $target = $workOrder->report->customerReport->ensureReferenceCode();
                    } elseif (filled($workOrder->report->report_number)) {
                        $target = (string) $workOrder->report->report_number;
                    }
                }

                if ($target === null || Report::isLegacyReference($target)) {
                    $target = Report::generateReferenceCode();
                }

                if ($workOrder->work_order_number === $target) {
                    return;
                }

                if (WorkOrder::query()->withoutGlobalScopes()->where('work_order_number', $target)->whereKeyNot($workOrder->id)->exists()) {
                    $target = Report::generateReferenceCode();
                }

                $workOrder->forceFill(['work_order_number' => $target])->save();
            });
    }

    public function down(): void
    {
        // Prior RPT-/WO- values were not retained.
    }
};
