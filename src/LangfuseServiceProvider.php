<?php

declare(strict_types=1);

namespace DIJ\Langfuse\Laravel;

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
        $this->app->singleton(ClientInterface::class, fn ($app): ClientInterface => new Client([
            'base_uri' => Config::get('langfuse-laravel.base_uri'),
            'auth' => [Config::get('langfuse-laravel.public_key'), Config::get('langfuse-laravel.secret_key')],
        ]));

        $this->app->bind('langfuse', fn () => new Langfuse($this->app->make(HttpTransporter::class)));
    }

    public function configurePackage(Package $package): void
    {
        $package->name('langfuse-laravel')->hasConfigFile();
    }
}
