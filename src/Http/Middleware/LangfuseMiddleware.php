<?php

declare(strict_types=1);

namespace DIJ\Langfuse\Laravel\Http\Middleware;

use Closure;
use DIJ\Langfuse\Laravel\Tracing\TraceContext;
use DIJ\Langfuse\PHP\Langfuse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LangfuseMiddleware
{
    public function __construct(
        private readonly Langfuse $langfuse,
        private readonly TraceContext $traceContext,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $authId = $request->user()?->getAuthIdentifier();

        $trace = $this->langfuse->ingestion()->trace(
            name: $request->route()?->getName() ?? $request->method() . ' ' . $request->path(),
            userId: is_scalar($authId) ? (string) $authId : null,
            metadata: [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'source' => 'langfuse-middleware',
            ],
        );

        $this->traceContext->setCurrentTrace($trace);

        return $next($request);
    }
}
