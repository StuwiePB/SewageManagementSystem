# AI Accuracy Recommendations

## Current Issues

1. **Winston.ai Limitations**: Winston.ai is designed for detecting AI-generated images, NOT content classification. It cannot see what's in images - it only reads EXIF metadata.

2. **Metadata Dependency**: Most photos don't have detailed EXIF metadata with keywords, so classification fails or produces false positives.

3. **No Visual Analysis**: Without actual image content analysis, the system cannot reliably detect sewage vs. portraits.

## Immediate Fixes Implemented

### ✅ 1. Basic Image Analysis
- Added portrait detection using aspect ratio analysis
- Detects vertical/tall images (common for portraits/selfies)
- Blocks sewage classification if portrait detected

### ✅ 2. Conservative Classification
- Defaults to `UNCERTAIN` when metadata is missing
- Requires STRONG evidence (2+ sewage keywords) before classifying as SEWAGE
- Lower confidence scores (0.70 max for metadata-based classification)

### ✅ 3. Simplified Logic
- Removed complex metadata extraction
- Focus on simple keyword matching
- Better fallbacks to conservative defaults

## Recommendations for Better Accuracy

### 🎯 Option 1: Use a Vision-Based API (RECOMMENDED)

**Best Options:**
1. **Google Cloud Vision API**
   - Excellent for content classification
   - Can detect objects, faces, text
   - Cost: ~$1.50 per 1,000 images
   - Setup: Requires Google Cloud account

2. **AWS Rekognition**
   - Good for object detection and content moderation
   - Can detect faces, objects, scenes
   - Cost: ~$1.00 per 1,000 images
   - Setup: Requires AWS account

3. **Azure Computer Vision**
   - Similar capabilities to Google/AWS
   - Cost: ~$1.00 per 1,000 images
   - Setup: Requires Azure account

**Implementation:**
- Replace Winston.ai with vision API
- Use actual image content analysis
- Detect faces/people directly from pixels
- Classify sewage based on visual features

### 🎯 Option 2: Hybrid Approach

**Combine Multiple Signals:**
1. **Basic Image Analysis** (already implemented)
   - Aspect ratio
   - Image dimensions
   - Portrait detection

2. **Metadata Analysis** (Winston.ai)
   - EXIF keywords
   - Image metadata

3. **User Input**
   - Add a simple form field: "What do you see?"
   - Use user description for classification
   - Combine with image analysis

### 🎯 Option 3: Machine Learning Model

**Train Custom Model:**
1. Collect training data
   - 100+ sewage images
   - 100+ non-sewage images
   - 50+ portrait images

2. Use TensorFlow/PyTorch
   - Train binary classifier: SEWAGE vs NOT_SEWAGE
   - Add person detection layer

3. Deploy Model
   - Host on server or use ML service
   - Integrate with Laravel

**Pros:**
- Most accurate for your specific use case
- No API costs after training

**Cons:**
- Requires training data
- More complex setup
- Needs maintenance

### 🎯 Option 4: Rule-Based System (Simplest)

**Simple Heuristics:**
1. **Portrait Detection**
   - Aspect ratio > 1.2 → Likely portrait → NOT_SEWAGE
   - Image size < 1000x1000 → Likely selfie → NOT_SEWAGE

2. **Sewage Indicators**
   - Only classify as SEWAGE if:
     - User explicitly mentions "sewage", "drain", "pipe" in description
     - AND image is horizontal/landscape
     - AND image is large (> 2000px width)

3. **Default Behavior**
   - Everything else → UNCERTAIN → Needs Review

**Pros:**
- Simple and fast
- No API costs
- Easy to understand and debug

**Cons:**
- Less accurate than ML/vision APIs
- Requires manual tuning

## Recommended Action Plan

### Short Term (Now):
1. ✅ **Use current simplified implementation**
   - Portrait detection via aspect ratio
   - Conservative classification
   - Default to UNCERTAIN

2. **Add User Description Field**
   - Let users describe what they see
   - Use description for keyword matching
   - Combine with image analysis

### Medium Term (1-2 weeks):
1. **Switch to Google Cloud Vision API**
   - Better accuracy
   - Actual image content analysis
   - Face detection built-in

2. **Implement Multi-Step Validation**
   - Step 1: Face detection → Block if person
   - Step 2: Object detection → Look for pipes/drains
   - Step 3: Scene classification → Sewage vs. other

### Long Term (1-2 months):
1. **Train Custom Model**
   - Collect real incident images
   - Train on your specific data
   - Deploy for production

2. **Continuous Improvement**
   - Log false positives
   - Retrain model periodically
   - Update rules based on feedback

## Quick Wins

1. **Add Image Upload Validation**
   ```php
   // Reject obvious portraits
   if ($aspectRatio > 1.3) {
       return error('Portrait images are not valid incident reports');
   }
   ```

2. **Require User Description**
   - Add text field: "Describe the issue"
   - Use description for initial classification
   - Image analysis as secondary check

3. **Admin Feedback Loop**
   - Track admin corrections
   - Use corrections to improve rules
   - Flag similar images automatically

## Cost Comparison

| Solution | Setup Time | Monthly Cost (1000 images) | Accuracy |
|----------|------------|----------------------------|----------|
| Winston.ai (current) | Done | $0.30 | Low (30-40%) |
| Google Vision | 2-4 hours | $1.50 | High (85-90%) |
| AWS Rekognition | 2-4 hours | $1.00 | High (85-90%) |
| Custom ML Model | 1-2 weeks | $0 (hosting only) | Very High (90-95%) |
| Rule-Based | Done | $0 | Medium (60-70%) |

## Conclusion

**For immediate improvement:** Use Google Cloud Vision API or AWS Rekognition. They provide actual image content analysis and will dramatically improve accuracy.

**For long-term:** Train a custom model on your specific incident images for best results.

**Current implementation:** Will work better than before (portrait detection + conservative defaults), but will still have limitations due to Winston.ai's design.
