<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Incident extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'photo_path',
        'review_status',
        'ai_label',
        'ai_confidence',
        'ai_severity',
        'ai_reasons',
        'risk_score',
        'evidence_count',
        'evidence',
        'proof',
        'relevant_incident_photo',
        'has_person',
        'person_confidence',
        'person_reason',
        'has_water_or_discharge',
        'person_detected',
        'sewage_score',
        'sewage_indicators',
        'ai_generated',
        'ai_generated_score',
        'analysis_error',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ai_confidence' => 'float',
            'person_confidence' => 'float',
            'sewage_score' => 'float',
            'sewage_indicators' => 'array',
            'ai_generated' => 'boolean',
            'ai_generated_score' => 'float',
            'risk_score' => 'integer',
            'ai_reasons' => 'array',
            'evidence' => 'array',
            'proof' => 'array',
            'evidence_count' => 'integer',
            'relevant_incident_photo' => 'boolean',
            'has_person' => 'boolean',
            'person_detected' => 'boolean',
            'has_water_or_discharge' => 'boolean',
        ];
    }

    /**
     * Get the user that reported the incident.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
