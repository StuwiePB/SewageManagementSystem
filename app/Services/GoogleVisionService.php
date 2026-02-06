<?php

namespace App\Services;

use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\Image;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Feature\Type as FeatureType;
use Google\Cloud\Vision\V1\AnnotateImageRequest;
use Google\Cloud\Vision\V1\BatchAnnotateImagesRequest;
use Google\ApiCore\ApiException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * Google Vision Service using Service Account JSON
 * Handles face detection and label detection
 */
class GoogleVisionService
{
    private ?ImageAnnotatorClient $client = null;
    private string $credentialsPath;

    public function __construct()
    {
        $this->credentialsPath = $this->getCredentialsPath();
    }

    /**
     * Analyze image for face detection and label detection
     * 
     * @param string $imagePath Path to image in storage (relative to storage/app)
     * @param int|null $incidentId Optional incident ID for logging
     * @return array{
     *     person_detected: bool,
     *     person_confidence: float,
     *     labels: array<array{description: string, score: float}>,
     *     error?: string
     * }
     */
    public function analyzeImage(string $imagePath, ?int $incidentId = null): array
    {
        // Get absolute path first for logging
        $absolutePath = Storage::disk('local')->path($imagePath);
        
        Log::info('Starting Google Vision analysis', [
            'incident_id' => $incidentId,
            'image_path_relative' => $imagePath,
            'image_path_absolute' => $absolutePath,
            'file_exists' => file_exists($absolutePath),
            'file_size' => file_exists($absolutePath) ? filesize($absolutePath) : null,
        ]);

        if (!Storage::disk('local')->exists($imagePath)) {
            $error = "Image not found at path: {$absolutePath}";
            Log::error('Image not found for Google Vision analysis', [
                'incident_id' => $incidentId,
                'image_path_relative' => $imagePath,
                'image_path_absolute' => $absolutePath,
                'storage_exists' => Storage::disk('local')->exists($imagePath),
                'file_exists' => file_exists($absolutePath),
            ]);
            return $this->getDefaultResponse($error);
        }

        try {
            // Initialize client
            $client = $this->getClient();
            if (!$client) {
                $error = 'Google Vision client initialization failed';
                Log::error($error, [
                    'incident_id' => $incidentId,
                    'credentials_path' => $this->credentialsPath,
                ]);
                return $this->getDefaultResponse($error);
            }

            // Read image content
            $imageContent = file_get_contents($absolutePath);
            if ($imageContent === false) {
                $error = "Failed to read image file: {$absolutePath}";
                Log::error('Failed to read image file', [
                    'incident_id' => $incidentId,
                    'image_path_absolute' => $absolutePath,
                    'file_exists' => file_exists($absolutePath),
                    'is_readable' => is_readable($absolutePath),
                ]);
                return $this->getDefaultResponse($error);
            }

            // Create Image object
            $image = new Image();
            $image->setContent($imageContent);

            // Create features for face detection and label detection
            $faceFeature = new Feature();
            $faceFeature->setType(FeatureType::FACE_DETECTION);
            $faceFeature->setMaxResults(10);

            $labelFeature = new Feature();
            $labelFeature->setType(FeatureType::LABEL_DETECTION);
            $labelFeature->setMaxResults(20);

            // Create annotate image request
            $annotateRequest = new AnnotateImageRequest();
            $annotateRequest->setImage($image);
            $annotateRequest->setFeatures([$faceFeature, $labelFeature]);

            // Create batch request
            $batchRequest = new BatchAnnotateImagesRequest();
            $batchRequest->setRequests([$annotateRequest]);

            // Call API with detailed error handling
            try {
                $response = $client->batchAnnotateImages($batchRequest);
                $responses = $response->getResponses();
            } catch (ApiException $e) {
                // Google API specific exception
                $errorDetails = $this->extractGoogleApiError($e, $incidentId);
                return $this->getDefaultResponse($errorDetails['message'], $errorDetails);
            } catch (\Throwable $e) {
                // Other exceptions
                Log::error('VISION_UNKNOWN_ERROR', [
                    'incident_id' => $incidentId,
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'exception_class' => get_class($e),
                    'exception_code' => $e->getCode(),
                ]);
                return $this->getDefaultResponse('Vision failed: ' . $e->getMessage(), [
                    'exception_class' => get_class($e),
                    'exception_code' => $e->getCode(),
                ]);
            }
            
            if (empty($responses)) {
                Log::error('Empty response from Google Vision API', [
                    'incident_id' => $incidentId,
                ]);
                return $this->getDefaultResponse('Empty response from API');
            }

            $annotateResponse = $responses[0];
            
            // Check for errors in the response
            $error = $annotateResponse->getError();
            if ($error) {
                $errorMessage = $error->getMessage();
                $errorCode = $error->getCode();
                Log::error('Google Vision API returned error in response', [
                    'incident_id' => $incidentId,
                    'error_message' => $errorMessage,
                    'error_code' => $errorCode,
                ]);
                return $this->getDefaultResponse("API error: {$errorMessage} (code: {$errorCode})", [
                    'error_code' => $errorCode,
                ]);
            }

            // Extract face detection results (PRIMARY METHOD - most reliable)
            $faceAnnotations = $annotateResponse->getFaceAnnotations();
            $personDetectedFromFace = false;
            $maxFaceConfidence = 0.0;
            $faceCount = 0;

            if (count($faceAnnotations) > 0) {
                foreach ($faceAnnotations as $face) {
                    $detectionConfidence = $face->getDetectionConfidence();
                    $maxFaceConfidence = max($maxFaceConfidence, $detectionConfidence);
                    $faceCount++;
                }
                // Face detection is reliable - if faces found, trust it
                if ($maxFaceConfidence >= 0.50) {
                    $personDetectedFromFace = true;
                }
            }

            // Extract label detection results
            $labelAnnotations = $annotateResponse->getLabelAnnotations();
            $labels = [];
            
            foreach ($labelAnnotations as $label) {
                $labels[] = [
                    'description' => $label->getDescription(),
                    'score' => $label->getScore(),
                ];
            }

            // Label-based backup detection (SECONDARY METHOD - more strict)
            // Only use if face detection found nothing, and require HIGH confidence
            $personDetectedFromLabels = false;
            $labelBasedConfidence = 0.0;
            $personLabelMatches = [];

            if (!$personDetectedFromFace) {
                // More specific person-related labels (avoid false positives)
                $personLabels = [
                    'person',      // Direct person detection
                    'face',        // Face detection via labels
                    'portrait',    // Portrait photos
                    'selfie',      // Selfie photos
                    'headshot',    // Headshot photos
                ];
                
                // Less reliable labels - require higher confidence
                $lessReliableLabels = [
                    'human',       // Could be statue, drawing, etc.
                    'people',      // Could be crowd scene
                    'man',         // Could be statue, drawing
                    'woman',       // Could be statue, drawing
                    'child',       // Could be statue, drawing
                ];

                foreach ($labels as $label) {
                    $description = strtolower($label['description']);
                    $score = $label['score'] ?? 0.0;
                    
                    // Check reliable labels with lower threshold
                    foreach ($personLabels as $personLabel) {
                        if (stripos($description, $personLabel) !== false) {
                            // Require higher confidence for reliable labels
                            if ($score >= 0.70) {
                                $personDetectedFromLabels = true;
                                $labelBasedConfidence = max($labelBasedConfidence, $score);
                                $personLabelMatches[] = $label['description'] . " (score: {$score})";
                            }
                        }
                    }
                    
                    // Check less reliable labels with MUCH higher threshold
                    foreach ($lessReliableLabels as $lessReliableLabel) {
                        if (stripos($description, $lessReliableLabel) !== false) {
                            // Require very high confidence (0.85+) for less reliable labels
                            if ($score >= 0.85) {
                                $personDetectedFromLabels = true;
                                $labelBasedConfidence = max($labelBasedConfidence, $score);
                                $personLabelMatches[] = $label['description'] . " (score: {$score})";
                            }
                        }
                    }
                }
            }

            // Final decision: prioritize face detection over label detection
            $personDetected = $personDetectedFromFace || $personDetectedFromLabels;
            $finalConfidence = $personDetectedFromFace ? $maxFaceConfidence : $labelBasedConfidence;

            // Log detailed information for debugging
            Log::info('Google Vision analysis completed', [
                'incident_id' => $incidentId,
                'face_detection' => [
                    'faces_found' => $faceCount,
                    'person_detected' => $personDetectedFromFace,
                    'max_confidence' => $maxFaceConfidence,
                ],
                'label_detection' => [
                    'person_detected' => $personDetectedFromLabels,
                    'confidence' => $labelBasedConfidence,
                    'matched_labels' => $personLabelMatches,
                ],
                'final_result' => [
                    'person_detected' => $personDetected,
                    'person_confidence' => $finalConfidence,
                    'method' => $personDetectedFromFace ? 'face_detection' : ($personDetectedFromLabels ? 'label_detection' : 'none'),
                ],
                'all_labels' => array_slice($labels, 0, 10), // Log first 10 labels for debugging
                'label_count' => count($labels),
            ]);

            return [
                'person_detected' => $personDetected,
                'person_confidence' => $finalConfidence,
                'labels' => $labels,
                'detection_method' => $personDetectedFromFace ? 'face' : ($personDetectedFromLabels ? 'label' : 'none'),
                'face_count' => $faceCount,
                'person_label_matches' => $personLabelMatches ?? [],
            ];
        } catch (ApiException $e) {
            // Google API specific exception with detailed error extraction
            $errorDetails = $this->extractGoogleApiError($e, $incidentId);
            return $this->getDefaultResponse($errorDetails['message'], $errorDetails);
        } catch (\Throwable $e) {
            // Other exceptions with full details
            Log::error('VISION_UNKNOWN_ERROR', [
                'incident_id' => $incidentId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'exception_class' => get_class($e),
                'exception_code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'image_path' => $absolutePath ?? $imagePath,
            ]);
            return $this->getDefaultResponse('Vision failed: ' . $e->getMessage(), [
                'exception_class' => get_class($e),
                'exception_code' => $e->getCode(),
            ]);
        }
    }

