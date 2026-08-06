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
        Schema::table('risk_cells', function (Blueprint $table) {
            $table->decimal('slope_pct', 6, 2)->nullable()->after('drainage_capacity');
            $table->decimal('landslide_score', 5, 2)->default(0)->after('current_band');
            $table->string('landslide_band', 16)->default('low')->after('landslide_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('risk_cells', function (Blueprint $table) {
            $table->dropColumn(['slope_pct', 'landslide_score', 'landslide_band']);
        });
    }
};
