<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrder extends Model
{
    protected $fillable = [
        'work_order_number',
        'report_id',
        'crew_id',
        'type',
        'priority',
        'location_address',
        'latitude',
        'longitude',
        'description',
        'notes',
        'status',
        'assigned_at',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the report associated with this work order
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    /**
     * Get the crew assigned to this work order
     */
    public function crew(): BelongsTo
    {
        return $this->belongsTo(Crew::class);
    }

    /**
     * Generate a unique work order number
     */
    public static function generateWorkOrderNumber(): string
    {
        do {
            $number = 'WO-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        } while (self::where('work_order_number', $number)->exists());

        return $number;
    }
}
