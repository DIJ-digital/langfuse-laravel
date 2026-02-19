<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Langfuse API Base URI
    |--------------------------------------------------------------------------
    |
    | The base URL for the Langfuse API. Defaults to the EU cloud instance.
    | Change this if you use the US cloud (https://us.cloud.langfuse.com)
    | or a self-hosted Langfuse instance.
    |
    */

    'base_uri' => env('LANGFUSE_BASE_URI', 'https://cloud.langfuse.com'),

    /*
    |--------------------------------------------------------------------------
    | API Keys
    |--------------------------------------------------------------------------
    |
    | Authentication credentials for the Langfuse API. These are available
    | in your Langfuse project settings under "API Keys". The public key
    | is used as the Basic Auth username, the secret key as the password.
    |
    */

    'public_key' => env('LANGFUSE_PUBLIC_KEY'),
    'secret_key' => env('LANGFUSE_SECRET_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | The environment label attached to all traces and observations.
    | This allows you to separate data from different deployment
    | contexts (e.g. production, staging, local) in Langfuse.
    |
    */

    'environment' => env('LANGFUSE_ENVIRONMENT', env('APP_ENV', 'default')),

    /*
    |--------------------------------------------------------------------------
    | Default Label
    |--------------------------------------------------------------------------
    |
    | The default label used when fetching prompts. This allows you to
    | separate prompts for different environments (e.g. production,
    | acceptance, local). Defaults to the application environment.
    |
    */

    'default_label' => env('LANGFUSE_DEFAULT_LABEL', env('APP_ENV', 'latest')),

    /*
    |--------------------------------------------------------------------------
    | HTTP Timeouts
    |--------------------------------------------------------------------------
    |
    | Timeouts (in seconds) for the Guzzle HTTP client used to communicate
    | with the Langfuse API. Increase these if you experience timeout
    | issues on slow networks.
    |
    */

    'connect_timeout' => (int) env('LANGFUSE_CONNECT_TIMEOUT', 5),
    'timeout' => (int) env('LANGFUSE_TIMEOUT', 10),
];
