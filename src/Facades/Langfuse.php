<?php

declare(strict_types=1);

namespace DIJ\Langfuse\Laravel\Facades;

use DIJ\Langfuse\PHP\Langfuse as BaseLangfuse;
use DIJ\Langfuse\PHP\Prompt;
use DIJ\Langfuse\PHP\Ingestion;
use DIJ\Langfuse\PHP\Transporters\HttpTransporter;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Facade;

/**
 * @see BaseLangfuse
 *
 * @method static Prompt prompt()
 * @method static Ingestion ingestion(string $environment = 'default')
 */
class Langfuse extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'langfuse';
    }

    /**
     * @param array<int, Response> $responses
     */
    public static function fake(array $responses = []): BaseLangfuse
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $fake = new BaseLangfuse(new HttpTransporter($client));
        self::swap($fake);

        return $fake;
    }
}
