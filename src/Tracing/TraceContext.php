<?php

declare(strict_types=1);

namespace DIJ\Langfuse\Laravel\Tracing;

use DIJ\Langfuse\PHP\Ingestion\Trace;

class TraceContext
{
    private ?Trace $currentTrace = null;

    public function currentTrace(): ?Trace
    {
        return $this->currentTrace;
    }

    public function setCurrentTrace(Trace $trace): void
    {
        $this->currentTrace = $trace;
    }

    public function reset(): void
    {
        $this->currentTrace = null;
    }
}
