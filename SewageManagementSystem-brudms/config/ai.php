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
        'person_detected_min' => 0.70, // Minimum confidence for face detection
        'person_label_min' => 0.70,    // Minimum confidence for label-based person detection (reliable labels)
        'person_label_min_less_reliable' => 0.85, // Minimum confidence for less reliable labels (human, people, etc.)
        'person_override_confidence' => 0.88, // Strong person detection still does not hard-reject; used for ambiguity handling
        'person_penalty_base' => 0.35, // How much person signals reduce drainage score

        // Drainage relevance score thresholds
        'sewage_high' => 0.70,      // Above this → SEWAGE
        'sewage_low' => 0.28,       // Below this (with no positives) → NOT_SEWAGE
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
        'flooded',
        'waterlogging',
        'dirty water',
        'sludge',
        'manhole',
        'gutter',
        'culvert',
        'ditch',
        'storm drain',
        'drainage',
        'waste pipe',
        'leak',
        'effluent',
    ],

    /*
    |--------------------------------------------------------------------------
    | Non-drainage Context Keywords
    |--------------------------------------------------------------------------
    | Signals commonly associated with unrelated photos (people/portraits/indoors)
    */
    'non_sewage_keywords' => [
        'selfie',
        'portrait',
        'person',
        'people',
        'face',
        'human',
        'indoor',
        'room',
        'furniture',
        'clothing',
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Source Weights
    |--------------------------------------------------------------------------
    */
    'weights' => [
        'labels' => 1.0,
        'objects' => 0.9,
        'web_entities' => 0.7,
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
