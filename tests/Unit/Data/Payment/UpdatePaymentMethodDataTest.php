<?php

declare(strict_types=1);

use App\Data\Payment\UpdatePaymentMethodData;
use Spatie\LaravelData\Optional;

it('may be created with all optional fields', function (): void {
    $data = new UpdatePaymentMethodData(
        name: new Optional(),
        code: new Optional(),
        is_active: new Optional(),
    );

    expect($data->name)->toBeInstanceOf(Optional::class)
        ->and($data->code)->toBeInstanceOf(Optional::class)
        ->and($data->is_active)->toBeInstanceOf(Optional::class);
});

it('may be created with concrete values', function (): void {
    $data = new UpdatePaymentMethodData(
        name: 'Updated Cash',
        code: 'updated_cash',
        is_active: false,
    );

    expect($data->name)->toBe('Updated Cash')
        ->and($data->code)->toBe('updated_cash')
        ->and($data->is_active)->toBeFalse();
});
