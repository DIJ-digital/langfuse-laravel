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
