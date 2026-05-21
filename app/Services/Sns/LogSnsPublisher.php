<?php

namespace App\Services\Sns;

use App\Contracts\SnsPublisher;
use Illuminate\Support\Facades\Log;

/**
 * Used when SNS is disabled (local/tests). Records what would have been published.
 */
final class LogSnsPublisher implements SnsPublisher
{
    public function __construct(private SnsAlertMessageFormatter $formatter) {}

    public function publish(string $event, string $subject, array $payload, string $severity = 'info'): void
    {
        Log::debug('SNS alert (not sent — AWS_SNS_ENABLED=false)', [
            'event' => $event,
            'subject' => $subject,
            'severity' => $severity,
            'message' => $this->formatter->format($event, $payload, $severity),
        ]);
    }
}
