<?php

use App\Jobs\PublishSnsAlert;
use App\Models\Report;
use App\Services\Sns\SnsAlertMessageFormatter;
use App\Services\Sns\SnsNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('app.name', 'BruDMS');
});

test('high risk incident message is plain text not json', function () {
    $message = app(SnsAlertMessageFormatter::class)->format('incident.high_risk', [
        'incident_id' => 2,
        'priority' => 'HIGH',
        'risk_score' => 85,
        'ai_label' => 'SEWAGE_CONFIRMED',
        'url' => 'https://brudms.me/incidents/2/image',
    ], 'critical');

    expect($message)
        ->toStartWith('BruDMS alert (CRITICAL)')
        ->toContain('high-risk sewage incident')
        ->toContain('Incident #2')
        ->toContain('https://brudms.me/incidents/2/image')
        ->not->toStartWith('{');
});

test('urgent report message includes reference and problem', function () {
    $message = app(SnsAlertMessageFormatter::class)->format('report.urgent_submitted', [
        'reference_code' => 'FR SAL/0520/26(2053)',
        'problem_type' => 'Overflow',
        'reporter_name' => 'Ahmad',
        'address' => 'Bandar Seri Begawan',
    ], 'critical');

    expect($message)
        ->toContain('urgent customer report')
        ->toContain('FR SAL/0520/26(2053)')
        ->toContain('Overflow')
        ->toContain('Ahmad');
});

test('work order message describes priority and location', function () {
    $message = app(SnsAlertMessageFormatter::class)->format('work_order.created', [
        'work_order_number' => 'WO-20260520-14BB6A',
        'priority' => 'critical',
        'type' => 'Emergency repair',
        'location_address' => 'Kg Ayer',
    ], 'critical');

    expect($message)
        ->toContain('Critical priority work order')
        ->toContain('WO-20260520-14BB6A')
        ->toContain('Kg Ayer');
});

test('sns notifier subject uses app name', function () {
    Config::set('sns.enabled', true);
    Config::set('sns.queue', true);
    Bus::fake();

    $report = Report::create([
        'reference_code' => 'FR SAL/0520/26(9999)',
        'problem_type' => 'Leak',
        'severity' => 'urgent',
        'status' => Report::STATUS_PENDING,
    ]);

    app(SnsNotifier::class)->customerReportSubmitted($report);

    Bus::assertDispatched(PublishSnsAlert::class, function (PublishSnsAlert $job) {
        return str_starts_with($job->subject, '[BruDMS]')
            && str_contains($job->subject, 'Urgent report');
    });
});
