<?php

declare(strict_types=1);

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

test('to array', function (): void {
    $payment = Payment::factory()->create()->refresh();

    expect(array_keys($payment->toArray()))
        ->toBe([
            'id',
            'payment_method_id',
            'user_id',
            'reference_no',
            'payable_type',
            'payable_id',
            'amount',
            'payment_date',
            'note',
            'created_at',
            'updated_at',
        ]);
});

it('belongs to paymentMethod', function (): void {
    $payment = new Payment();

    expect($payment->paymentMethod())
        ->toBeInstanceOf(BelongsTo::class);
});

it('can access paymentMethod', function (): void {
    $paymentMethod = PaymentMethod::factory()->create();
    $payment = Payment::factory()->create([
        'payment_method_id' => $paymentMethod->id,
    ]);

    expect($payment->paymentMethod)
        ->toBeInstanceOf(PaymentMethod::class)
        ->id->toBe($paymentMethod->id);
});

it('belongs to user', function (): void {
    $payment = new Payment();

    expect($payment->user())
        ->toBeInstanceOf(BelongsTo::class);
});

it('can access user', function (): void {
    $user = User::factory()->create();
    $payment = Payment::factory()->create([
        'user_id' => $user->id,
    ]);

    expect($payment->user)
        ->toBeInstanceOf(User::class)
        ->id->toBe($user->id);
});

it('has payable morphTo relationship', function (): void {
    $payment = new Payment();

    expect($payment->payable())
        ->toBeInstanceOf(MorphTo::class);
});

it('can access payable as Sale', function (): void {
    $sale = Sale::factory()->create();
    $payment = Payment::factory()->create([
        'payable_type' => Sale::class,
        'payable_id' => $sale->id,
    ]);

    expect($payment->payable)
        ->toBeInstanceOf(Sale::class)
        ->id->toBe($sale->id);
});

it('can access payable as Purchase', function (): void {
    $purchase = Purchase::factory()->create();
    $payment = Payment::factory()->create([
        'payable_type' => Purchase::class,
        'payable_id' => $purchase->id,
    ]);

    expect($payment->payable)
        ->toBeInstanceOf(Purchase::class)
        ->id->toBe($purchase->id);
});

it('filters by recent scope', function (): void {
    Payment::factory()->create(['payment_date' => now()->subDays(10)]);
    Payment::factory()->create(['payment_date' => now()->subDays(35)]);
    Payment::factory()->create(['payment_date' => now()->subDays(5)]);

    $results = Payment::recent()->get();

    expect($results)->toHaveCount(2);
});

it('filters by recent scope with custom days', function (): void {
    Payment::factory()->create(['payment_date' => now()->subDays(10)]);
    Payment::factory()->create(['payment_date' => now()->subDays(20)]);

    $results = Payment::recent(15)->get();

    expect($results)->toHaveCount(1);
});

it('filters by today scope', function (): void {
    Payment::factory()->create(['payment_date' => now()]);
    Payment::factory()->create(['payment_date' => now()->subDay()]);
    Payment::factory()->create(['payment_date' => now()->addDay()]);

    $results = Payment::today()->get();

    expect($results)->toHaveCount(1);
});
