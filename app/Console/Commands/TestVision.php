<?php

namespace App\Console\Commands;

use App\Services\GoogleVisionService;
use App\Services\SewageClassifier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestVision extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:vision {image_path : Path to image file relative to storage/app}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Google Vision API with a sample image';

    /**
     * Execute the console command.
     */
    public function handle(GoogleVisionService $visionService, SewageClassifier $classifier): int
    {
        $imagePath = $this->argument('image_path');

        if (!Storage::disk('local')->exists($imagePath)) {
            $this->error("Image not found: {$imagePath}");
            $this->line("Make sure the image is in storage/app/ directory");
            return Command::FAILURE;
        }

        $this->info("Testing Google Vision API...");
        $this->line("Image: {$imagePath}");
        $this->newLine();

        // Test Google Vision
        $this->info("Step 1: Google Vision Analysis");
        $this->line("─────────────────────────────────");
        
        $visionResult = $visionService->analyzeImage($imagePath);

        if (isset($visionResult['error'])) {
            $this->error("Error: {$visionResult['error']}");
            return Command::FAILURE;
        }

        $this->line("Person Detected: " . ($visionResult['person_detected'] ? 'YES ⚠️' : 'NO ✅'));
        $this->line("Person Confidence: " . number_format($visionResult['person_confidence'], 2));
        $this->line("Detection Method: " . ($visionResult['detection_method'] ?? 'unknown'));
        
        if (isset($visionResult['face_count'])) {
            $this->line("Faces Found: " . $visionResult['face_count']);
        }
        
        if (!empty($visionResult['person_label_matches'] ?? [])) {
            $this->line("Person Label Matches:");
            foreach ($visionResult['person_label_matches'] as $match) {
                $this->line("  ⚠️  {$match}");
            }
        }
        
        $this->line("Labels Found: " . count($visionResult['labels']));
        $this->newLine();

        // Show top labels
        if (!empty($visionResult['labels'])) {
            $this->line("Top Labels:");
            $topLabels = array_slice($visionResult['labels'], 0, 10);
            foreach ($topLabels as $label) {
                $this->line("  - {$label['description']}: " . number_format($label['score'], 3));
            }
            $this->newLine();
        }

        // Test Classification
        $this->info("Step 2: Sewage Classification");
        $this->line("─────────────────────────────────");
        
        $classification = $classifier->classify($visionResult);

        $this->line("Label: {$classification['label']}");
        $this->line("Confidence: " . number_format($classification['confidence'], 2));
        $this->line("Reason: {$classification['reason']}");
        $this->newLine();

        $evidence = $classification['evidence'];
        $this->line("Evidence:");
        $this->line("  - Person Detected: " . ($evidence['person_detected'] ? 'YES' : 'NO'));
        $this->line("  - Sewage Score: " . number_format($evidence['sewage_score'], 3));
        
        if (!empty($evidence['sewage_indicators'])) {
            $this->line("  - Sewage Indicators:");
            foreach ($evidence['sewage_indicators'] as $indicator) {
                $this->line("    • {$indicator}");
            }
        } else {
            $this->line("  - Sewage Indicators: None");
        }

        $this->newLine();
        $this->info("✅ Test completed successfully!");

        return Command::SUCCESS;
    }
}
