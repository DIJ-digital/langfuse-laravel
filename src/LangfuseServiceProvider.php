<?php

declare(strict_types=1);

namespace DIJ\Langfuse\Laravel;

use DIJ\Langfuse\Langfuse;
use DIJ\Langfuse\Transporters\HttpTransporter;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LangfuseServiceProvider extends PackageServiceProvider
{
    public function registeringPackage(): void
    {
        $this->app->singleton(Langfuse::class, static fn (): Langfuse => new Langfuse(
            new HttpTransporter(
                Http::baseUrl(Config::string('langfuse-laravel.base_url'))
                    ->withBasicAuth(Config::string('langfuse-laravel.public_key'), Config::string('langfuse-laravel.secret_key'))
                    ->buildClient()
            )
        ));

        $this->app->alias(Langfuse::class, 'langfuse');
    }

    public function configurePackage(Package $package): void
    {
        $package->name('langfuse-laravel')->hasConfigFile();
    }
}
