<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the rate limiting configuration for the document
    | generation system. These limits help prevent abuse and ensure
    | system stability.
    |
    */

    'document_generation' => [
        'max_attempts' => env('RATE_LIMIT_DOCUMENT_GENERATION', 10),
        'decay_minutes' => 1,
    ],

    'document_download' => [
        'max_attempts' => env('RATE_LIMIT_DOCUMENT_DOWNLOAD', 30),
        'decay_minutes' => 1,
    ],

    'api_requests' => [
        'max_attempts' => env('RATE_LIMIT_API', 60),
        'decay_minutes' => 1,
    ],

    'preview_generation' => [
        'max_attempts' => env('RATE_LIMIT_PREVIEW', 20),
        'decay_minutes' => 1,
    ],
];