<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Worker extends Model
{
    protected $fillable = [
        'name',
        'photo',
        'employee_id',
        'date_of_birth',
        'address',
        'role',
        'contact_phone',
        'contact_email',
        'status',
        'skills',
        'notes',
        'crew_id',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    /**
     * Crew this worker belongs to (can be null if unassigned)
     */
    public function crew(): BelongsTo
    {
        return $this->belongsTo(Crew::class);
    }

    /**
     * Attendance records for this worker
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(WorkerAttendance::class);
    }
}
