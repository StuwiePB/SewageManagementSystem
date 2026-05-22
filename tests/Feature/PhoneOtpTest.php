<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

test('guest report submit is rejected without phone verification', function () {
    $phone = '+673 987 6543';

    $this->postJson(route('guest.report.submit'), [
        'problem_type' => 'Leak',
        'reporter_name' => 'Test User',
        'phone' => $phone,
        'severity' => 'nonurgent',
        'description' => 'Test',
        'address' => 'Somewhere',
        'latitude' => '4.5',
        'longitude' => '114.5',
    ])
        ->assertStatus(422)
        ->assertJsonFragment(['phone']);
});

test('guest report submit succeeds after phone otp verify', function () {
    $phone = '+673 123 4567';

    $this->postJson(route('phone-otp.send'), ['phone' => $phone])->assertOk();
    $this->postJson(route('phone-otp.verify'), [
        'phone' => $phone,
        'code' => '123456',
    ])->assertOk();

    $this->postJson(route('guest.report.submit'), [
        'problem_type' => 'Leak',
        'reporter_name' => 'Test User',
        'phone' => $phone,
        'severity' => 'nonurgent',
        'description' => 'Test',
        'address' => 'Somewhere',
        'latitude' => '4.5',
        'longitude' => '114.5',
    ])
        ->assertOk()
        ->assertJson(['ok' => true]);
});

test('profile phone change requires otp verification', function () {
    $this->seed(RoleSeeder::class);

    $user = User::factory()->create([
        'phone' => '+6731111111',
    ]);
    $user->assignRole(User::ROLE_CUSTOMER);

    $this->actingAs($user);

    $this->post(route('customer.profile.update'), [
        'name' => $user->name,
        'phone' => '+673 222 2222',
    ])
        ->assertSessionHasErrors('phone');

    $this->postJson(route('phone-otp.send'), ['phone' => '+673 222 2222'])
        ->assertOk();

    $this->postJson(route('phone-otp.verify'), [
        'phone' => '+673 222 2222',
        'code' => '123456',
    ])
        ->assertOk();

    $this->post(route('customer.profile.update'), [
        'name' => $user->name,
        'phone' => '+673 222 2222',
    ])
        ->assertRedirect(route('customer.general', ['name' => $user->profileSlug()]));

    expect($user->fresh()->phone)->toBe('+6732222222');
});
