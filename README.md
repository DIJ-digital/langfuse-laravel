## Langfuse Laravel - A Laravel Facade for the PHP Langfuse API package.

This package provides a wrapper around the [langfuse-php](https://github.com/DIJ-digital/langfuse-php) package, allowing you to easily integrate Langfuse into your Laravel applications. It uses as few dependencies as possible.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dij-digital/langfuse-laravel.svg?style=flat-square)](https://packagist.org/packages/dij-digital/langfuse-laravel)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/dij-digital/langfuse-laravel/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/dij-digital/langfuse-laravel/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/dij-digital/langfuse-laravel/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/dij-digital/langfuse-laravel/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/dij-digital/langfuse-laravel.svg?style=flat-square)](https://packagist.org/packages/dij-digital/langfuse-laravel)

### Features

- **Tracing** - Create traces, spans and generations that send directly to the Langfuse API
- **Prompts** - Fetch, compile and create text and chat prompts with fallback support
- Facade with direct access to `prompt()` and `ingestion()` methods

> **Requires [PHP 8.3](https://php.net/releases/) or [PHP 8.4](https://php.net/releases/) in combination with [Laravel 11](https://laravel.com/docs/11.x) or [Laravel 12](https://laravel.com/docs/12.x)**

### Installation

```bash
composer require dij-digital/langfuse-laravel
```

### Configuration

Add the following environment variables to your `.env` file:

```dotenv
LANGFUSE_BASE_URI=https://cloud.langfuse.com
LANGFUSE_PUBLIC_KEY=
LANGFUSE_SECRET_KEY=

# Optional - defaults to config('app.env')
LANGFUSE_ENVIRONMENT=
```

Publish the config file (optional):

```bash
php artisan vendor:publish --tag=langfuse-laravel-config
```

### Usage

#### Tracing

Every call sends directly to the Langfuse API. No buffering, no flushing.

```php
use DIJ\Langfuse\Laravel\Facades\Langfuse;

// Create a trace
$trace = Langfuse::ingestion()->trace(name: 'handle-request', userId: 'user-1', input: 'hello');

// Nest spans and generations under the trace
$span = $trace->span(name: 'search');
$generation = $span->generation(
    input: 'prompt',
    output: 'response',
    name: 'llm',
    model: 'gpt-4o',
);

// Update any object (sends immediately)
$span->update(output: 'done', endTime: date('c'));
$trace->update(output: 'final answer');
```

#### Prompts

```php
use DIJ\Langfuse\Laravel\Facades\Langfuse;

Langfuse::prompt()->text('promptName', 'fallback')->compile(['key' => 'value']);
Langfuse::prompt()->chat('chatName', ['role' => 'user', 'content' => 'fallback'])->compile(['key' => 'value']);
Langfuse::prompt()->list();
Langfuse::prompt()->create('promptName', 'text', PromptType::TEXT);
```

#### Testing

Use the `fake()` method to mock HTTP responses:

```php
use DIJ\Langfuse\Laravel\Facades\Langfuse;

Langfuse::fake([
    new GetPromptResponse(data: [
        'name' => 'my-prompt',
        'type' => 'text',
        'prompt' => 'Hello {{name}}',
    ]),
]);

$prompt = Langfuse::prompt()->text('my-prompt');
```

### Development

```bash
composer test        # Run tests
composer codestyle   # Format, refactor and analyse
```

**Langfuse Laravel** was created by **[Tycho Engberink](https://dij.digital)** under the **[MIT license](https://opensource.org/licenses/MIT)**.
