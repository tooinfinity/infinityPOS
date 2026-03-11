<?php

declare(strict_types=1);

use App\Actions\PaymentMethod\UpdatePaymentMethod;
use App\Data\Payment\PaymentMethodData;
use App\Models\PaymentMethod;

it('may update a payment method', function (): void {
    $method = PaymentMethod::factory()->create([
        'name' => 'Old Name',
        'code' => 'OLD',
        'is_active' => true,
    ]);

    $action = resolve(UpdatePaymentMethod::class);

    $data = new PaymentMethodData(
        name: 'New Name',
        code: 'NEW',
        is_active: false,
    );

    $updatedMethod = $action->handle($method, $data);

    expect($updatedMethod->name)->toBe('New Name')
        ->and($updatedMethod->code)->toBe('NEW')
        ->and($updatedMethod->is_active)->toBeFalse();
});

it('updates only provided fields', function (): void {
    $method = PaymentMethod::factory()->create([
        'name' => 'Original Name',
        'code' => 'ORG',
        'is_active' => true,
    ]);

    $action = resolve(UpdatePaymentMethod::class);

    $data = new PaymentMethodData(
        name: 'Updated Name',
        code: 'ORG',
        is_active: true,
    );

    $updatedMethod = $action->handle($method, $data);

    expect($updatedMethod->name)->toBe('Updated Name')
        ->and($updatedMethod->code)->toBe('ORG')
        ->and($updatedMethod->is_active)->toBeTrue();
});
