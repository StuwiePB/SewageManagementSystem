# Troubleshooting: All Images Going to NEEDS_REVIEW

## Problem

All uploaded images are being classified as `NEEDS_REVIEW` instead of being properly analyzed.

## Root Cause

This happens when **Google Vision API is not working**. The system defaults to `NEEDS_REVIEW` when:
1. Google Vision client initialization fails
2. Credentials file is missing
3. API call fails
4. No labels are returned

## Quick Diagnosis

Run this command to check your setup:

```bash
php artisan vision:diagnose
```

This will check:
- ✅ Composer package installed
- ✅ Environment variable set
- ✅ Credentials file exists
- ✅ Client initialization works
- ✅ Recent errors

## Common Issues & Fixes

### Issue 1: Credentials File Missing

**Symptom**: All incidents show `NEEDS_REVIEW` with error

**Fix**:
1. Place your service account JSON file at:
   ```
   storage/app/google/vision-service-account.json
   ```

2. Verify `.env` has:
   ```env
   GOOGLE_APPLICATION_CREDENTIALS=storage/app/google/vision-service-account.json
   ```

3. Check if file exists:
   ```bash
   dir storage\app\google\vision-service-account.json
   ```

### Issue 2: Composer Package Not Installed

**Symptom**: Client initialization fails

**Fix**:
```bash
composer require google/cloud-vision
```

### Issue 3: Invalid JSON File

**Symptom**: "Credentials file is NOT valid JSON"

**Fix**:
- Verify the JSON file is valid
- Make sure it's the complete service account JSON (not truncated)
- Check for syntax errors

### Issue 4: Service Account Permissions

**Symptom**: API calls fail with permission errors

**Fix**:
1. Go to Google Cloud Console
2. Enable "Cloud Vision API" for your project
3. Ensure service account has "Cloud Vision API User" role

## Check Logs

View recent errors:

```bash
# Windows PowerShell
Get-Content storage\logs\laravel.log -Tail 50

# Or check specific errors
Select-String -Path storage\logs\laravel.log -Pattern "Google Vision" | Select-Object -Last 10
```

Look for:
- `Google Vision client initialization failed`
- `Credentials file not found`
- `Failed to initialize Google Vision client`
- `Analysis failed: ...`

## Check Database

See what errors are stored:

```bash
php artisan tinker
```

```php
// Check recent incidents with errors
$incidents = App\Models\Incident::whereNotNull('analysis_error')
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get(['id', 'analysis_error', 'review_status']);

foreach ($incidents as $inc) {
    echo "Incident #{$inc->id}: {$inc->analysis_error}\n";
}
```

## Fallback Behavior

I've added a **fallback classification** that:
- Detects portraits using aspect ratio (when Google Vision fails)
- Returns `NOT_SEWAGE` for obvious portraits
- Still uses `NEEDS_REVIEW` for uncertain cases

This means:
- **Portrait images** → Will be `NOT_SEWAGE` even if Google Vision fails
- **Other images** → Will be `NEEDS_REVIEW` until Google Vision is fixed

## Fix Steps

1. **Run diagnostics:**
   ```bash
   php artisan vision:diagnose
   ```

2. **Fix any issues found** (install package, place JSON file, etc.)

3. **Clear cache:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

4. **Reanalyze incidents:**
   ```bash
   php artisan incidents:reanalyze --all
   ```

5. **Check dashboard** - Should now show proper classifications

## Expected Behavior After Fix

- **Portrait images** → `NOT_SEWAGE` (95% confidence)
- **Sewage images** → `NEEDS_REVIEW` (85% confidence) with sewage indicators
- **Clean images** → `NOT_SEWAGE` (70% confidence)
- **Uncertain** → `NEEDS_REVIEW` (60% confidence)

## Still Having Issues?

1. Check logs: `storage/logs/laravel.log`
2. Run diagnostics: `php artisan vision:diagnose`
3. Verify credentials file exists and is valid JSON
4. Check Google Cloud Console - ensure Vision API is enabled
5. Verify service account has proper permissions
