<?php

declare(strict_types=1);

use DIJ\Langfuse\Laravel\Facades\Langfuse;
use DIJ\Langfuse\Laravel\Http\Middleware\LangfuseMiddleware;
use DIJ\Langfuse\Laravel\Tracing\TraceContext;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;

function ingestionResponse(): Response
{
    return new Response(200, [], json_encode(['successes' => [], 'errors' => []]));
}

it('creates a trace for the request', function (): void {
    Langfuse::fake([ingestionResponse()]);

    $middleware = app(LangfuseMiddleware::class);
    $request = Request::create('/api/chat', 'POST');

    $response = $middleware->handle($request, fn () => new HttpResponse('OK'));

    expect($response->getStatusCode())->toBe(200);
});

it('uses route name when available', function (): void {
    Langfuse::fake([ingestionResponse()]);

    $middleware = app(LangfuseMiddleware::class);
    $request = Request::create('/api/chat', 'POST');

    $route = new \Illuminate\Routing\Route('POST', '/api/chat', fn () => 'ok');
    $route->name('api.chat');
    $request->setRouteResolver(fn () => $route);

    $middleware->handle($request, fn () => new HttpResponse('OK'));

    $trace = app(TraceContext::class)->currentTrace();
    expect($trace)->not->toBeNull();
});

it('sets current trace on the trace context', function (): void {
    Langfuse::fake([ingestionResponse()]);

    $middleware = app(LangfuseMiddleware::class);
    $request = Request::create('/test', 'GET');

    $middleware->handle($request, fn () => new HttpResponse('OK'));

    $trace = app(TraceContext::class)->currentTrace();
    expect($trace)->not->toBeNull()
        ->and($trace)->toBeInstanceOf(\DIJ\Langfuse\PHP\Ingestion\Trace::class);
});

it('passes response through unchanged', function (): void {
    Langfuse::fake([ingestionResponse()]);

    $middleware = app(LangfuseMiddleware::class);
    $request = Request::create('/test', 'GET');
    $expectedResponse = new HttpResponse('Hello World', 200);

    $response = $middleware->handle($request, fn () => $expectedResponse);

    expect($response)->toBe($expectedResponse);
});

it('falls back to method and path when route has no name', function (): void {
    Langfuse::fake([ingestionResponse()]);

    $middleware = app(LangfuseMiddleware::class);
    $request = Request::create('/api/chat', 'POST');

    $middleware->handle($request, fn () => new HttpResponse('OK'));

    $trace = app(TraceContext::class)->currentTrace();
    expect($trace)->not->toBeNull();
});
