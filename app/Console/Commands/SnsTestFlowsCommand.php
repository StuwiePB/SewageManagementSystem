<?php

namespace App\Console\Commands;

use App\Models\Incident;
use App\Models\Report;
use App\Models\WorkOrder;
use App\Services\Reports\CustomerReportOperationsSync;
use App\Services\Sns\SnsNotifier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

class SnsTestFlowsCommand extends Command
{
    protected $signature = 'sns:test-flows
                            {--sync : Process SNS jobs immediately (no queue worker needed)}
                            {--dry-run : Show what would run without publishing}
                            {--force : Run even when AWS_SNS_ENABLED=false}';

    protected $description = 'Run all Step 4 SNS alert flows (urgent report, send to ops, work order, high-risk incident)';

    public function handle(SnsNotifier $notifier): int
    {
        if (! config('sns.enabled') && ! $this->option('force')) {
            $this->error('AWS_SNS_ENABLED is false. Set it to true in .env, or pass --force.');

            return self::FAILURE;
        }

        if (! config('sns.topic_arn')) {
            $this->error('AWS_SNS_TOPIC_ARN is not set in .env.');

            return self::FAILURE;
        }

        if ($this->option('sync')) {
            config(['queue.default' => 'sync']);
            $this->info('Queue: sync (SNS jobs run in this command).');
        } elseif (config('sns.queue')) {
            $this->warn('AWS_SNS_QUEUE=true — start `php artisan queue:work` or re-run with --sync.');
        }

        $this->info('Topic: '.config('sns.topic_arn'));
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->comment('Dry run — listing flows only.');

            return self::SUCCESS;
        }

        $tag = now()->format('Ymd-His');

        $this->runFlow('1/5 report.urgent_submitted', function () use ($notifier, $tag) {
            $report = Report::create([
                'reference_code' => Report::generateReferenceCode(),
                'problem_type' => 'SNS test overflow',
                'description' => "SNS Step 4 test {$tag}",
                'status' => Report::STATUS_PENDING,
            ]);
            $notifier->customerReportSubmitted($report);
            $this->line("  Report {$report->reference_code} (id {$report->id})");
        });

        $this->runFlow('2/5 report.sent_to_operations', function () use ($notifier, $tag) {
            $report = Report::create([
                'reference_code' => Report::generateReferenceCode(),
                'problem_type' => 'SNS test send to ops',
                'description' => "SNS Step 4 test {$tag}",
                'status' => Report::STATUS_PENDING,
            ]);
            $ops = CustomerReportOperationsSync::syncFromCustomerReport($report);
            $notifier->customerReportSentToOperations($report, $ops);
            $this->line("  Operations report {$ops->report_number}");
        });

        $this->runFlow('3/5 incident.sent_to_operations', function () use ($notifier, $tag) {
            $path = $this->ensureTestIncidentImage();
            $incident = Incident::create([
                'photo_path' => $path,
                'review_status' => 'NEEDS_REVIEW',
                'risk_score' => 50,
            ]);
            $incident->update([
                'admin_message' => "SNS Step 4 test {$tag}",
                'sent_to_operations_at' => now(),
                'review_status' => 'SENT_TO_OPERATIONS',
            ]);
            $notifier->incidentSentToOperations($incident->fresh());
            $this->line("  Incident #{$incident->id}");
        });

        $this->runFlow('4/5 work_order.created (critical)', function () use ($notifier, $tag) {
            $workOrder = WorkOrder::create([
                'work_order_number' => WorkOrder::generateWorkOrderNumber(),
                'type' => 'SNS test repair',
                'priority' => 'critical',
                'location_address' => "Test location {$tag}",
                'status' => 'pending',
            ]);
            $notifier->workOrderCreated($workOrder);
            $this->line("  Work order {$workOrder->work_order_number}");
        });

        $this->runFlow('5/5 incident.high_risk', function () use ($notifier) {
            $path = $this->ensureTestIncidentImage();
            $incident = Incident::create([
                'photo_path' => $path,
                'review_status' => 'NEEDS_REVIEW',
                'ai_label' => 'SEWAGE_CONFIRMED',
                'risk_score' => max((int) config('sns.min_incident_risk_score', 70), 85),
                'ai_confidence' => 0.92,
            ]);
            $notifier->incidentHighRisk($incident);
            $this->line("  Incident #{$incident->id} risk_score={$incident->risk_score}");
        });

        $pending = Queue::size();
        if ($pending > 0 && ! $this->option('sync')) {
            $this->newLine();
            $this->warn("{$pending} job(s) waiting in queue — run: php artisan queue:work");
        }

        $this->newLine();
        $this->info('Done. Check your SNS email/SMS subscriptions for 5 messages.');

        return self::SUCCESS;
    }

    private function runFlow(string $label, callable $callback): void
    {
        $this->components->task($label, function () use ($callback) {
            $callback();
        });
    }

    private function ensureTestIncidentImage(): string
    {
        $path = 'incidents/sns-test-1x1.png';

        if (! Storage::disk('local')->exists($path)) {
            Storage::disk('local')->put($path, base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
            ));
        }

        return $path;
    }
}
