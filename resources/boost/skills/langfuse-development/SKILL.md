---
name: langfuse-development
description: Build and work with Langfuse Laravel features including tracing, prompts, auto-tracing middleware, and testing.
---

# Langfuse Laravel Development

## When to use this skill

Use this skill when:
- Adding LLM observability or tracing to a Laravel application
- Working with Langfuse traces, spans, or generations
- Fetching or managing prompts from Langfuse
- Setting up auto-tracing for HTTP requests, Laravel AI, or Prism
- Writing tests that involve Langfuse API calls

## Tracing

Every ingestion call sends directly to the Langfuse API. No buffering, no flushing.

```php
use DIJ\Langfuse\Laravel\Facades\Langfuse;

// Create a trace
$trace = Langfuse::ingestion()->trace(
    name: 'handle-request',
    userId: 'user-1',
    input: 'hello',
);

// Nest a span under the trace
$span = $trace->span(name: 'search');

// Nest a generation under the span
$generation = $span->generation(
    name: 'llm',
    input: 'prompt text',
    output: 'model response',
    model: 'gpt-4o',
);

// Update any object (sends immediately)
$span->update(output: 'done', endTime: date('c'));
$trace->update(output: 'final answer');
```

## Prompts

```php
use DIJ\Langfuse\Laravel\Facades\Langfuse;
use DIJ\Langfuse\PHP\Enums\PromptType;

// Fetch and compile text prompt
Langfuse::prompt()->text('promptName', fallback: 'fallback text')
    ->compile(['key' => 'value']);

// Fetch and compile chat prompt
Langfuse::prompt()->chat('chatName', fallback: [['role' => 'user', 'content' => 'fallback']])
    ->compile(['key' => 'value']);

// List all prompts (auto-paginated Generator)
foreach (Langfuse::prompt()->list() as $item) {
    echo $item->name;
}

// Create a prompt
Langfuse::prompt()->create('promptName', 'text', PromptType::TEXT);

// Update prompt labels
Langfuse::prompt()->update(promptName: 'promptName', version: 1, labels: ['production']);
```

## Auto-Tracing Middleware

The `langfuse` middleware creates a root trace per HTTP request. All AI calls within the request become children of this trace.

```php
// In routes
Route::middleware('langfuse')->group(function () {
    Route::post('/chat', ChatController::class);
});
```

The middleware captures:
- Route name (or `METHOD /path` as fallback)
- Authenticated user ID
- HTTP method and full URL

## Laravel AI Auto-Tracing

Enable via `LANGFUSE_LARAVEL_AI_ENABLED=true`. An event subscriber automatically traces:

| Event | Action |
|-------|--------|
| `PromptingAgent` / `StreamingAgent` | Creates root trace |
| `AgentPrompted` / `AgentStreamed` | Creates generation with model, usage, input/output |
| `InvokingTool` | Creates child span for tool execution |
| `ToolInvoked` | Ends span with tool result |

Events are correlated using Laravel AI's `invocationId`.

## Prism Auto-Tracing

Enable via `LANGFUSE_PRISM_ENABLED=true`. Wraps the PrismManager to intercept `text()`, `structured()`, and `stream()` calls.

Captured data:
- Input prompts and system prompts
- Model name, provider, and parameters (temperature, maxTokens, topP)
- Output text/structured response
- Errors with status messages

## Testing

Use `Langfuse::fake()` to mock all HTTP responses. Each API call consumes one response from the array.

```php
use DIJ\Langfuse\Laravel\Facades\Langfuse;
use DIJ\Langfuse\PHP\Testing\Responses\GetPromptResponse;
use GuzzleHttp\Psr7\Response;

// Mock prompt fetch
Langfuse::fake([
    new GetPromptResponse(data: [
        'name' => 'my-prompt',
        'type' => 'text',
        'prompt' => 'Hello {{name}}',
    ]),
]);

$prompt = Langfuse::prompt()->text('my-prompt');

// Mock ingestion calls (each trace/span/generation/update = 1 HTTP call)
Langfuse::fake([
    new Response(200, [], json_encode(['successes' => [], 'errors' => []])),
    new Response(200, [], json_encode(['successes' => [], 'errors' => []])),
]);

$trace = Langfuse::ingestion()->trace(name: 'test');
$trace->generation(name: 'llm', input: 'in', output: 'out');
```

Available test response helpers in `DIJ\Langfuse\PHP\Testing\Responses`:
- `GetPromptResponse`, `GetChatPromptResponse`
- `NoPromptFoundResponse` (404)
- `PostPromptResponse`, `PostChatPromptResponse`
- `PatchPromptLabelsResponse`
- `GetPromptListPageOneResponse`, `GetPromptListPageTwoResponse`

## Configuration

```php
// config/langfuse-laravel.php
return [
    'base_uri' => env('LANGFUSE_BASE_URI', 'https://cloud.langfuse.com'),
    'public_key' => env('LANGFUSE_PUBLIC_KEY'),
    'secret_key' => env('LANGFUSE_SECRET_KEY'),
    'environment' => env('LANGFUSE_ENVIRONMENT', env('APP_ENV', 'default')),
    'default_label' => env('LANGFUSE_DEFAULT_LABEL', env('APP_ENV', 'latest')),
    'connect_timeout' => (int) env('LANGFUSE_CONNECT_TIMEOUT', 5),
    'timeout' => (int) env('LANGFUSE_TIMEOUT', 10),
    'prism_enabled' => (bool) env('LANGFUSE_PRISM_ENABLED', false),
    'laravel_ai_enabled' => (bool) env('LANGFUSE_LARAVEL_AI_ENABLED', false),
];
```

## Key Classes

| Class | Purpose |
|-------|---------|
| `DIJ\Langfuse\Laravel\Facades\Langfuse` | Main facade |
| `DIJ\Langfuse\Laravel\Http\Middleware\LangfuseMiddleware` | Auto-tracing middleware |
| `DIJ\Langfuse\Laravel\Ai\LaravelAiSubscriber` | Laravel AI event subscriber |
| `DIJ\Langfuse\Laravel\Prism\TracingPrismManager` | Prism auto-tracing manager |
| `DIJ\Langfuse\Laravel\Prism\TracingProvider` | Prism provider tracing wrapper |
| `DIJ\Langfuse\Laravel\Tracing\TraceContext` | Scoped singleton holding the current trace |
