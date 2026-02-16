<?php

declare(strict_types=1);

namespace DIJ\Langfuse\Laravel;

use DIJ\Langfuse\Laravel\Jobs\SendTelemetryJob;
use DIJ\Langfuse\PHP\Contracts\TransporterInterface;
use DIJ\Langfuse\PHP\Ingestion;
use DIJ\Langfuse\PHP\Langfuse;
use DIJ\Langfuse\PHP\Transporters\HttpTransporter;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Throwable;

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

        $this->app->singleton(Langfuse::class, function (): LangfuseDecorator {
            $langfuse = new LangfuseDecorator(
                transporter: $this->app->make(HttpTransporter::class),
                serviceName: Config::get('langfuse-laravel.service_name') ?? Config::get('app.name', ''),
            );

            $environment = Config::get('langfuse-laravel.environment') ?? Config::get('app.env', 'default');
            $langfuse->setIngestion($langfuse->createIngestion($environment));

            return $langfuse;
        });

        $this->app->singleton(Ingestion::class, fn (): Ingestion => $this->app->make(Langfuse::class)->ingestion());

        $this->app->bind(TransporterInterface::class, fn (): TransporterInterface => $this->app->make(HttpTransporter::class));

        $this->app->alias(Langfuse::class, 'langfuse');
    }

    public function bootingPackage(): void
    {
        $this->app->terminating(fn () => $this->dispatchTelemetry());

        Event::listen([JobProcessed::class, JobFailed::class, JobExceptionOccurred::class], fn () => $this->dispatchTelemetry());
    }

    public function configurePackage(Package $package): void
    {
        $package->name('langfuse-laravel')->hasConfigFile();
    }

    private function dispatchTelemetry(): void
    {
        if (! $this->app->resolved(Ingestion::class)) {
            return;
        }

        try {
            $payload = $this->app->make(Ingestion::class)->drain();

            if ($payload === null) {
                return;
            }

            if (Config::get('langfuse-laravel.async', true)) {
                SendTelemetryJob::dispatch($payload);
            } else {
                $this->app->make(TransporterInterface::class)
                    ->postJson('/api/public/otel/v1/traces', $payload);
            }
        } catch (Throwable $e) {
            report($e);
        }
    }
}
