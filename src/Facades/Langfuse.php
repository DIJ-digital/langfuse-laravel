<?php

declare(strict_types=1);

namespace DIJ\Langfuse\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \DIJ\Langfuse\Langfuse
 */
class Langfuse extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \DIJ\Langfuse\Langfuse::class;
    }
}
