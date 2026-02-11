<?php

namespace App\Console\Commands\AI;

use App\Models\Incident;
use App\Services\AI\GoogleVisionService;
use App\Services\AI\SewageClassifier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Test Google Vision API using the same call path as the queue job
 * Reproduces: AnalyzeIncidentImage job -> GoogleVisionService -> SewageClassifier
 */
class VisionTest extends Command
{
    protected $signature = 'vision:test {--incident= : Incident ID to test}';
    protected $description = 'Test Google Vision API using the same call path as queue jobs';

    public function handle(GoogleVisionService $visionService, SewageClassifier $classifier): int
    {
        $incidentId = $this->option('incident');
        
        if (!$incidentId) {
            $this->error('Please provide an incident ID: --incident=40');
            $this->line('');
            $this->line('Example: php artisan vision:test --incident=40');
            return Command::FAILURE;
        }

        $incident = Incident::find($incidentId);
        
        if (!$incident) {
            $this->error("Incident #{$incidentId} not found.");
            return Command::FAILURE;
        }

        $this->info("🧪 Testing Google Vision API (same path as queue job)");
        $this->line("Incident ID: {$incident->id}");
        $this->line("Image Path: {$incident->photo_path}");
        $this->newLine();

        // Step 1: Validate image (same as job)
        $this->info("Step 1: Image Validation");
        $this->line("─────────────────────────────────");
        
        $absolutePath = Storage::disk('local')->path($incident->photo_path);
        $fileExists = file_exists($absolutePath);
        $fileSize = $fileExists ? filesize($absolutePath) : 0;
        
        $this->line("Absolute Path: {$absolutePath}");
        $this->line("File Exists: " . ($fileExists ? '✅ YES' : '❌ NO'));
        $this->line("File Size: " . ($fileExists ? number_format($fileSize) . " bytes" : 'N/A'));
        
        if (!$fileExists) {
            $this->error("❌ Image file not found!");
            return Command::FAILURE;
        }
        
        if (!Storage::disk('local')->exists($incident->photo_path)) {
            $this->error("❌ Image not found in storage!");
            return Command::FAILURE;
        }
        $this->newLine();

        // Step 2: Google Vision analysis (same as job)
        $this->info("Step 2: Google Vision API Call");
        $this->line("─────────────────────────────────");
        
        try {
            $visionResult = $visionService->analyzeImage($incident->photo_path, $incident->id);
        } catch (\Throwable $e) {
            $this->error("❌ Exception during Vision API call:");
            $this->line("   Class: " . get_class($e));
            $this->line("   Message: " . $e->getMessage());
            $this->line("   Code: " . $e->getCode());
            $this->line("   File: " . $e->getFile() . ":" . $e->getLine());
            return Command::FAILURE;
        }

        if (isset($visionResult['error'])) {
            $this->error("❌ Google Vision API Error:");
            $this->line("   Error: {$visionResult['error']}");
            
            if (isset($visionResult['error_details'])) {
                $this->line("   Details:");
                foreach ($visionResult['error_details'] as $key => $value) {
                    if ($key !== 'metadata') { // Don't print full metadata
                        $this->line("     {$key}: " . (is_array($value) ? json_encode($value) : $value));
                    }
                }
            }
            return Command::FAILURE;
        }

        $this->line("✅ Google Vision API call succeeded");
        $this->line("Person Detected: " . ($visionResult['person_detected'] ? 'YES ⚠️' : 'NO ✅'));
        $this->line("Person Confidence: " . number_format($visionResult['person_confidence'], 3));
        $this->line("Detection Method: " . ($visionResult['detection_method'] ?? 'unknown'));
        $this->line("Face Count: " . ($visionResult['face_count'] ?? 0));
        $this->line("Labels Count: " . count($visionResult['labels'] ?? []));
        $this->newLine();

        // Show labels
        if (!empty($visionResult['labels'])) {
            $this->line("Top Labels Detected:");
            foreach (array_slice($visionResult['labels'], 0, 10) as $label) {
                $this->line("  • {$label['description']}: " . number_format($label['score'], 3));
            }
        } else {
            $this->warn("⚠️  No labels detected (this might indicate an issue)");
        }
        $this->newLine();

        // Step 3: Classification (same as job)
        $this->info("Step 3: Sewage Classification");
        $this->line("─────────────────────────────────");
        
        try {
            $classification = $classifier->classify($visionResult, $incident->id);
        } catch (\Throwable $e) {
            $this->error("❌ Exception during classification:");
            $this->line("   Message: " . $e->getMessage());
            return Command::FAILURE;
        }

        $this->line("Label: {$classification['label']}");
        $this->line("Confidence: " . number_format($classification['confidence'], 3));
        $this->line("Reason: {$classification['reason']}");
        $this->newLine();

        $evidence = $classification['evidence'];
        $this->line("Evidence:");
        $this->line("  Person Detected: " . ($evidence['person_detected'] ? 'YES' : 'NO'));
        $this->line("  Sewage Score: " . number_format($evidence['sewage_score'], 3));
        
        if (!empty($evidence['sewage_indicators'])) {
            $this->line("  Sewage Indicators:");
            foreach ($evidence['sewage_indicators'] as $indicator) {
                $this->line("    • {$indicator}");
            }
        } else {
            $this->line("  Sewage Indicators: None");
        }
        
        if (isset($evidence['labels_empty'])) {
            $this->warn("  ⚠️  Labels Empty: " . ($evidence['labels_empty'] ? 'YES' : 'NO'));
        }
        $this->newLine();

        // Summary
        $this->info("📋 Summary:");
        if (empty($visionResult['labels'])) {
            $this->warn("  ⚠️  Google Vision returned empty labels - this might indicate:");
            $this->line("     • API not enabled (enable vision.googleapis.com)");
            $this->line("     • Permission denied (check service account roles)");
            $this->line("     • Image format issue");
        } else {
            $this->line("  ✅ Google Vision API is working correctly");
            $this->line("  ✅ Labels detected: " . count($visionResult['labels']));
        }
        
        $this->newLine();
        $this->line("Check logs for more details: storage/logs/laravel.log");

        return Command::SUCCESS;
    }
}
