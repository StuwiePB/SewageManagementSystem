<?php

use App\Jobs\PublishSnsAlert;
use App\Models\Incident;
use App\Models\Report;
use App\Models\User;
use App\Services\Sns\SnsNotifier;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
    $this->seed(RoleSeeder::class);
    Bus::fake();
    Config::set('sns.enabled', true);
    Config::set('sns.topic_arn', 'arn:aws:sns:us-east-1:123456789012:bruflow-alerts');
    Config::set('sns.queue', true);
});

test('step 4: urgent guest report submits and dispatches sns', function () {
    $phone = '+673 888 7777';

    $this->postJson(route('phone-otp.send'), ['phone' => $phone])->assertOk();
    $this->postJson(route('phone-otp.verify'), ['phone' => $phone, 'code' => '123456'])->assertOk();

    $this->postJson(route('guest.report.submit'), [
        'problem_type' => 'Overflow',
        'reporter_name' => 'SNS Test Guest',
        'phone' => $phone,
        'severity' => 'urgent',
        'description' => 'Step 4 SNS test',
        'address' => 'Bandar Seri Begawan',
        'latitude' => '4.89',
        'longitude' => '114.94',
    ])->assertOk();

    Bus::assertDispatched(PublishSnsAlert::class, fn (PublishSnsAlert $job) => $job->event === 'report.urgent_submitted');
});

test('step 4: admin sends customer report to operations and dispatches sns', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole(User::ROLE_ADMIN);

    $report = Report::create([
        'reference_code' => Report::generateReferenceCode(),
        'problem_type' => 'Leak',
        'severity' => 'nonurgent',
        'status' => Report::STATUS_PENDING,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.customer-reports.send-to-operations', $report))
        ->assertRedirect();

    Bus::assertDispatched(PublishSnsAlert::class, fn (PublishSnsAlert $job) => $job->event === 'report.sent_to_operations');
});

test('step 4: admin sends incident to operations and dispatches sns', function () {
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole(User::ROLE_ADMIN);

    Storage::disk('local')->put('incidents/test.png', base64_decode(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
    ));

    $incident = Incident::create([
        'photo_path' => 'incidents/test.png',
        'review_status' => 'NEEDS_REVIEW',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.incidents.send-to-operations', $incident), [
            'admin_message' => 'Step 4 SNS test',
        ])
        ->assertRedirect();

    Bus::assertDispatched(PublishSnsAlert::class, fn (PublishSnsAlert $job) => $job->event === 'incident.sent_to_operations');
});

test('step 4: operator creates critical work order and dispatches sns', function () {
    $operator = User::factory()->create(['email_verified_at' => now()]);
    $operator->assignRole(User::ROLE_OPERATOR);

    $this->actingAs($operator)
        ->post(route('operations.work-orders.store'), [
            'type' => 'Emergency repair',
            'priority' => 'critical',
            'location_address' => 'Test Site, Brunei',
            'description' => 'Step 4 SNS test',
        ])
        ->assertRedirect();

    Bus::assertDispatched(PublishSnsAlert::class, fn (PublishSnsAlert $job) => $job->event === 'work_order.created');
});

test('step 4: high risk incident after ai analysis dispatches sns', function () {
    Bus::fake([PublishSnsAlert::class]);

    Storage::disk('local')->put('incidents/ai-test.png', base64_decode(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
    ));

    $incident = Incident::create([
        'photo_path' => 'incidents/ai-test.png',
        'review_status' => 'PENDING_AI',
    ]);

    $incident->update([
        'ai_label' => 'SEWAGE_CONFIRMED',
        'review_status' => 'NEEDS_REVIEW',
        'risk_score' => 85,
        'ai_confidence' => 0.9,
    ]);

    app(SnsNotifier::class)->incidentHighRisk($incident->fresh());

    Bus::assertDispatched(PublishSnsAlert::class, fn (PublishSnsAlert $job) => $job->event === 'incident.high_risk');
});
