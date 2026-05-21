<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * HTTPS subscription endpoint for an SNS topic.
 * Confirm the subscription in AWS, then SNS will POST notifications here.
 */
class SnsWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        try {
            $message = Message::fromJsonString($request->getContent());
        } catch (\Throwable $e) {
            Log::warning('SNS webhook: invalid message body', ['error' => $e->getMessage()]);

            return response('Invalid SNS message', 400);
        }

        if (config('sns.webhook.validate_signature')) {
            $validator = new MessageValidator;

            if (! $validator->isValid($message)) {
                Log::warning('SNS webhook: signature validation failed');

                return response('Invalid signature', 403);
            }
        }

        $type = $message['Type'] ?? null;

        if ($type === 'SubscriptionConfirmation') {
            $subscribeUrl = $message['SubscribeURL'] ?? null;

            if (! $subscribeUrl) {
                return response('Missing SubscribeURL', 400);
            }

            Http::timeout(15)->get($subscribeUrl);

            Log::info('SNS subscription confirmed', [
                'topic_arn' => $message['TopicArn'] ?? null,
            ]);

            return response('Subscription confirmed', 200);
        }

        if ($type === 'UnsubscribeConfirmation') {
            Log::info('SNS unsubscribe confirmation received', [
                'topic_arn' => $message['TopicArn'] ?? null,
            ]);

            return response('OK', 200);
        }

        if ($type === 'Notification') {
            $body = $message['Message'] ?? '';
            $decoded = json_decode($body, true);

            Log::info('SNS notification received', [
                'message_id' => $message['MessageId'] ?? null,
                'topic_arn' => $message['TopicArn'] ?? null,
                'payload' => is_array($decoded) ? $decoded : ['raw' => $body],
            ]);

            return response('OK', 200);
        }

        return response('Unsupported message type', 400);
    }
}
