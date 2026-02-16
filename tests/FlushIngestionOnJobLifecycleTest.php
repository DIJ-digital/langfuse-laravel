<?php

declare(strict_types=1);

use DIJ\Langfuse\PHP\Langfuse;

it('resolves Langfuse as a singleton', function (): void {
    // Act
    $a = app(Langfuse::class);
    $b = app(Langfuse::class);

    // Assert
    expect($a)->toBe($b);
});

it('returns a new Ingestion each call', function (): void {
    // Arrange
    $langfuse = app(Langfuse::class);

    // Act
    $a = $langfuse->ingestion();
    $b = $langfuse->ingestion();

    // Assert
    expect($a)->not->toBe($b);
});
