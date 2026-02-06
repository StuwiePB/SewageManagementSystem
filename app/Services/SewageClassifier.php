<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Sewage Classifier
 * Classifies images based on Google Vision labels
 */
class SewageClassifier
{
    /**
     * Classify image based on labels and person detection
     * 
     * @param array $visionResult Result from GoogleVisionService
     * @param int|null $incidentId Optional incident ID for logging
     * @return array{
     *     label: string,
     *     confidence: float,
     *     reason: string,
     *     evidence: array{
     *         person_detected: bool,
     *         sewage_score: float,
     *         sewage_indicators: array<string>,
     *         labels: array<array{description: string, score: float}>
     *     }
     * }
     */
    public function classify(array $visionResult, ?int $incidentId = null): array
    {
        $personDetected = $visionResult['person_detected'] ?? false;
        $labels = $visionResult['labels'] ?? [];
        $thresholds = config('ai.thresholds', []);

        // Hard Rule 1: PEOPLE ARE NEVER SEWAGE
        if ($personDetected) {
            $personConfidence = $visionResult['person_confidence'] ?? 0.0;
            $detectionMethod = $visionResult['detection_method'] ?? 'unknown';
            $minConfidence = $thresholds['person_detected_min'] ?? 0.70;

            // Use different thresholds based on detection method
            if ($detectionMethod === 'face') {
                // Face detection is more reliable - use standard threshold
                $requiredConfidence = $minConfidence;
            } elseif ($detectionMethod === 'label') {
                // Label detection is less reliable - require higher confidence
                $requiredConfidence = $thresholds['person_label_min'] ?? 0.70;
            } else {
                // Unknown method - be conservative
                $requiredConfidence = 0.80;
            }

            if ($personConfidence >= $requiredConfidence) {
                Log::info('Person detected - returning NOT_SEWAGE', [
                    'incident_id' => $incidentId,
                    'person_confidence' => $personConfidence,
                    'detection_method' => $detectionMethod,
                    'required_confidence' => $requiredConfidence,
                    'face_count' => $visionResult['face_count'] ?? 0,
                    'person_label_matches' => $visionResult['person_label_matches'] ?? [],
                ]);

                $reason = match($detectionMethod) {
                    'face' => sprintf('Face detected in image (confidence: %.2f). People are never sewage.', $personConfidence),
                    'label' => sprintf('Person-related content detected via labels (confidence: %.2f). People are never sewage.', $personConfidence),
                    default => 'Person detected in image. People are never sewage.',
                };

                return [
                    'label' => 'NOT_SEWAGE',
                    'confidence' => $thresholds['confidence']['not_sewage_person'] ?? 0.95,
                    'reason' => $reason,
                    'evidence' => [
                        'person_detected' => true,
                        'person_confidence' => $personConfidence,
                        'detection_method' => $detectionMethod,
                        'sewage_score' => 0.0,
                        'sewage_indicators' => [],
                        'labels' => $labels,
                    ],
                ];
            } else {
                // Person detected but confidence too low - log warning and continue with sewage analysis
                Log::warning('Person detected but confidence too low - continuing with sewage analysis', [
                    'incident_id' => $incidentId,
                    'person_confidence' => $personConfidence,
                    'required_confidence' => $requiredConfidence,
                    'detection_method' => $detectionMethod,
                ]);
            }
        }

        // Calculate sewage score from labels
        $sewageResult = $this->calculateSewageScore($labels, $incidentId);
        $sewageScore = $sewageResult['score'];
        $sewageIndicators = $sewageResult['indicators'];

        // Decision logic
        $sewageHigh = $thresholds['sewage_high'] ?? 0.85;
        $sewageLow = $thresholds['sewage_low'] ?? 0.30;

        if ($sewageScore >= $sewageHigh) {
            // Hard Rule 3: Never auto-confirm SEWAGE
            return [
                'label' => 'NEEDS_REVIEW',
                'confidence' => $thresholds['confidence']['needs_review_high'] ?? 0.85,
                'reason' => 'Strong sewage indicators detected. Requires human verification.',
                'evidence' => [
                    'person_detected' => false,
                    'sewage_score' => $sewageScore,
                    'sewage_indicators' => $sewageIndicators,
                    'labels' => $labels,
                ],
            ];
        } elseif ($sewageScore <= $sewageLow) {
            // Check if labels are empty (Vision might have failed silently)
            $reason = empty($labels) 
                ? 'No labels detected from Google Vision. Requires manual review.'
                : 'No significant sewage indicators detected from image analysis.';
            
            return [
                'label' => empty($labels) ? 'NEEDS_REVIEW' : 'NOT_SEWAGE',
                'confidence' => empty($labels) 
                    ? ($thresholds['confidence']['needs_review_medium'] ?? 0.60)
                    : ($thresholds['confidence']['not_sewage_low_score'] ?? 0.70),
                'reason' => $reason,
                'evidence' => [
                    'person_detected' => false,
                    'sewage_score' => $sewageScore,
                    'sewage_indicators' => [],
                    'labels' => $labels,
                    'labels_empty' => empty($labels),
                ],
            ];
        } else {
            // Hard Rule 4: If uncertain → NEEDS_REVIEW
            return [
                'label' => 'NEEDS_REVIEW',
                'confidence' => $thresholds['confidence']['needs_review_medium'] ?? 0.60,
                'reason' => empty($labels) 
                    ? 'Uncertain classification. No labels detected from Google Vision.'
                    : 'Uncertain classification. Requires human review.',
                'evidence' => [
                    'person_detected' => false,
                    'sewage_score' => $sewageScore,
                    'sewage_indicators' => $sewageIndicators,
                    'labels' => $labels,
                    'labels_empty' => empty($labels),
                ],
            ];
        }
    }

    /**
     * Calculate sewage score from labels
     * 
     * @param array<array{description: string, score: float}> $labels
     * @param int|null $incidentId
     * @return array{score: float, indicators: array<string>}
     */
    private function calculateSewageScore(array $labels, ?int $incidentId): array
    {
        $keywords = config('ai.sewage_keywords', []);
        $maxScore = 0.0;
        $indicators = [];

        foreach ($labels as $label) {
            $description = strtolower($label['description'] ?? '');
            $score = $label['score'] ?? 0.0;

            foreach ($keywords as $keyword) {
                if (stripos($description, strtolower($keyword)) !== false) {
                    $maxScore = max($maxScore, $score);
                    $indicators[] = $label['description'];
                    break; // Found match, move to next label
                }
            }
        }

        Log::info('Sewage score calculated', [
            'incident_id' => $incidentId,
            'sewage_score' => $maxScore,
            'indicators' => $indicators,
            'matched_keywords' => count($indicators),
        ]);

        return [
            'score' => $maxScore,
            'indicators' => array_unique($indicators),
        ];
    }
}
