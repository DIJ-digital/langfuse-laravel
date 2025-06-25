<?php

declare(strict_types=1);

return [
    'cache_key_prefix' => 'langfuse_laravel',
    'ttl' => 3600,
    'base_uri' => env('LANGFUSE_BASE_URI', 'https://cloud.langfuse.com'),
    'public_key' => env('LANGFUSE_PUBLIC_KEY', 'pk-lf-b11c8934-8901-411a-bd56-e1891a6d73f3'),
    'secret_key' => env('LANGFUSE_SECRET_KEY', 'sk-lf-98bca5d3-48ff-46aa-8d41-6a48ecde4605'),
];
