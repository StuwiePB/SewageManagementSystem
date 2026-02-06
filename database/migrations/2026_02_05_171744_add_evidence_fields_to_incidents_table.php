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
            $table->unsignedTinyInteger('evidence_count')->nullable()->after('risk_score');
            $table->json('evidence')->nullable()->after('evidence_count');
            $table->boolean('relevant_incident_photo')->nullable()->after('evidence');
            $table->boolean('has_person')->nullable()->after('relevant_incident_photo');
            $table->boolean('has_water_or_discharge')->nullable()->after('has_person');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropColumn([
                'evidence_count',
                'evidence',
                'relevant_incident_photo',
                'has_person',
                'has_water_or_discharge',
            ]);
        });
    }
};
