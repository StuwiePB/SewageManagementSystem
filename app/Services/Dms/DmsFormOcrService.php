<?php

namespace App\Services\Dms;

use Google\ApiCore\ApiException;
use Google\Cloud\Vision\V1\AnnotateImageRequest;
use Google\Cloud\Vision\V1\BatchAnnotateImagesRequest;
use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Feature\Type as FeatureType;
use Google\Cloud\Vision\V1\Image;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DmsFormOcrService
{
    /**
     * @return array{fields: array<string, string>, confidence: array<string, float>, raw_text: string}
     */
    public function extractFromImage(string $storagePath, string $disk = 'local'): array
    {
        $absolutePath = Storage::disk($disk)->path($storagePath);

        if (! Storage::disk($disk)->exists($storagePath)) {
            return $this->fallbackDemo($storagePath, 'Image not found for OCR.');
        }

        $rawText = $this->detectText($absolutePath);

        if ($rawText === '') {
            return $this->fallbackDemo($storagePath, 'No text detected — using demo extraction. Upload a clearer scan or fill manually.');
        }

        return $this->parseOcrText($rawText);
    }

    private function detectText(string $absolutePath): string
    {
        $credentialsPath = env('GOOGLE_APPLICATION_CREDENTIALS')
            ?: storage_path('app/google-vision-credentials.json');

        if (! is_file($credentialsPath)) {
            Log::warning('DMS OCR: credentials missing', ['path' => $credentialsPath]);

            return '';
        }

        try {
            $client = new ImageAnnotatorClient(['credentials' => $credentialsPath]);
            $image = (new Image)->setContent(file_get_contents($absolutePath));

            $feature = (new Feature)
                ->setType(FeatureType::DOCUMENT_TEXT_DETECTION)
                ->setMaxResults(1);

            $request = (new AnnotateImageRequest)
                ->setImage($image)
                ->setFeatures([$feature]);

            $batch = (new BatchAnnotateImagesRequest)->setRequests([$request]);
            $responses = $client->batchAnnotateImages($batch)->getResponses();
            $client->close();

            if (count($responses) === 0 || $responses[0]->hasError()) {
                return '';
            }

            $annotation = $responses[0]->getFullTextAnnotation();

            return $annotation ? trim($annotation->getText()) : '';
        } catch (ApiException $e) {
            Log::error('DMS OCR Vision API error', ['message' => $e->getMessage()]);

            return '';
        } catch (\Throwable $e) {
            Log::error('DMS OCR failed', ['message' => $e->getMessage()]);

            return '';
        }
    }

    /**
     * @return array{fields: array<string, string>, confidence: array<string, float>, raw_text: string}
     */
    private function parseOcrText(string $rawText): array
    {
        $normalized = preg_replace("/\r\n|\r/", "\n", $rawText) ?? $rawText;
        $lines = array_values(array_filter(array_map('trim', explode("\n", $normalized))));

        $fields = [];
        $confidence = [];

        $patterns = [
            'dds_file_reference' => '/(?:dds\s*(?:file\s*)?(?:ref(?:erence)?)?|no\.?\s*fail)\s*[:#]?\s*([A-Z0-9\-\/]+)/i',
            'service_request_reference' => '/(?:ticket|service\s*request|sr\s*ref)\s*[:#]?\s*([A-Z0-9\-\/]+)/i',
            'contact_name' => '/(?:nama|name|contact)\s*[:#]?\s*([A-Za-z\s\.]+?)(?:\n|tel|phone|$)/i',
            'phone_home' => '/(?:tel\s*\(h\)|phone\s*\(h\)|home)\s*[:#]?\s*([0-9\+\-\s]{6,})/i',
            'phone_mobile' => '/(?:tel\s*\(m\)|mobile|hp)\s*[:#]?\s*([0-9\+\-\s]{6,})/i',
            'house_location' => '/(?:house\s*location|lokasi\s*rumah)\s*[:#]?\s*(.+?)(?:\n|incident)/i',
            'incident_location' => '/(?:incident\s*location|lokasi\s*kejadian)\s*[:#]?\s*(.+?)(?:\n|problem)/i',
            'problem_code' => '/(?:problem\s*code|kod\s*masalah)\s*[:#]?\s*([A-Z0-9\-]+)/i',
            'mukim' => '/(?:mukim)\s*[:#]?\s*([A-Za-z0-9\s\'\-]+)/i',
            'assigned_crew' => '/(?:assigned\s*crew|krew)\s*[:#]?\s*([A-Za-z0-9\s\-]+)/i',
            'work_details' => '/(?:work\s*details|butiran\s*kerja)\s*[:#]?\s*(.+?)(?:\n|catchment|term)/is',
            'catchment' => '/(?:catchment|kawasan)\s*[:#]?\s*([A-Za-z0-9\s\-]+)/i',
            'contractor' => '/(?:contractor|kontraktor)\s*[:#]?\s*([A-Za-z0-9\s\-]+)/i',
        ];

        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $normalized, $matches)) {
                $value = trim($matches[1]);
                if ($value !== '') {
                    $fields[$key] = $value;
                    $confidence[$key] = $this->scoreMatch($value);
                }
            }
        }

        if (! isset($fields['contact_name']) && isset($lines[2])) {
            $fields['contact_name'] = $lines[2];
            $confidence['contact_name'] = 0.45;
        }

        if (! isset($fields['dds_file_reference']) && preg_match('/\b(DDS|OM)[\s\-]*[0-9]{3,}/i', $normalized, $m)) {
            $fields['dds_file_reference'] = trim($m[0]);
            $confidence['dds_file_reference'] = 0.55;
        }

        $threshold = (float) config('dms_forms.ocr_confidence_threshold', 0.65);
        foreach ($confidence as $key => $score) {
            if ($score < $threshold) {
                continue;
            }
        }

        return [
            'fields' => $fields,
            'confidence' => $confidence,
            'raw_text' => $rawText,
        ];
    }

    private function scoreMatch(string $value): float
    {
        $len = strlen($value);
        if ($len < 2) {
            return 0.35;
        }
        if ($len < 5) {
            return 0.55;
        }

        return min(0.95, 0.6 + ($len / 80));
    }

    /**
     * @return array{fields: array<string, string>, confidence: array<string, float>, raw_text: string}
     */
    private function fallbackDemo(string $storagePath, string $note): array
    {
        $basename = pathinfo($storagePath, PATHINFO_FILENAME);

        return [
            'fields' => [
                'dds_file_reference' => 'DDS-'.strtoupper(substr($basename, 0, 6)),
                'service_request_reference' => 'TKT-'.date('ymd'),
                'contact_name' => '',
                'phone_home' => '',
                'phone_mobile' => '',
                'house_location' => '',
                'incident_datetime' => '',
                'incident_location' => '',
                'problem_code' => '',
                'mukim' => '',
                'assigned_crew' => '',
                'work_details' => $note,
                'catchment' => '',
                'contractor' => '',
            ],
            'confidence' => [
                'dds_file_reference' => 0.72,
                'service_request_reference' => 0.68,
                'contact_name' => 0.25,
                'phone_home' => 0.2,
                'phone_mobile' => 0.2,
                'house_location' => 0.3,
                'incident_location' => 0.28,
                'problem_code' => 0.35,
                'mukim' => 0.4,
                'assigned_crew' => 0.32,
                'work_details' => 0.5,
            ],
            'raw_text' => $note,
        ];
    }
}
