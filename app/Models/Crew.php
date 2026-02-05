<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Crew extends Model
{
    protected $fillable = [
        'name',
        'contact_phone',
        'contact_email',
        'status',
        'specialization',
        'notes',
    ];

    /**
     * Get all work orders assigned to this crew
     */
    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    /**
     * Get active work orders (pending, assigned, in_progress)
     */
    public function activeWorkOrders(): HasMany
    {
        return $this->workOrders()
            ->whereIn('status', ['pending', 'assigned', 'in_progress']);
    }

    /**
     * Workers assigned to this crew
     */
    public function workers(): HasMany
    {
        return $this->hasMany(Worker::class);
    }
}
