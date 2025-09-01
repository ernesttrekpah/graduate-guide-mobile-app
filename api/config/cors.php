<?php

return [
    'paths'                    => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods'          => ['*'],
    'allowed_origins'          => explode(',', env('CORS_ALLOWED_ORIGINS', '*')),
    'allowed_origins_patterns' => [],
    'allowed_headers'          => ['*'],
    'exposed_headers'          => [],
    'max_age'                  => 0,
    'supports_credentials'     => false, // mobile uses Bearer tokens, not cookies

    'version'                  => env('APP_VERSION', 'dev'),
    'commit'                   => env('APP_COMMIT', null),
    'built_at'                 => env('APP_BUILT_AT', null),
];
