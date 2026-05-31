<?php

namespace App\Services\Sns;

use App\Contracts\SnsPublisher;
use App\Http\Controllers\IncidentController;
use App\Jobs\PublishSnsAlert;
use App\Models\Incident;
use App\Models\OperationsReport;
use App\Models\Report;
use App\Models\WorkOrder;

final class SnsNotifier
{
    public function __construct(private SnsPublisher $publisher) {}

    public function incidentHighRisk(Incident $incident): void
    {
        if (! $this->shouldAlertHighRiskIncident($incident)) {
            return;
        }

        $this->dispatch(
            event: 'incident.high_risk',
            subject: $this->subject(sprintf('High-risk incident #%d', $incident->id)),
            severity: 'critical',
            data: $this->incidentPayload($incident),
        );
    }

    public function incidentSentToOperations(Incident $incident): void
    {
        $this->dispatch(
            event: 'incident.sent_to_operations',
            subject: $this->subject(sprintf('Incident #%d sent to operations', $incident->id)),
            severity: 'high',
            data: array_merge($this->incidentPayload($incident), [
                'admin_message' => $incident->admin_message,
            ]),
        );
    }

    public function customerReportSentToOperations(Report $report, OperationsReport $operationsReport): void
    {
        $this->dispatch(
            event: 'report.sent_to_operations',
            subject: $this->subject(sprintf('Report %s sent to operations', $report->reference_code)),
            severity: $report->isHighPriorityProblemType() ? 'critical' : 'high',
            data: [
                'report_id' => $report->id,
                'reference_code' => $report->reference_code,
                'problem_type' => $report->problem_type,
                'address' => $report->address,
                'operations_report_id' => $operationsReport->id,
                'operations_report_number' => $operationsReport->report_number,
            ],
        );
    }

    public function customerReportSubmitted(Report $report): void
    {
        if (! $report->isHighPriorityProblemType()) {
            return;
        }

        $this->dispatch(
            event: 'report.urgent_submitted',
            subject: $this->subject(sprintf('Urgent report %s submitted', $report->reference_code)),
            severity: 'critical',
            data: [
                'report_id' => $report->id,
                'reference_code' => $report->reference_code,
                'problem_type' => $report->problem_type,
                'reporter_name' => $report->reporter_name,
                'address' => $report->address,
            ],
        );
    }

    public function workOrderCreated(WorkOrder $workOrder): void
    {
        $priorities = config('sns.work_order_priorities', ['high', 'critical']);

        if (! in_array($workOrder->priority, $priorities, true)) {
            return;
        }

        $this->dispatch(
            event: 'work_order.created',
            subject: $this->subject(sprintf('%s priority work order %s', ucfirst($workOrder->priority), $workOrder->work_order_number)),
            severity: $workOrder->priority === 'critical' ? 'critical' : 'high',
            data: [
                'work_order_id' => $workOrder->id,
                'work_order_number' => $workOrder->work_order_number,
                'priority' => $workOrder->priority,
                'type' => $workOrder->type,
                'location_address' => $workOrder->location_address,
                'status' => $workOrder->status,
                'report_id' => $workOrder->report_id,
            ],
        );
    }

    private function shouldAlertHighRiskIncident(Incident $incident): bool
    {
        $minRisk = (int) config('sns.min_incident_risk_score', 70);

        if ($incident->risk_score !== null && $incident->risk_score >= $minRisk) {
            return true;
        }

        return $incident->review_status === 'NEEDS_REVIEW'
            && $incident->ai_label === 'SEWAGE_CONFIRMED';
    }

    /**
     * @return array<string, mixed>
     */
    private function incidentPayload(Incident $incident): array
    {
        return [
            'incident_id' => $incident->id,
            'review_status' => $incident->review_status,
            'ai_label' => $incident->ai_label,
            'risk_score' => $incident->risk_score,
            'priority' => IncidentController::getPriorityLevel($incident->risk_score),
            'ai_confidence' => $incident->ai_confidence,
            'url' => url('/incidents/'.$incident->id.'/image'),
        ];
    }

    private function subject(string $detail): string
    {
        return sprintf('[%s] %s', config('app.name'), $detail);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function dispatch(string $event, string $subject, array $data, string $severity): void
    {
        if (! config('sns.enabled')) {
            return;
        }

        if (config('sns.queue')) {
            PublishSnsAlert::dispatch($event, $subject, $data, $severity);

            return;
        }

        $this->publisher->publish($event, $subject, $data, $severity);
    }
}
