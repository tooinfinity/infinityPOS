<?php

declare(strict_types=1);

use App\Exceptions\InvalidBatchException;

it('creates exception with batch id and reason', function (): void {
    $exception = new InvalidBatchException(42, 'not found');

    expect($exception->getMessage())
        ->toBe('Batch 42: not found');
    expect($exception->batchId)->toBe(42);
    expect($exception->reason)->toBe('not found');
});

it('creates exception with string batch id', function (): void {
    $exception = new InvalidBatchException('batch-001', 'not in warehouse 5');

    expect($exception->getMessage())
        ->toBe('Batch batch-001: not in warehouse 5');
});
