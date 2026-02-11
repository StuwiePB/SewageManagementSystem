<?php

namespace App\Console\Commands\AI;

use App\Models\Incident;
use App\Services\AI\GoogleVisionService;
use App\Services\AI\SewageClassifier;
use Illuminate\Console\Command;

class DebugIncident extends Command
{
    protected $signature = 'incidents:debug {id : Incident ID to debug}';
    protected $description = 'Debug what Google Vision sees for a specific incident';

    public function handle(GoogleVisionService $visionService, SewageClassifier $classifier): int
    {
        $incidentId = $this->argument('id');
        $incident = Incident::find($incidentId);

        if (!$incident) {
            $this->error("Incident #{$incidentId} not found.");
            return Command::FAILURE;
        }

        $this->info("🔍 Debugging Incident #{$incidentId}");
        $this->line("Image: {$incident->photo_path}");
        $this->line("Current Status: {$incident->review_status}");
        $this->line("Current Label: {$incident->ai_label}");
        $this->newLine();

        // Run Google Vision analysis
        $this->info("📸 Google Vision Analysis");
        $this->line("─────────────────────────────────");
        
        $visionResult = $visionService->analyzeImage($incident->photo_path, $incident->id);

        if (isset($visionResult['error'])) {
            $this->error("❌ Error: {$visionResult['error']}");
            return Command::FAILURE;
        }

        // Person Detection Details
        $this->line("Person Detection:");
        $this->line("  Detected: " . ($visionResult['person_detected'] ? 'YES ⚠️' : 'NO ✅'));
        $this->line("  Confidence: " . number_format($visionResult['person_confidence'], 3));
        $this->line("  Method: " . ($visionResult['detection_method'] ?? 'unknown'));
        
        if (isset($visionResult['face_count'])) {
            $this->line("  Faces Found: " . $visionResult['face_count']);
        }
        
        if (!empty($visionResult['person_label_matches'] ?? [])) {
            $this->line("  Person Label Matches:");
            foreach ($visionResult['person_label_matches'] as $match) {
                $this->line("    ⚠️  {$match}");
            }
        }
        $this->newLine();

        // All Labels
        $this->line("All Labels Detected (" . count($visionResult['labels']) . "):");
        foreach (array_slice($visionResult['labels'], 0, 20) as $label) {
            $isPersonRelated = $this->isPersonRelated($label['description']);
            $marker = $isPersonRelated ? '⚠️ ' : '  ';
            $this->line("{$marker}{$label['description']}: " . number_format($label['score'], 3));
        }
        $this->newLine();

        // Classification
        $this->info("🎯 Classification Result");
        $this->line("─────────────────────────────────");
        
        $classification = $classifier->classify($visionResult, $incident->id);
        
        $this->line("Label: {$classification['label']}");
        $this->line("Confidence: " . number_format($classification['confidence'], 3));
        $this->line("Reason: {$classification['reason']}");
        $this->newLine();

        $evidence = $classification['evidence'];
        $this->line("Evidence:");
        $this->line("  Person Detected: " . ($evidence['person_detected'] ? 'YES ⚠️' : 'NO ✅'));
        $this->line("  Sewage Score: " . number_format($evidence['sewage_score'], 3));
        
        if (!empty($evidence['sewage_indicators'])) {
            $this->line("  Sewage Indicators:");
            foreach ($evidence['sewage_indicators'] as $indicator) {
                $this->line("    • {$indicator}");
            }
        }

        $this->newLine();
        $this->info("✅ Debug complete!");
        $this->line("Check logs for more details: storage/logs/laravel.log");

        return Command::SUCCESS;
    }

    private function isPersonRelated(string $label): bool
    {
        $personTerms = ['person', 'face', 'portrait', 'selfie', 'headshot', 'human', 'people', 'man', 'woman', 'child'];
        $labelLower = strtolower($label);
        
        foreach ($personTerms as $term) {
            if (stripos($labelLower, $term) !== false) {
                return true;
            }
        }
        
        return false;
    }
}
