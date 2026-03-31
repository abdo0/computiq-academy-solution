<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ZainCash Payment Gateway v2
    |--------------------------------------------------------------------------
    | New API: OAuth2 client_credentials flow
    | Test base URL: https://pg-api-uat.zaincash.iq
    */

    // OAuth2 credentials (provided by ZainCash)
    'client_id' => env('ZAINCASH_CLIENT_ID', '758055f4a8044779a35f6ceb69f858b3'),
    'client_secret' => env('ZAINCASH_CLIENT_SECRET', 'bibLCGTxVAig5To3OLLKPJQMlRR7Pefp'),

    // API base URL (UAT/test or production)
    'base_url' => env('ZAINCASH_BASE_URL', 'https://pg-api-uat.zaincash.iq'),

    // Your merchant phone number (MSISDN)
    'msisdn' => env('ZAINCASH_MSISDN', '9647829744545'),

    // Service type identifier (provided by ZainCash during onboarding, e.g. "JAWS")
    'service_type' => env('ZAINCASH_SERVICE_TYPE', 'Course Checkout'),

    // Currency (must be IQD)
    'currency' => env('ZAINCASH_CURRENCY', 'IQD'),

    // Language: en, ar, or ku
    'language' => env('ZAINCASH_LANGUAGE', 'en'),
];
