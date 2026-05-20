<?php

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
    Config::set('sns.webhook.validate_signature', false);
});

test('sns subscription confirmation calls subscribe url', function () {
    Http::fake([
        'https://sns.example.com/confirm' => Http::response('OK', 200),
    ]);

    $payload = [
        'Type' => 'SubscriptionConfirmation',
        'MessageId' => 'msg-1',
        'Token' => 'confirm-token',
        'TopicArn' => 'arn:aws:sns:us-east-1:123456789012:test',
        'Message' => 'You have chosen to subscribe...',
        'SubscribeURL' => 'https://sns.example.com/confirm',
        'Timestamp' => now()->toIso8601String(),
        'SignatureVersion' => '1',
        'Signature' => 'test',
        'SigningCertURL' => 'https://sns.us-east-1.amazonaws.com/cert.pem',
    ];

    $this->postJson(route('webhooks.sns'), $payload)->assertOk();

    Http::assertSent(fn ($request) => $request->url() === 'https://sns.example.com/confirm');
});

test('sns notification returns ok', function () {
    $inner = "BruDMS alert (CRITICAL) — 20 May 2026, 15:00\n\nA high-risk sewage incident requires attention.\nIncident #1 · priority HIGH · risk score 85.";

    $payload = [
        'Type' => 'Notification',
        'MessageId' => 'msg-2',
        'Token' => 'notify-token',
        'TopicArn' => 'arn:aws:sns:us-east-1:123456789012:test',
        'Message' => $inner,
        'Timestamp' => now()->toIso8601String(),
        'SignatureVersion' => '1',
        'Signature' => 'test',
        'SigningCertURL' => 'https://sns.us-east-1.amazonaws.com/cert.pem',
    ];

    $this->postJson(route('webhooks.sns'), $payload)->assertOk();
});
