<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class OperationUserSeeder extends Seeder
{
    /**
     * Default operations dashboard user (change password after first login).
     *
     * Username: brudms.ops (when users.username column exists)
     * Email: ops.demo@operation.brudms.jkr.bn
     * Password: override with OPERATION_USER_PASSWORD in .env
     */
    public function run(): void
    {
        Role::firstOrCreate(['name' => User::ROLE_OPERATION, 'guard_name' => 'web']);

        $email = 'ops.demo@operation.brudms.jkr.bn';

        $user = User::where('email', $email)->first();
        if ($user) {
            $user->assignRole(User::ROLE_OPERATION);

            return;
        }

        $attributes = [
            'name' => 'Operations Demo',
            'email' => $email,
            'password' => Hash::make(env('OPERATION_USER_PASSWORD', 'Brudms#OpsDemo2026!')),
            'role' => User::ROLE_OPERATION,
            'email_verified_at' => now(),
        ];

        if (Schema::hasColumn('users', 'username')) {
            $attributes['username'] = 'brudms.ops';
        }

        $user = User::create($attributes);

        $user->assignRole(User::ROLE_OPERATION);
    }
}
