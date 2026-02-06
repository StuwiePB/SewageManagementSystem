# Google Vision "Unavailable" Fix Summary

## Problem
Web UI showed "Google Vision unavailable" even though CLI `php artisan vision:diagnose` said client initialized successfully.

## Root Causes Fixed

### 1. **Insufficient Error Logging**
- **Before**: Exceptions were caught but not logged with full details
- **After**: All exceptions now log:
  - Exception class, message, code
  - File and line number
  - Full stack trace
  - Google API specific error details (status codes, error messages)
  - Image paths (absolute and relative)

### 2. **Path Resolution Issues**
- **Before**: Relative paths could fail depending on CWD
- **After**: All paths are resolved to absolute paths using `base_path()` and `realpath()`
- Credentials path is always absolute
- Image paths are logged with both relative and absolute versions

### 3. **Missing Google API Error Detection**
- **Before**: Generic error messages didn't indicate API enablement issues
- **After**: Detects specific Google API errors:
  - `SERVICE_DISABLED` → "Cloud Vision API not enabled"
  - `PERMISSION_DENIED` → "Permission denied. Check service account roles"
  - Provides actionable error messages with links to enable API

### 4. **Empty Labels vs Real Errors**
- **Before**: Empty labels from Vision API were treated as "unavailable"
- **After**: Distinguishes between:
  - **Real API errors** → Shows "Google Vision API error" with details
  - **Empty labels** (API succeeded but no labels) → Shows "No labels detected" or "No sewage indicators found"

### 5. **Project ID Not Visible**
- **Before**: No way to verify which GCP project was being used
- **After**: Project ID is logged and displayed in diagnostic commands

## Files Changed

### 1. `app/Services/GoogleVisionService.php`
- ✅ Enhanced exception handling with detailed logging
- ✅ Absolute path resolution for credentials
- ✅ Google API error extraction (`extractGoogleApiError()`)
- ✅ Project ID logging (without leaking private keys)
- ✅ Image path validation and logging
- ✅ Response error checking (checks `$annotateResponse->getError()`)

### 2. `app/Jobs/AnalyzeIncidentImage.php`
- ✅ Enhanced error handling with exception details
- ✅ Stores error details in evidence JSON
- ✅ Improved fallback classification with better error messages
- ✅ Distinguishes real exceptions from empty labels
- ✅ Logs image paths and file validation

### 3. `app/Services/SewageClassifier.php`
- ✅ Handles empty labels correctly
- ✅ Different messages for "no labels" vs "no sewage indicators"
- ✅ Sets `labels_empty` flag in evidence

### 4. `app/Console/Commands/DiagnoseVision.php`
- ✅ Shows project_id from credentials
- ✅ Shows private_key_id (for verification)
- ✅ Provides link to enable Cloud Vision API
- ✅ Detects PERMISSION_DENIED and SERVICE_DISABLED errors

### 5. `app/Console/Commands/VisionTest.php` (NEW)
- ✅ Reproduces exact queue job call path
- ✅ Tests: Image validation → Vision API → Classification
- ✅ Shows detailed results and error messages
- ✅ Helps debug why queue jobs fail when CLI works

## How to Use

### 1. Diagnose Setup
```bash
php artisan vision:diagnose
```
Shows:
- Package installation
- Credentials file location and validity
- Project ID
- Client initialization
- Recent errors

### 2. Test Specific Incident (Queue Job Path)
```bash
php artisan vision:test --incident=40
```
Reproduces the exact same call path as the queue job:
- Image validation
- Google Vision API call
- Classification
- Shows all results and errors

### 3. Debug Specific Incident
```bash
php artisan incidents:debug 40
```
Shows what Google Vision detected for an incident.

### 4. Check Logs
```bash
tail -f storage/logs/laravel.log
```
Look for:
- `[INFO] Starting Google Vision analysis` - Shows image paths
- `[INFO] Google Vision credentials loaded` - Shows project_id
- `[ERROR] Google Vision API exception` - Shows detailed error info
- `[ERROR] Google Vision analysis failed` - Shows exception details

## Error Messages Now Show

### Real API Errors:
- "Cloud Vision API not enabled. Enable 'Cloud Vision API' (vision.googleapis.com)..."
- "Permission denied. Check service account has 'Cloud Vision API User' role..."
- "Image file not found at path: ..."
- "API call failed: [exception message]"

### Success Cases:
- "No significant sewage indicators detected from image analysis."
- "No labels detected from Google Vision. Requires manual review."
- "Uncertain classification. Requires human review."

## Important Notes

1. **Queue Worker Must Restart**: After config changes, restart queue worker:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   # Restart queue worker
   ```

2. **API Enablement**: Make sure Cloud Vision API is enabled:
   - Go to: https://console.cloud.google.com/apis/library/vision.googleapis.com
   - Select your project (check project_id from `vision:diagnose`)
   - Click "Enable"

3. **Service Account Permissions**: Service account needs:
   - Role: "Cloud Vision API User" or "Cloud Vision API Client"
   - API must be enabled for the project

4. **Credentials Path**: Always uses absolute path now, so CWD doesn't matter

## Verification Checklist

- [ ] Run `php artisan vision:diagnose` → All checks pass
- [ ] Run `php artisan vision:test --incident=X` → Shows labels detected
- [ ] Check logs → No "Google Vision API exception" errors
- [ ] Upload new incident → Gets classified (not "unavailable")
- [ ] Check dashboard → Shows proper labels/indicators (not "unavailable")

## Next Steps if Still Failing

1. **Check Logs**: Look for detailed error messages in `storage/logs/laravel.log`
2. **Verify API**: Run `vision:diagnose` and check project_id matches your GCP project
3. **Enable API**: Use the link provided in diagnose output
4. **Test Path**: Use `vision:test --incident=X` to reproduce queue job path
5. **Check Queue Worker**: Make sure queue worker is using same .env/config as CLI
