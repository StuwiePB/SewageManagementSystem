<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DmsArchiveWorkOrder extends Model
{
    protected $fillable = [
        'work_order_number',
        'priority',
        'assigned_crew',
        'work_type',
        'estimated_completion_date',
        'location_address',
        'mukim',
        'problem_category',
        'description',
        'site_notes',
        'status',
        'record_created_at',
        'record_started_at',
        'record_completed_at',
        'dms_paper_report_id',
        'digitized_by',
    ];

    protected function casts(): array
    {
        return [
            'estimated_completion_date' => 'datetime',
            'record_created_at' => 'datetime',
            'record_started_at' => 'datetime',
            'record_completed_at' => 'datetime',
        ];
    }

    public function paperReport(): BelongsTo
    {
        return $this->belongsTo(DmsPaperReport::class, 'dms_paper_report_id');
    }

    public function digitizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'digitized_by');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(DmsArchiveWorkOrderPhoto::class);
    }

    public static function generateWorkOrderNumber(): string
    {
        return Report::generateReferenceCode();
    }
}
