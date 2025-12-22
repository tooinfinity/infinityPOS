<?php

declare(strict_types=1);

use App\Enums\PaymentMethodEnum;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('may add payment to purchase', function (): void {
    $purchase = Purchase::factory()->create(['created_by' => $this->user->id]);

    $response = $this->post(route('purchases.payments.store', $purchase), [
        'amount' => 50000,
        'method' => PaymentMethodEnum::CASH->value,
        'reference' => 'PAY-001',
        'notes' => 'Cash payment',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('payments', [
        'related_type' => Purchase::class,
        'related_id' => $purchase->id,
        'amount' => 50000,
        'method' => PaymentMethodEnum::CASH->value,
        'reference' => 'PAY-001',
        'created_by' => $this->user->id,
    ]);
});

it('records multiple payments for purchase', function (): void {
    $purchase = Purchase::factory()->create(['created_by' => $this->user->id]);

    $this->post(route('purchases.payments.store', $purchase), [
        'amount' => 30000,
        'method' => PaymentMethodEnum::CASH->value,
        'reference' => null,
        'notes' => null,
    ]);

    $this->post(route('purchases.payments.store', $purchase), [
        'amount' => 20000,
        'method' => PaymentMethodEnum::TRANSFER->value,
        'reference' => 'TRF-001',
        'notes' => null,
    ]);

    expect(Payment::query()->where('related_id', $purchase->id)
        ->where('related_type', Purchase::class)
        ->count())->toBe(2);
});
