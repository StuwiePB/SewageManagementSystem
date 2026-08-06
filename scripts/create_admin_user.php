<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

foreach (['super_admin', 'admin', 'operator', 'customer'] as $role) {
    \Spatie\Permission\Models\Role::findOrCreate($role, 'web');
}

$email = 'admin123@gmail.com';
$password = 'abc123$$';

$user = \App\Models\User::updateOrCreate(
    ['email' => $email],
    [
        'name' => 'Admin User',
        'password' => $password,
        'role' => \App\Models\User::ROLE_ADMIN,
        'staffname' => 'admin123',
        'is_active' => true,
    ]
);

$user->forceFill(['email_verified_at' => now()])->save();
$user->syncRoles([\App\Models\User::ROLE_ADMIN]);

echo "Admin account ready\n";
echo "  Email:    {$email}\n";
echo "  Password: {$password}\n";
echo "  Role:     admin\n";
