<?php

namespace App\Services;

use App\Jobs\IncidentAnalyzerJob;
use Illuminate\Support\Facades\Log;

/**
 * Vision AI Service (Legacy Compatibility)
 * 
 * This service now delegates to the new Sewage Sentinel pipeline.
 * The new pipeline uses VisionService + WinstonService + IncidentAnalyzerJob.
 * 
 * @deprecated Use IncidentAnalyzerJob directly for new implementations
 */
class VisionAIService
{
    /**
     * Analyze an image using vision AI.
     * 
     * This method now dispatches the new IncidentAnalyzerJob.
     * For direct analysis, use VisionService and WinstonService directly.
     */
    public function analyzeImage(string $imagePath, ?int $incidentId = null): array
    {
        // If we have an incident ID, dispatch the new job
        if ($incidentId) {
            Log::info('VisionAIService delegating to IncidentAnalyzerJob', [
                'incident_id' => $incidentId,
            ]);
            
            // Job will handle the analysis
            IncidentAnalyzerJob::dispatch($incidentId);
            
            // Return a placeholder response (job runs async)
            return [
                'label' => 'PENDING_AI',
                'confidence' => 0.0,
                'severity' => 'low',
                'reasons' => ['Analysis queued - processing in background'],
                'evidence_count' => 0,
                'evidence' => [
                    'source_visible' => false,
                    'active_discharge' => false,
                    'contamination_cues' => false,
                    'sewer_context' => false,
                ],
                'proof' => [
                    'source_object' => 'none',
                    'source_location' => 'unknown',
                    'discharge_description' => 'none',
                    'water_color' => 'unknown',
                    'foam_present' => false,
                    'debris_present' => false,
                ],
                'relevant_incident_photo' => false,
                'has_person' => false,
                'has_water_or_discharge' => false,
            ];
        }

        // Fallback for direct calls without incident ID
        Log::warning('VisionAIService called without incident ID - use IncidentAnalyzerJob directly');
        
        return [
            'label' => 'NEEDS_REVIEW',
            'confidence' => 0.0,
            'severity' => 'low',
            'reasons' => ['Please use IncidentAnalyzerJob for proper analysis'],
            'evidence_count' => 0,
            'evidence' => [
                'source_visible' => false,
                'active_discharge' => false,
                'contamination_cues' => false,
                'sewer_context' => false,
            ],
            'proof' => [
                'source_object' => 'none',
                'source_location' => 'unknown',
                'discharge_description' => 'none',
                'water_color' => 'unknown',
                'foam_present' => false,
                'debris_present' => false,
            ],
            'relevant_incident_photo' => false,
            'has_person' => false,
            'has_water_or_discharge' => false,
        ];
    }
}
