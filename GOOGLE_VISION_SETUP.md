# Google Vision Setup Guide

## Overview

This guide explains how to set up Google Cloud Vision API with service account authentication for the Sewage Sentinel pipeline.

## Prerequisites

- Google Cloud account
- Vision AI API enabled in Google Cloud Console
- Service Account JSON key file downloaded

## Step 1: Place Service Account JSON File

1. Create the directory if it doesn't exist:
   ```bash
   mkdir -p storage/app/google
   ```

2. Place your downloaded service account JSON file in:
   ```
   storage/app/google/vision-service-account.json
   ```

3. **Important**: The file is automatically ignored by git (see `.gitignore`)

## Step 2: Configure Environment

Add to your `.env` file:

```env
GOOGLE_APPLICATION_CREDENTIALS=storage/app/google/vision-service-account.json
```

**Note**: The path is relative to your project root. Laravel will use `base_path()` to resolve it.

## Step 3: Install Composer Dependency

```bash
composer require google/cloud-vision
```

## Step 4: Run Migration

```bash
php artisan migrate
```

This adds the required database fields:
- `person_detected` (boolean)
- `sewage_score` (float)
- `sewage_indicators` (json)
- `ai_generated` (boolean)
- `ai_generated_score` (float)
- `analysis_error` (text)

## Step 5: Test Setup

Test your Google Vision integration:

```bash
# Place a test image in storage/app/test-image.jpg
php artisan test:vision test-image.jpg
```

Expected output:
```
Testing Google Vision API...
Image: test-image.jpg

Step 1: Google Vision Analysis
─────────────────────────────────
Person Detected: NO
Person Confidence: 0.00
Labels Found: 20

Top Labels:
  - Water: 0.987
  - Pipe: 0.856
  - Infrastructure: 0.743
  ...

Step 2: Sewage Classification
─────────────────────────────────
Label: NEEDS_REVIEW
Confidence: 0.85
Reason: Strong sewage indicators detected. Requires human verification.

Evidence:
  - Person Detected: NO
  - Sewage Score: 0.856
  - Sewage Indicators:
    • Pipe
    • Wastewater

✅ Test completed successfully!
```

## File Structure

```
storage/app/google/
  └── vision-service-account.json  (your service account JSON)

app/Services/
  ├── GoogleVisionService.php      (Google Vision integration)
  ├── SewageClassifier.php         (Classification logic)
  └── WinstonService.php           (Winston AI integration)

app/Jobs/
  └── AnalyzeIncidentImage.php     (Pipeline orchestrator)

config/
  └── ai.php                        (Thresholds and keywords)
```

## How It Works

### GoogleVisionService

- Uses service account JSON for authentication
- Performs face detection and label detection
- Returns person detection status and all labels

### SewageClassifier

- Analyzes labels for sewage keywords
- Calculates sewage_score (0.0 - 1.0)
- Applies decision logic based on thresholds

### Pipeline Flow

```
Step 0: Validate image
  ↓
Step 1: Google Vision (face detection)
  ├─ Person detected? → NOT_SEWAGE (skip Winston)
  └─ No person → Continue
  ↓
Step 2: Google Vision (label detection) + Classification
  ├─ Calculate sewage_score
  └─ Determine label
  ↓
Step 3: Winston AI (authenticity check) [Optional]
  └─ Check if AI-generated
  ↓
Step 4: Save results
```

## Configuration

Edit `config/ai.php` to adjust:

- **Thresholds**: Person detection, sewage score thresholds
- **Keywords**: Sewage detection keywords
- **Confidence scores**: For different label types

## Troubleshooting

### "Credentials file not found"

- Check file exists at: `storage/app/google/vision-service-account.json`
- Verify `.env` has correct path: `GOOGLE_APPLICATION_CREDENTIALS=storage/app/google/vision-service-account.json`
- Check file permissions (should be readable)

### "Failed to initialize Google Vision client"

- Verify JSON file is valid JSON
- Check service account has Vision API enabled
- Ensure service account has proper permissions

### "Image not found"

- Verify image path is relative to `storage/app/`
- Check file exists and is readable
- Ensure correct disk is used (`local`)

## Security Notes

- ✅ Service account JSON is in `.gitignore` (never committed)
- ✅ Path uses `base_path()` for absolute resolution
- ✅ No API keys exposed to frontend
- ✅ All processing happens server-side

## Next Steps

1. Upload an incident via web interface
2. Check logs: `storage/logs/laravel.log`
3. Reanalyze existing incidents:
   ```bash
   php artisan incidents:reanalyze --id=30
   ```

## Support

- Check logs for detailed error messages
- Review `analysis_error` field in database
- Test with `php artisan test:vision <image_path>`
