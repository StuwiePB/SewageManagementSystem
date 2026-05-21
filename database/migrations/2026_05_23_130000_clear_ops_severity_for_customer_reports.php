<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('operations_reports') || ! Schema::hasColumn('operations_reports', 'severity')) {
            return;
        }

        Schema::table('operations_reports', function (Blueprint $table) {
            $table->string('severity')->nullable()->change();
        });

        if (Schema::hasColumn('operations_reports', 'customer_report_id')) {
            DB::table('operations_reports')
                ->whereNotNull('customer_report_id')
                ->update(['severity' => null]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('operations_reports') || ! Schema::hasColumn('operations_reports', 'severity')) {
            return;
        }

        DB::table('operations_reports')
            ->whereNotNull('customer_report_id')
            ->whereNull('severity')
            ->update(['severity' => 'nonurgent']);

        Schema::table('operations_reports', function (Blueprint $table) {
            $table->string('severity')->nullable(false)->change();
        });
    }
};
