<?php

declare(strict_types=1);

namespace DIJ\Langfuse\Laravel;

use DIJ\Langfuse\PHP\Contracts\TransporterInterface;
use DIJ\Langfuse\PHP\Langfuse;
use DIJ\Langfuse\PHP\Transporters\HttpTransporter;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Contracts\Foundation\Application;
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

        $this->app->bind(TransporterInterface::class, fn (Application $app): TransporterInterface => $app->make(HttpTransporter::class));

        $this->app->singleton(Langfuse::class, fn (Application $app): Langfuse => new Langfuse(
            transporter: $app->make(HttpTransporter::class),
            environment: Config::string('langfuse-laravel.environment'),
            label: Config::string('langfuse-laravel.default_label'),
            serviceName: $this->serviceName(),
        ));

        $this->app->alias(Langfuse::class, 'langfuse');
    }

    private function serviceName(): string
    {
        $name = Config::get('app.name');

        return is_string($name) && $name !== '' ? $name : 'unknown_service:php';
    }

    public function configurePackage(Package $package): void
    {
        $package->name('langfuse-laravel')->hasConfigFile();
    }
}
