<?php

declare(strict_types=1);

use DIJ\Langfuse\Laravel\Facades\Langfuse;
use DIJ\Langfuse\PHP\Langfuse as BaseLangfuse;
use DIJ\Langfuse\PHP\OtelIngestion;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

it('records the OTel spans sent through the facade', function (): void {
    Langfuse::fake([new Response(200)]);

    $trace = Langfuse::otelIngestion()->trace(name: 'run', sessionId: 'session-1');
    $trace->end();

    $requests = Langfuse::recorded();

    /** @var array{resourceSpans: list<array{resource: array{attributes: list<array{key: string, value: array{stringValue: string}}>}, scopeSpans: list<array{spans: list<array{traceId: string, name: string}>}>}>} $payload */
    $payload = json_decode((string) $requests[0]->getBody(), true, flags: JSON_THROW_ON_ERROR);

    expect(Langfuse::otelIngestion())->toBeInstanceOf(OtelIngestion::class)
        ->and($requests)->toHaveCount(1)
        ->and($requests[0]->getUri()->getPath())->toBe('/api/public/otel/v1/traces')
        ->and($payload['resourceSpans'][0]['scopeSpans'][0]['spans'][0]['traceId'])->toBe($trace->traceId)
        ->and($payload['resourceSpans'][0]['scopeSpans'][0]['spans'][0]['name'])->toBe('run');
});

it('starts every fake with an empty recording', function (): void {
    Langfuse::fake([new Response(200)]);
    Langfuse::otelIngestion()->trace(name: 'run')->end();

    Langfuse::fake();

    expect(Langfuse::recorded())->toBe([]);
});

it('uses the app name as the OTel service name', function (mixed $appName, string $serviceName): void {
    $history = [];
    $handlerStack = HandlerStack::create(new MockHandler([new Response(200)]));
    $handlerStack->push(Middleware::history($history));

    config()->set('app.name', $appName);
    app()->instance(ClientInterface::class, new Client(['handler' => $handlerStack]));
    app()->forgetInstance(BaseLangfuse::class);

    app(BaseLangfuse::class)->otelIngestion()->trace(name: 'run')->end();

    /** @var array{request: RequestInterface} $entry */
    $entry = $history[0];

    /** @var array{resourceSpans: list<array{resource: array{attributes: list<array{key: string, value: array{stringValue: string}}>}}>} $payload */
    $payload = json_decode((string) $entry['request']->getBody(), true, flags: JSON_THROW_ON_ERROR);

    expect($payload['resourceSpans'][0]['resource']['attributes'])->toBe([['key' => 'service.name', 'value' => ['stringValue' => $serviceName]]]);
})->with([
    'app name' => ['screening', 'screening'],
    'null' => [null, 'unknown_service:php'],
    'empty' => ['', 'unknown_service:php'],
    'not a string' => [['screening'], 'unknown_service:php'],
]);
