<?php

declare(strict_types=1);

use App\Data\Payment\CreatePaymentMethodData;

it('may be created with required fields', function (): void {
    $data = new CreatePaymentMethodData(
        name: 'Cash',
        code: 'cash',
        is_active: true,
    );

    expect($data->name)->toBe('Cash')
        ->and($data->code)->toBe('cash')
        ->and($data->is_active)->toBeTrue();
});

it('may be created with is_active false', function (): void {
    $data = new CreatePaymentMethodData(
        name: 'Inactive',
        code: 'inactive',
        is_active: false,
    );

    expect($data->is_active)->toBeFalse();
});
