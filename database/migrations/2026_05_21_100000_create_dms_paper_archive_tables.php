<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dms_paper_reports', function (Blueprint $table) {
            $table->id();
            $table->string('archive_number')->unique();
            $table->string('source', 16); // ocr | manual
            $table->string('scanned_image_path')->nullable();
            $table->text('ocr_raw_text')->nullable();
            $table->json('low_confidence_fields')->nullable();
            $table->foreignId('digitized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('dds_file_reference')->nullable();
            $table->string('service_request_reference')->nullable();
            $table->string('contact_name')->nullable();
            $table->json('form_data');
            $table->timestamps();
        });

        Schema::create('dms_archive_work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('work_order_number')->unique();
            $table->string('priority');
            $table->string('assigned_crew')->nullable();
            $table->string('work_type');
            $table->date('estimated_completion_date')->nullable();
            $table->string('location_address');
            $table->string('mukim')->nullable();
            $table->string('problem_category')->nullable();
            $table->text('description')->nullable();
            $table->text('site_notes')->nullable();
            $table->string('status')->default('pending');
            $table->date('record_created_at')->nullable();
            $table->date('record_started_at')->nullable();
            $table->date('record_completed_at')->nullable();
            $table->foreignId('dms_paper_report_id')->nullable()->constrained('dms_paper_reports')->nullOnDelete();
            $table->foreignId('digitized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('dms_archive_work_order_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dms_archive_work_order_id')->constrained('dms_archive_work_orders')->cascadeOnDelete();
            $table->string('path');
            $table->string('photo_type', 16)->default('before'); // before | after
            $table->string('original_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dms_archive_work_order_photos');
        Schema::dropIfExists('dms_archive_work_orders');
        Schema::dropIfExists('dms_paper_reports');
    }
};
