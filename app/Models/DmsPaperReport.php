<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DmsPaperReport extends Model
{
    protected $fillable = [
        'archive_number',
        'source',
        'scanned_image_path',
        'ocr_raw_text',
        'low_confidence_fields',
        'digitized_by',
        'dds_file_reference',
        'service_request_reference',
        'contact_name',
        'incident_at',
        'investigated_at',
        'form_data',
    ];

    protected function casts(): array
    {
        return [
            'form_data' => 'array',
            'low_confidence_fields' => 'array',
            'incident_at' => 'datetime',
            'investigated_at' => 'datetime',
        ];
    }

    public function digitizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'digitized_by');
    }

    public function archiveWorkOrders(): HasMany
    {
        return $this->hasMany(DmsArchiveWorkOrder::class, 'dms_paper_report_id');
    }

    public static function generateArchiveNumber(): string
    {
        do {
            $number = 'OM-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -6));
        } while (self::where('archive_number', $number)->exists());

        return $number;
    }
}
