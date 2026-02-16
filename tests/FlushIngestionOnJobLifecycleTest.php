<?php

declare(strict_types=1);

use DIJ\Langfuse\Laravel\Jobs\SendTelemetryJob;
use DIJ\Langfuse\PHP\Ingestion;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;

beforeEach(function (): void {
    Config::set('langfuse-laravel.async', true);
});

it('dispatches telemetry job when a job is processed', function (): void {
    // Arrange
    Bus::fake(SendTelemetryJob::class);
    $ingestion = Mockery::mock(Ingestion::class);
    $ingestion->shouldReceive('drain')->once()->andReturn(['resourceSpans' => []]);
    app()->instance(Ingestion::class, $ingestion);

    // Act
    event(new JobProcessed('default', Mockery::mock(Illuminate\Contracts\Queue\Job::class)));

    // Assert
    Bus::assertDispatched(SendTelemetryJob::class);
});

it('dispatches telemetry job when a job fails', function (): void {
    // Arrange
    Bus::fake(SendTelemetryJob::class);
    $ingestion = Mockery::mock(Ingestion::class);
    $ingestion->shouldReceive('drain')->once()->andReturn(['resourceSpans' => []]);
    app()->instance(Ingestion::class, $ingestion);

    // Act
    event(new JobFailed('default', Mockery::mock(Illuminate\Contracts\Queue\Job::class), new RuntimeException('test')));

    // Assert
    Bus::assertDispatched(SendTelemetryJob::class);
});

it('dispatches telemetry job when a job exception occurs', function (): void {
    // Arrange
    Bus::fake(SendTelemetryJob::class);
    $ingestion = Mockery::mock(Ingestion::class);
    $ingestion->shouldReceive('drain')->once()->andReturn(['resourceSpans' => []]);
    app()->instance(Ingestion::class, $ingestion);

    // Act
    event(new JobExceptionOccurred('default', Mockery::mock(Illuminate\Contracts\Queue\Job::class), new RuntimeException('test')));

    // Assert
    Bus::assertDispatched(SendTelemetryJob::class);
});

it('does not dispatch when ingestion was never resolved', function (): void {
    // Arrange
    Bus::fake(SendTelemetryJob::class);
    $ingestion = Mockery::mock(Ingestion::class);
    $ingestion->shouldNotReceive('drain');
    app()->bind(Ingestion::class, fn () => $ingestion);

    // Act
    event(new JobProcessed('default', Mockery::mock(Illuminate\Contracts\Queue\Job::class)));

    // Assert
    Bus::assertNotDispatched(SendTelemetryJob::class);
    expect(app()->resolved(Ingestion::class))->toBeFalse();
});

it('does not dispatch when drain returns null', function (): void {
    // Arrange
    Bus::fake(SendTelemetryJob::class);
    $ingestion = Mockery::mock(Ingestion::class);
    $ingestion->shouldReceive('drain')->once()->andReturnNull();
    app()->instance(Ingestion::class, $ingestion);

    // Act
    event(new JobProcessed('default', Mockery::mock(Illuminate\Contracts\Queue\Job::class)));

    // Assert
    Bus::assertNotDispatched(SendTelemetryJob::class);
});

it('flushes synchronously when async is disabled', function (): void {
    // Arrange
    Config::set('langfuse-laravel.async', false);
    Bus::fake(SendTelemetryJob::class);
    $ingestion = Mockery::mock(Ingestion::class);
    $ingestion->shouldReceive('drain')->once()->andReturn(['resourceSpans' => []]);
    app()->instance(Ingestion::class, $ingestion);

    // We expect the transporter to be called directly, not via a job.
    // Since we can't easily assert the transporter call here, we assert the job was NOT dispatched.
    // Act
    event(new JobProcessed('default', Mockery::mock(Illuminate\Contracts\Queue\Job::class)));

    // Assert
    Bus::assertNotDispatched(SendTelemetryJob::class);
});
