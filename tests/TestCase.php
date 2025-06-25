<?php

declare(strict_types=1);

namespace DIJ\Langfuse\Tests;

use DIJ\Langfuse\Laravel\LangfuseServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            LangfuseServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {

    }
}
