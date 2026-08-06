<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Winston AI Service
 * Handles AI-generated image detection (authenticity check)
 */
class WinstonService
{
    private string $apiKey;
    private int $timeout;
    private bool $enabled;

    public function __construct()
    {
        $this->apiKey = config('services.winston.api_key');
        $this->timeout = config('ai.winston.timeout', 20);
        $this->enabled = config('ai.winston.enabled', true);
    }

    /**
     * Check if image is AI-generated
     * 
     * @param string $publicUrl Publicly accessible URL to the image
     * @param int|null $incidentId Optional incident ID for logging
     * @return array{
     *     ai_generated: bool,
     *     ai_generated_score: float,
     *     human_probability: float,
     *     error?: string
     * }
     */
    public function checkAuthenticity(string $publicUrl, ?int $incidentId = null): array
    {
        if (!$this->enabled) {
            Log::info('Winston AI check skipped (disabled)', [
                'incident_id' => $incidentId,
            ]);
            return $this->getDefaultResponse();
        }

        if (!$this->apiKey) {
            Log::warning('Winston API key not configured', [
                'incident_id' => $incidentId,
            ]);
            return $this->getDefaultResponse('API key not configured');
        }

        try {
            $response = Http::timeout($this->timeout)->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.gowinston.ai/v1/image-detection', [
                'url' => $publicUrl,
                'version' => 'latest',
            ]);

            if (!$response->successful()) {
                Log::error('Winston AI API error', [
                    'incident_id' => $incidentId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return $this->getDefaultResponse('Winston API error: ' . $response->status());
            }

            $data = $response->json();
            $aiProbability = $data['ai_probability'] ?? 0.0;
            $humanProbability = $data['human_probability'] ?? 0.0;
            $threshold = config('ai.thresholds.ai_generated_min', 0.80);

            $aiGenerated = $aiProbability >= $threshold;

            Log::info('Winston AI authenticity check', [
                'incident_id' => $incidentId,
                'ai_probability' => $aiProbability,
                'human_probability' => $humanProbability,
                'ai_generated' => $aiGenerated,
            ]);

            return [
                'ai_generated' => $aiGenerated,
                'ai_generated_score' => $aiProbability,
                'human_probability' => $humanProbability,
            ];
        } catch (\Throwable $e) {
            Log::error('Winston AI check failed', [
                'incident_id' => $incidentId,
                'error' => $e->getMessage(),
            ]);
            return $this->getDefaultResponse('Check failed: ' . $e->getMessage());
        }
    }

    /**
     * Get default response on error
     */
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
