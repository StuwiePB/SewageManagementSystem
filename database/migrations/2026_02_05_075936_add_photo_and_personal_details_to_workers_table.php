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
        Schema::table('workers', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('name');
            $table->string('employee_id')->nullable()->after('photo');
            $table->date('date_of_birth')->nullable()->after('employee_id');
            $table->text('address')->nullable()->after('date_of_birth');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            $table->dropColumn(['photo', 'employee_id', 'date_of_birth', 'address']);
        });
    }
};
