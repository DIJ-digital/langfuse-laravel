<?php

declare(strict_types=1);

return [
    'base_uri' => env('LANGFUSE_BASE_URI', 'https://cloud.langfuse.com'),
    'public_key' => env('LANGFUSE_PUBLIC_KEY'),
    'secret_key' => env('LANGFUSE_SECRET_KEY'),

    'service_name' => env('LANGFUSE_SERVICE_NAME', env('APP_NAME', 'laravel')),
    'environment' => env('LANGFUSE_ENVIRONMENT', env('APP_ENV', 'default')),

    /*
    |--------------------------------------------------------------------------
    | Telemetry Dispatch
    |--------------------------------------------------------------------------
    |
    | When async is true, telemetry payloads are dispatched to a queue job
    | instead of being sent synchronously. This prevents Langfuse latency
    | from blocking your queue workers.
    |
    */

    'async' => env('LANGFUSE_ASYNC', true),
    'queue' => env('LANGFUSE_QUEUE', 'telemetry'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Timeouts
    |--------------------------------------------------------------------------
    |
    | Timeouts (in seconds) for the Guzzle HTTP client used to communicate
    | with the Langfuse API.
    |
    */

    'connect_timeout' => (int) env('LANGFUSE_CONNECT_TIMEOUT', 5),
    'timeout' => (int) env('LANGFUSE_TIMEOUT', 10),
];
