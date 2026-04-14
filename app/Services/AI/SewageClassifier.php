<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;

class SewageClassifier
{
    public function classify(array $visionResult, ?int $incidentId = null): array
    {
        $personDetected = $visionResult['person_detected'] ?? false;
        $labels = $visionResult['labels'] ?? [];
        $thresholds = config('ai.thresholds', []);

        if ($personDetected) {
            $personConfidence = $visionResult['person_confidence'] ?? 0.0;
            $detectionMethod = $visionResult['detection_method'] ?? 'unknown';
            $minConfidence = $thresholds['person_detected_min'] ?? 0.70;
            $requiredConfidence = ($detectionMethod === 'face') ? $minConfidence : ($thresholds['person_label_min'] ?? 0.70);
            if ($detectionMethod === 'unknown') {
                $requiredConfidence = 0.80;
            }

            if ($personConfidence >= $requiredConfidence) {
                $reason = $detectionMethod === 'face'
                    ? sprintf('Face detected (confidence: %.2f). Not drainage-related.', $personConfidence)
                    : sprintf('Person-related content detected (confidence: %.2f). Not drainage-related.', $personConfidence);
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
            }
        }

        $sewageResult = $this->calculateSewageScore($labels, $incidentId);
        $sewageScore = $sewageResult['score'];
        $sewageIndicators = $sewageResult['indicators'];
        $sewageHigh = $thresholds['sewage_high'] ?? 0.85;
        $sewageLow = $thresholds['sewage_low'] ?? 0.30;

        if ($sewageScore >= $sewageHigh) {
            return [
                'label' => 'NEEDS_REVIEW',
                'confidence' => $thresholds['confidence']['needs_review_high'] ?? 0.85,
                'reason' => 'Drainage-related indicators detected. Requires human verification.',
                'evidence' => [
                    'person_detected' => false,
                    'sewage_score' => $sewageScore,
                    'sewage_indicators' => $sewageIndicators,
                    'labels' => $labels,
                ],
            ];
        }
        if ($sewageScore <= $sewageLow) {
            $reason = empty($labels) ? 'No labels detected. Requires manual review.' : 'No significant drainage-related indicators detected.';
            return [
                'label' => empty($labels) ? 'NEEDS_REVIEW' : 'NOT_SEWAGE',
                'confidence' => empty($labels) ? ($thresholds['confidence']['needs_review_medium'] ?? 0.60) : ($thresholds['confidence']['not_sewage_low_score'] ?? 0.70),
                'reason' => $reason,
                'evidence' => [
                    'person_detected' => false,
                    'sewage_score' => $sewageScore,
                    'sewage_indicators' => [],
                    'labels' => $labels,
                    'labels_empty' => empty($labels),
                ],
            ];
        }

        return [
            'label' => 'NEEDS_REVIEW',
            'confidence' => $thresholds['confidence']['needs_review_medium'] ?? 0.60,
            'reason' => empty($labels) ? 'Uncertain. No labels from Vision.' : 'Uncertain. Requires human review.',
            'evidence' => [
                'person_detected' => false,
                'sewage_score' => $sewageScore,
                'sewage_indicators' => $sewageIndicators,
                'labels' => $labels,
                'labels_empty' => empty($labels),
            ],
        ];
    }

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
                    break;
                }
            }
        }
        return ['score' => $maxScore, 'indicators' => array_unique($indicators)];
    }
}
