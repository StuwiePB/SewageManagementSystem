<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Danny admin tables without clashing with customer `reports` (see {@see Report}).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crews', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('status')->default('available');
            $table->string('specialization')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('operations_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_number')->unique();
            $table->string('issue_type');
            $table->string('severity');
            $table->text('description')->nullable();
            $table->string('reporter_name')->nullable();
            $table->string('reporter_contact')->nullable();
            $table->string('location_address');
            $table->string('district')->nullable();
            $table->string('mukim')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('status')->default('new');
            $table->timestamps();
        });

        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('work_order_number')->unique();
            $table->foreignId('report_id')->nullable()->constrained('operations_reports')->nullOnDelete();
            $table->foreignId('crew_id')->nullable()->constrained('crews')->nullOnDelete();
            $table->string('type');
            $table->string('priority');
            $table->string('location_address');
            $table->string('district')->nullable();
            $table->string('mukim')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('work_order_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->timestamps();
        });

        Schema::create('workers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('photo')->nullable();
            $table->string('employee_id')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->text('address')->nullable();
            $table->string('role')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('status')->default('available');
            $table->text('skills')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('crew_id')->nullable()->constrained('crews')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('worker_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained('workers')->cascadeOnDelete();
            $table->date('date');
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('clock_in_at')->nullable();
            $table->timestamp('clock_out_at')->nullable();
            $table->timestamps();
            $table->unique(['worker_id', 'date']);
        });

        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('photo_path');
            $table->string('review_status')->default('PENDING_AI');
            $table->string('ai_label')->nullable();
            $table->float('ai_confidence')->nullable();
            $table->float('sewage_score')->nullable();
            $table->json('sewage_indicators')->nullable();
            $table->float('ai_generated_score')->nullable();
            $table->text('analysis_error')->nullable();
            $table->boolean('ai_generated')->nullable();
            $table->string('ai_severity')->nullable();
            $table->json('ai_reasons')->nullable();
            $table->unsignedTinyInteger('risk_score')->nullable();
            $table->unsignedTinyInteger('evidence_count')->nullable();
            $table->json('evidence')->nullable();
            $table->boolean('relevant_incident_photo')->nullable();
            $table->boolean('has_person')->nullable();
            $table->boolean('has_water_or_discharge')->nullable();
            $table->boolean('person_detected')->nullable();
            $table->json('proof')->nullable();
            $table->float('person_confidence')->nullable();
            $table->string('person_reason')->nullable();
            $table->text('admin_message')->nullable();
            $table->timestamp('sent_to_operations_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'crew_id')) {
                $table->foreignId('crew_id')->nullable()->after('profile_photo_path')->constrained('crews')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'crew_id')) {
                $table->dropForeign(['crew_id']);
                $table->dropColumn('crew_id');
            }
        });

        Schema::dropIfExists('incidents');
        Schema::dropIfExists('worker_attendances');
        Schema::dropIfExists('workers');
        Schema::dropIfExists('work_order_photos');
        Schema::dropIfExists('work_orders');
        Schema::dropIfExists('operations_reports');
        Schema::dropIfExists('crews');
    }
};
