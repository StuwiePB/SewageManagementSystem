<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

foreach (['super_admin', 'admin', 'operator', 'customer'] as $role) {
    \Spatie\Permission\Models\Role::findOrCreate($role, 'web');
}

$password = 'Password123!';

$ops = \App\Models\User::updateOrCreate(
    ['email' => 'ops@brudms.test'],
    [
        'name' => 'Operations User',
        'password' => $password,
        'role' => \App\Models\User::ROLE_OPERATOR,
        'staffname' => 'ops.user',
        'is_active' => true,
    ]
);
$ops->forceFill(['email_verified_at' => now()])->save();
$ops->syncRoles([\App\Models\User::ROLE_OPERATOR]);

$customer = \App\Models\User::updateOrCreate(
    ['email' => 'customer@brudms.test'],
    [
        'name' => 'Customer User',
        'password' => $password,
        'role' => \App\Models\User::ROLE_CUSTOMER,
        'staffname' => 'customer.user',
        'is_active' => true,
    ]
);
$customer->forceFill(['email_verified_at' => now()])->save();
$customer->syncRoles([\App\Models\User::ROLE_CUSTOMER]);

// Seeded operator account (common local login)
$seededOps = \App\Models\User::where('email', 'operator@ops.brudms.jkr.bn')->first();
if ($seededOps) {
    $seededOps->forceFill([
        'email_verified_at' => now(),
        'password' => env('OPERATOR_PASSWORD', 'Brudms#Operator2026!'),
        'is_active' => true,
    ])->save();
    $seededOps->syncRoles([\App\Models\User::ROLE_OPERATOR]);
}

echo "Operations login\n";
echo "  Email:    ops@brudms.test\n";
echo "  Password: {$password}\n";
echo "  URL:      /login (redirects to operator dashboard)\n\n";
echo "Customer login\n";
echo "  Email:    customer@brudms.test\n";
echo "  Password: {$password}\n";
echo "  URL:      /login (redirects to customer dashboard)\n";
