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
        Schema::create('risk_cells', function (Blueprint $table) {
            $table->id();
            $table->string('h3_index', 20)->unique();
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->string('district')->nullable();
            $table->decimal('elevation_m', 8, 2)->nullable();
            $table->decimal('drainage_capacity', 3, 2)->nullable(); // 0-1 scale
            $table->unsignedInteger('historical_flood_events')->default(0);
            $table->unsignedInteger('open_blockage_reports')->default(0);
            $table->decimal('current_score', 5, 2)->default(0);
            $table->string('current_band', 16)->default('low');
            $table->json('forecast_scores');
            $table->json('forecast_times');
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index(['current_band', 'current_score']);
            $table->index('district');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_cells');
    }
};
