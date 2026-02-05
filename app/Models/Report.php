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
