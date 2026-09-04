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

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'storage/*'],

    'allowed_methods' => ['*'],

    // Default lokal + domain production dari env CORS_ALLOWED_ORIGINS
    // (pisah koma). Contoh: https://fe-project-management.vercel.app
    'allowed_origins' => array_values(array_filter(array_merge([
        'http://localhost:3000',
        'http://127.0.0.1:3000',
    ], array_map('trim', explode(',', (string) env('CORS_ALLOWED_ORIGINS', '')))))),

    'allowed_origins_patterns' => [
        '#^http://(localhost|127\.0\.0\.1)(:\d+)?$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 86400,

    'supports_credentials' => false,

];
