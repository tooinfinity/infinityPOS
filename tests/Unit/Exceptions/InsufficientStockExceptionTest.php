<?php

declare(strict_types=1);

use App\Exceptions\InsufficientStockException;

it('creates exception with required and available quantity', function (): void {
    $exception = new InsufficientStockException(10, 5);

    expect($exception->getMessage())
        ->toBe('Insufficient stock. Required: 10, Available: 5');
    expect($exception->required)->toBe(10);
    expect($exception->available)->toBe(5);
    expect($exception->batchId)->toBeNull();
});

it('creates exception with batch id', function (): void {
    $exception = new InsufficientStockException(10, 5, 42);

    expect($exception->getMessage())
        ->toBe('Insufficient stock in batch 42. Required: 10, Available: 5');
    expect($exception->batchId)->toBe(42);
});
