<?php

declare(strict_types=1);

namespace DIJ\Langfuse\Laravel\Jobs;

use DIJ\Langfuse\PHP\Contracts\TransporterInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class SendTelemetryJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public int $tries = 5;

    /** @var array<int, int> */
    public array $backoff = [1, 5, 15, 30, 60];

    /**
     * @param array<string, mixed> $payload Pre-serialized OTLP JSON payload.
     */
    public function __construct(
        public readonly array $payload,
    ) {
        $this->onQueue(config('langfuse-laravel.queue', 'telemetry'));
    }

    public function handle(TransporterInterface $transporter): void
    {
        $transporter->postJson('/api/public/otel/v1/traces', $this->payload);
    }
}
