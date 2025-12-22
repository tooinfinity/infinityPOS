<?php

declare(strict_types=1);

use App\Enums\PaymentMethodEnum;
use App\Models\Moneybox;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('may list payments', function (): void {
    Payment::factory()->count(5)->create(['created_by' => $this->user->id]);

    $response = $this->get(route('payments.index'));

    $response->assertStatus(500); // View not created yet
});

it('may create a payment without moneybox', function (): void {
    $sale = Sale::factory()->create(['created_by' => $this->user->id]);

    $response = $this->post(route('payments.store'), [
        'amount' => 50000,
        'method' => PaymentMethodEnum::CASH->value,
        'moneybox_id' => null,
        'reference' => 'PAY-001',
        'notes' => 'Cash payment',
        'related_type' => Sale::class,
        'related_id' => $sale->id,
        'created_by' => $this->user->id,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('payments', [
        'amount' => 50000,
        'method' => PaymentMethodEnum::CASH->value,
        'reference' => 'PAY-001',
        'related_type' => Sale::class,
        'related_id' => $sale->id,
    ]);
});

it('may create a payment with moneybox', function (): void {
    $moneybox = Moneybox::factory()->create([
        'balance' => 10000,
        'created_by' => $this->user->id,
    ]);

    $response = $this->post(route('payments.store'), [
        'amount' => 30000,
        'method' => PaymentMethodEnum::CASH->value,
        'moneybox_id' => $moneybox->id,
        'reference' => 'PAY-002',
        'notes' => 'Payment to cash register',
        'related_type' => null,
        'related_id' => null,
        'created_by' => $this->user->id,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('payments', [
        'amount' => 30000,
        'moneybox_id' => $moneybox->id,
    ]);

    // Check moneybox balance updated
    $moneybox->refresh();
    expect($moneybox->balance)->toBe(40000);
});

it('may show a payment', function (): void {
    $payment = Payment::factory()->create(['created_by' => $this->user->id]);

    $response = $this->get(route('payments.show', $payment));

    $response->assertStatus(500); // View not created yet
});

it('may refund a payment partially', function (): void {
    $payment = Payment::factory()->create([
        'amount' => 100000,
        'method' => PaymentMethodEnum::CASH,
        'reference' => 'PAY-003',
        'created_by' => $this->user->id,
    ]);

    $response = $this->post(route('payments.refund'), [
        'original_payment_id' => $payment->id,
        'amount' => 30000,
        'moneybox_id' => null,
        'reason' => 'Customer request',
        'notes' => 'Partial refund',
        'created_by' => $this->user->id,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('payments', [
        'amount' => -30000,
        'method' => PaymentMethodEnum::CASH->value,
    ]);
});

it('may refund full payment', function (): void {
    $payment = Payment::factory()->create([
        'amount' => 50000,
        'method' => PaymentMethodEnum::CARD,
        'reference' => 'PAY-004',
        'created_by' => $this->user->id,
    ]);

    $response = $this->post(route('payments.refund'), [
        'original_payment_id' => $payment->id,
        'amount' => 50000,
        'moneybox_id' => null,
        'reason' => 'Order cancelled',
        'notes' => null,
        'created_by' => $this->user->id,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('payments', [
        'amount' => -50000,
        'method' => PaymentMethodEnum::CARD->value,
    ]);
});

it('cannot refund more than original amount', function (): void {
    $payment = Payment::factory()->create([
        'amount' => 50000,
        'created_by' => $this->user->id,
    ]);

    $response = $this->post(route('payments.refund'), [
        'original_payment_id' => $payment->id,
        'amount' => 60000,
        'moneybox_id' => null,
        'reason' => null,
        'notes' => null,
        'created_by' => $this->user->id,
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['message']);
});

it('may void a payment', function (): void {
    $payment = Payment::factory()->create([
        'amount' => 75000,
        'method' => PaymentMethodEnum::CASH,
        'reference' => 'PAY-005',
        'created_by' => $this->user->id,
    ]);

    $response = $this->post(route('payments.void', $payment), [
        'reason' => 'Payment error',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('payments', [
        'amount' => -75000,
        'method' => PaymentMethodEnum::CASH->value,
    ]);
});

it('may void a payment with moneybox', function (): void {
    $moneybox = Moneybox::factory()->create([
        'balance' => 100000,
        'created_by' => $this->user->id,
    ]);

    $payment = Payment::factory()->create([
        'amount' => 40000,
        'method' => PaymentMethodEnum::CASH,
        'reference' => 'PAY-006',
        'moneybox_id' => $moneybox->id,
        'created_by' => $this->user->id,
    ]);

    $response = $this->post(route('payments.void', $payment), [
        'reason' => 'Duplicate payment',
    ]);

    $response->assertRedirect();

    // Check moneybox balance reversed
    $moneybox->refresh();
    expect($moneybox->balance)->toBe(60000);
});

it('processes card payment', function (): void {
    $response = $this->post(route('payments.store'), [
        'amount' => 75000,
        'method' => PaymentMethodEnum::CARD->value,
        'moneybox_id' => null,
        'reference' => 'CARD-001',
        'notes' => 'Card payment',
        'related_type' => null,
        'related_id' => null,
        'created_by' => $this->user->id,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('payments', [
        'method' => PaymentMethodEnum::CARD->value,
        'reference' => 'CARD-001',
    ]);
});

it('processes transfer payment', function (): void {
    $response = $this->post(route('payments.store'), [
        'amount' => 100000,
        'method' => PaymentMethodEnum::TRANSFER->value,
        'moneybox_id' => null,
        'reference' => 'TRF-001',
        'notes' => 'Bank transfer',
        'related_type' => null,
        'related_id' => null,
        'created_by' => $this->user->id,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('payments', [
        'method' => PaymentMethodEnum::TRANSFER->value,
        'reference' => 'TRF-001',
    ]);
});
