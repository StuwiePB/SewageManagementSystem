<?php

use App\Contracts\SnsPublisher;
use App\Jobs\PublishSnsAlert;
use App\Models\Incident;
use App\Models\Report;
use App\Models\WorkOrder;
use App\Services\Sns\LogSnsPublisher;
use App\Services\Sns\SnsNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('sns.enabled', true);
    Config::set('sns.topic_arn', 'arn:aws:sns:us-east-1:123456789012:bruflow-alerts');
    Config::set('sns.queue', true);
});

test('high risk incident dispatches sns job when enabled', function () {
    Bus::fake();

    $incident = Incident::create([
        'photo_path' => 'incidents/test.jpg',
        'review_status' => 'NEEDS_REVIEW',
        'risk_score' => 85,
        'ai_label' => 'SEWAGE_CONFIRMED',
    ]);

    app(SnsNotifier::class)->incidentHighRisk($incident);

    Bus::assertDispatched(PublishSnsAlert::class, function (PublishSnsAlert $job) {
        return $job->event === 'incident.high_risk'
            && $job->severity === 'critical'
            && $job->data['incident_id'] !== null;
    });
});

test('low risk incident does not dispatch sns job', function () {
    Bus::fake();

    $incident = Incident::create([
        'photo_path' => 'incidents/test.jpg',
        'review_status' => 'NOT_SEWAGE_CONFIRMED',
        'risk_score' => 10,
        'ai_label' => 'NOT_SEWAGE',
    ]);

    app(SnsNotifier::class)->incidentHighRisk($incident);

    Bus::assertNotDispatched(PublishSnsAlert::class);
});

test('urgent customer report dispatches sns on submit', function () {
    Bus::fake();

    $report = Report::create([
        'reference_code' => 'FR SAL/0519/26(0001)',
        'problem_type' => 'Overflow',
        'severity' => 'urgent',
        'status' => Report::STATUS_PENDING,
    ]);

    app(SnsNotifier::class)->customerReportSubmitted($report);

    Bus::assertDispatched(PublishSnsAlert::class, function (PublishSnsAlert $job) {
        return $job->event === 'report.urgent_submitted';
    });
});

test('critical work order dispatches sns job', function () {
    Bus::fake();

    $workOrder = WorkOrder::create([
        'work_order_number' => 'WO-TEST-001',
        'type' => 'repair',
        'priority' => 'critical',
        'location_address' => 'Bandar Seri Begawan',
        'status' => 'pending',
    ]);

    app(SnsNotifier::class)->workOrderCreated($workOrder);

    Bus::assertDispatched(PublishSnsAlert::class, function (PublishSnsAlert $job) {
        return $job->event === 'work_order.created'
            && $job->data['work_order_number'] === 'WO-TEST-001';
    });
});

test('publish job calls sns publisher when sns enabled', function () {
    $publisher = Mockery::mock(SnsPublisher::class);
    $publisher->shouldReceive('publish')
        ->once()
        ->with('test.event', 'Test', ['foo' => 'bar'], 'high');

    $this->app->instance(SnsPublisher::class, $publisher);

    $job = new PublishSnsAlert('test.event', 'Test', ['foo' => 'bar'], 'high');
    $job->handle($publisher);
});

test('sns disabled skips dispatch entirely', function () {
    Bus::fake();
    Config::set('sns.enabled', false);

    $incident = Incident::create([
        'photo_path' => 'incidents/test.jpg',
        'review_status' => 'NEEDS_REVIEW',
        'risk_score' => 99,
    ]);

    app(SnsNotifier::class)->incidentHighRisk($incident);

    Bus::assertNotDispatched(PublishSnsAlert::class);
});

test('log publisher is bound when sns is disabled', function () {
    Config::set('sns.enabled', false);

    expect(app(SnsPublisher::class))->toBeInstanceOf(LogSnsPublisher::class);
});
