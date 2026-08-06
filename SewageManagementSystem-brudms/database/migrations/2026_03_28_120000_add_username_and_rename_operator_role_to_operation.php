<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
        });

        $table = config('permission.table_names.roles', 'roles');
        if (Schema::hasTable($table)) {
            $updated = DB::table($table)
                ->where('name', 'operator')
                ->where('guard_name', 'web')
                ->update(['name' => 'operation']);

            if ($updated === 0 && ! DB::table($table)->where('name', 'operation')->where('guard_name', 'web')->exists()) {
                DB::table($table)->insert([
                    'name' => 'operation',
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        DB::table('users')->where('role', 'operator')->update(['role' => 'operation']);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        $table = config('permission.table_names.roles', 'roles');
        if (Schema::hasTable($table)) {
            DB::table($table)
                ->where('name', 'operation')
                ->where('guard_name', 'web')
                ->update(['name' => 'operator']);
        }

        DB::table('users')->where('role', 'operation')->update(['role' => 'operator']);

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
