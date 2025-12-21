<?php

declare(strict_types=1);

use App\Actions\Sales\ProcessSalePayment;
use App\Enums\PaymentMethodEnum;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\User;

it('may process a sale payment', function (): void {
    $user = User::factory()->create();
    $sale = Sale::factory()->create(['created_by' => $user->id]);
    $action = resolve(ProcessSalePayment::class);

    $payment = $action->handle(
        sale: $sale,
        amount: 50000,
        method: PaymentMethodEnum::CASH,
        reference: 'PAY-001',
        notes: 'Cash payment',
        userId: $user->id
    );

    expect($payment)->toBeInstanceOf(Payment::class)
        ->and($payment->reference)->toBe('PAY-001')
        ->and($payment->amount)->toBe(50000)
        ->and($payment->method)->toBe(PaymentMethodEnum::CASH)
        ->and($payment->notes)->toBe('Cash payment')
        ->and($payment->related_type)->toBe(Sale::class)
        ->and($payment->related_id)->toBe($sale->id)
        ->and($payment->created_by)->toBe($user->id);
});