    /**
     * Get Google Vision client instance
     */
    private function getClient(): ?ImageAnnotatorClient
    {
        if ($this->client !== null) {
            return $this->client;
        }

        try {
            $credentialsPath = $this->credentialsPath;
            
            Log::info('Initializing Google Vision client', [
                'credentials_path' => $credentialsPath,
                'file_exists' => file_exists($credentialsPath),
                'is_readable' => file_exists($credentialsPath) ? is_readable($credentialsPath) : false,
            ]);
            
            if (!file_exists($credentialsPath)) {
                Log::error('Google Vision credentials file not found', [
                    'path' => $credentialsPath,
                    'resolved_path' => realpath($credentialsPath) ?: 'not resolved',
                ]);
                return null;
            }

            // Read and validate JSON (without logging private_key)
            $credentialsContent = file_get_contents($credentialsPath);
            $credentialsJson = json_decode($credentialsContent, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Invalid JSON in credentials file', [
                    'path' => $credentialsPath,
                    'json_error' => json_last_error_msg(),
                ]);
                return null;
            }
            
            $projectId = $credentialsJson['project_id'] ?? 'unknown';
            $clientEmail = $credentialsJson['client_email'] ?? 'unknown';
            
            Log::info('Google Vision credentials loaded', [
                'project_id' => $projectId,
                'client_email' => $clientEmail,
                'has_private_key' => !empty($credentialsJson['private_key'] ?? ''),
            ]);

