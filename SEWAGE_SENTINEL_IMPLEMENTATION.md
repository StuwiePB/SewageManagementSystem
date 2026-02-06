# Sewage Sentinel Implementation Guide

## Overview

The Sewage Sentinel pipeline is a reliable, two-stage AI system for detecting sewage incidents:

1. **Vision API** (Google Cloud Vision) - Content detection (person/face detection + sewage detection)
2. **Winston AI** - Authenticity check (AI-generated/deepfake detection)

## Architecture

```
Step 0: Image Validation (type, size, corruption)
    ↓
Step 1: Person/Face Detection Gate (Vision API)
    ├─ Person detected? → NOT_SEWAGE (skip Winston, save cost)
    └─ No person → Continue
    ↓
Step 2: Sewage Content Detection (Vision API)
    ├─ Calculate sewage_score (0.0 - 1.0)
    └─ Extract sewage indicators
    ↓
Step 3: AI-Generated Check (Winston AI) [Optional if person detected]
    ├─ Check if image is AI-generated
    └─ Store ai_generated_score
    ↓
Step 4: Decision Logic & Save Results
    ├─ Determine label (NOT_SEWAGE | NEEDS_REVIEW)
    └─ Update incident in database
```

## Hard Rules

1. **PEOPLE ARE NEVER SEWAGE** - If person/face detected → immediate NOT_SEWAGE
2. **Never auto-confirm SEWAGE** - All sewage detections go to NEEDS_REVIEW
3. **Default to NEEDS_REVIEW** - When uncertain, require human review

## Database Schema

### New Fields Added

- `person_detected` (boolean) - Whether person/face was detected
- `sewage_score` (float) - Sewage detection score (0.0 - 1.0)
- `ai_generated_score` (float) - AI-generated probability (0.0 - 1.0)
- `analysis_error` (text) - Error message if analysis failed

### Evidence JSON Structure

```json
{
  "person_detected": true/false,
  "sewage_indicators": ["pipe", "drain", "overflow"],
  "sewage_score": 0.85,
  "ai_generated": false,
  "ai_generated_score": 0.15
}
```

## Configuration

### Environment Variables

Add to `.env`:

```env
# Vision API Provider
VISION_PROVIDER=google

# Google Cloud Vision API Key
GOOGLE_VISION_API_KEY=your_api_key_here

# Winston AI
WINSTON_API_KEY=your_winston_key_here
WINSTON_ENABLED=true
```

### Thresholds (`config/sewage_sentinel.php`)

```php
'thresholds' => [
    'person_detected_min' => 0.50,    // Minimum confidence for person detection
    'sewage_high' => 0.85,            // Above this → NEEDS_REVIEW
    'sewage_low' => 0.30,             // Below this → NOT_SEWAGE
    'ai_generated_min' => 0.80,        // Above this → mark as AI-generated
],
```

## Services

### VisionService

**Location**: `app/Services/VisionService.php`

**Purpose**: Google Cloud Vision API integration for:
- Person/face detection
- Sewage content detection
- Label extraction

**Methods**:
- `analyzeImage(string $imagePath, ?int $incidentId): array`

**Returns**:
```php
[
    'person_detected' => bool,
    'person_confidence' => float,
    'sewage_score' => float,
    'sewage_indicators' => array,
    'labels' => array,
]
```

### WinstonService

**Location**: `app/Services/WinstonService.php`

**Purpose**: Winston AI integration for:
- AI-generated image detection
- Authenticity verification

**Methods**:
- `checkAuthenticity(string $publicUrl, ?int $incidentId): array`

**Returns**:
```php
[
    'ai_generated' => bool,
    'ai_generated_score' => float,
    'human_probability' => float,
]
```

### IncidentAnalyzerJob

**Location**: `app/Jobs/IncidentAnalyzerJob.php`

**Purpose**: Orchestrates the entire pipeline

**Flow**:
1. Validates image
2. Calls VisionService
3. Checks person detection (hard gate)
4. Calculates sewage score
5. Calls WinstonService (if person not detected)
6. Determines label using decision logic
7. Updates incident

## Decision Logic

```php
if (person_detected && person_confidence >= 0.50) {
    label = 'NOT_SEWAGE'
    confidence = 0.95
}
else if (sewage_score >= 0.85) {
    label = 'NEEDS_REVIEW'  // Never auto-confirm
    confidence = 0.85
}
else if (sewage_score <= 0.30) {
    label = 'NOT_SEWAGE'
    confidence = 0.70
}
else {
    label = 'NEEDS_REVIEW'  // Uncertain
    confidence = 0.60
}
```

