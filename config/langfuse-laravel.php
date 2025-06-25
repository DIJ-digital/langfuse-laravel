<?php

declare(strict_types=1);

return [
    'base_uri' => env('LANGFUSE_BASE_URI', 'https://cloud.langfuse.com'),
    'public_key' => env('LANGFUSE_PUBLIC_KEY'),
    'secret_key' => env('LANGFUSE_SECRET_KEY'),
];
