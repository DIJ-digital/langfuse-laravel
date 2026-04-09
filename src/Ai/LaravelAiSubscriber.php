<?php

declare(strict_types=1);

namespace DIJ\Langfuse\Laravel\Ai;

use DIJ\Langfuse\Laravel\Tracing\TraceContext;
use DIJ\Langfuse\PHP\Ingestion;
use DIJ\Langfuse\PHP\Ingestion\Span;
use DIJ\Langfuse\PHP\Ingestion\Trace;
use DIJ\Langfuse\PHP\Langfuse;
use Laravel\Ai\Events\AgentPrompted;
use Laravel\Ai\Events\AgentStreamed;
use Laravel\Ai\Events\InvokingTool;
use Laravel\Ai\Events\PromptingAgent;
use Laravel\Ai\Events\StreamingAgent;
use Laravel\Ai\Events\ToolInvoked;

class LaravelAiSubscriber
{
    /** @var array<string, float> */
    private array $startTimes = [];

    /** @var array<string, Trace> */
    private array $traces = [];

    /** @var array<string, Span> */
    private array $toolSpans = [];

    /** @var array<string, float> */
    private array $toolStartTimes = [];

    public function __construct(
        private readonly Langfuse $langfuse,
        private readonly TraceContext $traceContext,
    ) {
    }

    public function handlePromptingAgent(PromptingAgent $event): void
    {
        $this->startTimes[$event->invocationId] = microtime(true);
        $this->getOrCreateTrace($event);
    }

    public function handleAgentPrompted(AgentPrompted $event): void
    {
        $startTime = $this->startTimes[$event->invocationId] ?? microtime(true);
        $endTime = microtime(true);

        $trace = $this->getOrCreateTrace($event);
        $response = $event->response;

        $model = $response->meta->model ?? $event->prompt->model;

        $generation = $trace->generation(
            name: $model,
            input: $event->prompt->prompt,
            output: $response->text,
            model: $model,
            startTime: Ingestion::now(),
            endTime: Ingestion::now(),
        );

        unset($this->startTimes[$event->invocationId]);
    }

    public function handleInvokingTool(InvokingTool $event): void
    {
        $this->toolStartTimes[$event->toolInvocationId] = microtime(true);

        $trace = $this->getOrCreateTraceFromTool($event);
        $toolName = $this->getShortClassName($event->tool);

        $span = $trace->span(
            name: "tool-{$toolName}",
            input: $event->arguments,
            startTime: Ingestion::now(),
        );

        $this->toolSpans[$event->toolInvocationId] = $span;
    }

    public function handleToolInvoked(ToolInvoked $event): void
    {
        $span = $this->toolSpans[$event->toolInvocationId] ?? null;

        if ($span === null) {
            return;
        }

        $span->update(
            output: $event->result,
            endTime: Ingestion::now(),
        );

        unset($this->toolSpans[$event->toolInvocationId], $this->toolStartTimes[$event->toolInvocationId]);
    }

    /**
     * @return array<string, string>
     */
    public function subscribe(): array
    {
        return [
            PromptingAgent::class => 'handlePromptingAgent',
            StreamingAgent::class => 'handlePromptingAgent',
            AgentPrompted::class => 'handleAgentPrompted',
            AgentStreamed::class => 'handleAgentPrompted',
            InvokingTool::class => 'handleInvokingTool',
            ToolInvoked::class => 'handleToolInvoked',
        ];
    }

    private function getOrCreateTrace(PromptingAgent|AgentPrompted $event): Trace
    {
        return $this->resolveTrace($event->invocationId, [
            'name' => 'laravel-ai-' . $this->getShortClassName($event->prompt->agent),
            'input' => $event->prompt->prompt,
            'metadata' => [
                'model' => $event->prompt->model,
                'source' => 'laravel-ai-auto-instrumentation',
            ],
        ]);
    }

    private function getOrCreateTraceFromTool(InvokingTool $event): Trace
    {
        return $this->resolveTrace($event->invocationId, [
            'name' => 'laravel-ai-' . $this->getShortClassName($event->agent),
            'metadata' => [
                'source' => 'laravel-ai-auto-instrumentation',
            ],
        ]);
    }

    /**
     * @param array<string, mixed> $params
     */
    private function resolveTrace(string $invocationId, array $params): Trace
    {
        if (isset($this->traces[$invocationId])) {
            return $this->traces[$invocationId];
        }

        $existing = $this->traceContext->currentTrace();

        if ($existing !== null) {
            $this->traces[$invocationId] = $existing;

            return $existing;
        }

        $trace = $this->langfuse->ingestion()->trace(
            name: $params['name'],
            input: $params['input'] ?? null,
            metadata: $params['metadata'] ?? null,
        );

        $this->traceContext->setCurrentTrace($trace);
        $this->traces[$invocationId] = $trace;

        return $trace;
    }

    private function getShortClassName(object $object): string
    {
        $className = get_class($object);
        $parts = explode('\\', $className);

        return end($parts);
    }
}
