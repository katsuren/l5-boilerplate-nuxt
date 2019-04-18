<?php

return [

    'supportsCredentials' => true,
    'allowedOrigins' => [],
    'allowedOriginsPatterns' => explode(',', env('CORS_ORIGINS_PATTERNS', '*')),
    'allowedHeaders' => ['*'],
    'allowedMethods' => ['HEAD', 'OPTIONS', 'GET', 'POST', 'PUT', 'DELETE'],
    'exposedHeaders' => [],
    'maxAge' => 0,

];
