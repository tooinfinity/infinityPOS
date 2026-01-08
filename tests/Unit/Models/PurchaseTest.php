<?php

declare(strict_types=1);

use App\Collections\PurchaseCollection;
use App\Enums\PaymentMethodEnum;
use App\Enums\PurchaseStatusEnum;
use App\Models\Purchase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

test('to array', function (): void {
    $purchase = Purchase::factory()->create()->refresh();

    expect(array_keys($purchase->toArray()))
        ->toBe([
            'id',
            'store_id',
            'supplier_id',
            'reference_number',
            'invoice_number',
            'purchase_date',
            'total_cost',
            'paid_amount',
            'payment_status',
            'payment_method',
            'notes',
            'created_by',
            'created_at',
            'updated_at',
        ]);
});

test('new collection returns purchase collection', function (): void {
    $purchase = new Purchase();

    expect($purchase->newCollection([]))
        ->toBeInstanceOf(PurchaseCollection::class);
});

test('store relationship returns belongs to', function (): void {
    $purchase = new Purchase();

    expect($purchase->store())
        ->toBeInstanceOf(BelongsTo::class);
});

test('supplier relationship returns belongs to', function (): void {
    $purchase = new Purchase();

    expect($purchase->supplier())
        ->toBeInstanceOf(BelongsTo::class);
});

test('creator relationship returns belongs to', function (): void {
    $purchase = new Purchase();

    expect($purchase->creator())
        ->toBeInstanceOf(BelongsTo::class);
});

test('items relationship returns has many', function (): void {
    $purchase = new Purchase();

    expect($purchase->items())
        ->toBeInstanceOf(HasMany::class);
});

test('get total cost in dollars returns correct amount', function (): void {
    $purchase = Purchase::factory()->make(['total_cost' => 12345]);

    expect($purchase->getTotalCostInDollars())->toBe(123.45);
});

test('get total cost in dollars returns zero when cost is zero', function (): void {
    $purchase = Purchase::factory()->make(['total_cost' => 0]);

    expect($purchase->getTotalCostInDollars())->toBe(0.0);
});

test('get outstanding balance returns correct amount', function (): void {
    $purchase = Purchase::factory()->make([
        'total_cost' => 10000,
        'paid_amount' => 7500,
    ]);

    expect($purchase->getOutstandingBalance())->toBe(2500);
});

test('get outstanding balance returns zero when fully paid', function (): void {
    $purchase = Purchase::factory()->make([
        'total_cost' => 10000,
        'paid_amount' => 10000,
    ]);

    expect($purchase->getOutstandingBalance())->toBe(0);
});

test('casts returns correct array', function (): void {
    $purchase = new Purchase();

    expect($purchase->casts())
        ->toBe([
            'id' => 'integer',
            'store_id' => 'integer',
            'supplier_id' => 'integer',
            'reference_number' => 'string',
            'invoice_number' => 'string',
            'purchase_date' => 'date',
            'total_cost' => 'integer',
            'paid_amount' => 'integer',
            'payment_status' => PurchaseStatusEnum::class,
            'payment_method' => PaymentMethodEnum::class,
            'notes' => 'string',
            'created_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ]);
});

test('casts work correctly', function (): void {
    $purchase = Purchase::factory()->create()->refresh();

    expect($purchase->id)->toBeInt()
        ->and($purchase->total_cost)->toBeInt()
        ->and($purchase->purchase_date)->toBeInstanceOf(DateTimeInterface::class)
        ->and($purchase->created_at)->toBeInstanceOf(DateTimeInterface::class);
});

it('casts payment_status to PurchaseStatusEnum', function (): void {
    $purchase = Purchase::factory()->create([
        'payment_status' => PurchaseStatusEnum::COMPLETED,
    ]);

    expect($purchase->payment_status)->toBeInstanceOf(PurchaseStatusEnum::class)
        ->and($purchase->payment_status)->toBe(PurchaseStatusEnum::COMPLETED);
});

it('can set payment_status using enum value', function (): void {
    $purchase = Purchase::factory()->create([
        'payment_status' => 'pending',
    ]);

    expect($purchase->payment_status)->toBeInstanceOf(PurchaseStatusEnum::class)
        ->and($purchase->payment_status->value)->toBe('pending');
});

it('can access enum methods on payment_status', function (): void {
    $purchase = Purchase::factory()->create([
        'payment_status' => PurchaseStatusEnum::COMPLETED,
    ]);

    expect($purchase->payment_status->label())->toBe('Completed')
        ->and($purchase->payment_status->color())->toBeString()
        ->and($purchase->payment_status->icon())->toBeString()
        ->and($purchase->payment_status->isCompleted())->toBeTrue()
        ->and($purchase->payment_status->canBeModified())->toBeFalse();
});

it('casts payment_method to PaymentMethodEnum', function (): void {
    $purchase = Purchase::factory()->create([
        'payment_method' => PaymentMethodEnum::CASH,
    ]);

    expect($purchase->payment_method)->toBeInstanceOf(PaymentMethodEnum::class)
        ->and($purchase->payment_method)->toBe(PaymentMethodEnum::CASH);
});

it('can set payment_method using enum value', function (): void {
    $purchase = Purchase::factory()->create([
        'payment_method' => 'card',
    ]);

    expect($purchase->payment_method)->toBeInstanceOf(PaymentMethodEnum::class)
        ->and($purchase->payment_method->value)->toBe('card');
});

it('can access enum methods on payment_method', function (): void {
    $purchase = Purchase::factory()->create([
        'payment_method' => PaymentMethodEnum::CASH,
    ]);

    expect($purchase->payment_method->label())->toBe('Cash')
        ->and($purchase->payment_method->color())->toBeString()
        ->and($purchase->payment_method->icon())->toBeString()
        ->and($purchase->payment_method->isCash())->toBeTrue();
});
