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
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('photo_path');
            $table->string('review_status')->default('PENDING_AI');
            $table->string('ai_label')->nullable();
            $table->float('ai_confidence')->nullable();
            $table->string('ai_severity')->nullable();
            $table->json('ai_reasons')->nullable();
            $table->unsignedTinyInteger('risk_score')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
