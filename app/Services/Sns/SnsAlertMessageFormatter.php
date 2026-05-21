<?php

namespace App\Services\Sns;

final class SnsAlertMessageFormatter
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function format(string $event, array $data, string $severity): string
    {
        $app = config('app.name');
        $when = now()->format('d M Y, H:i');
        $severityLabel = strtoupper($severity);

        $body = match ($event) {
            'incident.high_risk' => $this->incidentHighRisk($data),
            'incident.sent_to_operations' => $this->incidentSentToOperations($data),
            'report.urgent_submitted' => $this->reportUrgentSubmitted($data),
            'report.sent_to_operations' => $this->reportSentToOperations($data),
            'work_order.created' => $this->workOrderCreated($data),
            default => $this->generic($event, $data),
        };

        return "{$app} alert ({$severityLabel}) — {$when}\n\n{$body}";
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function incidentHighRisk(array $data): string
    {
        $id = $data['incident_id'] ?? '?';
        $priority = $data['priority'] ?? 'UNKNOWN';
        $risk = $data['risk_score'] ?? 'n/a';
        $label = $data['ai_label'] ?? 'unknown';

        $lines = [
            'A high-risk sewage incident requires attention.',
            "Incident #{$id} · priority {$priority} · risk score {$risk} · AI label {$label}.",
        ];

        if (! empty($data['url'])) {
            $lines[] = 'View incident image: '.$data['url'];
        }

        return implode("\n", $lines);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function incidentSentToOperations(array $data): string
    {
        $id = $data['incident_id'] ?? '?';

        $lines = [
            'An incident has been sent to the operations team.',
            "Incident #{$id}.",
        ];

        if (! empty($data['admin_message'])) {
            $lines[] = 'Admin note: '.$this->truncate((string) $data['admin_message']);
        }

        if (! empty($data['url'])) {
            $lines[] = 'View incident image: '.$data['url'];
        }

        return implode("\n", $lines);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function reportUrgentSubmitted(array $data): string
    {
        $ref = $data['reference_code'] ?? 'unknown';
        $problem = $data['problem_type'] ?? 'Not specified';
        $reporter = $data['reporter_name'] ?? 'Unknown reporter';
        $address = $data['address'] ?? 'Address not provided';

        return implode("\n", [
            'An urgent customer report was submitted.',
            "Reference {$ref} · problem: {$problem}.",
            "Reporter: {$reporter}.",
            "Location: {$this->truncate($address, 200)}.",
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function reportSentToOperations(array $data): string
    {
        $ref = $data['reference_code'] ?? 'unknown';
        $ops = $data['operations_report_number'] ?? 'n/a';
        $problem = $data['problem_type'] ?? 'Not specified';
        $address = $data['address'] ?? 'Address not provided';

        return implode("\n", [
            'A customer report was forwarded to operations.',
            "Customer reference {$ref} → operations report {$ops}.",
            "Problem: {$problem}.",
            "Location: {$this->truncate($address, 200)}.",
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function workOrderCreated(array $data): string
    {
        $number = $data['work_order_number'] ?? 'unknown';
        $priority = ucfirst((string) ($data['priority'] ?? 'unknown'));
        $type = $data['type'] ?? 'General';
        $location = $data['location_address'] ?? 'Location not specified';

        return implode("\n", [
            "A {$priority} priority work order was created.",
            "Work order {$number} · type: {$type}.",
            "Location: {$this->truncate($location, 200)}.",
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function generic(string $event, array $data): string
    {
        $summary = collect($data)
            ->except(['url'])
            ->map(fn ($value, $key) => "{$key}: ".(is_scalar($value) ? $value : json_encode($value)))
            ->take(6)
            ->implode(' · ');

        return "Operational alert ({$event}). {$summary}";
    }

    private function truncate(string $text, int $max = 500): string
    {
        $text = trim(preg_replace('/\s+/', ' ', $text) ?? '');

        if (mb_strlen($text) <= $max) {
            return $text;
        }

        return mb_substr($text, 0, $max - 3).'...';
    }
}
