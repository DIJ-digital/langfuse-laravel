<?php

declare(strict_types=1);

return [
    'base_uri' => env('LANGFUSE_BASE_URI', 'https://cloud.langfuse.com'),
    'public_key' => env('LANGFUSE_PUBLIC_KEY'),
    'secret_key' => env('LANGFUSE_SECRET_KEY'),

    'service_name' => env('LANGFUSE_SERVICE_NAME', env('APP_NAME', 'laravel')),
    'environment' => env('LANGFUSE_ENVIRONMENT', env('APP_ENV', 'default')),
];
