<?php

namespace App\Jobs;

use App\Models\Incident;
use App\Services\VisionService;
use App\Services\WinstonService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Incident Analyzer Job
 * Orchestrates the Sewage Sentinel pipeline:
 * Step 0: Validate image
 * Step 1: Person/face detection (Vision API)
 * Step 2: Sewage content detection (Vision API)
 * Step 3: AI-generated check (Winston AI)
 * Step 4: Save results and update status
 */
class IncidentAnalyzerJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $incidentId
    ) {
        //
    }

    /**
     * Execute the job
     */
    public function handle(VisionService $visionService, WinstonService $winstonService): void
    {
        $incident = Incident::findOrFail($this->incidentId);

        try {
            // Step 0: Validate image
            if (!$this->validateImage($incident)) {
                $this->updateIncidentWithError($incident, 'Image validation failed');
                return;
            }

            // Step 1 & 2: Vision API analysis (person detection + sewage detection)
            $visionResult = $visionService->analyzeImage($incident->photo_path, $incident->id);

            // Step 1: Check person detection first (HARD GATE)
            if ($visionResult['person_detected'] ?? false) {
                $personConfidence = $visionResult['person_confidence'] ?? 0.0;
                $minConfidence = config('sewage_sentinel.thresholds.person_detected_min', 0.50);

                if ($personConfidence >= $minConfidence) {
                    Log::warning('Person detected - blocking sewage analysis', [
                        'incident_id' => $incident->id,
                        'person_confidence' => $personConfidence,
                    ]);

                    $this->updateIncidentWithResult($incident, [
                        'label' => 'NOT_SEWAGE',
                        'confidence' => config('sewage_sentinel.thresholds.confidence.not_sewage_person', 0.95),
                        'reason' => 'Person/face detected in image. People are never sewage.',
                        'evidence' => [
                            'person_detected' => true,
                            'sewage_indicators' => [],
                            'sewage_score' => 0.0,
                            'ai_generated' => false,
                            'ai_generated_score' => 0.0,
                        ],
                    ]);

                    return; // Skip Winston check (cost optimization)
                }
            }

            // Step 2: Calculate sewage score and determine label
            $sewageScore = $visionResult['sewage_score'] ?? 0.0;
            $sewageIndicators = $visionResult['sewage_indicators'] ?? [];
            $decision = $this->determineLabel($sewageScore);

            // Step 3: Winston AI check (only if person not detected)
            $winstonResult = ['ai_generated' => false, 'ai_generated_score' => 0.0];
            $skipWinston = config('sewage_sentinel.winston.skip_if_person_detected', true);

            if (!$skipWinston || !($visionResult['person_detected'] ?? false)) {
                $publicUrl = $this->getPublicUrl($incident);
                if ($publicUrl) {
                    $winstonResult = $winstonService->checkAuthenticity($publicUrl, $incident->id);
                }
            }

            // Step 4: Update incident with results
            $this->updateIncidentWithResult($incident, [
                'label' => $decision['label'],
                'confidence' => $decision['confidence'],
                'reason' => $decision['reason'],
                'evidence' => [
                    'person_detected' => $visionResult['person_detected'] ?? false,
                    'sewage_indicators' => $sewageIndicators,
                    'sewage_score' => $sewageScore,
                    'ai_generated' => $winstonResult['ai_generated'] ?? false,
                    'ai_generated_score' => $winstonResult['ai_generated_score'] ?? 0.0,
                ],
            ]);

            Log::info("Incident {$incident->id} analyzed successfully", [
                'label' => $decision['label'],
                'confidence' => $decision['confidence'],
                'sewage_score' => $sewageScore,
                'person_detected' => $visionResult['person_detected'] ?? false,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to analyze incident {$incident->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->updateIncidentWithError($incident, $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate image (Step 0)
     */
    private function validateImage(Incident $incident): bool
    {
        if (!Storage::disk('local')->exists($incident->photo_path)) {
            Log::error('Image file not found', [
                'incident_id' => $incident->id,
                'photo_path' => $incident->photo_path,
            ]);
            return false;
        }

        // Check file size (max 10MB)
        $fileSize = Storage::disk('local')->size($incident->photo_path);
        if ($fileSize > 10 * 1024 * 1024) {
            Log::error('Image file too large', [
                'incident_id' => $incident->id,
                'file_size' => $fileSize,
            ]);
            return false;
        }

        // Check if it's a valid image
        $mimeType = Storage::disk('local')->mimeType($incident->photo_path);
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        if (!in_array($mimeType, $allowedTypes)) {
            Log::error('Invalid image type', [
                'incident_id' => $incident->id,
                'mime_type' => $mimeType,
            ]);
            return false;
        }

        return true;
    }

    /**
     * Determine label based on sewage score
     */
    private function determineLabel(float $sewageScore): array
    {
        $thresholds = config('sewage_sentinel.thresholds', []);

        // NEVER auto-confirm SEWAGE - always use NEEDS_REVIEW
        if ($sewageScore >= ($thresholds['sewage_high'] ?? 0.85)) {
            return [
                'label' => 'NEEDS_REVIEW',
                'confidence' => config('sewage_sentinel.thresholds.confidence.needs_review_high', 0.85),
                'reason' => 'Strong sewage indicators detected. Requires human verification.',
            ];
        }

        if ($sewageScore <= ($thresholds['sewage_low'] ?? 0.30)) {
            return [
                'label' => 'NOT_SEWAGE',
                'confidence' => config('sewage_sentinel.thresholds.confidence.not_sewage_low_score', 0.70),
                'reason' => 'No significant sewage indicators detected.',
            ];
        }

        // Medium score - needs review
        return [
            'label' => 'NEEDS_REVIEW',
            'confidence' => config('sewage_sentinel.thresholds.confidence.needs_review_medium', 0.60),
            'reason' => 'Uncertain classification. Requires human review.',
        ];
    }

    /**
     * Get public URL for image (required by Winston AI)
     */
    private function getPublicUrl(Incident $incident): ?string
    {
        try {
            return url("/incidents/{$incident->id}/image");
        } catch (\Throwable $e) {
            Log::warning('Failed to generate public URL', [
                'incident_id' => $incident->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Update incident with analysis result
     */
    private function updateIncidentWithResult(Incident $incident, array $result): void
    {
        $evidence = $result['evidence'] ?? [];

        $incident->update([
            'ai_label' => $result['label'],
            'ai_confidence' => $result['confidence'],
            'review_status' => $result['label'], // Use label as status
            'sewage_score' => $evidence['sewage_score'] ?? 0.0,
            'ai_generated_score' => $evidence['ai_generated_score'] ?? 0.0,
            'person_detected' => $evidence['person_detected'] ?? false,
            'has_person' => $evidence['person_detected'] ?? false,
            'person_confidence' => $evidence['person_detected'] ? ($evidence['person_confidence'] ?? 0.0) : null,
            'ai_reasons' => [$result['reason']],
            'evidence' => $evidence,
            'analysis_error' => null,
        ]);
    }

    /**
     * Update incident with error
     */
    private function updateIncidentWithError(Incident $incident, string $error): void
    {
        $incident->update([
            'review_status' => 'NEEDS_REVIEW',
            'ai_label' => 'NEEDS_REVIEW',
            'ai_confidence' => 0.0,
            'analysis_error' => $error,
            'evidence' => [
                'person_detected' => false,
                'sewage_indicators' => [],
                'sewage_score' => 0.0,
                'ai_generated' => false,
                'ai_generated_score' => 0.0,
                'error' => $error,
            ],
        ]);
    }
}
