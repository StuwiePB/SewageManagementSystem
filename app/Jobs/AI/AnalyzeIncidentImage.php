<?php

namespace App\Jobs\AI;

use App\Models\Incident;
use App\Services\AI\GoogleVisionService;
use App\Services\AI\SewageClassifier;
use App\Services\AI\WinstonService;
use App\Services\Sns\SnsNotifier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AnalyzeIncidentImage implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $incidentId
    ) {}

    public function handle(
        GoogleVisionService $visionService,
        SewageClassifier $classifier,
        WinstonService $winstonService,
        SnsNotifier $snsNotifier,
    ): void {
        $incident = Incident::findOrFail($this->incidentId);

        try {
            if (! $this->validateImage($incident)) {
                $this->updateIncidentWithError($incident, 'Image validation failed');

                return;
            }

            $visionResult = $visionService->analyzeImage($incident->photo_path, $incident->id);

            if (isset($visionResult['error'])) {
                $errorMessage = $visionResult['error'];
                $errorDetails = $visionResult['error_details'] ?? [];
                $isRealException = ! empty($errorDetails)
                    || str_contains($errorMessage, 'API')
                    || str_contains($errorMessage, 'failed')
                    || str_contains($errorMessage, 'not found')
                    || str_contains($errorMessage, 'not enabled')
                    || str_contains($errorMessage, 'Permission denied');

                if ($isRealException) {
                    $fallbackResult = $this->fallbackClassification($incident, $errorMessage, $errorDetails);
                    if ($fallbackResult) {
                        $this->updateIncidentWithResult($incident, $fallbackResult['classification'], $fallbackResult['evidence'], []);
                        $snsNotifier->incidentHighRisk($incident->fresh());

                        return;
                    }
                }
                $this->updateIncidentWithError($incident, $errorMessage, $errorDetails);

                return;
            }

            $classification = $classifier->classify($visionResult, $incident->id);

            $winstonResult = ['ai_generated' => false, 'ai_generated_score' => 0.0, 'error' => null];
            $skipWinston = config('ai.winston.skip_if_person_detected', true);
            if (! $skipWinston || ! ($visionResult['person_detected'] ?? false)) {
                $publicUrl = url("/incidents/{$incident->id}/image");
                $winstonResult = $winstonService->checkAuthenticity($publicUrl, $incident->id);
            }

            $evidence = $classification['evidence'];
            if (! isset($evidence['person_confidence']) && isset($visionResult['person_confidence'])) {
                $evidence['person_confidence'] = $visionResult['person_confidence'];
            }
            $evidence['ai_generated'] = $winstonResult['ai_generated'] ?? false;
            $evidence['ai_generated_score'] = $winstonResult['ai_generated_score'] ?? 0.0;
            if (isset($winstonResult['error'])) {
                $evidence['winston_error'] = $winstonResult['error'];
            }

            $this->updateIncidentWithResult($incident, $classification, $evidence, $visionResult);
            $snsNotifier->incidentHighRisk($incident->fresh());
        } catch (\Exception $e) {
            Log::error("Failed to analyze incident {$incident->id}", [
                'incident_id' => $incident->id,
                'error' => $e->getMessage(),
            ]);
            $this->updateIncidentWithError($incident, $e->getMessage(), [
                'exception_class' => get_class($e),
                'exception_code' => $e->getCode(),
            ]);
            throw $e;
        }
    }

    private function validateImage(Incident $incident): bool
    {
        if (! Storage::disk('local')->exists($incident->photo_path)) {
            return false;
        }
        if (Storage::disk('local')->size($incident->photo_path) > 10 * 1024 * 1024) {
            return false;
        }
        $mimeType = Storage::disk('local')->mimeType($incident->photo_path);

        return in_array($mimeType, ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'], true);
    }

    private function updateIncidentWithResult(Incident $incident, array $classification, array $evidence, array $visionResult = []): void
    {
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

    private function fallbackClassification(Incident $incident, string $error, array $errorDetails = []): ?array
    {
        try {
            $imagePath = Storage::disk('local')->path($incident->photo_path);
            if (! file_exists($imagePath)) {
                return null;
            }
            $imageInfo = @getimagesize($imagePath);
            if (! $imageInfo) {
                return null;
            }
            $width = $imageInfo[0];
            $height = $imageInfo[1];
            $aspectRatio = $width > 0 ? $height / $width : 1.0;

            if ($aspectRatio > 1.2 && $width < 1500) {
                return [
                    'classification' => [
                        'label' => 'NOT_SEWAGE',
                        'confidence' => 0.80,
                        'reason' => 'Portrait-like image detected. Google Vision API error, using fallback.',
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

            $reason = 'Google Vision API error. Requires manual review.';
            if (str_contains($error, 'not enabled')) {
                $reason = 'Cloud Vision API not enabled. Enable vision.googleapis.com in Google Cloud.';
            } elseif (str_contains($error, 'Permission denied')) {
                $reason = 'Permission denied. Check service account has Cloud Vision API User role.';
            } elseif (str_contains($error, 'not found')) {
                $reason = 'Image file not found.';
            }

            return [
                'classification' => ['label' => 'NEEDS_REVIEW', 'confidence' => 0.50, 'reason' => $reason],
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
            return null;
        }
    }

    private function mapLabelToStatus(string $label): string
    {
        return match ($label) {
            'NOT_SEWAGE' => 'NOT_SEWAGE_CONFIRMED',
            'NEEDS_REVIEW' => 'NEEDS_REVIEW',
            'SEWAGE_CONFIRMED' => 'SEWAGE_CONFIRMED',
            default => 'NEEDS_REVIEW',
        };
    }

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
