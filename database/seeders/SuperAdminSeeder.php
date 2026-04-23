<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Default super admin (change password after first login).
     *
     * Staffname: brudms.root
     * Email: superadmin@admin.brudms.jkr.bn
     * Password: override with SUPER_ADMIN_PASSWORD in .env
     */
    public function run(): void
    {
        if (User::where('email', 'superadmin@admin.brudms.jkr.bn')->exists()) {
            return;
        }

        $user = User::create([
            'staffname' => 'brudms.root',
            'name' => 'BruDMS Super Administrator',
            'email' => 'superadmin@admin.brudms.jkr.bn',
            'password' => Hash::make(env('SUPER_ADMIN_PASSWORD', 'Brudms#SuperRoot2026!')),
            'role' => User::ROLE_SUPER_ADMIN,
            'email_verified_at' => now(),
        ]);

        $user->assignRole(User::ROLE_SUPER_ADMIN);
    }
}
