<?php

declare(strict_types=1);

namespace DIJ\Langfuse\Laravel;

use DIJ\Langfuse\PHP\Contracts\TransporterInterface;
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
            'connect_timeout' => Config::get('langfuse-laravel.connect_timeout', 5),
            'timeout' => Config::get('langfuse-laravel.timeout', 10),
        ]));

        $this->app->bind(TransporterInterface::class, fn (): TransporterInterface => $this->app->make(HttpTransporter::class));

        $this->app->singleton(Langfuse::class, fn (): Langfuse => new Langfuse(
            transporter: $this->app->make(HttpTransporter::class),
            environment: Config::string('langfuse-laravel.environment', Config::string('app.env', 'default')),
        ));

        $this->app->alias(Langfuse::class, 'langfuse');
    }

    public function configurePackage(Package $package): void
    {
        $package->name('langfuse-laravel')->hasConfigFile();
    }
}
