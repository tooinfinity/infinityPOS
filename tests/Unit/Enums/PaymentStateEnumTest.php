<?php

declare(strict_types=1);

use App\Enums\PaymentStateEnum;

it('payment state to array', function (): void {
    expect(PaymentStateEnum::toArray())->toBeArray();
});

it('payment state label', function (): void {
    expect(PaymentStateEnum::Active->label())->toBe('Active')
        ->and(PaymentStateEnum::Voided->label())->toBe('Voided');
});
