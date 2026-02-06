<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * Vision Service for Google Cloud Vision API
 * Handles person detection and sewage content detection
 */
class VisionService
{
    private string $apiKey;
    private int $timeout;

    public function __construct()
    {
        $this->apiKey = config('services.google_vision.api_key');
        $this->timeout = config('sewage_sentinel.vision.timeout', 30);
    }

    /**
     * Analyze image for person detection and sewage content
     * 
     * @param string $imagePath Path to image in storage
     * @param int|null $incidentId Optional incident ID for logging
     * @return array{
     *     person_detected: bool,
     *     person_confidence: float,
     *     sewage_score: float,
     *     sewage_indicators: array<string>,
     *     labels: array<string>,
     *     error?: string
     * }
     */
    public function analyzeImage(string $imagePath, ?int $incidentId = null): array
    {
        if (!$this->apiKey) {
            Log::warning('Google Vision API key not configured', [
                'incident_id' => $incidentId,
            ]);
            return $this->getDefaultResponse('API key not configured');
        }

        if (!Storage::disk('local')->exists($imagePath)) {
            Log::error('Image not found for Vision API analysis', [
                'incident_id' => $incidentId,
                'imagePath' => $imagePath,
            ]);
            return $this->getDefaultResponse('Image not found');
        }

        try {
            $imageContent = Storage::disk('local')->get($imagePath);
            $imageBase64 = base64_encode($imageContent);

            // Call Google Cloud Vision API
            $response = Http::timeout($this->timeout)->withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://vision.googleapis.com/v1/images:annotate?key={$this->apiKey}", [
                'requests' => [
                    [
                        'image' => [
                            'content' => $imageBase64,
                        ],
                        'features' => [
                            [
                                'type' => 'LABEL_DETECTION',
                                'maxResults' => 20,
                            ],
                            [
                                'type' => 'FACE_DETECTION',
                                'maxResults' => 10,
                            ],
                            [
                                'type' => 'OBJECT_LOCALIZATION',
                                'maxResults' => 20,
                            ],
                        ],
                    ],
                ],
            ]);

            if (!$response->successful()) {
                Log::error('Google Vision API error', [
                    'incident_id' => $incidentId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return $this->getDefaultResponse('Vision API error: ' . $response->status());
            }

            $data = $response->json();
            $responses = $data['responses'][0] ?? [];

            // Extract person detection
            $personResult = $this->detectPerson($responses, $incidentId);

            // Extract sewage indicators
            $sewageResult = $this->detectSewage($responses, $incidentId);

            return [
                'person_detected' => $personResult['detected'],
                'person_confidence' => $personResult['confidence'],
                'sewage_score' => $sewageResult['score'],
                'sewage_indicators' => $sewageResult['indicators'],
                'labels' => $sewageResult['labels'],
            ];
        } catch (\Throwable $e) {
            Log::error('Vision API analysis failed', [
                'incident_id' => $incidentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->getDefaultResponse('Analysis failed: ' . $e->getMessage());
        }
    }

    /**
     * Detect if person/face is present in image
     */
    private function detectPerson(array $responses, ?int $incidentId): array
    {
        $faces = $responses['faceAnnotations'] ?? [];
        $labels = $responses['labelAnnotations'] ?? [];
        $objects = $responses['localizedObjectAnnotations'] ?? [];

        $personDetected = false;
        $maxConfidence = 0.0;

        // Check face annotations
        if (!empty($faces)) {
            $personDetected = true;
            foreach ($faces as $face) {
                $confidence = $face['detectionConfidence'] ?? 0.0;
                $maxConfidence = max($maxConfidence, $confidence);
            }
        }

        // Check labels for person-related terms
        $personLabels = config('sewage_sentinel.person_labels', []);
        foreach ($labels as $label) {
            $description = strtolower($label['description'] ?? '');
            $score = $label['score'] ?? 0.0;

            foreach ($personLabels as $personLabel) {
                if (stripos($description, $personLabel) !== false && $score >= 0.5) {
                    $personDetected = true;
                    $maxConfidence = max($maxConfidence, $score);
                }
            }
        }

        // Check objects for person
        foreach ($objects as $object) {
            $name = strtolower($object['name'] ?? '');
            $score = $object['score'] ?? 0.0;

            if (stripos($name, 'person') !== false || stripos($name, 'face') !== false) {
                if ($score >= 0.5) {
                    $personDetected = true;
                    $maxConfidence = max($maxConfidence, $score);
                }
            }
        }

        Log::info('Person detection result', [
            'incident_id' => $incidentId,
            'detected' => $personDetected,
            'confidence' => $maxConfidence,
        ]);

        return [
            'detected' => $personDetected,
            'confidence' => $maxConfidence,
        ];
    }

    /**
     * Detect sewage-related content
     */
    private function detectSewage(array $responses, ?int $incidentId): array
    {
        $labels = $responses['labelAnnotations'] ?? [];
        $objects = $responses['localizedObjectAnnotations'] ?? [];

        $sewageKeywords = config('sewage_sentinel.sewage_keywords', []);
        $indicators = [];
        $allLabels = [];
        $totalScore = 0.0;
        $matchCount = 0;

        // Check labels
        foreach ($labels as $label) {
            $description = strtolower($label['description'] ?? '');
            $score = $label['score'] ?? 0.0;
            $allLabels[] = $description;

            foreach ($sewageKeywords as $keyword) {
                if (stripos($description, $keyword) !== false && $score >= 0.3) {
                    $indicators[] = $description;
                    $totalScore += $score;
                    $matchCount++;
                }
            }
        }

        // Check objects
        foreach ($objects as $object) {
            $name = strtolower($object['name'] ?? '');
            $score = $object['score'] ?? 0.0;

            foreach ($sewageKeywords as $keyword) {
                if (stripos($name, $keyword) !== false && $score >= 0.3) {
                    $indicators[] = $name;
                    $totalScore += $score;
                    $matchCount++;
                }
            }
        }

        // Calculate sewage score (0.0 - 1.0)
        // Average of matching scores, normalized
        $sewageScore = $matchCount > 0 ? min(1.0, $totalScore / $matchCount) : 0.0;

        // Boost score if multiple indicators found
        if ($matchCount >= 2) {
            $sewageScore = min(1.0, $sewageScore * 1.2);
        }

        Log::info('Sewage detection result', [
            'incident_id' => $incidentId,
            'score' => $sewageScore,
            'indicators' => $indicators,
            'match_count' => $matchCount,
        ]);

        return [
            'score' => $sewageScore,
            'indicators' => array_unique($indicators),
            'labels' => $allLabels,
        ];
    }

    /**
     * Get default response on error
     */
    private function getDefaultResponse(string $error): array
    {
        return [
            'person_detected' => false,
            'person_confidence' => 0.0,
            'sewage_score' => 0.0,
            'sewage_indicators' => [],
            'labels' => [],
            'error' => $error,
        ];
    }
}
