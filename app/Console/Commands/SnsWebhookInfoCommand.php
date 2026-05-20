<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SnsWebhookInfoCommand extends Command
{
    protected $signature = 'sns:webhook-info {--check : HTTP GET the public webhook health URL}';

    protected $description = 'Show the SNS HTTPS subscription URL for AWS Console';

    public function handle(): int
    {
        $base = rtrim((string) config('app.url'), '/');
        $url = $base.'/webhooks/sns';

        $this->info('SNS HTTPS subscription endpoint:');
        $this->line("  {$url}");
        $this->newLine();
        $this->line('AWS Console → SNS → Topics → your topic → Create subscription');
        $this->line('  Protocol: HTTPS');
        $this->line("  Endpoint: {$url}");
        $this->line('  Raw message delivery: Disabled (default)');
        $this->newLine();
        $this->line('After deploy, open the URL in a browser — you should see a plain-text ready message.');
        $this->line('SNS will POST SubscriptionConfirmation; the app auto-confirms.');

        if ($this->option('check')) {
            $this->newLine();
            $this->components->task('GET health check', function () use ($url) {
                $response = Http::timeout(15)->get($url);

                if (! $response->successful()) {
                    throw new \RuntimeException("HTTP {$response->status()} — deploy latest code or fix APP_URL ({$url})");
                }

                if (! str_contains($response->body(), 'SNS webhook is ready')) {
                    throw new \RuntimeException('Unexpected response body');
                }
            });
        }

        return self::SUCCESS;
    }
}