            // Set environment variable for Google Cloud SDK (use absolute path)
            putenv("GOOGLE_APPLICATION_CREDENTIALS={$credentialsPath}");

            $this->client = new ImageAnnotatorClient([
                'credentials' => $credentialsPath,
            ]);

            Log::info('Google Vision client initialized successfully', [
                'project_id' => $projectId,
            ]);

            return $this->client;
        } catch (ApiException $e) {
            $errorDetails = $this->extractGoogleApiError($e, null);
            Log::error('Failed to initialize Google Vision client (API Exception)', [
                'error' => $errorDetails['message'],
                'details' => $errorDetails['details'],
                'credentials_path' => $this->credentialsPath,
            ]);
            return null;
        } catch (\Throwable $e) {
            Log::error('VISION_UNKNOWN_ERROR', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'exception_class' => get_class($e),
                'exception_code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'credentials_path' => $this->credentialsPath,
            ]);
            return null;
        }
    }

    /**
     * Get credentials file path (always returns absolute path)
     */
    private function getCredentialsPath(): string
    {
        $envPath = env('GOOGLE_APPLICATION_CREDENTIALS');
        
        if ($envPath) {
            // If relative path, make it absolute from project root
            if (!str_starts_with($envPath, '/') && !preg_match('/^[A-Z]:/', $envPath)) {
                $absolutePath = base_path($envPath);
            } else {
                $absolutePath = $envPath;
            }
            
            // Resolve to real path if possible
            $resolvedPath = realpath($absolutePath) ?: $absolutePath;
            
            Log::debug('Resolved credentials path', [
                'env_path' => $envPath,
                'absolute_path' => $absolutePath,
                'resolved_path' => $resolvedPath,
            ]);
            
            return $resolvedPath;
        }

        // Default path (fallback if env not set) - always absolute
        $defaultPath = base_path('storage/app/google/sewage-image-ai-e535f34264f1.json');
        $resolvedDefault = realpath($defaultPath) ?: $defaultPath;
        
        return $resolvedDefault;
    }

    /**
     * Extract detailed error information from Google API exception
     * Uses safe method calls as recommended by Google API patterns
     */
    private function extractGoogleApiError(ApiException $e, ?int $incidentId): array
    {
        // Safely extract status and basic message (methods may not exist in all versions)
        $status = method_exists($e, 'getStatus') ? $e->getStatus() : null;
        $basicMessage = method_exists($e, 'getBasicMessage') ? $e->getBasicMessage() : null;
        $metadata = method_exists($e, 'getMetadata') ? $e->getMetadata() : null;
        
        $errorDetails = [
            'exception_class' => get_class($e),
            'status' => $status, // Store full status object for evidence
            'status_code' => $status && property_exists($status, 'code') ? $status->code : null,
            'status_message' => $status && property_exists($status, 'message') ? $status->message : null,
            'basic_message' => $basicMessage,
            'metadata' => $metadata,
        ];
        
        // Check for specific Google API errors
        $message = $e->getMessage();
        $actionableMessage = $basicMessage ?? $message;
        
        // Check for API not enabled
        $statusCode = $status && property_exists($status, 'code') ? $status->code : null;
        if (str_contains($message, 'SERVICE_DISABLED') || 
            str_contains($message, 'vision.googleapis.com') ||
            $statusCode === 7) { // PERMISSION_DENIED
            $actionableMessage = "Cloud Vision API not enabled. Enable 'Cloud Vision API' (vision.googleapis.com) in Google Cloud Console for project.";
        }
        
        // Check for permission denied
        if (str_contains($message, 'PERMISSION_DENIED') || $statusCode === 7) {
            $actionableMessage = "Permission denied. Check service account has 'Cloud Vision API User' role and API is enabled.";
        }
        
        Log::error('GOOGLE_VISION_API_EXCEPTION', [
            'incident_id' => $incidentId,
            'message' => $message,
            'status' => $status,
            'basicMessage' => $basicMessage,
            'metadata' => $metadata,
            'actionable_message' => $actionableMessage,
        ]);
        
        return [
            'message' => $actionableMessage,
            'details' => $errorDetails,
        ];
    }

    /**
     * Get default response on error
     */
    private function getDefaultResponse(string $error, array $errorDetails = []): array
    {
        $response = [
            'person_detected' => false,
            'person_confidence' => 0.0,
            'labels' => [],
            'error' => $error,
        ];
        
        // Include error details in evidence format (matching recommended pattern)
        if (!empty($errorDetails)) {
            $response['error_details'] = $errorDetails;
            // Also include google_status and metadata if available
            if (isset($errorDetails['details']['status'])) {
                $response['google_status'] = $errorDetails['details']['status'];
            }
            if (isset($errorDetails['details']['metadata'])) {
                $response['metadata'] = $errorDetails['details']['metadata'];
            }
        }
        
        return $response;
    }

    /**
     * Cleanup client on destruction
     */
    public function __destruct()
    {
        if ($this->client !== null) {
            $this->client->close();
        }
    }
}
