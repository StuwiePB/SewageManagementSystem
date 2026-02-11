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
        Schema::create('crews', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('employee_id')->unique();
            $table->enum('role', [
                'Work Leader',
                'Senior Technician',
                'Technician',
                'Equipment Operator',
                'Safety Officer',
                'Maintenance Worker'
            ]);
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->enum('status', ['Active', 'On Leave', 'Inactive'])->default('Active');
            $table->date('hire_date')->nullable();
            $table->text('specialization')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crews');
    }
};
