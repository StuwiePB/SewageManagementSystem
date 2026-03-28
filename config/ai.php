<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Detection Thresholds
    |--------------------------------------------------------------------------
    |
    | Configure thresholds for drainage-relevance detection and classification.
    |
    */

    'thresholds' => [
        // Person detection confidence thresholds
        'person_detected_min' => 0.70, // Minimum confidence for face detection (raised from 0.50)
        'person_label_min' => 0.70,    // Minimum confidence for label-based person detection (reliable labels)
        'person_label_min_less_reliable' => 0.85, // Minimum confidence for less reliable labels (human, people, etc.)

        // Drainage relevance score thresholds
        'sewage_high' => 0.85,      // Above this → NEEDS_REVIEW (likely drainage-related)
        'sewage_low' => 0.30,       // Below this → NOT_SEWAGE (not drainage-related)
        // Between low and high → NEEDS_REVIEW

        // AI-generated detection threshold
        'ai_generated_min' => 0.80, // Above this → mark as AI-generated

        // Confidence scores for different labels
        'confidence' => [
            'not_sewage_person' => 0.95,      // When person detected
            'not_sewage_low_score' => 0.70,   // When sewage_score is low
            'needs_review_high' => 0.85,      // When sewage_score is high
            'needs_review_medium' => 0.60,     // When sewage_score is medium
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Drainage-Relevance Keywords
    |--------------------------------------------------------------------------
    | Keywords that indicate drainage-related content when detected by Vision API
    */
    'sewage_keywords' => [
        'sewage',
        'wastewater',
        'sewer',
        'drain',
        'pipe',
        'overflow',
        'flood',
        'dirty water',
        'sludge',
        'manhole',
        'leak',
        'effluent',
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
];
