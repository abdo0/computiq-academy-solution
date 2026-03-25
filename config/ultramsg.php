<?php

return [
    /*
    |--------------------------------------------------------------------------
    | UltraMsg WhatsApp API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for UltraMsg WhatsApp API integration
    |
    */

    'base_url' => env('ULTRAMSG_BASE_URL', 'https://api.ultramsg.com'),

    // Default instance for WhatsApp messaging
    'default_instance_id' => env('ULTRAMSG_DEFAULT_INSTANCE_ID'),
    'default_token' => env('ULTRAMSG_DEFAULT_TOKEN'),

    // API settings
    'timeout' => env('ULTRAMSG_TIMEOUT', 30),
    'retry_attempts' => env('ULTRAMSG_RETRY_ATTEMPTS', 3),

    // Message settings
    'default_country_code' => env('ULTRAMSG_DEFAULT_COUNTRY_CODE', '+964'),

    // Test mode settings (for safety with real data)
    'test_mode' => env('ULTRAMSG_TEST_MODE', true),
    'test_phone_number' => env('ULTRAMSG_TEST_PHONE_NUMBER'),

    // Logging
    'log_requests' => env('ULTRAMSG_LOG_REQUESTS', true),
    'log_responses' => env('ULTRAMSG_LOG_RESPONSES', true),
    'default_priority' => env('ULTRAMSG_DEFAULT_PRIORITY', 10),
];
