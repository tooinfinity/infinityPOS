<?php

declare(strict_types=1);

use App\Actions\PaymentMethod\DeletePaymentMethod;
use App\Models\PaymentMethod;

describe(DeletePaymentMethod::class, function (): void {
    it('may delete a payment method', function (): void {
        $method = PaymentMethod::factory()->create();

        $action = resolve(DeletePaymentMethod::class);

        $result = $action->handle($method);

        expect($result)->toBeTrue()
            ->and(PaymentMethod::query()->where('id', $method->id)->exists())->toBeFalse();
    });

    it('throws exception when deleting method with payments', function (): void {
        $method = PaymentMethod::factory()->create();
        App\Models\Payment::factory()->for($method, 'paymentMethod')->create();

        $action = resolve(DeletePaymentMethod::class);

        expect(fn () => $action->handle($method))->toThrow(App\Exceptions\InvalidOperationException::class);
    });
});
