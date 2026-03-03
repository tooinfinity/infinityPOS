<?php

declare(strict_types=1);

use App\Actions\Payment\UpdatePaymentMethod;
use App\Data\Payment\UpdatePaymentMethodData;
use App\Models\PaymentMethod;
use Spatie\LaravelData\Optional;

it('may update a payment method', function (): void {
    $paymentMethod = PaymentMethod::factory()->create([
        'name' => 'Cash',
        'code' => 'cash',
        'is_active' => true,
    ]);

    $action = resolve(UpdatePaymentMethod::class);

    $data = new UpdatePaymentMethodData(
        name: 'Updated Cash',
        code: 'updated_cash',
        is_active: false,
    );

    $updated = $action->handle($paymentMethod, $data);

    expect($updated->name)->toBe('Updated Cash')
        ->and($updated->code)->toBe('updated_cash')
        ->and($updated->is_active)->toBeFalse();
});

it('may update only specific fields', function (): void {
    $paymentMethod = PaymentMethod::factory()->create([
        'name' => 'Cash',
        'code' => 'cash',
        'is_active' => true,
    ]);

    $action = resolve(UpdatePaymentMethod::class);

    $data = new UpdatePaymentMethodData(
        name: new Optional(),
        code: 'new_code',
        is_active: new Optional(),
    );

    $updated = $action->handle($paymentMethod, $data);

    expect($updated->name)->toBe('Cash')
        ->and($updated->code)->toBe('new_code')
        ->and($updated->is_active)->toBeTrue();
});

it('stores updated payment method in database', function (): void {
    $paymentMethod = PaymentMethod::factory()->create([
        'name' => 'Original',
        'code' => 'original',
        'is_active' => true,
    ]);

    $action = resolve(UpdatePaymentMethod::class);

    $data = new UpdatePaymentMethodData(
        name: 'Changed',
        code: 'changed',
        is_active: false,
    );

    $action->handle($paymentMethod, $data);

    $this->assertDatabaseHas('payment_methods', [
        'id' => $paymentMethod->id,
        'name' => 'Changed',
        'code' => 'changed',
        'is_active' => false,
    ]);
});
