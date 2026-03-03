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

it('filters by active scope', function (): void {
    Payment::factory()->create();
    Payment::factory()->voided()->create();
    Payment::factory()->create();

    $results = Payment::query()->active()->get();

    expect($results)->toHaveCount(2);
});

it('filters by voided scope', function (): void {
    Payment::factory()->create();
    Payment::factory()->voided()->create();
    Payment::factory()->voided()->create();

    $results = Payment::query()->voided()->get();

    expect($results)->toHaveCount(2);
});

it('isActive returns true for active payment', function (): void {
    $payment = Payment::factory()->create();

    expect($payment->isActive())->toBeTrue();
});

it('isActive returns false for voided payment', function (): void {
    $payment = Payment::factory()->voided()->create();

    expect($payment->isActive())->toBeFalse();
});

it('isVoided returns true for voided payment', function (): void {
    $payment = Payment::factory()->voided()->create();

    expect($payment->isVoided())->toBeTrue();
});

it('isVoided returns false for active payment', function (): void {
    $payment = Payment::factory()->create();

    expect($payment->isVoided())->toBeFalse();
});

it('canBeVoided returns true for active payment', function (): void {
    $payment = Payment::factory()->create();

    expect($payment->canBeVoided())->toBeTrue();
});

it('canBeVoided returns false for voided payment', function (): void {
    $payment = Payment::factory()->voided()->create();

    expect($payment->canBeVoided())->toBeFalse();
});

it('canBeUnvoided returns true for voided payment', function (): void {
    $payment = Payment::factory()->voided()->create();

    expect($payment->canBeUnvoided())->toBeTrue();
});

it('canBeUnvoided returns false for active payment', function (): void {
    $payment = Payment::factory()->create();

    expect($payment->canBeUnvoided())->toBeFalse();
});

it('has voidedBy relationship', function (): void {
    $user = User::factory()->create();
    $payment = Payment::factory()->voided()->create([
        'voided_by' => $user->id,
    ]);

    expect($payment->voidedBy)
        ->toBeInstanceOf(User::class)
        ->id->toBe($user->id);
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
