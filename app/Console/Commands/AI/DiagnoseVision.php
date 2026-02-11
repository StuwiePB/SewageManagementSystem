<?php

namespace App\Console\Commands\AI;

use App\Services\AI\GoogleVisionService;
use Google\ApiCore\ApiException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DiagnoseVision extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vision:diagnose';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnose Google Vision API setup and configuration';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Diagnosing Google Vision API Setup...');
        $this->newLine();

        // Check 1: Composer package
        $this->info('1. Checking Composer Package...');
        if (class_exists(\Google\Cloud\Vision\V1\Client\ImageAnnotatorClient::class)) {
            $this->line('   ✅ google/cloud-vision package is installed');
        } else {
            $this->error('   ❌ google/cloud-vision package NOT installed');
            $this->line('   Run: composer require google/cloud-vision');
            return Command::FAILURE;
        }
        $this->newLine();

        // Check 2: Environment variable
        $this->info('2. Checking Environment Configuration...');
        $envPath = env('GOOGLE_APPLICATION_CREDENTIALS');
        if ($envPath) {
            $this->line("   ✅ GOOGLE_APPLICATION_CREDENTIALS is set: {$envPath}");
        } else {
            $this->warn('   ⚠️  GOOGLE_APPLICATION_CREDENTIALS not set in .env');
            $this->line('   Add to .env: GOOGLE_APPLICATION_CREDENTIALS=storage/app/google/vision-service-account.json');
        }
        $this->newLine();

        // Check 3: Credentials file
        $this->info('3. Checking Credentials File...');
        $defaultPath = base_path('storage/app/google/vision-service-account.json');
        $envPathResolved = $envPath ? (str_starts_with($envPath, '/') || preg_match('/^[A-Z]:/', $envPath) ? $envPath : base_path($envPath)) : $defaultPath;
        
        if (File::exists($envPathResolved)) {
            $this->line("   ✅ Credentials file found: {$envPathResolved}");
            
            // Check if it's valid JSON
            $content = File::get($envPathResolved);
            $json = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->line('   ✅ Credentials file is valid JSON');
                
                // Check for required fields
                $required = ['type', 'project_id', 'private_key_id', 'private_key', 'client_email'];
                $missing = [];
                foreach ($required as $field) {
                    if (!isset($json[$field])) {
                        $missing[] = $field;
                    }
                }
                
                if (empty($missing)) {
                    $this->line('   ✅ All required fields present');
                    $this->line("   📧 Service Account Email: {$json['client_email']}");
                    $this->line("   🆔 Project ID: {$json['project_id']}");
                    $this->line("   🔑 Private Key ID: {$json['private_key_id']}");
                } else {
                    $this->error('   ❌ Missing required fields: ' . implode(', ', $missing));
                }
            } else {
                $this->error('   ❌ Credentials file is NOT valid JSON');
                $this->line('   Error: ' . json_last_error_msg());
            }
        } else {
            $this->error("   ❌ Credentials file NOT found: {$envPathResolved}");
            $this->line('   Expected location: storage/app/google/vision-service-account.json');
            $this->line('   Make sure you placed your service account JSON file there');
        }
        $this->newLine();

        // Check 4: Test client initialization
        $this->info('4. Testing Client Initialization...');
        try {
            $service = new GoogleVisionService();
            $reflection = new \ReflectionClass($service);
            $method = $reflection->getMethod('getClient');
            $method->setAccessible(true);
            
            $client = $method->invoke($service);
            if ($client) {
                $this->line('   ✅ Google Vision client initialized successfully');
                
                // Try to get project_id from credentials
                if (isset($json['project_id'])) {
                    $this->line("   📋 Project ID: {$json['project_id']}");
                    $this->line("   ⚠️  Make sure 'Cloud Vision API' (vision.googleapis.com) is enabled for this project");
                    $this->line("   🔗 Enable at: https://console.cloud.google.com/apis/library/vision.googleapis.com?project={$json['project_id']}");
                }
            } else {
                $this->error('   ❌ Failed to initialize Google Vision client');
                $this->line('   Check logs for details: storage/logs/laravel.log');
            }
        } catch (ApiException $e) {
            $this->error('   ❌ Google API Exception: ' . $e->getMessage());
            $status = $e->getStatus();
            if ($status && ($status->code ?? 0) === 7) {
                $this->warn('   ⚠️  This looks like PERMISSION_DENIED or SERVICE_DISABLED');
                $this->line('   Enable Cloud Vision API: https://console.cloud.google.com/apis/library/vision.googleapis.com');
            }
            $this->line('   ' . $e->getFile() . ':' . $e->getLine());
        } catch (\Throwable $e) {
            $this->error('   ❌ Error initializing client: ' . $e->getMessage());
            $this->line('   ' . $e->getFile() . ':' . $e->getLine());
        }
        $this->newLine();

        // Check 5: Check recent incidents
        $this->info('5. Checking Recent Incidents...');
        $recentIncidents = \App\Models\Incident::whereNotNull('analysis_error')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        if ($recentIncidents->isEmpty()) {
            $this->line('   ℹ️  No incidents with errors found');
        } else {
            $this->warn("   ⚠️  Found {$recentIncidents->count()} incident(s) with errors:");
            foreach ($recentIncidents as $incident) {
                $this->line("      - Incident #{$incident->id}: {$incident->analysis_error}");
            }
        }
        $this->newLine();

        // Summary
        $this->info('📋 Summary:');
        $this->line('   If you see errors above, fix them and then:');
        $this->line('   1. Run: php artisan config:clear');
        $this->line('   2. Reanalyze incidents: php artisan incidents:reanalyze --all');
        $this->line('   3. Check logs: tail -f storage/logs/laravel.log');

        return Command::SUCCESS;
    }
}
