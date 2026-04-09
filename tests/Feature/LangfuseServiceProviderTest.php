<?php

declare(strict_types=1);

use DIJ\Langfuse\Laravel\Ai\LaravelAiSubscriber;
use DIJ\Langfuse\Laravel\LangfuseServiceProvider;
use DIJ\Langfuse\Laravel\Tracing\TraceContext;
use DIJ\Langfuse\PHP\Langfuse;

it('registers the service provider', function (): void {
    expect($this->app->getProviders(LangfuseServiceProvider::class))->not->toBeEmpty();
});

it('merges config', function (): void {
    expect(config('langfuse-laravel'))->toBeArray()
        ->and(config('langfuse-laravel.base_uri'))->toBe('https://cloud.langfuse.com');
});

it('binds Langfuse as singleton', function (): void {
    $langfuse1 = $this->app->make(Langfuse::class);
    $langfuse2 = $this->app->make(Langfuse::class);

    expect($langfuse1)->toBeInstanceOf(Langfuse::class)
        ->and($langfuse1)->toBe($langfuse2);
});

it('binds TraceContext as scoped', function (): void {
    $context1 = $this->app->make(TraceContext::class);
    $context2 = $this->app->make(TraceContext::class);

    expect($context1)->toBeInstanceOf(TraceContext::class)
        ->and($context1)->toBe($context2);
});

it('registers the langfuse middleware alias', function (): void {
    /** @var Illuminate\Routing\Router $router */
    $router = $this->app->make(Illuminate\Routing\Router::class);

    expect($router->getMiddleware())->toHaveKey('langfuse');
});

it('does not register laravel ai subscriber when disabled', function (): void {
    config(['langfuse-laravel.laravel_ai_enabled' => false]);

    expect($this->app->bound(LaravelAiSubscriber::class))->toBeFalse();
});

it('registers laravel ai subscriber when enabled', function (): void {
    config(['langfuse-laravel.laravel_ai_enabled' => true]);

    $provider = new LangfuseServiceProvider($this->app);
    $provider->register();
    $provider->boot();

    expect($this->app->bound(LaravelAiSubscriber::class))->toBeTrue();
});
