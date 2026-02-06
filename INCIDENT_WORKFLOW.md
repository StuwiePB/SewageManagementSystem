# Incident Workflow & Status Flow

## Complete Workflow

```
1. UPLOAD
   ↓
   Status: PENDING_AI
   (Image uploaded, waiting for AI analysis)
   
2. AI ANALYSIS (Background Job)
   ↓
   Status: NEEDS_REVIEW (most cases)
   OR
   Status: SEWAGE_CONFIRMED (if confidence ≥ 90%)
   
3. ADMIN REVIEW (if NEEDS_REVIEW)
   ↓
   Admin clicks "Confirm Sewage" → SEWAGE_CONFIRMED
   OR
   Admin clicks "Not Sewage" → NOT_SEWAGE
   
4. FINAL STATES
   ✅ SEWAGE_CONFIRMED - Confirmed sewage incident
   ✅ NOT_SEWAGE - Confirmed not sewage
```

## Status Definitions

### PENDING_AI
- **When**: Immediately after upload
- **Meaning**: Waiting for AI analysis
- **Next**: Automatically moves to NEEDS_REVIEW or SEWAGE_CONFIRMED

### NEEDS_REVIEW
- **When**: After AI analysis (confidence < 90%)
- **Meaning**: Requires human admin review
- **Next**: Admin confirms or rejects → SEWAGE_CONFIRMED or NOT_SEWAGE

### SEWAGE_CONFIRMED
- **When**: 
  - AI confidence ≥ 90% (auto-confirmed)
  - OR Admin clicks "Confirm Sewage"
- **Meaning**: Confirmed as actual sewage incident
- **Next**: Final state - incident is resolved

### NOT_SEWAGE
- **When**: Admin clicks "Not Sewage"
- **Meaning**: Confirmed as NOT a sewage incident
- **Next**: Final state - incident is resolved

## Where Incidents Go

1. **Dashboard** (`/incidents/dashboard`)
   - Shows ALL incidents with risk scores
   - Sorted by risk score (highest first)
   - Includes all statuses

2. **Admin Review** (`/admin/incidents/review`)
   - Shows ONLY incidents with NEEDS_REVIEW status
   - Where admins make decisions

3. **Final States**
   - SEWAGE_CONFIRMED → Confirmed sewage (action needed)
   - NOT_SEWAGE → False alarm (no action needed)

## Missing: Resolved/Archived View

Currently, there's no dedicated view for:
- Resolved incidents
- Archived incidents
- Completed reviews

Would you like me to add a "Resolved Incidents" view?
