<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->softDeletes();
            $table->string('deletion_reason', 64)->nullable()->after('status');
            $table->text('deletion_notes')->nullable()->after('deletion_reason');
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['deletion_reason', 'deletion_notes']);
        });
    }
};
