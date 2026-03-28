<?php

namespace App\Services\Reports;

use App\Models\OperationsReport;
use App\Models\Report;

/**
 * Creates an {@see OperationsReport} when an admin sends a customer report to operations (not on customer submit).
 */
final class CustomerReportOperationsSync
{
    public static function syncFromCustomerReport(Report $report): OperationsReport
    {
        $existing = OperationsReport::query()->where('customer_report_id', $report->id)->first();
        if ($existing !== null) {
            return $existing;
        }

        $address = trim((string) ($report->address ?? ''));
        if ($address === '') {
            if ($report->latitude !== null && $report->longitude !== null) {
                $address = sprintf('%.6f, %.6f', (float) $report->latitude, (float) $report->longitude);
            } else {
                $address = 'Location not specified';
            }
        }

        $reporterName = $report->reporter_name ?: $report->user?->name;

        return OperationsReport::create([
            'customer_report_id' => $report->id,
            'report_number' => OperationsReport::generateReportNumber(),
            'issue_type' => $report->problem_type,
            'severity' => $report->severity ?? 'nonurgent',
            'description' => $report->description,
            'reporter_name' => $reporterName,
            'reporter_contact' => $report->phone,
            'location_address' => $address,
            'district' => null,
            'mukim' => null,
            'latitude' => $report->latitude,
            'longitude' => $report->longitude,
            'status' => 'new',
        ]);
    }
}
