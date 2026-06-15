<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OpenAPI / Swagger Info
    |--------------------------------------------------------------------------
    */
    'openapi' => [
        'title'           => env('APP_NAME', 'Laravel') . ' API',
        'version'         => '1.0.0',
        'server_url'      => '/api',
        'security_scheme' => 'sanctum',
    ],

    /*
    |--------------------------------------------------------------------------
    | Controller Generator Defaults
    |--------------------------------------------------------------------------
    */
    'generator' => [
        'namespace' => 'App\\Http\\Controllers\\Api',
        'path'      => 'app/Http/Controllers/Api',
    ],
];
