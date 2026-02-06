# Incident Lifecycle - Complete Guide

## 📊 Visual Workflow

```
┌─────────────────┐
│   UPLOAD IMAGE  │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  PENDING_AI     │ ← Initial state after upload
│  (Waiting...)   │
└────────┬────────┘
         │
         │ AI Analysis Job Runs
         ▼
    ┌────────┐
    │  AI    │
    │ Analysis│
    └───┬────┘
        │
        ├─────────────────┬─────────────────┐
        │                 │                 │
        ▼                 ▼                 ▼
┌──────────────┐  ┌──────────────┐  ┌──────────────┐
│ NEEDS_REVIEW │  │ SEWAGE_      │  │ NEEDS_REVIEW │
│ (Conf < 90%) │  │ CONFIRMED    │  │ (Conf < 90%) │
│              │  │ (Conf ≥ 90%) │  │              │
└──────┬───────┘  └──────────────┘  └──────┬───────┘
        │                                    │
        │ Admin Reviews                      │ Admin Reviews
        │                                    │
        ├──────────────┬──────────────┐     ├──────────────┬──────────────┐
        │              │              │     │              │              │
        ▼              ▼              ▼     ▼              ▼              ▼
┌──────────────┐ ┌──────────────┐ ┌──────────────┐ ┌──────────────┐ ┌──────────────┐
│ SEWAGE_      │ │ NOT_SEWAGE  │ │ SEWAGE_      │ │ NOT_SEWAGE  │ │ SEWAGE_      │
│ CONFIRMED    │ │             │ │ CONFIRMED    │ │             │ │ CONFIRMED    │
│ (Confirmed)  │ │ (Rejected)  │ │ (Confirmed)  │ │ (Rejected)  │ │ (Confirmed)  │
└──────────────┘ └──────────────┘ └──────────────┘ └──────────────┘ └──────────────┘
        │              │              │              │              │
        └──────────────┴──────────────┴──────────────┴──────────────┘
                              │
                              ▼
                    ┌─────────────────┐
                    │   FINAL STATE   │
                    │   (Resolved)    │
                    └─────────────────┘
```

## 🎯 Where Incidents Go

### 1. **Upload Stage** (`/incidents/create`)
- User uploads image
- Status: `PENDING_AI`
- AI job dispatched automatically

### 2. **AI Analysis** (Background)
- AI analyzes image
- Status changes to:
  - `SEWAGE_CONFIRMED` (if confidence ≥ 90%)
  - `NEEDS_REVIEW` (if confidence < 90%)

### 3. **Review Stage** (`/admin/incidents/review`)
- Shows ONLY `NEEDS_REVIEW` incidents
- Admin clicks:
  - **"Confirm Sewage"** → `SEWAGE_CONFIRMED`
  - **"Not Sewage"** → `NOT_SEWAGE`

### 4. **Dashboard** (`/incidents/dashboard`)
- Shows ALL incidents with risk scores
- Sorted by risk (highest first)
- Includes all statuses

### 5. **Resolved** (`/incidents/resolved`) ⭐ NEW!
- Shows ONLY resolved incidents:
  - `SEWAGE_CONFIRMED` (confirmed sewage)
  - `NOT_SEWAGE` (false alarms)
- Final destination for completed reviews

## 📍 Final States

### ✅ SEWAGE_CONFIRMED
- **Meaning**: Confirmed as actual sewage incident
- **Action Required**: Yes - needs response/cleanup
- **Where**: 
  - Dashboard (with high priority)
  - Resolved page (completed)

### ✅ NOT_SEWAGE
- **Meaning**: Confirmed as NOT sewage (false alarm)
- **Action Required**: No - just documentation
- **Where**: 
  - Resolved page (completed)

## 🔄 Status Transitions

| From | To | Trigger |
|------|-----|---------|
| `PENDING_AI` | `NEEDS_REVIEW` | AI analysis (confidence < 90%) |
| `PENDING_AI` | `SEWAGE_CONFIRMED` | AI analysis (confidence ≥ 90%) |
| `NEEDS_REVIEW` | `SEWAGE_CONFIRMED` | Admin clicks "Confirm Sewage" |
| `NEEDS_REVIEW` | `NOT_SEWAGE` | Admin clicks "Not Sewage" |

## 📊 Views Summary

| View | URL | Shows |
|------|-----|-------|
| Upload | `/incidents/create` | Upload form |
| Review | `/admin/incidents/review` | `NEEDS_REVIEW` only |
| Dashboard | `/incidents/dashboard` | All incidents (sorted by risk) |
| Resolved | `/incidents/resolved` | `SEWAGE_CONFIRMED` + `NOT_SEWAGE` |

## 🎯 Answer: Where Do Incidents Go?

**At the end, incidents go to:**

1. **`/incidents/resolved`** - The final destination
   - Shows all completed reviews
   - Separated by: Confirmed Sewage vs Not Sewage
   - Includes statistics

2. **They stay in the database** with final status:
   - `SEWAGE_CONFIRMED` - Action needed
   - `NOT_SEWAGE` - No action needed

3. **They appear on Dashboard** but sorted by priority
   - High priority sewage incidents at top
   - Low priority/false alarms at bottom

The **Resolved** page is the archive/final view where you can see all completed incidents!
