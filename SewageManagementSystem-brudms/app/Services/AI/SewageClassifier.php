<?php

namespace App\Services\AI;

class SewageClassifier
{
    public function classify(array $visionResult, ?int $incidentId = null): array
    {
        $personDetected = (bool) ($visionResult['person_detected'] ?? false);
        $personConfidence = (float) ($visionResult['person_confidence'] ?? 0.0);
        $detectionMethod = (string) ($visionResult['detection_method'] ?? 'none');
        $labels = (array) ($visionResult['labels'] ?? []);
        $objects = (array) ($visionResult['objects'] ?? []);
        $webEntities = (array) ($visionResult['web_entities'] ?? []);
        $thresholds = config('ai.thresholds', []);
        $sewageResult = $this->calculateDrainageEvidence($labels, $objects, $webEntities, $incidentId);
        $sewageScore = (float) ($sewageResult['score'] ?? 0.0);
        $sewageIndicators = (array) ($sewageResult['indicators'] ?? []);
        $negativeSignals = (array) ($sewageResult['negative_signals'] ?? []);
        $negativeScore = (float) ($sewageResult['negative_score'] ?? 0.0);

        $personPenaltyBase = (float) ($thresholds['person_penalty_base'] ?? 0.35);
        $personPenalty = $personDetected ? ($personPenaltyBase * min(1.0, max(0.0, $personConfidence))) : 0.0;
        $finalScore = max(0.0, min(1.0, $sewageScore - $personPenalty - $negativeScore));

        $sewageHigh = (float) ($thresholds['sewage_high'] ?? 0.70);
        $sewageLow = (float) ($thresholds['sewage_low'] ?? 0.28);
        $personStrongThreshold = (float) ($thresholds['person_override_confidence'] ?? 0.88);
        $personStrong = $personDetected && $personConfidence >= $personStrongThreshold;
        $hasPositiveIndicators = ! empty($sewageIndicators);

        $reasonParts = [];
        if ($personDetected) {
            $reasonParts[] = $detectionMethod === 'face'
                ? sprintf('Face/person signal detected (confidence: %.2f).', $personConfidence)
                : sprintf('Person-related signal detected (confidence: %.2f).', $personConfidence);
        }
        if ($hasPositiveIndicators) {
            $reasonParts[] = 'Drainage indicators found: '.implode(', ', array_slice($sewageIndicators, 0, 5)).'.';
        } else {
            $reasonParts[] = 'No strong drainage indicators were found.';
        }
        if (! empty($negativeSignals)) {
            $reasonParts[] = 'Strong non-drainage context: '.implode(', ', array_slice($negativeSignals, 0, 4)).'.';
        }
        $reasonParts[] = sprintf(
            'Weighted score %.2f (drainage %.2f, person penalty %.2f, non-drainage penalty %.2f).',
            $finalScore,
            $sewageScore,
            $personPenalty,
            $negativeScore
        );

        if ($finalScore >= $sewageHigh) {
            return [
                'label' => 'SEWAGE',
                'confidence' => max(0.60, $finalScore),
                'reason' => implode(' ', $reasonParts),
                'evidence' => [
                    'person_detected' => $personDetected,
                    'person_confidence' => $personConfidence,
                    'detection_method' => $detectionMethod,
                    'sewage_score' => $sewageScore,
                    'weighted_score' => $finalScore,
                    'person_penalty' => $personPenalty,
                    'negative_score' => $negativeScore,
                    'sewage_indicators' => $sewageIndicators,
                    'negative_signals' => $negativeSignals,
                    'labels' => $labels,
                    'objects' => $objects,
                    'web_entities' => $webEntities,
                ],
            ];
        }

        if ($finalScore <= $sewageLow && ! $hasPositiveIndicators && ! $personStrong) {
            return [
                'label' => 'NOT_SEWAGE',
                'confidence' => 1.0 - min(0.5, $finalScore),
                'reason' => implode(' ', $reasonParts),
                'evidence' => [
                    'person_detected' => $personDetected,
                    'person_confidence' => $personConfidence,
                    'detection_method' => $detectionMethod,
                    'sewage_score' => $sewageScore,
                    'weighted_score' => $finalScore,
                    'person_penalty' => $personPenalty,
                    'negative_score' => $negativeScore,
                    'sewage_indicators' => $sewageIndicators,
                    'negative_signals' => $negativeSignals,
                    'labels' => $labels,
                    'objects' => $objects,
                    'web_entities' => $webEntities,
                ],
            ];
        }

        // Ambiguous / mixed signals default to manual review.
        return [
            'label' => 'NEEDS_REVIEW',
            'confidence' => $thresholds['confidence']['needs_review_medium'] ?? 0.60,
            'reason' => implode(' ', $reasonParts).' Human verification recommended.',
            'evidence' => [
                'person_detected' => $personDetected,
                'person_confidence' => $personConfidence,
                'detection_method' => $detectionMethod,
                'sewage_score' => $sewageScore,
                'weighted_score' => $finalScore,
                'person_penalty' => $personPenalty,
                'negative_score' => $negativeScore,
                'sewage_indicators' => $sewageIndicators,
                'negative_signals' => $negativeSignals,
                'labels' => $labels,
                'objects' => $objects,
                'web_entities' => $webEntities,
                'labels_empty' => empty($labels),
            ],
        ];
    }

    private function calculateDrainageEvidence(array $labels, array $objects, array $webEntities, ?int $incidentId): array
    {
        $positiveKeywords = (array) config('ai.sewage_keywords', []);
        $negativeKeywords = (array) config('ai.non_sewage_keywords', []);
        $weights = (array) config('ai.weights', []);
        $labelWeight = (float) ($weights['labels'] ?? 1.0);
        $objectWeight = (float) ($weights['objects'] ?? 0.9);
        $webWeight = (float) ($weights['web_entities'] ?? 0.7);

        $positiveScore = 0.0;
        $negativeScore = 0.0;
        $indicators = [];
        $negativeSignals = [];

        $scan = function (array $items, string $textKey, string $scoreKey, float $weight) use (
            $positiveKeywords,
            $negativeKeywords,
            &$positiveScore,
            &$negativeScore,
            &$indicators,
            &$negativeSignals
        ): void {
            foreach ($items as $item) {
                $text = strtolower((string) ($item[$textKey] ?? ''));
                $score = (float) ($item[$scoreKey] ?? 0.0);
                if ($text === '') {
                    continue;
                }

                foreach ($positiveKeywords as $keyword) {
                    if ($keyword !== '' && str_contains($text, strtolower($keyword))) {
                        $positiveScore = max($positiveScore, $score * $weight);
                        $indicators[] = (string) ($item[$textKey] ?? '');
                        break;
                    }
                }

                foreach ($negativeKeywords as $keyword) {
                    if ($keyword !== '' && str_contains($text, strtolower($keyword))) {
                        $negativeScore = max($negativeScore, $score * $weight);
                        $negativeSignals[] = (string) ($item[$textKey] ?? '');
                        break;
                    }
                }
            }
        };

        $scan($labels, 'description', 'score', $labelWeight);
        $scan($objects, 'name', 'score', $objectWeight);
        $scan($webEntities, 'description', 'score', $webWeight);

        return [
            'score' => max(0.0, min(1.0, $positiveScore)),
            'negative_score' => max(0.0, min(0.75, $negativeScore)),
            'indicators' => array_values(array_unique(array_filter($indicators))),
            'negative_signals' => array_values(array_unique(array_filter($negativeSignals))),
        ];
    }
}
