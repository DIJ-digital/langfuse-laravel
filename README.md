## Langfuse Laravel - A Laravel Facade for the PHP Langfuse API package.

This package provides a wrapper around the [langfuse-php](https://github.com/DIJ-digital/langfuse-php) package, allowing you to easily integrate Langfuse into your Laravel applications. It uses as few dependencies as possible.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dij-digital/langfuse-laravel.svg?style=flat-square)](https://packagist.org/packages/dij-digital/langfuse-laravel)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/dij-digital/langfuse-laravel/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/dij-digital/langfuse-laravel/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/dij-digital/langfuse-laravel/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/dij-digital/langfuse-laravel/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/dij-digital/langfuse-laravel.svg?style=flat-square)](https://packagist.org/packages/dij-digital/langfuse-laravel)

### Features

- **Tracing** - Create traces, spans and generations with a buffer-then-flush approach
- **Prompts** - Fetch, compile, create, list and update prompts with fallback support
- Automatic flush via a `terminating` callback in the service provider
- Singleton `Ingestion` instance per request (via `LangfuseDecorator`)
- Facade with `fake()` for testing

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

# Optional - defaults to config('app.name') and config('app.env')
LANGFUSE_SERVICE_NAME=
LANGFUSE_ENVIRONMENT=
```

Publish the config file (optional):

```bash
php artisan vendor:publish --tag=langfuse-laravel-config
```

### Usage

#### Tracing

Everything is buffered in memory until `flush()` is called. The package automatically flushes at the end of each request via a `terminating` callback registered in the service provider.

```php
use DIJ\Langfuse\Laravel\Facades\Langfuse;

// Create a trace — returns a Trace object
$trace = Langfuse::ingestion()->trace(name: 'handle-request', userId: 'user-1', input: 'hello');

// Nest spans and generations under the trace
$span = $trace->span(name: 'search');
$generation = $span->generation(
    input: 'prompt',
    output: 'response',
    name: 'llm',
    model: 'gpt-4o',
);

// Update any object in memory
$trace->update(output: 'done');

// Flush happens automatically at the end of the request.
// To flush manually:
Langfuse::flush();
```

The `Ingestion` instance is a singleton per request — calling `Langfuse::ingestion()` multiple times returns the same instance, so all spans and traces are flushed together.

#### Prompts

```php
use DIJ\Langfuse\Laravel\Facades\Langfuse;

// Get and compile prompts
Langfuse::prompt()->text('promptName', fallback: 'fallback text')->compile(['key' => 'value']);
Langfuse::prompt()->chat('chatName', fallback: [['role' => 'user', 'content' => 'fallback']])->compile(['key' => 'value']);

// List all prompts (auto-paginated Generator)
foreach (Langfuse::prompt()->list() as $item) {
    echo $item->name;
}

// Create a prompt
Langfuse::prompt()->create('promptName', 'text', PromptType::TEXT);

// Update prompt labels
Langfuse::prompt()->update(promptName: 'promptName', version: 1, labels: ['production']);
```

#### Testing

Use the `fake()` method to mock HTTP responses. The `langfuse-php` package ships with testing response helpers that provide sensible defaults — just override the fields you care about:

```php
use DIJ\Langfuse\Laravel\Facades\Langfuse;
use DIJ\Langfuse\PHP\Testing\Responses\GetPromptResponse;

Langfuse::fake([
    new GetPromptResponse(data: [
        'name' => 'my-prompt',
        'type' => 'text',
        'prompt' => 'Hello {{name}}',
    ]),
]);

$prompt = Langfuse::prompt()->text('my-prompt');
```

Available test response helpers in `DIJ\Langfuse\PHP\Testing\Responses`:

- `GetPromptResponse` — text prompt fetch
- `GetChatPromptResponse` — chat prompt fetch
- `NoPromptFoundResponse` — 404 prompt not found
- `PostPromptResponse` — text prompt creation
- `PostChatPromptResponse` — chat prompt creation
- `PatchPromptLabelsResponse` — prompt label update
- `GetPromptListPageOneResponse` — first page of prompt list
- `GetPromptListPageTwoResponse` — second page of prompt list

Responses are consumed sequentially (Guzzle `MockHandler`), so the order of responses must match the order of HTTP calls your code makes.

### Development

```bash
composer test        # Run tests
composer codestyle   # Format, refactor and analyse
```

**Langfuse Laravel** was created by **[Tycho Engberink](https://dij.digital)** under the **[MIT license](https://opensource.org/licenses/MIT)**.
