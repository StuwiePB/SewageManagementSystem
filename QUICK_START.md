# Quick Start Guide - Google Vision Integration

## 🚀 Setup in 5 Steps

### Step 1: Install Composer Package

```bash
composer require google/cloud-vision
```

### Step 2: Place Service Account JSON

```bash
# Create directory
mkdir -p storage/app/google

# Copy your downloaded service account JSON file to:
# storage/app/google/vision-service-account.json
```

**Important**: The file is automatically ignored by git (already in `.gitignore`)

### Step 3: Configure Environment

Add to your `.env` file:

```env
GOOGLE_APPLICATION_CREDENTIALS=storage/app/google/vision-service-account.json
```

**Note**: Path is relative to project root. Laravel uses `base_path()` to resolve it.

### Step 4: Run Migration

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

### Step 5: Test

```bash
# Place a test image in storage/app/
php artisan test:vision test-image.jpg
```

## 📁 File Structure

```
storage/app/google/
  └── vision-service-account.json  ← Your service account JSON here

app/Services/
  ├── GoogleVisionService.php      ← Google Vision integration
  ├── SewageClassifier.php         ← Classification logic
  └── WinstonService.php           ← Winston AI (authenticity)

app/Jobs/
  └── AnalyzeIncidentImage.php     ← Pipeline orchestrator

config/
  └── ai.php                        ← Thresholds & keywords
```

## 🔧 Configuration

Edit `config/ai.php` to adjust:

- **Thresholds**: Detection sensitivity
- **Keywords**: Sewage detection terms
- **Confidence scores**: Label confidence levels

## ✅ Usage

### Automatic (Upload Incident)

When you upload an incident via web interface, analysis runs automatically.

### Manual Reanalysis

```bash
# Reanalyze specific incident
php artisan incidents:reanalyze --id=30

# Reanalyze all NEEDS_REVIEW incidents
php artisan incidents:reanalyze --status=NEEDS_REVIEW

# Reanalyze all incidents
php artisan incidents:reanalyze --all
```

### Test Vision API

```bash
php artisan test:vision storage/app/your-image.jpg
```

## 🛡️ Hard Rules

1. **PEOPLE ARE NEVER SEWAGE** - Face detection blocks sewage analysis
2. **Never Auto-Confirm SEWAGE** - All sewage detections → NEEDS_REVIEW
3. **Default to NEEDS_REVIEW** - Uncertain cases require human review

## 📊 Output Format

Results stored in database:

```json
{
  "ai_label": "NOT_SEWAGE" | "NEEDS_REVIEW",
  "ai_confidence": 0.95,
  "person_detected": true,
  "sewage_score": 0.15,
  "sewage_indicators": [],
  "ai_generated": false,
  "ai_generated_score": 0.20
}
```

## 🔍 Troubleshooting

### "Credentials file not found"
- Check file exists: `storage/app/google/vision-service-account.json`
- Verify `.env` path is correct
- Check file permissions

### "Failed to initialize Google Vision client"
- Verify JSON file is valid
- Check service account has Vision API enabled
- Ensure proper permissions

### Test Command Fails
- Verify image path is relative to `storage/app/`
- Check image file exists and is readable
- Review logs: `storage/logs/laravel.log`

## 📚 Documentation

- **Full Setup Guide**: `GOOGLE_VISION_SETUP.md`
- **Implementation Details**: `IMPLEMENTATION_COMPLETE.md`
- **Config Reference**: `config/ai.php`

## ✨ Next Steps

1. ✅ Install `google/cloud-vision`
2. ✅ Place service account JSON
3. ✅ Configure `.env`
4. ✅ Run migration
5. ✅ Test with sample image
6. ✅ Upload incident and verify

## 🎯 Status

✅ **Ready to Use** - All code implemented and tested
