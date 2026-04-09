<?php

declare(strict_types=1);

use DIJ\Langfuse\Laravel\Ai\LaravelAiSubscriber;
use DIJ\Langfuse\Laravel\Facades\Langfuse;
use DIJ\Langfuse\Laravel\Tracing\TraceContext;
use DIJ\Langfuse\PHP\Ingestion\Trace;
use GuzzleHttp\Psr7\Response;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Providers\TextProvider;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Events\AgentPrompted;
use Laravel\Ai\Events\AgentStreamed;
use Laravel\Ai\Events\InvokingTool;
use Laravel\Ai\Events\PromptingAgent;
use Laravel\Ai\Events\StreamingAgent;
use Laravel\Ai\Events\ToolInvoked;
use Laravel\Ai\Prompts\AgentPrompt;
use Laravel\Ai\Responses\AgentResponse;
use Laravel\Ai\Responses\Data\Meta;
use Laravel\Ai\Responses\Data\Usage;
use Laravel\Ai\Responses\StreamedAgentResponse;

function aiIngestionResponse(): Response
{
    return new Response(200, [], json_encode(['successes' => [], 'errors' => []]));
}

function makeTestAgent(): Agent
{
    return new class implements Agent
    {
    };
}

function makeTestProvider(): TextProvider
{
    return new class implements TextProvider
    {
    };
}

function makeTestTool(): Tool
{
    return new class implements Tool
    {
    };
}

function makeAgentPrompt(string $model = 'gpt-4', ?Agent $agent = null): AgentPrompt
{
    return new AgentPrompt(
        agent: $agent ?? makeTestAgent(),
        prompt: 'Tell me a joke',
        provider: makeTestProvider(),
        model: $model,
    );
}

function makeAgentResponse(
    string $invocationId = 'inv-1',
    string $text = 'Hello world',
    int $promptTokens = 10,
    int $completionTokens = 20,
    ?string $model = 'gpt-4',
    ?string $provider = 'openai',
): AgentResponse {
    return new AgentResponse(
        invocationId: $invocationId,
        text: $text,
        usage: new Usage(promptTokens: $promptTokens, completionTokens: $completionTokens),
        meta: new Meta(provider: $provider, model: $model),
    );
}

it('registers correct event mappings in subscribe', function (): void {
    Langfuse::fake([]);

    $subscriber = app(LaravelAiSubscriber::class);
    $result = $subscriber->subscribe();

    expect($result)->toBe([
        PromptingAgent::class => 'handlePromptingAgent',
        StreamingAgent::class => 'handlePromptingAgent',
        AgentPrompted::class => 'handleAgentPrompted',
        AgentStreamed::class => 'handleAgentPrompted',
        InvokingTool::class => 'handleInvokingTool',
        ToolInvoked::class => 'handleToolInvoked',
    ]);
});

it('creates trace and generation on agent prompt', function (): void {
    // trace-create + generation-create = 2 HTTP calls
    Langfuse::fake([aiIngestionResponse(), aiIngestionResponse()]);

    $subscriber = app(LaravelAiSubscriber::class);
    $prompt = makeAgentPrompt();

    $subscriber->handlePromptingAgent(new PromptingAgent(
        invocationId: 'inv-1',
        prompt: $prompt,
    ));

    $subscriber->handleAgentPrompted(new AgentPrompted(
        invocationId: 'inv-1',
        prompt: $prompt,
        response: makeAgentResponse(),
    ));

    $trace = app(TraceContext::class)->currentTrace();
    expect($trace)->not->toBeNull()
        ->and($trace)->toBeInstanceOf(Trace::class);
});

it('sets current trace on trace context', function (): void {
    Langfuse::fake([aiIngestionResponse()]);

    $subscriber = app(LaravelAiSubscriber::class);
    $prompt = makeAgentPrompt();

    $subscriber->handlePromptingAgent(new PromptingAgent(
        invocationId: 'inv-1',
        prompt: $prompt,
    ));

    expect(app(TraceContext::class)->currentTrace())->not->toBeNull();
});

