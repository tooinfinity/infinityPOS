<?php

declare(strict_types=1);

use App\Enums\PaymentMethodEnum;
use App\Models\Sale;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('may process a sale payment', function (): void {
    $sale = Sale::factory()->create(['created_by' => $this->user->id]);

    $response = $this->post(route('sales.payments.store', $sale), [
        'amount' => 50000,
        'method' => PaymentMethodEnum::CASH->value,
        'reference' => 'PAY-001',
        'notes' => 'Cash payment',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('payments', [
        'related_type' => Sale::class,
        'related_id' => $sale->id,
        'amount' => 50000,
        'method' => PaymentMethodEnum::CASH->value,
        'reference' => 'PAY-001',
    ]);
});

it('validates required payment fields', function (): void {
    $sale = Sale::factory()->create(['created_by' => $this->user->id]);

    $response = $this->post(route('sales.payments.store', $sale), [
        'amount' => null,
        'method' => null,
    ]);

    $response->assertSessionHasErrors(['amount', 'method']);
});

it('validates amount must be positive', function (): void {
    $sale = Sale::factory()->create(['created_by' => $this->user->id]);

    $response = $this->post(route('sales.payments.store', $sale), [
        'amount' => 0,
        'method' => PaymentMethodEnum::CASH->value,
    ]);

    $response->assertSessionHasErrors(['amount']);
});

it('validates method must be valid enum', function (): void {
    $sale = Sale::factory()->create(['created_by' => $this->user->id]);

    $response = $this->post(route('sales.payments.store', $sale), [
        'amount' => 50000,
        'method' => 'invalid_method',
    ]);

    $response->assertSessionHasErrors();
});

it('may process payment with cash method', function (): void {
    $sale = Sale::factory()->create(['created_by' => $this->user->id]);

    $response = $this->post(route('sales.payments.store', $sale), [
        'amount' => 50000,
        'method' => PaymentMethodEnum::CASH->value,
        'reference' => 'PAY-CASH',
        'notes' => 'Payment via cash',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('payments', [
        'related_type' => Sale::class,
        'related_id' => $sale->id,
        'amount' => 50000,
        'method' => PaymentMethodEnum::CASH->value,
    ]);
});

it('may process payment with card method', function (): void {
    $sale = Sale::factory()->create(['created_by' => $this->user->id]);

    $response = $this->post(route('sales.payments.store', $sale), [
        'amount' => 50000,
        'method' => PaymentMethodEnum::CARD->value,
        'reference' => 'PAY-CARD',
        'notes' => 'Payment via card',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('payments', [
        'related_type' => Sale::class,
        'related_id' => $sale->id,
        'amount' => 50000,
        'method' => PaymentMethodEnum::CARD->value,
    ]);
});

it('may process payment with transfer method', function (): void {
    $sale = Sale::factory()->create(['created_by' => $this->user->id]);

    $response = $this->post(route('sales.payments.store', $sale), [
        'amount' => 50000,
        'method' => PaymentMethodEnum::TRANSFER->value,
        'reference' => 'PAY-TRANSFER',
        'notes' => 'Payment via transfer',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('payments', [
        'related_type' => Sale::class,
        'related_id' => $sale->id,
        'amount' => 50000,
        'method' => PaymentMethodEnum::TRANSFER->value,
    ]);
});

it('may process payment without optional fields', function (): void {
    $sale = Sale::factory()->create(['created_by' => $this->user->id]);

    $response = $this->post(route('sales.payments.store', $sale), [
        'amount' => 50000,
        'method' => PaymentMethodEnum::CASH->value,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('payments', [
        'related_type' => Sale::class,
        'related_id' => $sale->id,
        'amount' => 50000,
    ]);
});

it('requires authentication', function (): void {
    auth()->logout();

    $sale = Sale::factory()->create();

    $response = $this->post(route('sales.payments.store', $sale), [
        'amount' => 50000,
        'method' => PaymentMethodEnum::CASH->value,
    ]);

    $response->assertStatus(302);
    $response->assertRedirect(route('login'));
});
