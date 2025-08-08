## Langfuse Laravel - A Laravel Facade for the PHP Langfuse API package.
This package provides a wrapper around the [langfuse-php](https://github.com/DIJ-digital/langfuse-php) package, allowing you to easily integrate Langfuse into your Laravel applications. It uses as few dependencies as possible.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dij-digital/langfuse-laravel.svg?style=flat-square)](https://packagist.org/packages/dij-digital/langfuse-laravel)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/dij-digital/langfuse-laravel/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/dij-digital/langfuse-laravel/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/dij-digital/langfuse-laravel/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/dij-digital/langfuse-laravel/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/dij-digital/langfuse-laravel.svg?style=flat-square)](https://packagist.org/packages/dij-digital/langfuse-laravel)

### This package supports the following features:

#### Prompts
- Get text prompts
- Get chat prompts
- Compile text prompts
- Compile chat prompts
- Create text prompts
- Create chat prompts
- Fallback handling for prompt fetching errors
- Fallback handling when no prompt is found

#### Ingestion
- Create traces
- Create generations

> **Requires [PHP 8.3](https://php.net/releases/) or [PHP 8.4](https://php.net/releases/) in combination with [Laravel 11](https://laravel.com/docs/11.x) or [Laravel 12](https://laravel.com/docs/12.x)**

⚡️ Install the package using **Composer**:
```bash  
composer require dij-digital/langfuse-laravel  
```  

### How to use this package

#### Prompt
Add the following env keys to your projects `.env` file:
```dotenv
LANGFUSE_BASE_URI=https://cloud.langfuse.com
LANGFUSE_PUBLIC_KEY=
LANGFUSE_SECRET_KEY=
```

```php
use DIJ\Langfuse\Laravel\Facades\Langfuse;

Langfuse::prompt()->text('promptName', 'fallback')->compile(['key' => 'value']);
Langfuse::prompt()->text('promptName', 'fallback')->compile(['key' => 'value']);
Langfuse::prompt()->chat('chatName', ['role' => 'user', 'content' => 'fallback'])->compile(['key' => 'value']);
Langfuse::prompt()->list();
Langfuse::prompt()->create('promptName', 'text', PromptType::TEXT);
```
#### Ingestion
```php
use DIJ\Langfuse\Laravel\Facades\Langfuse;

// Creates a trace and a generation visible in Langfuse UI
$traceId = 'trace-id-123';

Langfuse::ingestion()->trace(
    input: 'prompt text',
    output: null,
    traceId: $traceId,
    name: 'name',
    sessionId: null,
    metadata: ['key' => 'value']
);

Langfuse::ingestion()->generation(
    input: 'prompt text',
    output: 'model output',
    traceId: $traceId,
    name: 'name',
    sessionId: null,
    promptName: 'promptName',
    promptVersion: 1,
    model: 'gpt-4o',
    modelParameters: ['temperature' => 0.7],
    metadata: ['key' => 'value']
);
```

**Langfuse Laravel** was created by **[Tycho Engberink](https://github.com/tychoengberinkDIJ)** and is maintained by **[DIJ Digital](https://dij.digital)** under the **[MIT license](https://opensource.org/licenses/MIT)**.
