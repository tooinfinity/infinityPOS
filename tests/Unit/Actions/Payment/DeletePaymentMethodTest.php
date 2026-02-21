<?php

declare(strict_types=1);

use App\Actions\Payment\DeletePaymentMethod;
use App\Models\Payment;
use App\Models\PaymentMethod;

it('may delete a payment method', function (): void {
    $paymentMethod = PaymentMethod::factory()->create();

    $action = resolve(DeletePaymentMethod::class);

    $result = $action->handle($paymentMethod);

    expect($result)->toBeTrue()
        ->and($paymentMethod->exists)->toBeFalse();
});

it('removes payment method from database', function (): void {
    $paymentMethod = PaymentMethod::factory()->create();

    $action = resolve(DeletePaymentMethod::class);

    $action->handle($paymentMethod);

    $this->assertDatabaseMissing('payment_methods', [
        'id' => $paymentMethod->id,
    ]);
});

it('throws exception when payment method has associated payments', function (): void {
    $paymentMethod = PaymentMethod::factory()->create();

    Payment::factory()->count(3)->create([
        'payment_method_id' => $paymentMethod->id,
    ]);

    $action = resolve(DeletePaymentMethod::class);

    $action->handle($paymentMethod);
})->throws(RuntimeException::class, 'Cannot delete payment method with associated payments.');
