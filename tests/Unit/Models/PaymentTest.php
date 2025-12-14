<?php

declare(strict_types=1);

use App\Enums\PaymentMethodEnum;
use App\Models\Moneybox;
use App\Models\Payment;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create();

    $payment = Payment::factory()->create(['created_by' => $user->id])->refresh();

    expect(array_keys($payment->toArray()))
        ->toBe([
            'id',
            'reference',
            'amount',
            'method',
            'notes',
            'related_type',
            'related_id',
            'moneybox_id',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ]);
});

test('payment relationships', function (): void {
    $user = User::factory()->create()->refresh();
    $moneybox = Moneybox::factory()->create(['created_by' => $user->id]);
    $payment = Payment::factory()->create(['moneybox_id' => $moneybox->id, 'created_by' => $user->id]);

    $payment->update(['updated_by' => $user->id]);

    expect($payment->creator->id)->toBe($user->id)
        ->and($payment->updater->id)->toBe($user->id)
        ->and($payment->moneybox->id)->toBe($moneybox->id);
});

test('payment type', function (): void {
    $user = User::factory()->create()->refresh();
    $moneybox = Moneybox::factory()->create(['created_by' => $user->id]);
    $payment = Payment::factory()->create(['method' => PaymentMethodEnum::CASH, 'moneybox_id' => $moneybox->id, 'created_by' => $user->id]);

    expect($payment->isCash())->toBeTrue()
        ->and($payment->isCard())->toBeFalse()
        ->and($payment->isTransfer())->toBeFalse();
});
