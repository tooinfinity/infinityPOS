<?php

declare(strict_types=1);

use App\Data\PaymentData;
use App\Models\Moneybox;
use App\Models\Payment;
use App\Models\User;

it('transforms a payment model into PaymentData', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();
    $moneyBox = Moneybox::factory()->create();

    $payment = Payment::factory()
        ->for($creator, 'creator')
        ->for($updater, 'updater')
        ->for($moneyBox, 'moneybox')
        ->create();

    $data = PaymentData::from(
        $payment->load(['creator', 'updater', 'moneybox'])
    );

    expect($data)
        ->toBeInstanceOf(PaymentData::class)
        ->id->toBe($payment->id)
        ->amount->toBe($payment->amount)
        ->and($data->creator->id)->toBe($creator->id)
        ->and($data->updater->id)->toBe($updater->id)
        ->and($data->moneybox->id)->toBe($moneyBox->id)
        ->and($data->created_at)->toBe($payment->created_at->toDateTimeString())
        ->and($data->updated_at)->toBe($payment->updated_at->toDateTimeString());

});
