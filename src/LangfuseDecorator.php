<?php

declare(strict_types=1);

namespace DIJ\Langfuse\Laravel;

use DIJ\Langfuse\PHP\Ingestion;
use DIJ\Langfuse\PHP\Langfuse;

class LangfuseDecorator extends Langfuse
{
    private ?Ingestion $resolvedIngestion = null;

    /**
     * Set the singleton Ingestion instance.
     */
    public function setIngestion(Ingestion $ingestion): void
    {
        $this->resolvedIngestion = $ingestion;
    }

    /**
     * Create a new Ingestion instance via the parent class.
     */
    public function createIngestion(string $environment = 'default'): Ingestion
    {
        return parent::ingestion($environment);
    }

    /**
     * Return the singleton Ingestion instance instead of creating a new one.
     */
    public function ingestion(string $environment = 'default'): Ingestion
    {
        if ($this->resolvedIngestion !== null) {
            return $this->resolvedIngestion;
        }

        $this->resolvedIngestion = parent::ingestion($environment);

        return $this->resolvedIngestion;
    }

    public function flush(): void
    {
        $this->resolvedIngestion?->flush();
    }
}
