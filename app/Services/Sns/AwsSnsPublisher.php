<?php

namespace App\Services\Sns;

use App\Contracts\SnsPublisher;
use Aws\Sns\SnsClient;
use Illuminate\Support\Facades\Log;

final class AwsSnsPublisher implements SnsPublisher
{
    public function __construct(
        private SnsClient $client,
        private SnsAlertMessageFormatter $formatter,
    ) {}

    public function publish(string $event, string $subject, array $payload, string $severity = 'info'): void
    {
        $topicArn = config('sns.topic_arn');

        if (! $topicArn) {
            Log::warning('AWS SNS publish skipped: AWS_SNS_TOPIC_ARN is not set.');

            return;
        }

        $message = $this->formatter->format($event, $payload, $severity);

        $this->client->publish([
            'TopicArn' => $topicArn,
            'Subject' => mb_substr($subject, 0, 100),
            'Message' => $message,
            'MessageAttributes' => [
                'event' => [
                    'DataType' => 'String',
                    'StringValue' => $event,
                ],
                'severity' => [
                    'DataType' => 'String',
                    'StringValue' => $severity,
                ],
            ],
        ]);
    }
}
