<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WinstonService
{
    private string $apiKey;

    private int $timeout;

    private bool $enabled;

    public function __construct()
    {
        $this->apiKey = config('services.winston.api_key') ?? '';
        $this->timeout = config('ai.winston.timeout', 20);
        $this->enabled = config('ai.winston.enabled', true);
    }

    public function checkAuthenticity(string $publicUrl, ?int $incidentId = null): array
    {
        if (! $this->enabled) {
            return $this->getDefaultResponse();
        }
        if (! $this->apiKey) {
            Log::warning('Winston API key not configured', ['incident_id' => $incidentId]);
            return $this->getDefaultResponse('API key not configured');
        }
        try {
            $response = Http::timeout($this->timeout)->withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.gowinston.ai/v1/image-detection', [
                'url' => $publicUrl,
                'version' => 'latest',
            ]);
            if (! $response->successful()) {
                Log::error('Winston AI API error', ['incident_id' => $incidentId, 'status' => $response->status()]);
                return $this->getDefaultResponse('Winston API error: '.$response->status());
            }
            $data = $response->json();
            $aiProbability = $data['ai_probability'] ?? 0.0;
            $humanProbability = $data['human_probability'] ?? 0.0;
            $threshold = config('ai.thresholds.ai_generated_min', 0.80);
            $aiGenerated = $aiProbability >= $threshold;
            return [
                'ai_generated' => $aiGenerated,
                'ai_generated_score' => $aiProbability,
                'human_probability' => $humanProbability,
            ];
        } catch (\Throwable $e) {
            Log::error('Winston AI check failed', ['incident_id' => $incidentId, 'error' => $e->getMessage()]);
            return $this->getDefaultResponse('Check failed: '.$e->getMessage());
        }
    }

    private function getDefaultResponse(?string $error = null): array
    {
        return [
            'ai_generated' => false,
            'ai_generated_score' => 0.0,
            'human_probability' => 1.0,
            ...($error ? ['error' => $error] : []),
        ];
    }
}
