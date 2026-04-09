<?php

declare(strict_types=1);

namespace DIJ\Langfuse\Laravel;

use DIJ\Langfuse\Laravel\Http\Middleware\LangfuseMiddleware;
use DIJ\Langfuse\Laravel\Tracing\TraceContext;
use DIJ\Langfuse\PHP\Contracts\TransporterInterface;
use DIJ\Langfuse\PHP\Langfuse;
use DIJ\Langfuse\PHP\Transporters\HttpTransporter;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LangfuseServiceProvider extends PackageServiceProvider
{
    public function registeringPackage(): void
    {
        $this->app->singleton(ClientInterface::class, fn (): ClientInterface => new Client([
            'base_uri' => Config::get('langfuse-laravel.base_uri'),
            'auth' => [Config::get('langfuse-laravel.public_key'), Config::get('langfuse-laravel.secret_key')],
            'connect_timeout' => Config::get('langfuse-laravel.connect_timeout', 5),
            'timeout' => Config::get('langfuse-laravel.timeout', 10),
        ]));

        $this->app->bind(TransporterInterface::class, fn (): TransporterInterface => $this->app->make(HttpTransporter::class));

        $this->app->singleton(Langfuse::class, fn (): Langfuse => new Langfuse(
            transporter: $this->app->make(HttpTransporter::class),
            environment: Config::string('langfuse-laravel.environment'),
            label: Config::string('langfuse-laravel.default_label'),
        ));

        $this->app->alias(Langfuse::class, 'langfuse');

        $this->app->scoped(TraceContext::class);
    }

    public function bootingPackage(): void
    {
        /** @var Router $router */
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('langfuse', LangfuseMiddleware::class);

        $this->registerLaravelAiIntegration();
        $this->registerPrismIntegration();
    }

    public function configurePackage(Package $package): void
    {
        $package->name('langfuse-laravel')->hasConfigFile();
    }

    private function registerLaravelAiIntegration(): void
    {
        if (! class_exists(\Laravel\Ai\Events\PromptingAgent::class)) {
            return;
        }

        if (! Config::get('langfuse-laravel.laravel_ai_enabled', false)) {
            return;
        }

        $this->app->scoped(Ai\LaravelAiSubscriber::class);

        Event::subscribe(Ai\LaravelAiSubscriber::class);
    }

    private function registerPrismIntegration(): void
    {
        if (! class_exists(\Prism\Prism\PrismManager::class)) {
            return;
        }

        if (! $this->shouldEnablePrism()) {
            return;
        }

        $this->app->extend(\Prism\Prism\PrismManager::class, function (\Prism\Prism\PrismManager $manager) {
            return new Prism\TracingPrismManager(
                app: $this->app,
                inner: $manager,
                langfuse: $this->app->make(Langfuse::class),
                traceContext: $this->app->make(TraceContext::class),
            );
        });
    }

    /**
     * Prism tracing is enabled when explicitly configured or when
     * Laravel AI is enabled, since Laravel AI uses Prism under the hood.
     */
    private function shouldEnablePrism(): bool
    {
        return (bool) Config::get('langfuse-laravel.prism_enabled', false)
            || (bool) Config::get('langfuse-laravel.laravel_ai_enabled', false);
    }
}
