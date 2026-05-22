<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $table = config('permission.table_names.roles', 'roles');
        if (Schema::hasTable($table)) {
            DB::table($table)
                ->where('name', 'operation')
                ->where('guard_name', 'web')
                ->update(['name' => 'operator']);
        }

        DB::table('users')->where('role', 'operation')->update(['role' => 'operator']);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        $table = config('permission.table_names.roles', 'roles');
        if (Schema::hasTable($table)) {
            DB::table($table)
                ->where('name', 'operator')
                ->where('guard_name', 'web')
                ->update(['name' => 'operation']);
        }

        DB::table('users')->where('role', 'operator')->update(['role' => 'operation']);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
