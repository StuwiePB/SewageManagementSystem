<?php

use App\Models\OperationsReport;
use App\Models\Report;
use App\Models\WorkOrder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        OperationsReport::query()
            ->whereNotNull('customer_report_id')
            ->with('customerReport')
            ->orderBy('id')
            ->each(function (OperationsReport $operationsReport): void {
                $operationsReport->alignsReportNumberWithCustomerReference();
            });

        WorkOrder::query()
            ->withoutGlobalScopes()
            ->whereNotNull('report_id')
            ->with('report.customerReport')
            ->orderBy('id')
            ->each(function (WorkOrder $workOrder): void {
                $operationsReport = $workOrder->report;
                if ($operationsReport === null || $operationsReport->customerReport === null) {
                    return;
                }

                $reference = $operationsReport->customerReport->ensureReferenceCode();
                if ($workOrder->work_order_number === $reference) {
                    return;
                }

                if (WorkOrder::query()->withoutGlobalScopes()->where('work_order_number', $reference)->whereKeyNot($workOrder->id)->exists()) {
                    return;
                }

                $workOrder->forceFill(['work_order_number' => $reference])->save();
            });

        Report::query()
            ->whereNull('reference_code')
            ->orderBy('id')
            ->each(function (Report $report): void {
                $report->ensureReferenceCode();
            });
    }

    public function down(): void
    {
        // Non-reversible: prior RPT-/WO- values are not stored.
    }
};
