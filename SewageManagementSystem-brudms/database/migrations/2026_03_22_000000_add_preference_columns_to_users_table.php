<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('preference_appearance', 20)->nullable()->after('profile_photo_path');
            $table->string('preference_language', 10)->nullable()->after('preference_appearance');
            $table->string('preference_anonymous', 20)->nullable()->after('preference_language');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['preference_appearance', 'preference_language', 'preference_anonymous']);
        });
    }
};
