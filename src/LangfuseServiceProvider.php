<?php

declare(strict_types=1);

namespace DIJ\Langfuse\Laravel;

use DIJ\Langfuse\PHP\Ingestion;
use DIJ\Langfuse\PHP\Langfuse;
use DIJ\Langfuse\PHP\Transporters\HttpTransporter;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Support\Facades\Config;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LangfuseServiceProvider extends PackageServiceProvider
{
    public function registeringPackage(): void
    {
        $this->app->singleton(ClientInterface::class, fn (): ClientInterface => new Client([
            'base_uri' => Config::get('langfuse-laravel.base_uri'),
            'auth' => [Config::get('langfuse-laravel.public_key'), Config::get('langfuse-laravel.secret_key')],
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

        $this->app->alias(Langfuse::class, 'langfuse');
    }

    public function bootingPackage(): void
    {
        $this->app->terminating(function (): void {
            if (! $this->app->resolved(Ingestion::class)) {
                return;
            }

            try {
                $this->app->make(Ingestion::class)->flush();
            } catch (\Throwable $e) {
                report($e);
            }
        });
    }

    public function configurePackage(Package $package): void
    {
        $package->name('langfuse-laravel')->hasConfigFile();
    }
}
