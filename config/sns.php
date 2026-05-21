<?php

return [

    /*
  |--------------------------------------------------------------------------
  | Amazon SNS
  |--------------------------------------------------------------------------
  |
  | Publish operational alerts to an SNS topic (email, SMS, SQS, HTTPS, etc.).
  | Set AWS_SNS_ENABLED=true and AWS_SNS_TOPIC_ARN after creating the topic in AWS.
  |
  */

    'enabled' => (bool) env('AWS_SNS_ENABLED', false),

    'topic_arn' => env('AWS_SNS_TOPIC_ARN'),

    'region' => env('AWS_SNS_REGION', env('AWS_DEFAULT_REGION', 'us-east-1')),

    'key' => env('AWS_ACCESS_KEY_ID'),

    'secret' => env('AWS_SECRET_ACCESS_KEY'),

    'queue' => (bool) env('AWS_SNS_QUEUE', true),

    'min_incident_risk_score' => (int) env('AWS_SNS_MIN_INCIDENT_RISK_SCORE', 70),

    'work_order_priorities' => array_values(array_filter(array_map(
        trim(...),
        explode(',', (string) env('AWS_SNS_WORK_ORDER_PRIORITIES', 'high,critical'))
    ))),

    'webhook' => [
        'validate_signature' => (bool) env('AWS_SNS_WEBHOOK_VALIDATE_SIGNATURE', true),
    ],

];
