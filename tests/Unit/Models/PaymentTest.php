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
            'status',
            'voided_by',
            'voided_at',
            'void_reason',
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

it('filters by activeForPayable scope for Sale', function (): void {
    $sale = Sale::factory()->create();
    $otherSale = Sale::factory()->create();

    Payment::factory()->create([
        'payable_type' => Sale::class,
        'payable_id' => $sale->id,
    ]);
    Payment::factory()->create([
        'payable_type' => Sale::class,
        'payable_id' => $sale->id,
    ]);
    Payment::factory()->create([
        'payable_type' => Sale::class,
        'payable_id' => $otherSale->id,
    ]);

    $results = Payment::query()->activeForPayable(Sale::class, $sale->id)->get();

    expect($results)->toHaveCount(2);
});

it('filters by activeForPayable scope excludes voided payments', function (): void {
    $sale = Sale::factory()->create();

    Payment::factory()->create([
        'payable_type' => Sale::class,
        'payable_id' => $sale->id,
    ]);
    Payment::factory()->voided()->create([
        'payable_type' => Sale::class,
        'payable_id' => $sale->id,
    ]);
    Payment::factory()->create([
        'payable_type' => Sale::class,
        'payable_id' => $sale->id,
    ]);

    $results = Payment::query()->activeForPayable(Sale::class, $sale->id)->get();

    expect($results)->toHaveCount(2);
});

it('filters by activeForPayable scope for Purchase', function (): void {
    $purchase = Purchase::factory()->create();
    $otherPurchase = Purchase::factory()->create();

    Payment::factory()->create([
        'payable_type' => Purchase::class,
        'payable_id' => $purchase->id,
    ]);
    Payment::factory()->create([
        'payable_type' => Purchase::class,
        'payable_id' => $purchase->id,
    ]);
    Payment::factory()->create([
        'payable_type' => Purchase::class,
        'payable_id' => $otherPurchase->id,
    ]);

    $results = Payment::query()->activeForPayable(Purchase::class, $purchase->id)->get();

    expect($results)->toHaveCount(2);
});

it('filters by activeForPayable scope returns empty when no matches', function (): void {
    $sale = Sale::factory()->create();

    Payment::factory()->create([
        'payable_type' => Purchase::class,
        'payable_id' => 999,
    ]);

    $results = Payment::query()->activeForPayable(Sale::class, $sale->id)->get();

    expect($results)->toHaveCount(0);
});

it('filters by activeForPayable scope with mixed payable types', function (): void {
    $sale = Sale::factory()->create();
    $purchase = Purchase::factory()->create();

    Payment::factory()->create([
        'payable_type' => Sale::class,
        'payable_id' => $sale->id,
    ]);
    Payment::factory()->create([
        'payable_type' => Purchase::class,
        'payable_id' => $purchase->id,
    ]);

    $saleResults = Payment::query()->activeForPayable(Sale::class, $sale->id)->get();
    $purchaseResults = Payment::query()->activeForPayable(Purchase::class, $purchase->id)->get();

    expect($saleResults)->toHaveCount(1)
        ->and($purchaseResults)->toHaveCount(1);
});
