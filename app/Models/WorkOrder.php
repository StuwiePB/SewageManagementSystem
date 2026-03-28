<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkOrder extends Model
{
    protected $fillable = [
        'work_order_number',
        'report_id',
        'crew_id',
        'type',
        'priority',
        'location_address',
        'district',
        'mukim',
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
     * Get the report associated with this work order
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(OperationsReport::class, 'report_id');
    }

    /**
     * Get the crew assigned to this work order
     */
    public function crew(): BelongsTo
    {
        return $this->belongsTo(Crew::class);
    }

    /**
     * Get photos attached to this work order (site visit evidence)
     */
    public function photos(): HasMany
    {
        return $this->hasMany(WorkOrderPhoto::class);
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
