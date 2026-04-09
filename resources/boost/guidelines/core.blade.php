## Langfuse Laravel

This package provides a Laravel integration for [Langfuse](https://langfuse.com), the open-source LLM observability platform. It wraps the `dij-digital/langfuse-php` SDK and adds Laravel-specific features like auto-tracing middleware, event subscribers, and a facade.

### Features

- **Tracing** - Create traces, spans, and generations that send directly to the Langfuse API.
- **Prompts** - Fetch, compile, and create text and chat prompts with fallback support.
- **Auto-tracing middleware** - Automatically create a root trace per HTTP request.
- **Laravel AI auto-tracing** - Automatic tracing for Laravel AI agent and tool events.
- **Prism auto-tracing** - Automatic tracing for Prism AI provider calls.
- **Testing** - Facade with `fake()` method for mocking all HTTP responses in tests.

### Configuration

Environment variables:

```dotenv
LANGFUSE_BASE_URI=https://cloud.langfuse.com
LANGFUSE_PUBLIC_KEY=
LANGFUSE_SECRET_KEY=
LANGFUSE_PRISM_ENABLED=false
LANGFUSE_LARAVEL_AI_ENABLED=false
```

### Usage

@verbatim
<code-snippet name="Create a trace with nested generation" lang="php">
use DIJ\Langfuse\Laravel\Facades\Langfuse;

$trace = Langfuse::ingestion()->trace(name: 'handle-request', userId: 'user-1', input: 'hello');
$generation = $trace->generation(
    name: 'llm',
    input: 'prompt',
    output: 'response',
    model: 'gpt-4o',
);
</code-snippet>
@endverbatim

@verbatim
<code-snippet name="Fetch and compile a prompt" lang="php">
use DIJ\Langfuse\Laravel\Facades\Langfuse;

$compiled = Langfuse::prompt()->text('my-prompt')->compile(['name' => 'World']);
</code-snippet>
@endverbatim

@verbatim
<code-snippet name="Apply auto-tracing middleware to routes" lang="php">
Route::middleware('langfuse')->group(function () {
    // All AI calls within these routes are automatically traced
});
</code-snippet>
@endverbatim

@verbatim
<code-snippet name="Testing with fake responses" lang="php">
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
</code-snippet>
@endverbatim

### Conventions

- Always use the `Langfuse` facade for accessing prompts, ingestion, and scores.
- Use `Langfuse::fake([...])` in tests to mock HTTP responses. Responses are consumed sequentially.
- Enable auto-tracing via environment variables, not by modifying the service provider.
- The `langfuse` middleware alias is registered automatically by the service provider.
