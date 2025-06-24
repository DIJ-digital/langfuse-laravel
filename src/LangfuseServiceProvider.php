<?php

declare(strict_types=1);

namespace DIJ\Langfuse;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LangfuseServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('langfuse-laravel')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_langfuse_laravel_table');
    }
}
