<?php

declare(strict_types=1);

use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

test('to array', function (): void {
    $paymentMethod = PaymentMethod::factory()->create()->refresh();

    expect(array_keys($paymentMethod->toArray()))
        ->toBe([
            'id',
            'name',
            'code',
            'is_active',
            'created_at',
            'updated_at',
        ]);
});

test('only returns active payment methods by default', function (): void {
    PaymentMethod::factory()->count(2)->create([
        'is_active' => true,
    ]);
    PaymentMethod::factory()->count(2)->create([
        'is_active' => false,
    ]);

    $paymentMethods = PaymentMethod::all();

    expect($paymentMethods)
        ->toHaveCount(2);
});

it('has many payments', function (): void {
    $paymentMethod = new PaymentMethod();

    expect($paymentMethod->payments())
        ->toBeInstanceOf(HasMany::class);
});

it('can create payments', function (): void {
    $paymentMethod = PaymentMethod::factory()->create();
    Payment::factory()->count(3)->create(['payment_method_id' => $paymentMethod->id]);

    expect($paymentMethod->payments)
        ->toHaveCount(3)
        ->each->toBeInstanceOf(Payment::class);
});

it('returns empty collection when no payments exist', function (): void {
    $paymentMethod = PaymentMethod::factory()->create();

    expect($paymentMethod->payments)
        ->toBeEmpty()
        ->toBeInstanceOf(Collection::class);
});
