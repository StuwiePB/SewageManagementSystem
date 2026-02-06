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
            $table->json('proof')->nullable()->after('evidence');
            $table->float('person_confidence')->nullable()->after('has_person');
            $table->string('person_reason')->nullable()->after('person_confidence');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropColumn(['proof', 'person_confidence', 'person_reason']);
        });
    }
};
