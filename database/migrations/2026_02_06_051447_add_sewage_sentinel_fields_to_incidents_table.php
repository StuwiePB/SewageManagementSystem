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
            // New fields for Sewage Sentinel pipeline
            $table->boolean('person_detected')->nullable()->after('has_person');
            $table->float('sewage_score')->nullable()->after('ai_confidence');
            $table->float('ai_generated_score')->nullable()->after('sewage_score');
            
            // Restructure evidence field to match new schema
            // Keep existing evidence field, but we'll use it differently
            // Add analysis_error field for API failures
            $table->text('analysis_error')->nullable()->after('ai_generated_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropColumn([
                'person_detected',
                'sewage_score',
                'ai_generated_score',
                'analysis_error',
            ]);
        });
    }
};
