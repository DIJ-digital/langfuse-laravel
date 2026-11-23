<?php

declare(strict_types=1);

namespace DIJ\Langfuse\Laravel\Facades;

use DIJ\Langfuse\PHP\Ingestion;
use DIJ\Langfuse\PHP\Langfuse as BaseLangfuse;
use DIJ\Langfuse\PHP\OtelIngestion;
use DIJ\Langfuse\PHP\Prompt;
use DIJ\Langfuse\PHP\Score;
use DIJ\Langfuse\PHP\Transporters\HttpTransporter;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Facade;
use Psr\Http\Message\RequestInterface;

/**
 * @see BaseLangfuse
 *
 * @method static Prompt prompt()
 * @method static Ingestion ingestion()
 * @method static OtelIngestion otelIngestion()
 * @method static Score score()
 */
class Langfuse extends Facade
{
    /** @var list<RequestInterface> */
    private static array $recorded = [];

    /**
     * @param array<int, Response> $responses
     */
    public static function fake(array $responses = []): BaseLangfuse
    {
        self::$recorded = [];

        $handlerStack = HandlerStack::create(new MockHandler($responses));
        $handlerStack->push(Middleware::mapRequest(static function (RequestInterface $request): RequestInterface {
            self::$recorded[] = $request;

            return $request;
        }));

        $langfuse = new BaseLangfuse(
            transporter: new HttpTransporter(new Client(['handler' => $handlerStack])),
        );

        self::swap($langfuse);

        return $langfuse;
    }

    /**
     * @return list<RequestInterface>
     */
    public static function recorded(): array
    {
        return self::$recorded;
    }

    protected static function getFacadeAccessor(): string
    {
        return 'langfuse';
    }
}
