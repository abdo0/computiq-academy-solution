<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'storage/*',  // Important: Allow CORS for storage files
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        // Production domains
        'https://dws-hr-port.com',
        'https://test.dws-hr-port.com',

        // Local domains
        'http://hr-system.test',
        'https://hr-system.test',
        'http://test.hr-system.test',
        'https://test.hr-system.test',

        'http://localhost:3000',
        'http://localhost:5173',
        'http://127.0.0.1:8000',
        'https://nakhwaa.test',
        'http://computiq-academy.test',
        'https://computiq-academy.test',
    ],

    'allowed_origins_patterns' => [
        '/^https?:\/\/.*\.dws-hr-port\.com$/',     // All production subdomains
        '/^https?:\/\/.*\.hr-system\.test$/',     // All local subdomains
        '/^https?:\/\/dws-hr-port\.com$/',        // Main production domain
        '/^https?:\/\/hr-system\.test$/',         // Main local domain
        '/^https?:\/\/.*\.computiq-academy\.test$/', // All local computiq subdomains
        '/^https?:\/\/computiq-academy\.test$/',     // Main local computiq domain
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 3600,

    'supports_credentials' => true,
];
