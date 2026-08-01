<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('operations_reports') || ! Schema::hasColumn('operations_reports', 'severity')) {
            return;
        }

        Schema::table('operations_reports', function (Blueprint $table) {
            $table->dropColumn('severity');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('operations_reports')) {
            return;
        }

        Schema::table('operations_reports', function (Blueprint $table) {
            $table->string('severity')->default('nonurgent')->after('issue_type');
        });
    }
};