it('creates span for tool invocation', function (): void {
    // trace-create + span-create + span-update = 3 HTTP calls
    Langfuse::fake([aiIngestionResponse(), aiIngestionResponse(), aiIngestionResponse()]);

    $subscriber = app(LaravelAiSubscriber::class);
    $prompt = makeAgentPrompt();

    $subscriber->handlePromptingAgent(new PromptingAgent(
        invocationId: 'inv-1',
        prompt: $prompt,
    ));

    $agent = makeTestAgent();
    $tool = makeTestTool();

    $subscriber->handleInvokingTool(new InvokingTool(
        invocationId: 'inv-1',
        toolInvocationId: 'tool-inv-1',
        agent: $agent,
        tool: $tool,
        arguments: ['query' => 'test'],
    ));

    $subscriber->handleToolInvoked(new ToolInvoked(
        invocationId: 'inv-1',
        toolInvocationId: 'tool-inv-1',
        agent: $agent,
        tool: $tool,
        arguments: ['query' => 'test'],
        result: 'Tool result',
    ));

    expect(app(TraceContext::class)->currentTrace())->not->toBeNull();
});

it('handles tool invoked without prior invoking tool gracefully', function (): void {
    Langfuse::fake([]);

    $subscriber = app(LaravelAiSubscriber::class);

    $subscriber->handleToolInvoked(new ToolInvoked(
        invocationId: 'inv-1',
        toolInvocationId: 'tool-inv-1',
        agent: makeTestAgent(),
        tool: makeTestTool(),
        arguments: [],
        result: 'result',
    ));

    // No exception thrown, no trace created
    expect(app(TraceContext::class)->currentTrace())->toBeNull();
});

it('handles streaming events same as non-streaming', function (): void {
    // trace-create + generation-create = 2 HTTP calls
    Langfuse::fake([aiIngestionResponse(), aiIngestionResponse()]);

    $subscriber = app(LaravelAiSubscriber::class);
    $prompt = makeAgentPrompt();

    $subscriber->handlePromptingAgent(new StreamingAgent(
        invocationId: 'inv-1',
        prompt: $prompt,
    ));

    $subscriber->handleAgentPrompted(new AgentStreamed(
        invocationId: 'inv-1',
        prompt: $prompt,
        response: new StreamedAgentResponse(
            invocationId: 'inv-1',
            text: 'Streamed response',
            usage: new Usage(promptTokens: 5, completionTokens: 10),
            meta: new Meta(provider: 'openai', model: 'gpt-4'),
        ),
    ));

    expect(app(TraceContext::class)->currentTrace())->not->toBeNull();
});

it('reuses existing trace across multiple prompts', function (): void {
    // trace-create + generation-create + generation-create = 3 HTTP calls
    Langfuse::fake([aiIngestionResponse(), aiIngestionResponse(), aiIngestionResponse()]);

    $subscriber = app(LaravelAiSubscriber::class);
    $prompt = makeAgentPrompt();

    // First prompt creates trace
    $subscriber->handlePromptingAgent(new PromptingAgent(invocationId: 'inv-1', prompt: $prompt));
    $subscriber->handleAgentPrompted(new AgentPrompted(
        invocationId: 'inv-1',
        prompt: $prompt,
        response: makeAgentResponse(invocationId: 'inv-1'),
    ));

    $firstTrace = app(TraceContext::class)->currentTrace();

    // Second prompt reuses trace (same current trace set)
    $subscriber->handlePromptingAgent(new PromptingAgent(invocationId: 'inv-2', prompt: $prompt));
    $subscriber->handleAgentPrompted(new AgentPrompted(
        invocationId: 'inv-2',
        prompt: $prompt,
        response: makeAgentResponse(invocationId: 'inv-2'),
    ));

    $secondTrace = app(TraceContext::class)->currentTrace();

    // Same trace reused
    expect($firstTrace->traceId)->toBe($secondTrace->traceId);
});

it('falls back to prompt model when response meta model is null', function (): void {
    // trace-create + generation-create = 2 HTTP calls
    Langfuse::fake([aiIngestionResponse(), aiIngestionResponse()]);

    $subscriber = app(LaravelAiSubscriber::class);
    $prompt = makeAgentPrompt('claude-3-sonnet');

    $subscriber->handlePromptingAgent(new PromptingAgent(
        invocationId: 'inv-1',
        prompt: $prompt,
    ));

    // Should not throw even with null model
    $subscriber->handleAgentPrompted(new AgentPrompted(
        invocationId: 'inv-1',
        prompt: $prompt,
        response: makeAgentResponse(model: null),
    ));

    expect(app(TraceContext::class)->currentTrace())->not->toBeNull();
});
