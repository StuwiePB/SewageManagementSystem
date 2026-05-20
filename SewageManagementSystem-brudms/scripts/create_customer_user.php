<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

$email = $argv[1] ?? null;
$password = $argv[2] ?? null;
$name = $argv[3] ?? 'Customer';

if (! is_string($email) || $email === '' || ! is_string($password) || $password === '') {
    fwrite(STDERR, "Usage: php scripts/create_customer_user.php <email> <password> [name]\n");
    exit(2);
}

/** @var \App\Models\User $user */
$user = \App\Models\User::updateOrCreate(
    ['email' => $email],
    [
        'name' => $name,
        'password' => $password, // hashed cast in model
        'role' => \App\Models\User::ROLE_CUSTOMER,
    ]
);

\Spatie\Permission\Models\Role::findOrCreate(\App\Models\User::ROLE_CUSTOMER, 'web');
$user->syncRoles([\App\Models\User::ROLE_CUSTOMER]);

echo "OK user_id={$user->id} email={$user->email}\n";

