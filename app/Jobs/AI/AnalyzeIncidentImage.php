<?php

namespace App\Jobs\AI;

use App\Models\Incident;
use App\Services\AI\GoogleVisionService;
use App\Services\AI\SewageClassifier;
use App\Services\AI\WinstonService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

/**
 * Analyze Incident Image Job
 * 
 * Pipeline:
 * Step 0: Validate image
 * Step 1: Google Vision face detection gate
 * Step 2: Google Vision label detection + sewage scoring
 * Step 3: Winston AI ai-generated check
 * Step 4: Save results and update incident
 */
class AnalyzeIncidentImage implements ShouldQueue
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
    public function handle(
        GoogleVisionService $visionService,
        SewageClassifier $classifier,
        WinstonService $winstonService
    ): void {
        $incident = Incident::findOrFail($this->incidentId);
        
        // Store vision result for later use
        $visionResult = null;

        try {
            // Step 0: Validate image
            if (!$this->validateImage($incident)) {
                $this->updateIncidentWithError($incident, 'Image validation failed');
                return;
            }

            // Step 1 & 2: Google Vision analysis
            $visionResult = $visionService->analyzeImage($incident->photo_path, $incident->id);

            // Check for errors
            if (isset($visionResult['error'])) {
                $errorMessage = $visionResult['error'];
                $errorDetails = $visionResult['error_details'] ?? [];
                
                Log::error('Google Vision API failed', [
                    'incident_id' => $incident->id,
                    'error' => $errorMessage,
                    'error_details' => $errorDetails,
                    'image_path' => $incident->photo_path,
                    'absolute_path' => Storage::disk('local')->path($incident->photo_path),
                ]);
                
                // Check if it's a real API exception (not just empty labels)
                $isRealException = !empty($errorDetails) || 
                                   str_contains($errorMessage, 'API') ||
                                   str_contains($errorMessage, 'failed') ||
                                   str_contains($errorMessage, 'not found') ||
                                   str_contains($errorMessage, 'not enabled') ||
                                   str_contains($errorMessage, 'Permission denied');
                
                // If Google Vision fails with real exception, use basic fallback classification
                if ($isRealException) {
                    $fallbackResult = $this->fallbackClassification($incident, $errorMessage, $errorDetails);
                    if ($fallbackResult) {
                        $this->updateIncidentWithResult($incident, $fallbackResult['classification'], $fallbackResult['evidence'], []);
                        return;
                    }
                }
                
                // Only use NEEDS_REVIEW if fallback also fails or if it's a real exception
                $this->updateIncidentWithError($incident, $errorMessage, $errorDetails);
                return;
            }
            
            // Check if labels are empty (Vision succeeded but no labels found)
            if (empty($visionResult['labels'] ?? [])) {
                Log::warning('Google Vision returned empty labels', [
                    'incident_id' => $incident->id,
                    'person_detected' => $visionResult['person_detected'] ?? false,
                ]);
                // Continue with classification - empty labels just means no sewage indicators
            }

            // Step 2: Classify using sewage classifier
            $classification = $classifier->classify($visionResult, $incident->id);

            // Step 3: Winston AI authenticity check (if person not detected, skip for cost)
            $winstonResult = [
                'ai_generated' => false,
                'ai_generated_score' => 0.0,
                'error' => null,
            ];

            $skipWinston = config('ai.winston.skip_if_person_detected', true);
            if (!$skipWinston || !($visionResult['person_detected'] ?? false)) {
                $publicUrl = $this->getPublicUrl($incident);
                if ($publicUrl) {
                    $winstonResult = $winstonService->checkAuthenticity($publicUrl, $incident->id);
                }
            }

            // Merge Winston results into evidence
            $evidence = $classification['evidence'];
            // Ensure person_confidence is included from vision result
            if (!isset($evidence['person_confidence']) && isset($visionResult['person_confidence'])) {
                $evidence['person_confidence'] = $visionResult['person_confidence'];
            }
            $evidence['ai_generated'] = $winstonResult['ai_generated'] ?? false;
            $evidence['ai_generated_score'] = $winstonResult['ai_generated_score'] ?? 0.0;
            if (isset($winstonResult['error'])) {
                $evidence['winston_error'] = $winstonResult['error'];
            }

            // Step 4: Update incident
            $this->updateIncidentWithResult($incident, $classification, $evidence, $visionResult);

            Log::info("Incident {$incident->id} analyzed successfully", [
                'label' => $classification['label'],
                'confidence' => $classification['confidence'],
                'sewage_score' => $evidence['sewage_score'] ?? 0.0,
                'person_detected' => $evidence['person_detected'] ?? false,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to analyze incident {$incident->id}", [
                'incident_id' => $incident->id,
                'exception_class' => get_class($e),
                'error' => $e->getMessage(),
                'exception_code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'image_path' => $incident->photo_path ?? 'unknown',
            ]);

            $this->updateIncidentWithError($incident, $e->getMessage(), [
                'exception_class' => get_class($e),
                'exception_code' => $e->getCode(),
            ]);
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
    private function updateIncidentWithResult(Incident $incident, array $classification, array $evidence, array $visionResult = []): void
    {
        // Get person_confidence from evidence or vision result
        $personConfidence = null;
        if ($evidence['person_detected'] ?? false) {
            $personConfidence = $evidence['person_confidence'] ?? ($visionResult['person_confidence'] ?? 0.0);
        }
        
        $sewageScore = $evidence['sewage_score'] ?? 0.0;
        $personDetected = $evidence['person_detected'] ?? false;
        $riskScore = $personDetected ? 0 : (int) round($sewageScore * 100);

        $incident->update([
            'ai_label' => $classification['label'],
            'ai_confidence' => $classification['confidence'],
            'review_status' => $this->mapLabelToStatus($classification['label']),
            'risk_score' => $riskScore,
            'person_detected' => $personDetected,
            'has_person' => $personDetected,
            'person_confidence' => $personConfidence,
            'sewage_score' => $sewageScore,
            'sewage_indicators' => $evidence['sewage_indicators'] ?? [],
            'ai_generated_score' => $evidence['ai_generated_score'] ?? 0.0,
            'ai_generated' => $evidence['ai_generated'] ?? false,
            'ai_reasons' => [$classification['reason']],
            'evidence' => $evidence,
            'analysis_error' => null,
        ]);
    }

    /**
     * Fallback classification when Google Vision fails
     * Uses basic image analysis (aspect ratio, file size, etc.)
     */
    private function fallbackClassification(Incident $incident, string $error, array $errorDetails = []): ?array
    {
        try {
            $imagePath = Storage::disk('local')->path($incident->photo_path);
            
            Log::info('Attempting fallback classification', [
                'incident_id' => $incident->id,
                'image_path' => $imagePath,
                'file_exists' => file_exists($imagePath),
                'error' => $error,
            ]);
            
            if (!file_exists($imagePath)) {
                Log::error('Fallback: Image file not found', [
                    'incident_id' => $incident->id,
                    'image_path' => $imagePath,
                ]);
                return null;
            }

            // Basic image analysis
            $imageInfo = @getimagesize($imagePath);
            if (!$imageInfo) {
                Log::error('Fallback: Failed to get image size', [
                    'incident_id' => $incident->id,
                    'image_path' => $imagePath,
                ]);
                return null;
            }

            $width = $imageInfo[0];
            $height = $imageInfo[1];
            $aspectRatio = $width > 0 ? $height / $width : 1.0;
            $fileSize = filesize($imagePath);

            Log::info('Fallback: Image analysis', [
                'incident_id' => $incident->id,
                'width' => $width,
                'height' => $height,
                'aspect_ratio' => $aspectRatio,
                'file_size' => $fileSize,
            ]);

            // Check if it's likely a portrait (vertical, tall image)
            // Portraits are almost never sewage
            if ($aspectRatio > 1.2 && $width < 1500) {
                Log::info('Fallback: Portrait detected via aspect ratio', [
                    'incident_id' => $incident->id,
                    'aspect_ratio' => $aspectRatio,
                ]);

                return [
                    'classification' => [
                        'label' => 'NOT_SEWAGE',
                        'confidence' => 0.80,
                        'reason' => 'Portrait-like image detected. Google Vision API error, using fallback analysis.',
                    ],
                    'evidence' => [
                        'person_detected' => true,
                        'person_confidence' => 0.75,
                        'sewage_score' => 0.0,
                        'sewage_indicators' => [],
                        'labels' => [],
                        'fallback_used' => true,
                        'fallback_reason' => $error,
                        'fallback_error_details' => $errorDetails,
                    ],
                ];
            }

            // For other images, determine reason based on error type
            $reason = 'Google Vision API error occurred. Requires manual review.';
            if (str_contains($error, 'not enabled')) {
                $reason = 'Cloud Vision API not enabled in Google Cloud. Enable vision.googleapis.com API.';
            } elseif (str_contains($error, 'Permission denied')) {
                $reason = 'Permission denied. Check service account has Cloud Vision API User role.';
            } elseif (str_contains($error, 'not found')) {
                $reason = 'Image file not found. Check file path.';
            }

            // For other images, default to NEEDS_REVIEW (conservative)
            return [
                'classification' => [
                    'label' => 'NEEDS_REVIEW',
                    'confidence' => 0.50,
                    'reason' => $reason,
                ],
                'evidence' => [
                    'person_detected' => false,
                    'sewage_score' => 0.0,
                    'sewage_indicators' => [],
                    'labels' => [],
                    'fallback_used' => true,
                    'fallback_reason' => $error,
                    'fallback_error_details' => $errorDetails,
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('Fallback classification failed', [
                'incident_id' => $incident->id,
                'error' => $e->getMessage(),
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Map label to review status
     */
    private function mapLabelToStatus(string $label): string
    {
        return match($label) {
            'NOT_SEWAGE' => 'NOT_SEWAGE_CONFIRMED',
            'NEEDS_REVIEW' => 'NEEDS_REVIEW',
            'SEWAGE_CONFIRMED' => 'SEWAGE_CONFIRMED',
            default => 'NEEDS_REVIEW',
        };
    }

    /**
     * Update incident with error
     */
    private function updateIncidentWithError(Incident $incident, string $error, array $errorDetails = []): void
    {
        $incident->update([
            'review_status' => 'NEEDS_REVIEW',
            'ai_label' => 'NEEDS_REVIEW',
            'ai_confidence' => 0.0,
            'risk_score' => 50,
            'analysis_error' => $error,
            'evidence' => [
                'person_detected' => false,
                'sewage_score' => 0.0,
                'sewage_indicators' => [],
                'ai_generated' => false,
                'ai_generated_score' => 0.0,
                'labels' => [],
                'error' => $error,
                'error_details' => $errorDetails,
            ],
        ]);
    }
}
