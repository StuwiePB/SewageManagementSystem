<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

<<<<<<< HEAD
=======
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    ],

    'ziqah' => [
        'database_schema' => env('ZIQAH_AI_DATABASE_SCHEMA', true),
        'database_tools' => env('ZIQAH_AI_DATABASE_TOOLS', true),
        'max_tool_rounds' => (int) env('ZIQAH_AI_DB_MAX_ROUNDS', 4),
        'max_select_rows' => (int) env('ZIQAH_AI_DB_MAX_ROWS', 50),
        'location_catalog' => env('ZIQAH_AI_LOCATION_CATALOG', true),
        'location_catalog_per_source' => (int) env('ZIQAH_AI_LOCATION_PER_SOURCE', 60),
        'location_catalog_max_chars' => (int) env('ZIQAH_AI_LOCATION_MAX_CHARS', 14000),
        'nearest_issues' => env('ZIQAH_AI_NEAREST_ISSUES', true),
        'nearest_issues_limit' => (int) env('ZIQAH_AI_NEAREST_ISSUES_LIMIT', 8),
        'nearest_issues_candidates_per_table' => (int) env('ZIQAH_AI_NEAREST_ISSUES_CANDIDATES', 200),
    ],

>>>>>>> 3wayfusionn-(use-this)
];
