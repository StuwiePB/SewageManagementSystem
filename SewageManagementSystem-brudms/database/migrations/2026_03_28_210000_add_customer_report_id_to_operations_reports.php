<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operations_reports', function (Blueprint $table) {
            $table->foreignId('customer_report_id')
                ->nullable()
                ->after('id')
                ->unique()
                ->constrained('reports')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('operations_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_report_id');
        });
    }
};
