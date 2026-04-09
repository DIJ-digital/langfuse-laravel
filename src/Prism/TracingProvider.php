<?php

declare(strict_types=1);

namespace DIJ\Langfuse\Laravel\Prism;

use DIJ\Langfuse\Laravel\Tracing\TraceContext;
use DIJ\Langfuse\PHP\Ingestion;
use DIJ\Langfuse\PHP\Ingestion\Trace;
use DIJ\Langfuse\PHP\Langfuse;
use Generator;
use Illuminate\Http\Client\RequestException;
use Prism\Prism\Audio\AudioResponse as TextToSpeechResponse;
use Prism\Prism\Audio\SpeechToTextRequest;
use Prism\Prism\Audio\TextResponse as SpeechToTextResponse;
use Prism\Prism\Audio\TextToSpeechRequest;
use Prism\Prism\Embeddings\Request as EmbeddingsRequest;
use Prism\Prism\Embeddings\Response as EmbeddingsResponse;
use Prism\Prism\Images\Request as ImagesRequest;
use Prism\Prism\Images\Response as ImagesResponse;
use Prism\Prism\Moderation\Request as ModerationRequest;
use Prism\Prism\Moderation\Response as ModerationResponse;
use Prism\Prism\Providers\Provider;
use Prism\Prism\Streaming\Events\StreamEndEvent;
use Prism\Prism\Streaming\Events\TextDeltaEvent;
use Prism\Prism\Structured\Request as StructuredRequest;
use Prism\Prism\Structured\Response as StructuredResponse;
use Prism\Prism\Text\Request as TextRequest;
use Prism\Prism\Text\Response as TextResponse;
use Throwable;

class TracingProvider extends Provider
{
    public function __construct(
        private readonly Provider $inner,
        private readonly Langfuse $langfuse,
        private readonly TraceContext $traceContext,
    ) {
    }

    public function text(TextRequest $request): TextResponse
    {
        try {
            $response = $this->inner->text($request);

            $this->recordGeneration(
                request: $request,
                output: $response->text,
                usage: $response->usage,
                finishReason: $response->finishReason->name,
            );

            return $response;
        } catch (Throwable $e) {
            $this->recordGenerationError($request, $e);

            throw $e;
        }
    }

    public function structured(StructuredRequest $request): StructuredResponse
    {
        try {
            $response = $this->inner->structured($request);

            $this->recordGeneration(
                request: $request,
                output: $response->structured,
                usage: $response->usage,
                finishReason: $response->finishReason->name,
            );

            return $response;
        } catch (Throwable $e) {
            $this->recordGenerationError($request, $e);

            throw $e;
        }
    }

    /**
     * @return Generator<\Prism\Prism\Streaming\Events\StreamEvent>
     */
    public function stream(TextRequest $request): Generator
    {
        try {
            yield from $this->traceStream($request);
        } catch (Throwable $e) {
            $this->recordGenerationError($request, $e);

            throw $e;
        }
    }

    public function embeddings(EmbeddingsRequest $request): EmbeddingsResponse
    {
        return $this->inner->embeddings($request);
    }

    public function images(ImagesRequest $request): ImagesResponse
    {
        return $this->inner->images($request);
    }

    public function moderation(ModerationRequest $request): ModerationResponse
    {
        return $this->inner->moderation($request);
    }

    public function textToSpeech(TextToSpeechRequest $request): TextToSpeechResponse
    {
        return $this->inner->textToSpeech($request);
    }

    public function speechToText(SpeechToTextRequest $request): SpeechToTextResponse
    {
        return $this->inner->speechToText($request);
    }

    public function handleRequestException(string $model, RequestException $e): never
    {
        $this->inner->handleRequestException($model, $e);
    }

    /**
     * @return Generator<\Prism\Prism\Streaming\Events\StreamEvent>
     */
    private function traceStream(TextRequest $request): Generator
    {
        $text = '';
        $streamUsage = null;
        $finishReason = null;

        foreach ($this->inner->stream($request) as $event) {
            if ($event instanceof TextDeltaEvent) {
                $text .= $event->delta;
            }

            if ($event instanceof StreamEndEvent) {
                $streamUsage = $event->usage;
                $finishReason = $event->finishReason->name;
            }

            yield $event;
        }

        $this->recordGeneration(
            request: $request,
            output: $text,
            usage: $streamUsage,
            finishReason: $finishReason,
        );
    }

    private function recordGeneration(
        TextRequest|StructuredRequest $request,
        mixed $output,
        ?\Prism\Prism\ValueObjects\Usage $usage,
        ?string $finishReason,
    ): void {
        $trace = $this->getOrCreateTrace($request);

        $trace->generation(
            name: $request->model(),
            input: $this->extractInput($request),
            output: is_string($output) ? $output : json_encode($output, JSON_THROW_ON_ERROR),
            model: $request->model(),
            modelParameters: $this->extractModelParameters($request),
            startTime: Ingestion::now(),
            endTime: Ingestion::now(),
        );
    }

    private function recordGenerationError(
        TextRequest|StructuredRequest $request,
        Throwable $e,
    ): void {
        $trace = $this->getOrCreateTrace($request, ['error' => $e->getMessage()]);

        $trace->generation(
            name: $request->model(),
            input: $this->extractInput($request),
            output: $e->getMessage(),
            model: $request->model(),
            modelParameters: $this->extractModelParameters($request),
            metadata: ['error' => $e->getMessage(), 'level' => 'ERROR'],
            startTime: Ingestion::now(),
            endTime: Ingestion::now(),
        );
    }

    /**
     * @param  array<string, mixed>  $extraMetadata
     */
    private function getOrCreateTrace(
        TextRequest|StructuredRequest $request,
        array $extraMetadata = [],
    ): Trace {
        $existing = $this->traceContext->currentTrace();

        if ($existing !== null) {
            return $existing;
        }

        $trace = $this->langfuse->ingestion()->trace(
            name: 'prism-'.$request->model(),
            input: $this->extractInput($request),
            metadata: [
                'provider' => $request->provider(),
                'source' => 'prism-auto-instrumentation',
                ...$extraMetadata,
            ],
        );

        $this->traceContext->setCurrentTrace($trace);

        return $trace;
    }

    /**
     * @return array<string, mixed>
     */
    private function extractInput(TextRequest|StructuredRequest $request): array
    {
        $input = [];

        $systemPrompts = $request->systemPrompts();
        if ($systemPrompts !== []) {
            $input['systemPrompts'] = array_map(
                fn ($sp): string => $sp->content,
                $systemPrompts,
            );
        }

        if ($request->prompt() !== null) {
            $input['prompt'] = $request->prompt();
        }

        $messages = $request->messages();
        if ($messages !== []) {
            $input['messageCount'] = count($messages);
        }

        return $input;
    }

    /**
     * @return array<string, mixed>
     */
    private function extractModelParameters(TextRequest|StructuredRequest $request): array
    {
        return array_filter([
            'temperature' => $request->temperature(),
            'maxTokens' => $request->maxTokens(),
            'topP' => $request->topP(),
        ], fn (mixed $v): bool => $v !== null);
    }
}
