<?php

declare(strict_types=1);

namespace DIJ\Langfuse\Laravel\Facades;

use DIJ\Langfuse\PHP\Ingestion;
use DIJ\Langfuse\PHP\Langfuse as BaseLangfuse;
use DIJ\Langfuse\PHP\Prompt;
use DIJ\Langfuse\PHP\Score;
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
 * @method static Score score()
 */
class Langfuse extends Facade
{
    /**
     * @param array<int, Response> $responses
     */
    public static function fake(array $responses = []): BaseLangfuse
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $langfuse = new BaseLangfuse(new HttpTransporter($client));

        self::swap($langfuse);

        return $langfuse;
    }

    protected static function getFacadeAccessor(): string
    {
        return 'langfuse';
    }
}
