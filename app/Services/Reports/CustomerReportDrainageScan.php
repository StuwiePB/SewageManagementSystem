<?php

namespace App\Services\Reports;

use App\Models\Report;
use App\Services\AI\GoogleVisionService;
use App\Services\AI\SewageClassifier;

/**
 * Runs Cloud Vision + {@see SewageClassifier} on a customer report photo (public disk).
 */
final class CustomerReportDrainageScan
{
    public const VERDICT_DRAINAGE = 'drainage';

    public const VERDICT_NEEDS_REVIEW = 'needs_review';

    public const VERDICT_NOT_DRAINAGE = 'not_drainage';

    public function scanAndPersist(Report $report): string
    {
        if (! $report->photo_path) {
            throw new \InvalidArgumentException('Report has no photo.');
        }

        $vision = app(GoogleVisionService::class)->analyzeImage($report->photo_path, $report->id, 'public');
        if (isset($vision['error'])) {
            $verdict = self::VERDICT_NEEDS_REVIEW;
            $report->update(['drainage_ai_verdict' => $verdict]);

            return $verdict;
        }

        $classification = app(SewageClassifier::class)->classify($vision, $report->id);
        $verdict = self::mapClassification($classification);
        $report->update(['drainage_ai_verdict' => $verdict]);

        return $verdict;
    }

    public static function mapClassification(array $classification): string
    {
        $label = $classification['label'] ?? '';
        $evidence = $classification['evidence'] ?? [];
        $score = (float) ($evidence['sewage_score'] ?? 0);
        $high = (float) (config('ai.thresholds.sewage_high', 0.85));

        if ($label === 'SEWAGE') {
            return self::VERDICT_DRAINAGE;
        }

        if ($label === 'NOT_SEWAGE') {
            return self::VERDICT_NOT_DRAINAGE;
        }

        if ($label === 'NEEDS_REVIEW') {
            if (! empty($evidence['labels_empty'])) {
                return self::VERDICT_NEEDS_REVIEW;
            }
            if ($score >= $high) {
                return self::VERDICT_DRAINAGE;
            }

            return self::VERDICT_NEEDS_REVIEW;
        }

        return self::VERDICT_NEEDS_REVIEW;
    }
}
