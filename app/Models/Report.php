<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Report extends Model
{
    protected $fillable = [
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

    /**
     * Get the work order associated with this report
     */
    public function workOrder(): HasOne
    {
        return $this->hasOne(WorkOrder::class);
    }

    /**
     * Display name for district (Brunei)
     */
    public function getDistrictDisplayAttribute(): ?string
    {
        return $this->district ? (config("brunei.districts.{$this->district}") ?? $this->district) : null;
    }

    /**
     * Display name for mukim (whereabouts)
     */
    public function getMukimDisplayAttribute(): ?string
    {
        if (!$this->district || !$this->mukim) {
            return null;
        }
        return config("brunei.mukims.{$this->district}.{$this->mukim}") ?? $this->mukim;
    }

    /**
     * Generate a unique report number
     */
    public static function generateReportNumber(): string
    {
        do {
            $number = 'RPT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        } while (self::where('report_number', $number)->exists());

        return $number;
    }
}
