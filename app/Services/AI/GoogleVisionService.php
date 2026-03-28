<?php

namespace App\Services\AI;

use Google\ApiCore\ApiException;
use Google\Cloud\Vision\V1\AnnotateImageRequest;
use Google\Cloud\Vision\V1\BatchAnnotateImagesRequest;
use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Feature\Type as FeatureType;
use Google\Cloud\Vision\V1\Image;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GoogleVisionService
{
    private ?ImageAnnotatorClient $client = null;

    private string $credentialsPath;

    /** Set when {@see getClient()} cannot build the gRPC client (for diagnostics). */
    private ?string $lastClientInitError = null;

    public function __construct()
    {
        $this->credentialsPath = $this->getCredentialsPath();
    }

    public function analyzeImage(string $imagePath, ?int $incidentId = null, string $disk = 'local'): array
    {
        $absolutePath = Storage::disk($disk)->path($imagePath);

        Log::info('Starting Google Vision analysis', [
            'incident_id' => $incidentId,
            'disk' => $disk,
            'image_path_relative' => $imagePath,
            'image_path_absolute' => $absolutePath,
            'file_exists' => file_exists($absolutePath),
            'file_size' => file_exists($absolutePath) ? filesize($absolutePath) : null,
        ]);

        if (! Storage::disk($disk)->exists($imagePath)) {
            $error = "Image not found at path: {$absolutePath}";
            Log::error('Image not found for Google Vision analysis', ['incident_id' => $incidentId, 'image_path_relative' => $imagePath]);

            return $this->getDefaultResponse($error);
        }

        try {
            $client = $this->getClient();
            if (! $client) {
                return $this->getDefaultResponse('Google Vision client initialization failed');
            }

            $imageContent = file_get_contents($absolutePath);
            if ($imageContent === false) {
                return $this->getDefaultResponse("Failed to read image file: {$absolutePath}");
            }

            $image = new Image;
            $image->setContent($imageContent);

            $faceFeature = new Feature;
            $faceFeature->setType(FeatureType::FACE_DETECTION);
            $faceFeature->setMaxResults(10);

            $labelFeature = new Feature;
            $labelFeature->setType(FeatureType::LABEL_DETECTION);
            $labelFeature->setMaxResults(20);

            $annotateRequest = new AnnotateImageRequest;
            $annotateRequest->setImage($image);
            $annotateRequest->setFeatures([$faceFeature, $labelFeature]);

            $batchRequest = new BatchAnnotateImagesRequest;
            $batchRequest->setRequests([$annotateRequest]);

            try {
                $response = $client->batchAnnotateImages($batchRequest);
                $responses = $response->getResponses();
            } catch (ApiException $e) {
                $errorDetails = $this->extractGoogleApiError($e, $incidentId);

                return $this->getDefaultResponse($errorDetails['message'], $errorDetails);
            } catch (\Throwable $e) {
                Log::error('VISION_UNKNOWN_ERROR', ['incident_id' => $incidentId, 'message' => $e->getMessage()]);

                return $this->getDefaultResponse('Vision failed: '.$e->getMessage(), ['exception_class' => get_class($e)]);
            }

            if (empty($responses)) {
                return $this->getDefaultResponse('Empty response from API');
            }

            $annotateResponse = $responses[0];
            $error = $annotateResponse->getError();
            if ($error) {
                return $this->getDefaultResponse('API error: '.$error->getMessage(), ['error_code' => $error->getCode()]);
            }

            $faceAnnotations = $annotateResponse->getFaceAnnotations();
            $personDetectedFromFace = false;
            $maxFaceConfidence = 0.0;
            $faceCount = 0;
            if (count($faceAnnotations) > 0) {
                foreach ($faceAnnotations as $face) {
                    $maxFaceConfidence = max($maxFaceConfidence, $face->getDetectionConfidence());
                    $faceCount++;
                }
                if ($maxFaceConfidence >= 0.50) {
                    $personDetectedFromFace = true;
                }
            }

            $labelAnnotations = $annotateResponse->getLabelAnnotations();
            $labels = [];
            foreach ($labelAnnotations as $label) {
                $labels[] = ['description' => $label->getDescription(), 'score' => $label->getScore()];
            }

            $personLabels = ['person', 'face', 'portrait', 'selfie', 'headshot'];
            $lessReliableLabels = ['human', 'people', 'man', 'woman', 'child'];
            $personDetectedFromLabels = false;
            $labelBasedConfidence = 0.0;
            $personLabelMatches = [];

            if (! $personDetectedFromFace) {
                foreach ($labels as $label) {
                    $description = strtolower($label['description']);
                    $score = $label['score'] ?? 0.0;
                    foreach ($personLabels as $personLabel) {
                        if (stripos($description, $personLabel) !== false && $score >= 0.70) {
                            $personDetectedFromLabels = true;
                            $labelBasedConfidence = max($labelBasedConfidence, $score);
                            $personLabelMatches[] = $label['description']." (score: {$score})";
                            break;
                        }
                    }
                    foreach ($lessReliableLabels as $lessReliableLabel) {
                        if (stripos($description, $lessReliableLabel) !== false && $score >= 0.85) {
                            $personDetectedFromLabels = true;
                            $labelBasedConfidence = max($labelBasedConfidence, $score);
                            $personLabelMatches[] = $label['description']." (score: {$score})";
                            break;
                        }
                    }
                }
            }

            $personDetected = $personDetectedFromFace || $personDetectedFromLabels;
            $finalConfidence = $personDetectedFromFace ? $maxFaceConfidence : $labelBasedConfidence;

            return [
                'person_detected' => $personDetected,
                'person_confidence' => $finalConfidence,
                'labels' => $labels,
                'detection_method' => $personDetectedFromFace ? 'face' : ($personDetectedFromLabels ? 'label' : 'none'),
                'face_count' => $faceCount,
                'person_label_matches' => $personLabelMatches,
            ];
        } catch (ApiException $e) {
            $errorDetails = $this->extractGoogleApiError($e, $incidentId);

            return $this->getDefaultResponse($errorDetails['message'], $errorDetails);
        } catch (\Throwable $e) {
            Log::error('VISION_UNKNOWN_ERROR', ['incident_id' => $incidentId, 'message' => $e->getMessage()]);

            return $this->getDefaultResponse('Vision failed: '.$e->getMessage());
        }
    }

    private function getClient(): ?ImageAnnotatorClient
    {
        if ($this->client !== null) {
            return $this->client;
        }
        $this->lastClientInitError = null;
        try {
            if (! file_exists($this->credentialsPath)) {
                $this->lastClientInitError = 'Credentials file not found at '.$this->credentialsPath;
                Log::error('Google Vision credentials file not found', ['path' => $this->credentialsPath]);

                return null;
            }
            $credentialsContent = file_get_contents($this->credentialsPath);
            $credentialsJson = json_decode($credentialsContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->lastClientInitError = 'Invalid JSON in credentials file: '.json_last_error_msg();

                return null;
            }
            putenv("GOOGLE_APPLICATION_CREDENTIALS={$this->credentialsPath}");

            $clientOptions = [
                'credentials' => $this->credentialsPath,
            ];
            $transport = config('sewage_sentinel.vision.transport', 'rest');
            if (in_array($transport, ['rest', 'grpc', 'grpc-fallback'], true)) {
                $clientOptions['transport'] = $transport;
            }
            $this->client = new ImageAnnotatorClient($clientOptions);

            return $this->client;
        } catch (\Throwable $e) {
            $this->lastClientInitError = $e->getMessage();
            Log::error('Failed to initialize Google Vision client', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Run diagnostics for Vision API setup (credentials, client, optional live check).
     * Used by the vision:diagnose Artisan command.
     */
    public function diagnose(): array
    {
        $envPath = env('GOOGLE_APPLICATION_CREDENTIALS');
        $path = $this->credentialsPath;
        $checks = [
            'env_set' => (bool) $envPath,
            'env_value' => $envPath ?: '(not set, using default path)',
            'credentials_path' => $path,
            'file_exists' => file_exists($path),
            'readable' => is_readable($path),
            'valid_json' => false,
            'client_initialized' => false,
            'live_api_ok' => null,
            'client_error' => null,
        ];

        if (! $checks['file_exists']) {
            return $checks;
        }

        $content = file_get_contents($path);
        if ($content !== false) {
            $json = json_decode($content, true);
            $checks['valid_json'] = json_last_error() === JSON_ERROR_NONE;
            if ($checks['valid_json'] && is_array($json)) {
                $checks['project_id'] = $json['project_id'] ?? null;
            }
        }

        try {
            $client = $this->getClient();
            $checks['client_initialized'] = $client !== null;
            if ($client) {
                $checks['live_api_ok'] = $this->testVisionApiCall($client);
            } else {
                $checks['client_error'] = $this->lastClientInitError
                    ?? 'Client returned null (check storage/logs/laravel.log).';
            }
        } catch (\Throwable $e) {
            $checks['client_error'] = $e->getMessage();
        }

        return $checks;
    }

    private function testVisionApiCall(ImageAnnotatorClient $client): bool
    {
        try {
            $image = new Image;
            $image->setContent(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', true));
            $feature = new Feature;
            $feature->setType(FeatureType::LABEL_DETECTION);
            $feature->setMaxResults(1);
            $request = new AnnotateImageRequest;
            $request->setImage($image);
            $request->setFeatures([$feature]);
            $batch = new BatchAnnotateImagesRequest;
            $batch->setRequests([$request]);
            $response = $client->batchAnnotateImages($batch);
            $responses = $response->getResponses();

            return ! empty($responses) && $responses[0]->getError() === null;
        } catch (\Throwable $e) {
            Log::warning('Vision API test call failed', ['message' => $e->getMessage()]);

            return false;
        }
    }

    private function getCredentialsPath(): string
    {
        $envPath = env('GOOGLE_APPLICATION_CREDENTIALS');
        if ($envPath) {
            $absolutePath = (! str_starts_with($envPath, '/') && ! preg_match('/^[A-Z]:/', $envPath))
                ? base_path($envPath) : $envPath;

            return realpath($absolutePath) ?: $absolutePath;
        }
        $defaultPath = base_path('storage/app/google/vision-service-account.json');

        return realpath($defaultPath) ?: $defaultPath;
    }

    private function extractGoogleApiError(ApiException $e, ?int $incidentId): array
    {
        $status = method_exists($e, 'getStatus') ? $e->getStatus() : null;
        $message = $e->getMessage();
        $statusCode = $status && property_exists($status, 'code') ? $status->code : null;
        $actionableMessage = $message;
        if (str_contains($message, 'SERVICE_DISABLED') || str_contains($message, 'vision.googleapis.com') || $statusCode === 7) {
            $actionableMessage = "Cloud Vision API not enabled. Enable 'Cloud Vision API' (vision.googleapis.com) in Google Cloud Console.";
        }
        if (str_contains($message, 'PERMISSION_DENIED') || $statusCode === 7) {
            $actionableMessage = "Permission denied. Check service account has 'Cloud Vision API User' role.";
        }
        Log::error('GOOGLE_VISION_API_EXCEPTION', ['incident_id' => $incidentId, 'message' => $message]);

        return [
            'message' => $actionableMessage,
            'details' => ['exception_class' => get_class($e), 'status' => $status],
        ];
    }

    private function getDefaultResponse(string $error, array $errorDetails = []): array
    {
        $response = ['person_detected' => false, 'person_confidence' => 0.0, 'labels' => [], 'error' => $error];
        if (! empty($errorDetails)) {
            $response['error_details'] = $errorDetails;
        }

        return $response;
    }

    public function __destruct()
    {
        if ($this->client !== null) {
            $this->client->close();
        }
    }
}
