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
            $table->json('forecast_rainfall_mm')->nullable()->after('forecast_times');
            $table->unsignedTinyInteger('now_index')->default(0)->after('forecast_rainfall_mm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('risk_cells', function (Blueprint $table) {
            $table->dropColumn(['forecast_rainfall_mm', 'now_index']);
        });
    }
};
