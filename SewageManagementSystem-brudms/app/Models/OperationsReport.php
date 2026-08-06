<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Operational / field reports (Danny admin) — separate from customer {@see Report}.
 */
class OperationsReport extends Model
{
    protected $table = 'operations_reports';

    protected $fillable = [
        'customer_report_id',
        'report_number',
        'issue_type',
        'severity',
        'description',
        'reporter_name',
        'reporter_contact',
        'location_address',
        'district',
        'mukim',
        'latitude',
        'longitude',
        'status',
    ];

    public function customerReport(): BelongsTo
    {
        return $this->belongsTo(Report::class, 'customer_report_id');
    }

    public function workOrder(): HasOne
    {
        return $this->hasOne(WorkOrder::class, 'report_id');
    }

    public function getDistrictDisplayAttribute(): ?string
    {
        return $this->district ? (config("brunei.districts.{$this->district}") ?? $this->district) : null;
    }

    public function getMukimDisplayAttribute(): ?string
    {
        if (! $this->district || ! $this->mukim) {
            return null;
        }

        return config("brunei.mukims.{$this->district}.{$this->mukim}") ?? $this->mukim;
    }

    public static function generateReportNumber(): string
    {
        do {
            $number = 'RPT-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -6));
        } while (self::where('report_number', $number)->exists());

        return $number;
    }
}
