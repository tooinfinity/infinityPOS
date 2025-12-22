<?php

declare(strict_types=1);

use App\Actions\Purchases\ProcessPurchasePayment;
use App\Enums\PaymentMethodEnum;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\User;

it('may process a purchase payment', function (): void {
    $user = User::factory()->create();
    $purchase = Purchase::factory()->create(['created_by' => $user->id]);
    $action = resolve(ProcessPurchasePayment::class);

    $payment = $action->handle(
        purchase: $purchase,
        amount: 50000,
        method: PaymentMethodEnum::TRANSFER,
        reference: 'PAY-001',
        notes: 'Bank transfer payment',
        userId: $user->id
    );

    expect($payment)->toBeInstanceOf(Payment::class)
        ->and($payment->reference)->toBe('PAY-001')
        ->and($payment->amount)->toBe(50000)
        ->and($payment->method)->toBe(PaymentMethodEnum::TRANSFER)
        ->and($payment->notes)->toBe('Bank transfer payment')
        ->and($payment->related_type)->toBe(Purchase::class)
        ->and($payment->related_id)->toBe($purchase->id)
        ->and($payment->created_by)->toBe($user->id);
});
