<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sewage Sentinel Detection Thresholds
    |--------------------------------------------------------------------------
    |
    | Configure thresholds for the Sewage Sentinel AI pipeline.
    | Adjust these values to fine-tune detection sensitivity.
    |
    */

    'thresholds' => [
        // Person detection confidence threshold
        'person_detected_min' => 0.50, // Minimum confidence to consider person detected

        // Sewage score thresholds
        'sewage_high' => 0.85,      // Above this → NEEDS_REVIEW
        'sewage_low' => 0.30,       // Below this → NOT_SEWAGE
        // Between low and high → NEEDS_REVIEW

        // AI-generated detection threshold
        'ai_generated_min' => 0.80, // Above this → mark as AI-generated

        // Confidence scores for different labels
        'confidence' => [
            'not_sewage_person' => 0.95,  // When person detected
            'not_sewage_low_score' => 0.70, // When sewage_score is low
            'needs_review_high' => 0.85,  // When sewage_score is high
            'needs_review_medium' => 0.60, // When sewage_score is medium
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Vision API Configuration
    |--------------------------------------------------------------------------
    */
    'vision' => [
        'provider' => env('VISION_PROVIDER', 'google'), // 'google' or 'aws' or 'openai'
        'timeout' => 30, // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Winston AI Configuration
    |--------------------------------------------------------------------------
    */
    'winston' => [
        'enabled' => env('WINSTON_ENABLED', true),
        'timeout' => 20, // seconds
        'skip_if_person_detected' => true, // Skip Winston check if person already detected
    ],

    /*
    |--------------------------------------------------------------------------
    | Sewage Detection Keywords
    |--------------------------------------------------------------------------
    | Keywords that indicate sewage-related content when detected by Vision API
    */
    'sewage_keywords' => [
        'sewage',
        'sewer',
        'wastewater',
        'drain',
        'manhole',
        'pipe',
        'overflow',
        'septic',
        'waste',
        'contamination',
        'sewerage',
    ],

    /*
    |--------------------------------------------------------------------------
    | Person Detection Labels
    |--------------------------------------------------------------------------
    | Labels from Vision API that indicate a person/face
    */
    'person_labels' => [
        'person',
        'face',
        'portrait',
        'selfie',
        'headshot',
        'human',
        'people',
        'man',
        'woman',
        'child',
    ],
];
