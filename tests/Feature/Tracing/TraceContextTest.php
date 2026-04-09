<?php

declare(strict_types=1);

use DIJ\Langfuse\Laravel\Tracing\TraceContext;
use DIJ\Langfuse\PHP\Ingestion\Trace;

it('starts with no current trace', function (): void {
    $context = new TraceContext();

    expect($context->currentTrace())->toBeNull();
});

it('stores and retrieves a trace', function (): void {
    $context = new TraceContext();
    $trace = Mockery::mock(Trace::class);

    $context->setCurrentTrace($trace);

    expect($context->currentTrace())->toBe($trace);
});

it('resets the current trace', function (): void {
    $context = new TraceContext();
    $trace = Mockery::mock(Trace::class);

    $context->setCurrentTrace($trace);
    $context->reset();

    expect($context->currentTrace())->toBeNull();
});
