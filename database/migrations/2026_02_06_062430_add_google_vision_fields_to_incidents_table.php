<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            // Ensure fields exist (may already exist from previous migrations)
            if (!Schema::hasColumn('incidents', 'person_detected')) {
                $table->boolean('person_detected')->nullable()->after('has_person');
            }
            if (!Schema::hasColumn('incidents', 'sewage_score')) {
                $table->float('sewage_score')->nullable()->after('ai_confidence');
            }
            if (!Schema::hasColumn('incidents', 'sewage_indicators')) {
                $table->json('sewage_indicators')->nullable()->after('sewage_score');
            }
            if (!Schema::hasColumn('incidents', 'ai_generated')) {
                $table->boolean('ai_generated')->nullable()->after('ai_generated_score');
            }
            if (!Schema::hasColumn('incidents', 'analysis_error')) {
                $table->text('analysis_error')->nullable()->after('ai_generated');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $columns = [
                'person_detected',
                'sewage_score',
                'sewage_indicators',
                'ai_generated',
                'analysis_error',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('incidents', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
