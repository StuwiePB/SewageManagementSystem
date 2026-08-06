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
        Schema::table('reports', function (Blueprint $table) {
            // Nearest risk_cells.h3_index for this report's lat/lng — lets risk:sync count
            // open reports per hex cell for the drainage risk score's blockage factor, which
            // was silently a no-op until now (the sync command guards on this column existing
            // and just returns an empty count map when it doesn't).
            $table->string('h3_index', 20)->nullable()->after('longitude');
            $table->index('h3_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropIndex(['h3_index']);
            $table->dropColumn('h3_index');
        });
    }
};