## Usage

### Automatic Analysis

When an incident is uploaded, `IncidentAnalyzerJob` is automatically dispatched:

```php
// In IncidentController::store()
IncidentAnalyzerJob::dispatch($incident->id);
```

### Manual Reanalysis

Use the artisan command:

```bash
# Reanalyze specific incident
php artisan incidents:reanalyze --id=30

# Reanalyze all incidents with NEEDS_REVIEW status
php artisan incidents:reanalyze --status=NEEDS_REVIEW

# Reanalyze all incidents
php artisan incidents:reanalyze --all
```

## Setup Instructions

### 1. Install Dependencies

No additional packages required - uses Laravel's built-in HTTP client.

### 2. Get API Keys

**Google Cloud Vision API**:
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a project or select existing
3. Enable "Cloud Vision API"
4. Create API key in "Credentials"
5. Add to `.env`: `GOOGLE_VISION_API_KEY=your_key`

**Winston AI**:
1. Go to [Winston AI Developer Dashboard](https://dev.gowinston.ai)
2. Sign up/login
3. Generate API key
4. Add to `.env`: `WINSTON_API_KEY=your_key`

### 3. Run Migration

```bash
php artisan migrate
```

This adds the new fields:
- `person_detected`
- `sewage_score`
- `ai_generated_score`
- `analysis_error`

### 4. Configure Thresholds

Edit `config/sewage_sentinel.php` to adjust detection thresholds.

### 5. Test

Upload an incident image and check logs:

```bash
tail -f storage/logs/laravel.log
```

## Cost Optimization

- **Skip Winston if person detected** - Saves 300 credits per blocked image
- **Timeout limits** - Prevents hanging requests
- **Error handling** - Graceful fallbacks prevent wasted API calls

## Error Handling

If any API fails:
- Incident status → `NEEDS_REVIEW`
- Error stored in `analysis_error` field
- Evidence JSON includes error details
- Logs contain full error trace

## Logging

All steps are logged with incident ID:
- `Person detection result`
- `Sewage detection result`
- `Winston AI authenticity check`
- `Incident analyzed successfully`

Check logs: `storage/logs/laravel.log`

## Testing

### Test Person Detection

Upload a portrait/selfie image → Should be classified as `NOT_SEWAGE` immediately.

### Test Sewage Detection

Upload an image with sewage indicators → Should be classified as `NEEDS_REVIEW` with sewage_score > 0.85.

### Test Error Handling

Disable API keys → Should fallback to `NEEDS_REVIEW` with error message.

## Future Enhancements

1. **AWS Rekognition Support** - Swap provider via config
2. **OpenAI Vision Support** - Alternative vision provider
3. **Batch Processing** - Analyze multiple images at once
4. **Confidence Thresholds** - Per-incident threshold adjustment
5. **Admin Override** - Manual label correction with feedback loop

## Troubleshooting

### "API key not configured"
- Check `.env` file has correct keys
- Run `php artisan config:clear`

### "Image not found"
- Check `storage/app/incidents/` directory exists
- Verify file permissions

### "Winston API error"
- Check Winston API key is valid
- Verify image URL is publicly accessible
- Check Winston credits balance

### "Vision API error"
- Check Google Cloud Vision API is enabled
- Verify API key has correct permissions
- Check billing/quota limits

## Files Changed

1. `database/migrations/2026_02_06_051447_add_sewage_sentinel_fields_to_incidents_table.php`
2. `app/Services/VisionService.php` (new)
3. `app/Services/WinstonService.php` (new)
4. `app/Jobs/IncidentAnalyzerJob.php` (new)
5. `app/Models/Incident.php` (updated)
6. `app/Http/Controllers/IncidentController.php` (updated)
7. `app/Console/Commands/ReanalyzeIncident.php` (new)
8. `config/sewage_sentinel.php` (new)
9. `config/services.php` (updated)
10. `.env.example` (updated)

## Support

For issues or questions:
1. Check logs: `storage/logs/laravel.log`
2. Review error messages in `analysis_error` field
3. Verify API keys and quotas
4. Test with `php artisan incidents:reanalyze --id=X`
