<?php

declare(strict_types=1);

use App\Exceptions\StateTransitionException;

it('creates exception with string status labels', function (): void {
    $exception = new StateTransitionException('Pending', 'Received');

    expect($exception->getMessage())
        ->toBe('Invalid state transition from "Pending" to "Received"');
});

it('creates exception with enum cases using label method', function (): void {
    $exception = new StateTransitionException(
        App\Enums\PurchaseStatusEnum::Pending->label(),
        App\Enums\PurchaseStatusEnum::Received->label()
    );

    expect($exception->getMessage())
        ->toContain('Pending')
        ->toContain('Received');
});

it('creates exception with Stringable objects', function (): void {
    $fromStatus = new class implements Stringable
    {
        public function __toString(): string
        {
            return 'Current Status';
        }
    };

    $toStatus = new class implements Stringable
    {
        public function __toString(): string
        {
            return 'Target Status';
        }
    };

    $exception = new StateTransitionException($fromStatus, $toStatus);

    expect($exception->getMessage())
        ->toBe('Invalid state transition from "Current Status" to "Target Status"');
});
