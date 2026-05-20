<?php

namespace App\Console\Commands;

use App\Services\AI\GoogleVisionService;
use Illuminate\Console\Command;

class VisionDiagnoseCommand extends Command
{
    protected $signature = 'vision:diagnose';

    protected $description = 'Diagnose Google Cloud Vision API credentials, client, and a minimal live API call';

    public function handle(GoogleVisionService $vision): int
    {
        $this->info('Google Cloud Vision — configuration check');
        $this->newLine();

        $checks = $vision->diagnose();

        $rows = [
            ['GOOGLE_APPLICATION_CREDENTIALS set', $checks['env_set'] ? 'yes' : 'no'],
            ['Env / resolved path', (string) ($checks['env_value'] ?? '')],
            ['Credentials file path', (string) ($checks['credentials_path'] ?? '')],
            ['File exists', ! empty($checks['file_exists']) ? 'yes' : 'no'],
            ['File readable', ! empty($checks['readable']) ? 'yes' : 'no'],
            ['Valid JSON', ! empty($checks['valid_json']) ? 'yes' : 'no'],
        ];

        if (isset($checks['project_id'])) {
            $rows[] = ['project_id (from JSON)', (string) $checks['project_id']];
        }

        $rows[] = ['Client initialized', ! empty($checks['client_initialized']) ? 'yes' : 'no'];

        if (array_key_exists('live_api_ok', $checks) && $checks['live_api_ok'] !== null) {
            $rows[] = ['Live API (1×1 PNG test)', $checks['live_api_ok'] ? 'ok' : 'failed'];
        }

        $this->table(['Check', 'Result'], $rows);

        if (empty($checks['file_exists'])) {
            $this->newLine();
            $this->error('Root cause: the credentials JSON file is missing at the path above.');
            $this->line('Readable, valid JSON, client init, and live API all depend on that file — so they show "no" until it exists.');
            $this->newLine();
            $this->warn('Fix: download a service account key from Google Cloud Console (IAM → Service Accounts → Keys → JSON) and save it as:');
            $this->line('  '.$checks['credentials_path']);
            $this->newLine();
            $this->line('Or set GOOGLE_APPLICATION_CREDENTIALS in .env to an absolute path to your .json file (e.g. C:\keys\vision-sa.json).');
            $this->line('Enable the Cloud Vision API for that Google Cloud project and grant the account "Cloud Vision API User".');
        } elseif (! empty($checks['client_error'])) {
            $this->newLine();
            $this->error('Client / API: '.$checks['client_error']);
        }

        $ok = ! empty($checks['file_exists'])
            && ! empty($checks['valid_json'])
            && ! empty($checks['client_initialized'])
            && ($checks['live_api_ok'] ?? false) === true;

        $this->newLine();

        return $ok ? self::SUCCESS : self::FAILURE;
    }
}
