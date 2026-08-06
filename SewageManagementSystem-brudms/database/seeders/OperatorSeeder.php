<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class OperatorSeeder extends Seeder
{
    /**
     * Default operator account for local/dev testing.
     *
     * Email: operator@ops.brudms.jkr.bn
     * Password: override with OPERATOR_PASSWORD in .env
     */
    public function run(): void
    {
        if (User::where('email', 'operator@ops.brudms.jkr.bn')->exists()) {
            return;
        }

        $payload = [
            'name' => 'BruDMS Operations Operator',
            'email' => 'operator@ops.brudms.jkr.bn',
            'password' => Hash::make(env('OPERATOR_PASSWORD', 'Brudms#Operator2026!')),
            'email_verified_at' => now(),
        ];

        if (Schema::hasColumn('users', 'role')) {
            $payload['role'] = User::ROLE_OPERATOR;
        }
        if (Schema::hasColumn('users', 'staffname')) {
            $payload['staffname'] = 'brudms.operator';
        } elseif (Schema::hasColumn('users', 'username')) {
            $payload['username'] = 'brudms.operator';
        }
        if (Schema::hasColumn('users', 'is_active')) {
            $payload['is_active'] = true;
        }

        $user = User::create($payload);

        try {
            $user->assignRole(User::ROLE_OPERATOR);
        } catch (\Throwable $e) {
            // Ignore when permission tables/roles are not migrated in local legacy DB.
        }

        // Keep legacy schema fallback in sync when Spatie tables/role linkage are unavailable.
        if (Schema::hasColumn('users', 'role')) {
            DB::table('users')->where('id', $user->id)->update(['role' => User::ROLE_OPERATOR]);
        }
    }
}
