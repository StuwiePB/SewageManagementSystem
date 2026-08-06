<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dms_paper_reports', function (Blueprint $table) {
            $table->dateTime('incident_at')->nullable()->after('contact_name');
            $table->dateTime('investigated_at')->nullable()->after('incident_at');
            $table->index('incident_at');
        });

        Schema::table('dms_archive_work_orders', function (Blueprint $table) {
            $table->dateTime('record_created_at')->nullable()->change();
            $table->dateTime('record_started_at')->nullable()->change();
            $table->dateTime('record_completed_at')->nullable()->change();
            $table->dateTime('estimated_completion_date')->nullable()->change();
            $table->index('record_created_at');
        });

        $this->backfillPaperReportDatetimes();
    }

    public function down(): void
    {
        Schema::table('dms_paper_reports', function (Blueprint $table) {
            $table->dropIndex(['incident_at']);
            $table->dropColumn(['incident_at', 'investigated_at']);
        });

        Schema::table('dms_archive_work_orders', function (Blueprint $table) {
            $table->dropIndex(['record_created_at']);
            $table->date('record_created_at')->nullable()->change();
            $table->date('record_started_at')->nullable()->change();
            $table->date('record_completed_at')->nullable()->change();
            $table->date('estimated_completion_date')->nullable()->change();
        });
    }

    private function backfillPaperReportDatetimes(): void
    {
        DB::table('dms_paper_reports')->orderBy('id')->each(function (object $row): void {
            $formData = json_decode($row->form_data, true);
            if (! is_array($formData)) {
                return;
            }

            $incident = $formData['contact']['incident_datetime'] ?? null;
            $investigated = $formData['page_two']['investigated']['datetime'] ?? null;

            DB::table('dms_paper_reports')->where('id', $row->id)->update([
                'incident_at' => $this->parseDatetime($incident),
                'investigated_at' => $this->parseDatetime($investigated),
            ]);
        });
    }

    private function parseDatetime(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (Throwable) {
            return null;
        }
    }
};
